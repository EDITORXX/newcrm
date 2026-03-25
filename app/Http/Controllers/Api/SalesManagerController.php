<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use App\Models\SalesManagerProfile;
use App\Models\Prospect;
use App\Models\Target;
use App\Models\Lead;
use App\Models\LeadFavorite;
use App\Models\LeadFormField;
use App\Models\LeadAssignment;
use App\Models\Task;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SalesManagerController extends Controller
{
    private function resolveLeadRemarkForUi(Lead $lead): string
    {
        $formValues = $lead->relationLoaded('formFieldValues')
            ? $lead->formFieldValues->pluck('field_value', 'field_key')->toArray()
            : [];

        $candidates = [
            $lead->manager_remark ?? null,
            $lead->remark ?? null,
            $lead->notes ?? null,
            $lead->requirements ?? null,
            $formValues['manager_remark'] ?? null,
            $formValues['remark'] ?? null,
        ];

        foreach ($candidates as $value) {
            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return 'No remark added';
    }

    private function getFavoriteLeadPayload(User $user, int $limit = 5): array
    {
        $favorites = LeadFavorite::query()
            ->where('user_id', $user->id)
            ->with([
                'lead' => function ($query) {
                    $query->select([
                        'id',
                        'name',
                        'phone',
                        'status',
                        'created_at',
                        'updated_at',
                        'notes',
                        'requirements',
                    ])->with('formFieldValues:lead_id,field_key,field_value');
                },
            ])
            ->latest()
            ->take(max(1, $limit))
            ->get();

        return $favorites
            ->filter(fn ($favorite) => $favorite->lead !== null)
            ->map(function ($favorite) {
                $lead = $favorite->lead;

                return [
                    'lead_id' => $lead->id,
                    'name' => $lead->name,
                    'phone' => $lead->phone,
                    'status' => $lead->status,
                    'remark' => $this->resolveLeadRemarkForUi($lead),
                    'is_favorite' => true,
                    'favorited_at' => optional($favorite->created_at)->toIso8601String(),
                ];
            })
            ->values()
            ->all();
    }

    private function applySubordinateProspectVerificationScope($query, User $user, $teamMemberIds = null)
    {
        $teamMemberIds = $teamMemberIds instanceof \Illuminate\Support\Collection
            ? $teamMemberIds
            : collect($teamMemberIds ?? []);

        return $query->where(function ($q) use ($user, $teamMemberIds) {
            if ($teamMemberIds->isNotEmpty()) {
                $q->whereIn('telecaller_id', $teamMemberIds);
            }

            $q->orWhereHas('telecaller', function ($telecallerQuery) use ($user) {
                $telecallerQuery->where('manager_id', $user->id);
            });
        });
    }

    private function prospectRequiresManagerVerification(?Prospect $prospect, User $user, $teamMemberIds = null): bool
    {
        if (!$prospect) {
            return false;
        }

        if (!in_array($prospect->verification_status ?? '', ['pending', 'pending_verification'], true)) {
            return false;
        }

        if (!$prospect->telecaller_id) {
            return false;
        }

        $teamMemberIds = $teamMemberIds instanceof \Illuminate\Support\Collection
            ? $teamMemberIds
            : collect($teamMemberIds ?? []);

        if ($teamMemberIds->isNotEmpty() && $teamMemberIds->contains((int) $prospect->telecaller_id)) {
            return true;
        }

        if (!$prospect->relationLoaded('telecaller')) {
            $prospect->load('telecaller');
        }

        return (int) ($prospect->telecaller->manager_id ?? 0) === (int) $user->id;
    }

    /**
     * Get profile data with team members
     */
    public function getProfile(Request $request)
    {
        // Get user from request (works with Sanctum)
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }
        
        $user->load('role', 'manager', 'salesManagerProfile');
        
        // Log for debugging
        \Log::info('Senior Manager getProfile - User info', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name,
        ]);
        
        // Get team members (telecallers and sales executives under this manager)
        // Include all users where manager_id matches, regardless of role
        $teamMembersQuery = User::where('manager_id', $user->id)
            ->with(['role', 'telecallerProfile'])
            ->orderBy('name');
        
        // Log raw query for debugging
        \Log::info('Senior Manager getProfile - Team members query', [
            'manager_id' => $user->id,
            'raw_sql' => $teamMembersQuery->toSql(),
            'bindings' => $teamMembersQuery->getBindings(),
        ]);
        
        $teamMembers = $teamMembersQuery->get()->map(function($member) {
                // Get today's stats for the team member
                $todayProspects = Prospect::where('telecaller_id', $member->id)
                    ->whereDate('created_at', Carbon::today())
                    ->count();
                
                $isAbsent = false;
                $absentReason = null;
                
                if ($member->telecallerProfile) {
                    $isAbsent = $member->telecallerProfile->is_absent ?? false;
                    $absentReason = $member->telecallerProfile->absent_reason ?? null;
                }
                
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'phone' => $member->phone,
                    'role' => $member->role->name ?? '-',
                    'profile_picture' => $member->profile_picture_url,
                    'is_active' => $member->is_active,
                    'is_absent' => $isAbsent,
                    'absent_reason' => $absentReason,
                    'joined_at' => $member->created_at ? $member->created_at->format('d M Y') : '-',
                    'today_prospects' => $todayProspects,
                ];
            });
        
        // Log team members found
        \Log::info('Senior Manager getProfile - Team members found', [
            'manager_id' => $user->id,
            'team_members_count' => $teamMembers->count(),
            'team_member_ids' => $teamMembers->pluck('id')->toArray(),
            'team_member_names' => $teamMembers->pluck('name')->toArray(),
        ]);
        
        // Get activity history (last 10 activities)
        $activityHistory = ActivityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['action', 'ip_address', 'created_at']);

        // Get pending verifications (prospects with pending_verification status)
        $teamMemberIds = $teamMembers->pluck('id');
        
        // Use same query structure as getProspects for consistency
        $pendingVerifications = $this->applySubordinateProspectVerificationScope(
            Prospect::query(),
            $user,
            $teamMemberIds
        )->whereIn('verification_status', ['pending', 'pending_verification'])->count();
        
        // Log for debugging
        \Log::info('Senior Manager getProfile - Pending verifications', [
            'manager_id' => $user->id,
            'manager_email' => $user->email,
            'team_member_ids' => $teamMemberIds->toArray(),
            'team_member_count' => $teamMemberIds->count(),
            'pending_verifications' => $pendingVerifications,
        ]);
        
        // Get assigned leads count for this sales manager
        // Include leads assigned to manager and all team members
        $teamMemberIds = $user->teamMembers()->pluck('id');
        $allUserIds = $teamMemberIds->merge([$user->id])->toArray();
        
        // Count distinct leads using same logic as Lead Section
        // Include all assignments (active + inactive) and verified prospects
        $assignedLeadsCount = \App\Models\Lead::where(function ($q) use ($user, $teamMemberIds, $allUserIds) {
            // Leads assigned to manager or any team member (all assignments, not just active)
            $q->whereHas('assignments', function ($assignmentQ) use ($allUserIds) {
                $assignmentQ->whereIn('assigned_to', $allUserIds);
            });
            
            // OR leads from verified prospects of team members
            if ($teamMemberIds->isNotEmpty()) {
                $q->orWhereHas('prospects', function ($subQ) use ($teamMemberIds) {
                    $subQ->whereIn('telecaller_id', $teamMemberIds)
                         ->whereIn('verification_status', ['verified', 'approved']);
                });
            }
        })->distinct()->count();
        
        // Log for debugging
        \Log::info('Senior Manager Lead Count Debug', [
            'user_id' => $user->id,
            'team_member_ids' => $teamMemberIds->toArray(),
            'all_user_ids' => $allUserIds,
            'total_assigned_leads' => $assignedLeadsCount,
        ]);
        
        // Get pending tasks count for this sales manager
        // Use same logic as getTasks when status filter is "pending" or "all"
        // Include both pending tasks and overdue tasks (scheduled more than 10 minutes ago)
        $tenMinutesAgo = now()->subMinutes(10);
        $tenMinutesFromNow = now()->addMinutes(10);

        $pendingTasksQuery = Task::where('assigned_to', $user->id)
            ->where('type', 'phone_call')
            ->whereIn('status', ['pending', 'in_progress'])
            ->where(function($q) use ($tenMinutesAgo, $tenMinutesFromNow) {
                // Include:
                // 1. Overdue tasks (scheduled more than 10 minutes ago)
                // 2. Normal pending tasks (not CNP, not overdue)
                // 3. CNP tasks scheduled within 10 minutes
                $q->where(function($overdueQ) use ($tenMinutesAgo) {
                    // Overdue tasks: scheduled_at < 10 minutes ago
                    $overdueQ->where('scheduled_at', '<', $tenMinutesAgo);
                })
                ->orWhere(function($normalQ) use ($tenMinutesAgo) {
                    // Normal pending tasks (not CNP, not overdue)
                    $normalQ->where('notes', 'not like', '%CNP retry task created%')
                            ->where('title', 'not like', '%CNP rescheduled%')
                            ->where('description', 'not like', '%previous call not picked%')
                            ->where('scheduled_at', '>=', $tenMinutesAgo); // Not overdue (within last 10 min or future)
                })
                ->orWhere(function($cnpQ) use ($tenMinutesFromNow, $tenMinutesAgo) {
                    // CNP tasks scheduled within 10 minutes (and not overdue)
                    $cnpQ->where(function($cnpMarkers) {
                        $cnpMarkers->where('notes', 'like', '%CNP retry task created%')
                                   ->orWhere('title', 'like', '%CNP rescheduled%')
                                   ->orWhere('description', 'like', '%previous call not picked%');
                    })
                    ->where('scheduled_at', '<=', $tenMinutesFromNow)
                    ->where('scheduled_at', '>=', $tenMinutesAgo); // Not overdue
                });
            });
        
        // Apply same deduplication logic as getTasks (one task per lead_id)
        $allPendingTasks = $pendingTasksQuery->get();
        $deduplicatedPendingTasks = $allPendingTasks->groupBy('lead_id')
            ->map(function($group) {
                // Sort by priority (lower number = higher priority) and then by scheduled_at
                return $group->sortBy(function($task) {
                    $priority = [
                        'pending' => 1,
                        'in_progress' => 2,
                        'completed' => 3,
                        'cancelled' => 4
                    ];
                    $priorityValue = $priority[$task->status] ?? 5;
                    $scheduledAt = $task->scheduled_at ? $task->scheduled_at->timestamp : PHP_INT_MAX;
                    return [$priorityValue, $scheduledAt];
                })->first();
            });
        
        $pendingTasksCount = $deduplicatedPendingTasks->count();
        
        // Get overdue tasks count (scheduled more than 10 minutes ago)
        $overdueTasksCount = Task::where('assigned_to', $user->id)
            ->where('type', 'phone_call')
            ->whereIn('status', ['pending', 'in_progress'])
            ->where('scheduled_at', '<', $tenMinutesAgo)
            ->count();

        // Log for debugging
        \Log::info('Senior Manager pending tasks count', [
            'user_id' => $user->id,
            'pending_tasks_count' => $pendingTasksCount,
            'overdue_tasks_count' => $overdueTasksCount,
            'tasks_before_dedup' => $allPendingTasks->count(),
            'tasks_after_dedup' => $pendingTasksCount,
            'ten_minutes_ago' => $tenMinutesAgo->format('Y-m-d H:i:s'),
        ]);
        
        // Get team stats
        $teamStats = [
            'total_members' => $teamMembers->count(),
            'active_members' => $teamMembers->where('is_active', true)->count(),
            'available_members' => $teamMembers->filter(function($member) {
                return !($member['is_absent'] ?? false);
            })->count(),
            'today_prospects' => $teamMembers->sum('today_prospects'),
            'pending_verifications' => $pendingVerifications,
            'assigned_leads' => $assignedLeadsCount,
            'pending_tasks' => $pendingTasksCount,
            'overdue_tasks' => $overdueTasksCount,
        ];
        $favoriteLeads = $this->getFavoriteLeadPayload($user, 5);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'profile_picture' => $user->profile_picture_url,
                'role' => $user->role->name ?? 'Senior Manager',
                'manager' => $user->manager ? $user->manager->name : null,
                'created_at' => $user->created_at ? $user->created_at->format('d M Y') : '-',
            ],
            'team_members' => $teamMembers,
            'team_stats' => $teamStats,
            'favorite_leads' => $favoriteLeads,
            'favorite_leads_count' => count($favoriteLeads),
            'activity_history' => $activityHistory->map(function ($log) {
                return [
                    'action' => $log->action,
                    'ip' => $log->ip_address,
                    'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                ];
            }),
        ]);
    }

    /**
     * Update profile (name, email, phone)
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        $user = $user->fresh(['role', 'manager']);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role->name ?? 'Senior Manager',
                'manager' => $user->manager ? $user->manager->name : 'Not Assigned',
                'created_at' => $user->created_at ? $user->created_at->format('d M Y') : '-',
            ],
        ]);
    }

    /**
     * Upload profile picture
     */
    public function uploadProfilePicture(Request $request)
    {
        try {
            $request->validate([
                'profile_picture' => 'required|image|mimes:jpeg,jpg,png|max:2048', // Max 2MB
            ]);

            $user = $request->user();

            // Delete old profile picture if exists
            if ($user->profile_picture) {
                $oldPath = $user->profile_picture;
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }

            // Store new profile picture
            $file = $request->file('profile_picture');
            $filename = $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profiles', $filename, 'public');

            // Update user profile picture
            $user->update([
                'profile_picture' => $path,
            ]);

            // Refresh to get updated URL
            $user->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Profile picture uploaded successfully',
                'profile_picture' => $user->profile_picture_url,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile picture: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    /**
     * Get team member details
     */
    public function getTeamMemberDetails(Request $request, $memberId)
    {
        $manager = $request->user();
        
        $member = User::where('id', $memberId)
            ->where('manager_id', $manager->id)
            ->with(['role', 'telecallerProfile'])
            ->firstOrFail();

        // Get member's performance stats
        $todayProspects = Prospect::where('telecaller_id', $member->id)
            ->whereDate('created_at', Carbon::today())
            ->count();
        
        $weekProspects = Prospect::where('telecaller_id', $member->id)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();
        
        $monthProspects = Prospect::where('telecaller_id', $member->id)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();

        return response()->json([
            'success' => true,
            'member' => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'phone' => $member->phone,
                'role' => $member->role->name,
                'profile_picture' => $member->profile_picture_url,
                'is_active' => $member->is_active,
                'is_absent' => $member->telecallerProfile->is_absent ?? false,
                'absent_reason' => $member->telecallerProfile->absent_reason ?? null,
                'absent_until' => $member->telecallerProfile->absent_until ?? null,
                'performance' => [
                    'today_prospects' => $todayProspects,
                    'week_prospects' => $weekProspects,
                    'month_prospects' => $monthProspects,
                ],
            ],
        ]);
    }

    /**
     * Get team performance overview
     */
    public function getTeamPerformance(Request $request)
    {
        $manager = $request->user();
        
        // Get all team members
        $teamMembers = User::where('manager_id', $manager->id)->pluck('id');
        
        // Get prospects created by team today
        $todayProspects = Prospect::whereIn('telecaller_id', $teamMembers)
            ->whereDate('created_at', Carbon::today())
            ->count();
        
        // Get prospects created by team this week
        $weekProspects = Prospect::whereIn('telecaller_id', $teamMembers)
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->count();
        
        // Get prospects created by team this month
        $monthProspects = Prospect::whereIn('telecaller_id', $teamMembers)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
        
        // Get top performers
        $topPerformers = Prospect::whereIn('telecaller_id', $teamMembers)
            ->whereMonth('created_at', Carbon::now()->month)
            ->selectRaw('telecaller_id, COUNT(*) as prospect_count')
            ->groupBy('telecaller_id')
            ->orderByDesc('prospect_count')
            ->limit(5)
            ->get()
            ->map(function($item) {
                $user = User::find($item->telecaller_id);
                return [
                    'name' => $user ? $user->name : 'Unknown',
                    'prospect_count' => $item->prospect_count,
                ];
            });

        return response()->json([
            'success' => true,
            'performance' => [
                'today_prospects' => $todayProspects,
                'week_prospects' => $weekProspects,
                'month_prospects' => $monthProspects,
                'top_performers' => $topPerformers,
            ],
        ]);
    }

    /**
     * Get achievements (target vs achieved) for sales manager / ASM.
     * When no target record exists (e.g. for ASM), use an in-memory target so achieved counts still show.
     */
    public function getAchievements(Request $request)
    {
        $user = $request->user();

        $target = Target::where('user_id', $user->id)
            ->whereYear('target_month', Carbon::now()->year)
            ->whereMonth('target_month', Carbon::now()->month)
            ->first();

        if (!$target) {
            $target = new Target([
                'user_id' => $user->id,
                'target_month' => Carbon::now()->startOfMonth(),
                'target_meetings' => 0,
                'target_visits' => 0,
                'target_closers' => 0,
            ]);
        }

        return response()->json([
            'success' => true,
            'meetings' => $target->getAchievementProgress('meetings'),
            'site_visits' => $target->getAchievementProgress('visits'),
            'closers' => $target->getAchievementProgress('closers'),
        ]);
    }

    /**
     * Get team prospects for Senior Manager (also accessible by Admin, CRM, Sales Head)
     */
    public function getProspects(Request $request)
    {
        // Get user from request (works with Sanctum)
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'per_page' => 15,
                'total' => 0,
                'last_page' => 1,
                'message' => 'Unauthenticated'
            ], 401);
        }
        
        // Ensure role is loaded
        if (!$user->relationLoaded('role')) {
            $user->load('role');
        }

        $restrictPendingVerificationToSubordinates =
            $user->isSalesManager() || $user->isSeniorManager() || $user->isAssistantSalesManager();
        
        // Query prospects
        $query = Prospect::with(['telecaller', 'manager', 'lead', 'createdBy']);
        
        // Role-based filtering
        if ($user->isAdmin() || $user->isCrm()) {
            // Admin and CRM can see all prospects
            // No additional filtering needed
        } elseif ($user->isSalesHead()) {
            // Sales Head can see all prospects from their entire team hierarchy
            $allTeamMemberIds = $user->getAllTeamMemberIds();
            if (!empty($allTeamMemberIds)) {
                $query->where(function($q) use ($allTeamMemberIds, $user) {
                    $q->whereIn('telecaller_id', $allTeamMemberIds)
                      ->orWhere('manager_id', $user->id)
                      ->orWhereIn('manager_id', $allTeamMemberIds);
                });
            } else {
                // No team members, show only their own
                $query->where('manager_id', $user->id);
            }
        } elseif ($user->isSalesManager() || $user->isSeniorManager() || $user->isAssistantSalesManager()) {
            // Senior Manager / Manager / Assistant Sales Manager: prospects from their direct team members
            $teamMemberIds = $user->teamMembers()->pluck('id');
            
            // Build query to show all prospects for this manager:
            // 1. Prospects created by team members (telecallers under this manager)
            // 2. Prospects assigned to this manager (via manager_id)
            // 3. Prospects assigned to this manager (via assigned_manager)
            // 4. Prospects where telecaller's manager_id matches this manager (for old prospects)
            $query->where(function($q) use ($teamMemberIds, $user) {
                // Start with manager_id check (always include this)
                $q->where('manager_id', $user->id)
                  ->orWhere('assigned_manager', $user->id);
                
                // If there are team members, include their prospects
                if ($teamMemberIds->isNotEmpty()) {
                    $q->orWhereIn('telecaller_id', $teamMemberIds);
                }
                
                // Also check if telecaller's manager_id matches (for old prospects without manager_id set)
                $q->orWhereHas('telecaller', function($telecallerQuery) use ($user) {
                    $telecallerQuery->where('manager_id', $user->id);
                });
            });
            
            // Log for debugging - check actual data
            $prospectCountBeforeFilter = (clone $query)->count();
            
            // Also check raw counts for debugging
            $managerIdCount = Prospect::where('manager_id', $user->id)->count();
            $assignedManagerCount = Prospect::where('assigned_manager', $user->id)->count();
            $telecallerManagerCount = Prospect::whereHas('telecaller', function($q) use ($user) {
                $q->where('manager_id', $user->id);
            })->count();
            $teamMemberProspectsCount = $teamMemberIds->isNotEmpty() 
                ? Prospect::whereIn('telecaller_id', $teamMemberIds)->count() 
                : 0;
            
            \Log::info('Senior Manager prospects query', [
                'manager_id' => $user->id,
                'manager_email' => $user->email,
                'manager_name' => $user->name,
                'team_member_ids' => $teamMemberIds->toArray(),
                'team_member_count' => $teamMemberIds->count(),
                'prospects_before_status_filter' => $prospectCountBeforeFilter,
                'debug_counts' => [
                    'manager_id_count' => $managerIdCount,
                    'assigned_manager_count' => $assignedManagerCount,
                    'telecaller_manager_count' => $telecallerManagerCount,
                    'team_member_prospects_count' => $teamMemberProspectsCount,
                ],
            ]);
        } else {
            // Other roles - return empty
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'per_page' => 15,
                'total' => 0,
                'last_page' => 1
            ]);
        }
        
        // Get base query for counts (before search filter)
        $baseQuery = clone $query;
        
        // Filter by verification status
        if ($request->has('verification_status') && $request->verification_status !== 'all' && $request->verification_status !== '') {
            $status = $request->verification_status;
            // Map frontend values to database values
            if ($status === 'pending_verification') {
                if ($restrictPendingVerificationToSubordinates) {
                    $this->applySubordinateProspectVerificationScope($query, $user, $teamMemberIds ?? collect());
                }
                $query->whereIn('verification_status', ['pending', 'pending_verification']);
            } elseif ($status === 'verified') {
                $query->whereIn('verification_status', ['verified', 'approved']);
            } elseif ($status === 'rejected') {
                $query->where('verification_status', 'rejected');
            } else {
                // For any other status, use exact match
                $query->where('verification_status', $status);
            }
        } else {
            // When status is "all" or not provided, exclude rejected prospects
            $query->where('verification_status', '!=', 'rejected');
        }
        
        // Search filter
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('preferred_location', 'like', "%{$search}%");
            });
        }
        
        // Filter by assigned user (telecaller_id or manager_id)
        if ($request->has('assigned_to') && $request->assigned_to) {
            $assignedToId = $request->assigned_to;
            $query->where(function($q) use ($assignedToId) {
                $q->where('telecaller_id', $assignedToId)
                  ->orWhere('manager_id', $assignedToId)
                  ->orWhere('assigned_manager', $assignedToId);
            });
        }

        // Filter by lead temperature/status
        if ($request->has('lead_status') && $request->lead_status && $request->lead_status !== 'all') {
            $query->where('lead_status', $request->lead_status);
        }
        
        // Order by created_at descending (newest first)
        $prospects = $query->latest('created_at')->paginate($request->get('per_page', 15));
        
        // Log final results for debugging - include sample prospect IDs
        $sampleProspectIds = $prospects->items() ? array_slice(array_map(function($p) { return $p->id; }, $prospects->items()), 0, 5) : [];
        \Log::info('Senior Manager prospects query result', [
            'manager_id' => $user->id,
            'manager_email' => $user->email,
            'total_prospects' => $prospects->total(),
            'current_page' => $prospects->currentPage(),
            'per_page' => $prospects->perPage(),
            'verification_status_filter' => $request->input('verification_status', 'all'),
            'search_query' => $request->input('search', ''),
            'sample_prospect_ids' => $sampleProspectIds,
            'raw_sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
        ]);
        
        // Calculate counts for status filters (without search filter)
        // Note: Database uses 'pending' but frontend expects 'pending_verification'
        // Database uses 'approved' but frontend expects 'verified'
        // "all" count excludes rejected prospects
        $pendingVerificationCountQuery = clone $baseQuery;
        if ($restrictPendingVerificationToSubordinates) {
            $this->applySubordinateProspectVerificationScope($pendingVerificationCountQuery, $user, $teamMemberIds ?? collect());
        }

        $counts = [
            'all' => (clone $baseQuery)->where('verification_status', '!=', 'rejected')->count(),
            'pending_verification' => $pendingVerificationCountQuery->whereIn('verification_status', ['pending', 'pending_verification'])->count(),
            'verified' => (clone $baseQuery)->whereIn('verification_status', ['verified', 'approved'])->count(),
            'rejected' => (clone $baseQuery)->where('verification_status', 'rejected')->count(),
        ];
        
        return response()->json([
            ...$prospects->toArray(),
            'counts' => $counts
        ]);
    }

    public function getFavoriteLeads(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => $this->getFavoriteLeadPayload($user, 5),
        ]);
    }

    public function addFavoriteLead(Request $request, Lead $lead)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        if (!$this->canAccessLead($user, $lead)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to favorite this lead.',
            ], 403);
        }

        LeadFavorite::firstOrCreate([
            'user_id' => $user->id,
            'lead_id' => $lead->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead marked as favorite.',
            'lead_id' => $lead->id,
            'is_favorite' => true,
            'favorites' => $this->getFavoriteLeadPayload($user, 5),
        ]);
    }

    public function removeFavoriteLead(Request $request, Lead $lead)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        if (!$this->canAccessLead($user, $lead)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to update favorite for this lead.',
            ], 403);
        }

        LeadFavorite::where('user_id', $user->id)
            ->where('lead_id', $lead->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lead removed from favorites.',
            'lead_id' => $lead->id,
            'is_favorite' => false,
            'favorites' => $this->getFavoriteLeadPayload($user, 5),
        ]);
    }

    /**
     * Create prospect (Manager can create directly)
     */
    public function createProspect(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'lead_id' => 'nullable|exists:leads,id',
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'budget' => 'nullable|numeric',
            'preferred_location' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'purpose' => 'nullable|in:end_user,investment',
            'possession' => 'nullable|string|max:255',
            'remark' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['manager_id'] = $user->id;
        $data['assigned_manager'] = $user->id;
        $data['created_by'] = $user->id;
        $data['verification_status'] = 'approved';

        // If lead_id provided, link it
        if (isset($data['lead_id'])) {
            $lead = Lead::find($data['lead_id']);
            if ($lead) {
                $data['telecaller_id'] = null; // Manager created, not from telecaller
            }
        }

        $prospect = Prospect::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Prospect created successfully',
            'data' => $prospect->load(['manager', 'lead']),
        ], 201);
    }

    /**
     * Get tasks assigned to current sales manager/executive
     */
    public function getTasks(Request $request)
    {
        try {
            $user = $request->user();
            $teamMemberIds = $user ? $user->teamMembers()->pluck('id') : collect();
            
            if (!$user) {
                \Log::warning('Senior Manager getTasks - User not authenticated');
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated',
                    'data' => [],
                ], 401);
            }
            
            \Log::info('Senior Manager getTasks - Starting', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_name' => $user->name,
                'status_filter' => $request->input('status', 'all'),
                'category_filter' => $request->input('category', 'all'),
            ]);
            
            // Query Tasks assigned to this user (using Task model for manager verification tasks)
            // Show all phone_call tasks (these are all manager verification tasks)
            $query = Task::where('assigned_to', $user->id)
                ->where('type', 'phone_call')
                ->with(['lead.prospects', 'assignedTo', 'creator']);
                
            // Debug: Log total tasks before filters
            $totalTasksBeforeFilter = (clone $query)->count();
            \Log::info('Senior Manager getTasks - Total tasks before filters', [
                'user_id' => $user->id,
                'total_tasks' => $totalTasksBeforeFilter,
            ]);

            // Get date filter first to check if it's applied
            $dateFilter = $request->input('date_filter');
            $hasDateFilter = $dateFilter && $dateFilter !== 'all';

            // Filter by status
            // For ASM: "All Tasks" = only pending/in_progress (verification complete = task completed = hide from list)
            $statusFilter = $request->input('status');
            if (!$statusFilter || $statusFilter === 'all' || $statusFilter === '') {
                // Default / All: exclude completed so verified items disappear from ASM view
                $query->where('status', '!=', 'completed');
            } elseif ($statusFilter && $statusFilter !== 'all' && $statusFilter !== '') {
                // Explicitly exclude completed tasks from pending/overdue views
                if (in_array($statusFilter, ['pending', 'overdue', 'rescheduled'])) {
                    $query->where('status', '!=', 'completed');
                }
                
                if ($statusFilter === 'rescheduled') {
                    // Rescheduled: Show CNP tasks scheduled more than 10 minutes in the future
                    // Exclude overdue tasks (more than 10 minutes old)
                    $tenMinutesFromNow = now()->addMinutes(10);
                    $tenMinutesAgo = now()->subMinutes(10);
                    
                    // Identify CNP tasks by markers in notes/title/description
                    $query->where(function($q) {
                        $q->where('notes', 'like', '%CNP retry task created%')
                          ->orWhere('title', 'like', '%CNP rescheduled%')
                          ->orWhere('description', 'like', '%previous call not picked%');
                    })
                    ->where('status', 'pending') // Only pending CNP tasks
                    ->where('scheduled_at', '>', $tenMinutesFromNow) // More than 10 minutes in future
                    ->where('scheduled_at', '>', $tenMinutesAgo); // Must be within last 10 minutes or future (not overdue)
                } elseif ($statusFilter === 'pending') {
                    // Pending: Show normal pending tasks + CNP tasks scheduled within 10 minutes
                    // Exclude overdue tasks (more than 10 minutes old) and rescheduled CNP tasks
                    $query->where('status', 'pending');
                    
                    if ($hasDateFilter) {
                        // When date filter is applied, show all pending tasks for that date range
                        // Don't apply the 10-minute restriction - let date filter handle the range
                        // Date filter will be applied below
                    } else {
                        // No date filter: Use existing logic (10-minute window for CNP tasks)
                        $tenMinutesFromNow = now()->addMinutes(10);
                        $tenMinutesAgo = now()->subMinutes(10);
                        
                        $query->where('scheduled_at', '>=', $tenMinutesAgo) // Not overdue (within last 10 minutes or future)
                            ->where(function($q) use ($tenMinutesFromNow) {
                                // Normal pending tasks (not CNP)
                                $q->where(function($notCnpQ) {
                                    $notCnpQ->where('notes', 'not like', '%CNP retry task created%')
                                            ->where('title', 'not like', '%CNP rescheduled%')
                                            ->where('description', 'not like', '%previous call not picked%');
                                })
                                // OR CNP tasks scheduled within 10 minutes (auto-moved to pending)
                                ->orWhere(function($cnpQ) use ($tenMinutesFromNow) {
                                    $cnpQ->where(function($cnpMarkers) {
                                        $cnpMarkers->where('notes', 'like', '%CNP retry task created%')
                                                   ->orWhere('title', 'like', '%CNP rescheduled%')
                                                   ->orWhere('description', 'like', '%previous call not picked%');
                                    })
                                    ->where('scheduled_at', '<=', $tenMinutesFromNow);
                                });
                            });
                    }
                } elseif ($statusFilter === 'overdue') {
                    // Overdue: Tasks scheduled more than 10 minutes ago with pending/in_progress status
                    $tenMinutesAgo = now()->subMinutes(10);
                    $query->whereIn('status', ['pending', 'in_progress'])
                          ->where('scheduled_at', '<', $tenMinutesAgo);
                } else {
                    // Other status filters (completed, in_progress, etc.) - use standard filter
                    $query->where('status', $statusFilter);
                }
            }

            // Date filter - works in combination with status filter
            // For pending tasks with date filter, show all pending tasks for that date range
            if ($dateFilter && $dateFilter !== 'all') {
                if ($dateFilter === 'today') {
                    $query->whereDate('scheduled_at', Carbon::today());
                } elseif ($dateFilter === 'tomorrow') {
                    $query->whereDate('scheduled_at', Carbon::tomorrow());
                } elseif ($dateFilter === 'this_week') {
                    $query->whereBetween('scheduled_at', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ]);
                } elseif ($dateFilter === 'this_month') {
                    $query->whereBetween('scheduled_at', [
                        Carbon::now()->startOfMonth(),
                        Carbon::now()->endOfMonth()
                    ]);
                } elseif ($dateFilter === 'custom' && $request->has('custom_date')) {
                    $customDate = $request->input('custom_date');
                    if ($customDate) {
                        $query->whereDate('scheduled_at', $customDate);
                    }
                }
            }

            // Search filter
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhereHas('lead', function($leadQ) use ($search) {
                          $leadQ->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                      });
                });
            }

            $tasks = $query->latest('scheduled_at')->paginate($request->get('per_page', 15));
            
            \Log::info('Senior Manager getTasks - Tasks found after filters', [
                'user_id' => $user->id,
                'tasks_count' => $tasks->count(),
                'total' => $tasks->total(),
                'status_filter' => $statusFilter,
            ]);

            // Deduplicate tasks: keep only one task per lead_id
            // Priority: pending > in_progress > completed > cancelled
            // If same status, keep the one with earliest scheduled_at
            $deduplicatedTasks = collect($tasks->items())->groupBy('lead_id')
                ->map(function($group) {
                    // Sort by priority (lower number = higher priority) and then by scheduled_at
                    return $group->sortBy(function($task) {
                        $priority = [
                            'pending' => 1,
                            'in_progress' => 2,
                            'completed' => 3,
                            'cancelled' => 4
                        ];
                        $priorityValue = $priority[$task->status] ?? 5;
                        $scheduledAt = $task->scheduled_at ? $task->scheduled_at->timestamp : PHP_INT_MAX;
                        return [$priorityValue, $scheduledAt];
                    })->first();
                })->values()->all();

            // Log for debugging - include SQL query details
            \Log::info('Senior Manager getTasks', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'status_filter' => $request->input('status', 'all'),
                'total_tasks_before_dedup' => $tasks->total(),
                'tasks_in_page_before_dedup' => $tasks->count(),
                'tasks_after_dedup' => count($deduplicatedTasks),
                'sql_query' => $query->toSql(),
                'bindings' => $query->getBindings(),
            ]);
            
            // Debug: Check total tasks without filters
            $totalTasksWithoutFilter = Task::where('assigned_to', $user->id)->count();
            $phoneCallTasks = Task::where('assigned_to', $user->id)
                ->where('type', 'phone_call')
                ->count();
            \Log::info('Senior Manager getTasks - Debug counts', [
                'total_tasks_for_manager' => $totalTasksWithoutFilter,
                'phone_call_tasks' => $phoneCallTasks,
            ]);

            $categoryFilter = $request->input('category');
            $normalizedCategoryFilter = $categoryFilter ? strtolower(trim($categoryFilter)) : null;

            // Transform tasks to array format for JSON response (using deduplicated tasks)
            $tasksArray = [];
            foreach ($deduplicatedTasks as $task) {
                // Check if overdue
                $isOverdue = $task->isOverdue();
                $scheduledAtFormatted = $task->scheduled_at ? $task->scheduled_at->format('Y-m-d H:i:s') : null;
                
                // Get prospect information if available
                $leadStatus = null;
                $hasProspect = false;
                $prospectData = null;
                
                if ($task->lead) {
                    // Load prospects if not already loaded
                    if (!$task->lead->relationLoaded('prospects')) {
                        $task->lead->load('prospects');
                    }
                    
                    // Get latest prospect
                    $prospect = $task->lead->prospects->sortByDesc('created_at')->first();
                    
                    if ($prospect) {
                        $hasProspect = $this->prospectRequiresManagerVerification($prospect, $user, $teamMemberIds);
                        $leadStatus = $prospect->lead_status ?? null;
                        $prospectData = [
                            'id' => $prospect->id,
                            'verification_status' => $prospect->verification_status ?? null,
                            'lead_status' => $prospect->lead_status ?? null,
                            'telecaller_id' => $prospect->telecaller_id ?? null,
                            'is_pending_verification' => $hasProspect,
                        ];
                    }
                }
                
                $taskText = strtolower(trim(
                    ($task->title ?? '') . ' ' .
                    ($task->description ?? '') . ' ' .
                    ($task->notes ?? '')
                ));
                $isFollowUpTask = str_contains($taskText, 'follow-up call') ||
                                  str_contains($taskText, 'follow up call') ||
                                  str_contains($taskText, 'follow-up scheduled');
                $isCloserTask = str_contains($taskText, 'closer');
                $isSiteVisitTask = str_contains($taskText, 'site visit') || str_contains($taskText, 'site-visit');
                $isMeetingTask = str_contains($taskText, 'meeting id') ||
                                 str_contains($taskText, 'pre-meeting') ||
                                 (str_contains($taskText, 'meeting') && !$isSiteVisitTask);
                $isProspectTask = $hasProspect ||
                                  str_contains($taskText, 'prospect verification') ||
                                  str_contains($taskText, 'prospect');
                $taskCategory = 'other';
                if ($isFollowUpTask) {
                    $taskCategory = 'follow_up';
                } elseif ($isCloserTask) {
                    $taskCategory = 'closer';
                } elseif ($isSiteVisitTask) {
                    $taskCategory = 'site_visit';
                } elseif ($isMeetingTask) {
                    $taskCategory = 'meeting';
                } elseif ($isProspectTask) {
                    $taskCategory = 'prospect';
                }

                $taskData = [
                    'id' => $task->id,
                    'lead_id' => $task->lead_id,
                    'assigned_to' => $task->assigned_to,
                    'type' => $task->type,
                    'category' => $taskCategory,
                    'title' => $task->title,
                    'description' => $task->description,
                    'notes' => $task->notes,
                    'status' => $task->status,
                    'priority' => $task->priority,
                    'scheduled_at' => $task->scheduled_at ? $task->scheduled_at->toDateTimeString() : null,
                    'scheduled_at_formatted' => $scheduledAtFormatted,
                    'due_date' => $task->due_date ? $task->due_date->toDateTimeString() : null,
                    'completed_at' => $task->completed_at ? $task->completed_at->toDateTimeString() : null,
                    'is_overdue' => $isOverdue,
                    'has_prospect' => $hasProspect, // Flag to indicate if lead has associated prospect
                    'prospect' => $prospectData, // Prospect data if exists
                    'lead' => $task->lead ? [
                        'id' => $task->lead->id,
                        'name' => $task->lead->name ?? 'N/A',
                        'phone' => $task->lead->phone ?? 'N/A',
                        'email' => $task->lead->email ?? null,
                        'source' => $task->lead->source ?? null, // Include source to check if from telecaller
                        'lead_status' => $leadStatus,
                        'lead_phone' => $task->lead->phone ?? 'N/A', // For backward compatibility
                    ] : null,
                    'assignedTo' => $task->assignedTo ? [
                        'id' => $task->assignedTo->id,
                        'name' => $task->assignedTo->name ?? 'N/A',
                    ] : null,
                ];
                
                $tasksArray[] = $taskData;
            }

            $tasksArray = array_values(array_filter($tasksArray, function ($task) {
                if (($task['category'] ?? null) !== 'prospect') {
                    return true;
                }

                return ($task['has_prospect'] ?? false) === true;
            }));

            if ($normalizedCategoryFilter && $normalizedCategoryFilter !== 'all') {
                $tasksArray = array_values(array_filter($tasksArray, function ($task) use ($normalizedCategoryFilter) {
                    return isset($task['category']) && $task['category'] === $normalizedCategoryFilter;
                }));
            }

            // Log response structure for debugging
            \Log::info('Senior Manager getTasks - Response', [
                'user_id' => $user->id,
                'tasks_count' => count($tasksArray),
                'response_structure' => [
                    'success' => true,
                    'data_type' => 'array',
                    'data_length' => count($tasksArray),
                    'total' => $tasks->total(),
                ],
                'sample_task' => $tasksArray[0] ?? null,
            ]);

            // Return paginated response with properly formatted data
            // Note: total count is based on deduplicated tasks
            return response()->json([
                'success' => true,
                'data' => $tasksArray, // Plain PHP array, properly serialized (deduplicated)
                'current_page' => $tasks->currentPage(),
                'per_page' => $tasks->perPage(),
                'total' => count($tasksArray), // Use deduplicated count
                'last_page' => $tasks->lastPage(),
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Senior Manager getTasks - Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load tasks: ' . $e->getMessage(),
                'error' => $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * Get single task details with lead and prospect data
     */
    public function getTask(Request $request, Task $task)
    {
        $user = $request->user();
        
        // Verify task is assigned to current user (admin/crm can view all)
        if ((int)$task->assigned_to !== (int)$user->id && !$user->isAdmin() && !$user->isCrm()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to task',
            ], 403);
        }

        $task->load(['lead.prospects', 'lead.formFieldValues', 'assignedTo', 'creator']);
        $task->is_overdue = $task->isOverdue();
        $task->scheduled_at_formatted = $task->scheduled_at ? $task->scheduled_at->format('Y-m-d H:i:s') : null;

        // Get prospect lead_status if available
        if ($task->lead) {
            $prospect = $task->lead->prospects()->latest()->first();
            if ($prospect) {
                $task->lead->lead_status = $prospect->lead_status ?? null;
                $task->prospect = $prospect;
            }
            
            // Add form fields to lead
            $task->lead->form_fields = $task->lead->getFormFieldsArray();
        }

        return response()->json([
            'success' => true,
            'data' => $task,
        ]);
    }

    /**
     * Update lead details and prospect from task form with verify/reject actions
     */
    public function updateLeadFromTask(Request $request, Task $task)
    {
        $user = $request->user();
        
        // Verify task is assigned to current user (admin/crm can view all)
        if ((int)$task->assigned_to !== (int)$user->id && !$user->isAdmin() && !$user->isCrm()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to task',
            ], 403);
        }

        $request->validate([
            'action' => 'required|in:verify,reject',
            'customer_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:10',
            'budget' => 'nullable|numeric|min:0',
            'preferred_location' => 'nullable|string|max:255',
            'size' => 'nullable|string|max:255',
            'purpose' => 'nullable|in:end_user,investment',
            'possession' => 'nullable|string|max:255',
            'lead_status' => 'required|in:hot,warm,cold,junk',
            'manager_remark' => 'nullable|string',
            'interested_projects' => 'required_if:action,verify|array|min:1',
            'interested_projects.*' => function ($attribute, $value, $fail) {
                // Allow both integer IDs and objects with name and is_custom
                if (is_int($value) || (is_array($value) && isset($value['name']) && isset($value['is_custom']))) {
                    return;
                }
                $fail('The ' . $attribute . ' must be either a project ID or a custom project object.');
            },
        ]);

        DB::beginTransaction();
        try {
            $action = $request->input('action');
            $lead = $task->lead;
            $prospect = null;

            // Get or create prospect
            if ($task->lead_id && $lead) {
                $prospect = $lead->prospects()->latest()->first();
                
                // Update lead
                $lead->update([
                    'name' => $request->input('customer_name'),
                    'phone' => $request->input('phone'),
                    'email' => $request->input('email'),
                    'address' => $request->input('address'),
                    'city' => $request->input('city'),
                    'state' => $request->input('state'),
                    'pincode' => $request->input('pincode'),
                    'preferred_location' => $request->input('preferred_location'),
                    'preferred_size' => $request->input('size'),
                    'budget' => $request->input('budget'),
                    'investment' => $request->input('budget'),
                ]);
            }

            // Update or create prospect
            $prospectData = [
                'customer_name' => $request->input('customer_name'),
                'phone' => $request->input('phone'),
                'budget' => $request->input('budget'),
                'preferred_location' => $request->input('preferred_location'),
                'size' => $request->input('size'),
                'purpose' => $request->input('purpose'),
                'possession' => $request->input('possession'),
                'lead_status' => $request->input('lead_status'),
                'manager_remark' => $request->input('manager_remark'),
            ];

            if ($action === 'verify') {
                $prospectData['verification_status'] = 'verified';
                $prospectData['verified_at'] = now();
                $prospectData['verified_by'] = $user->id;
                
                if ($prospect) {
                    $prospect->update($prospectData);
                } else if ($lead) {
                    $prospectData['lead_id'] = $lead->id;
                    $prospectData['manager_id'] = $user->id;
                    $prospectData['assigned_manager'] = $user->id;
                    $prospectData['created_by'] = $user->id;
                    $prospect = Prospect::create($prospectData);
                }
                
                // Sync interested projects (handle both IDs and custom project names)
                if ($prospect && $request->has('interested_projects')) {
                    $interestedProjects = $request->input('interested_projects');
                    $projectIds = [];
                    
                    foreach ($interestedProjects as $project) {
                        if (is_int($project) || is_numeric($project)) {
                            // It's an ID
                            $projectIds[] = (int)$project;
                        } elseif (is_array($project) && isset($project['name']) && isset($project['is_custom']) && $project['is_custom']) {
                            // It's a custom project - create or find it in the database
                            $projectName = trim($project['name']);
                            if ($projectName) {
                                $projectModel = \App\Models\InterestedProjectName::firstOrCreate(
                                    ['name' => $projectName],
                                    [
                                        'slug' => \Illuminate\Support\Str::slug($projectName),
                                        'is_active' => true,
                                        'created_by' => $user->id,
                                    ]
                                );
                                $projectIds[] = $projectModel->id;
                            }
                        }
                    }
                    
                    if (!empty($projectIds)) {
                        $prospect->interestedProjects()->sync($projectIds);
                    }
                }
            } else { // reject
                $prospectData['verification_status'] = 'rejected';
                $prospectData['rejection_reason'] = $request->input('manager_remark') ?: 'Rejected by manager';
                
                if ($prospect) {
                    $prospect->update($prospectData);
                } else if ($lead) {
                    $prospectData['lead_id'] = $lead->id;
                    $prospectData['manager_id'] = $user->id;
                    $prospectData['assigned_manager'] = $user->id;
                    $prospectData['created_by'] = $user->id;
                    $prospect = Prospect::create($prospectData);
                }
            }

            // Mark task as completed using Task model's method
            $task->markAsCompleted();

            DB::commit();

            $message = $action === 'verify' 
                ? 'Prospect verified and task marked as completed successfully'
                : 'Prospect rejected and task marked as completed successfully';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $task->fresh(['lead.prospects']),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating prospect from task: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to process request: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get lead requirement form for manager (all fields visible)
     */
    public function getLeadRequirementFormForTask(Request $request, Task $task)
    {
        try {
            $user = $request->user();
            
            // Verify task is assigned to current user (admin/crm can view all)
            if ((int)$task->assigned_to !== (int)$user->id && !$user->isAdmin() && !$user->isCrm()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access to task',
                ], 403);
            }
            
            $lead = $task->lead;
            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'error' => 'Lead not found for this task',
                ], 404);
            }
            
            // Get prospect if exists
            $prospect = $lead->prospects()->latest()->first();
            
            // Determine if this is a prospect (from telecaller) or direct lead
            $hasProspect = $prospect && in_array($prospect->verification_status ?? '', ['pending', 'pending_verification']);
            
            // Load form field values
            $lead->load('formFieldValues');
            
            // Get existing field values from LeadFormFieldValue table (primary source)
            $existingValues = $lead->getFormFieldsArray();
            
            // If prospect exists, merge values from Prospect model
            // This ensures telecaller's filled values are pre-populated even if LeadFormFieldValue is empty
            if ($prospect) {
                // Map Prospect purpose back to form format
                // Prospect stores: 'end_user' or 'investment'
                // Form needs: 'End Use', 'Short Term Investment', 'Long Term Investment', 'Rental Income', 'Investment + End Use', 'N.A'
                $prospectPurpose = $prospect->purpose;
                $mappedPurpose = null;
                
                // Only map if purpose is not already set in LeadFormFieldValue
                if (!isset($existingValues['purpose']) && $prospectPurpose) {
                    if ($prospectPurpose === 'end_user') {
                        $mappedPurpose = 'End Use';
                    } elseif ($prospectPurpose === 'investment') {
                        // For investment, we can't determine the specific type from Prospect model
                        // Use 'Short Term Investment' as default fallback
                        // The actual value should ideally be in LeadFormFieldValue
                        $mappedPurpose = 'Short Term Investment';
                    }
                    
                    if ($mappedPurpose) {
                        $existingValues['purpose'] = $mappedPurpose;
                    }
                }
                
                // Merge Prospect values into existingValues (only if not already set in LeadFormFieldValue)
                // This ensures LeadFormFieldValue takes priority
                if (!isset($existingValues['preferred_location']) && $prospect->preferred_location) {
                    $existingValues['preferred_location'] = $prospect->preferred_location;
                }
                
                if (!isset($existingValues['possession']) && $prospect->possession) {
                    $existingValues['possession'] = $prospect->possession;
                }
                
                if (!isset($existingValues['budget']) && $prospect->budget) {
                    $existingValues['budget'] = $prospect->budget;
                }
                
                // Note: category and type are NOT stored in Prospect model, only in LeadFormFieldValue
                // So they should already be in existingValues if telecaller filled them
            }
            
            // Get ALL active fields for manager (manager can see all fields)
            // First try with visibleToRole scope
            $visibleFields = LeadFormField::active()
                ->visibleToRole('sales_manager')
                ->orderBy('display_order')
                ->get();
            
            // If no fields found, try getting all active fields regardless of level
            // This is a fallback in case the scope is too restrictive
            if ($visibleFields->isEmpty()) {
                \Log::warning('No fields found with visibleToRole scope, trying all active fields', [
                    'task_id' => $task->id,
                    'lead_id' => $lead->id,
                ]);
                $visibleFields = LeadFormField::active()
                    ->orderBy('display_order')
                    ->get();
            }
            
            // If still empty, return default fields based on FormDetectionService
            if ($visibleFields->isEmpty()) {
                \Log::warning('No LeadFormField records found, using default fields from FormDetectionService', [
                    'task_id' => $task->id,
                    'lead_id' => $lead->id,
                ]);
                
                // Get default fields from FormDetectionService
                $formDetectionService = app(\App\Services\FormDetectionService::class);
                $defaultFields = $formDetectionService->getFieldDefinitions('prospect', 'prospect-details');
                
                // Filter out Basic Information fields (name, phone, email) as they're already in the form
                // Only include fields that should be in Lead Requirements section
                $defaultFields = array_filter($defaultFields, function($field) {
                    $basicInfoFields = ['customer_name', 'name', 'phone', 'email'];
                    return !in_array($field['field_key'], $basicInfoFields);
                });
                
                // Convert to the expected format
                $visibleFields = collect($defaultFields)->map(function($field) {
                    return (object)[
                        'field_key' => $field['field_key'],
                        'field_label' => $field['label'],
                        'field_type' => $field['field_type'],
                        'is_required' => $field['required'] ?? false,
                        'options' => $field['options'] ?? [],
                        'dependent_field' => $field['dependent_field'] ?? null,
                        'dependent_conditions' => $field['dependent_conditions'] ?? null,
                        'placeholder' => $field['placeholder'] ?? '',
                        'help_text' => $field['help_text'] ?? '',
                        'display_order' => $field['order'] ?? 0,
                    ];
                });
            }
            
            $mappedFields = $visibleFields->map(function($field) {
                return [
                    'key' => $field->field_key,
                    'field_key' => $field->field_key,
                    'label' => $field->field_label,
                    'field_label' => $field->field_label,
                    'type' => $field->field_type,
                    'field_type' => $field->field_type,
                    'required' => $field->is_required,
                    'is_required' => $field->is_required,
                    'options' => is_array($field->options) ? $field->options : (is_string($field->options) ? json_decode($field->options, true) ?? [] : []),
                    'dependent_field' => $field->dependent_field,
                    'dependent_conditions' => $field->dependent_conditions,
                    'placeholder' => $field->placeholder,
                    'help_text' => $field->help_text,
                    'display_order' => $field->display_order,
                ];
            });
            
            \Log::info('Manager lead requirement form retrieved', [
                'task_id' => $task->id,
                'lead_id' => $lead->id,
                'fields_count' => $mappedFields->count(),
                'field_keys' => $mappedFields->pluck('key')->toArray(),
                'prospect_id' => $prospect?->id,
                'has_prospect' => $hasProspect,
            ]);
            
            return response()->json([
                'success' => true,
                'task_id' => $task->id,
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
                'lead_phone' => $lead->phone,
                'lead_email' => $lead->email,
                'prospect_id' => $prospect?->id,
                'prospect_status' => $prospect?->verification_status,
                'has_prospect' => $hasProspect, // Flag to determine if this is a prospect or direct lead
                'form_values' => $existingValues,
                'form_fields' => $mappedFields->values()->all(), // Use mappedFields and ensure it's an array
            ]);
        } catch (\Exception $e) {
            \Log::error('Get Lead Requirement Form Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'task_id' => $task->id ?? null,
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to load form: ' . $e->getMessage(),
                'message' => 'An error occurred while loading the form. Please try again.',
            ], 500);
        }
    }

    /**
     * Submit ASM task outcome through a single endpoint.
     */
    public function submitTaskOutcome(Request $request, Task $task)
    {
        $user = $request->user();

        if ((int) $task->assigned_to !== (int) $user->id && !$user->isAdmin() && !$user->isCrm()) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to task',
            ], 403);
        }

        $validated = $request->validate([
            'outcome' => 'required|in:interested,not_interested,follow_up,cnp,junk',
            'next_datetime' => 'nullable|date|after:now',
            'remark' => 'nullable|string|max:2000',
            'lead_form_payload' => 'nullable|array',
        ]);

        $outcome = $validated['outcome'];

        if (in_array($outcome, ['follow_up', 'cnp'], true) && empty($validated['next_datetime'])) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => ['next_datetime' => ['Please select a future date and time.']],
            ], 422);
        }

        if ($outcome === 'junk' && blank($validated['remark'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => ['remark' => ['Remark is required for junk outcome.']],
            ], 422);
        }

        if ($outcome === 'interested') {
            $payload = $request->input('lead_form_payload', []);
            if (!is_array($payload) || empty($payload)) {
                $payload = $request->except(['outcome', 'next_datetime', 'remark', 'lead_form_payload']);
            }

            $payload['output_action'] = $payload['output_action'] ?? 'interested';
            $payload['follow_up_required'] = $payload['follow_up_required'] ?? '0';

            $childRequest = Request::create('/', 'POST', $payload);
            $childRequest->setUserResolver(fn () => $user);

            $response = $this->verifyProspectFromTask($childRequest, $task);
            $body = $response->getData(true);

            if (($body['success'] ?? false) !== true || $response->getStatusCode() >= 300) {
                return $response;
            }

            $task->refresh();
            $this->recordTaskOutcome($task, 'interested');

            $body['outcome'] = 'interested';

            return response()->json($body, $response->getStatusCode());
        }

        if ($outcome === 'cnp') {
            $childRequest = Request::create('/', 'POST', [
                'retry_at' => $validated['next_datetime'],
            ]);
            $childRequest->setUserResolver(fn () => $user);

            $response = $this->markAsCNP($childRequest, $task);
            $body = $response->getData(true);

            if (($body['success'] ?? false) !== true || $response->getStatusCode() >= 300) {
                return $response;
            }

            $task->refresh();
            $this->recordTaskOutcome($task, 'cnp', null, Carbon::parse($validated['next_datetime']));

            $body['outcome'] = 'cnp';

            return response()->json($body, $response->getStatusCode());
        }

        [$lead, $prospect] = $this->getOrCreateTaskLeadAndProspect($task, $user, $request);

        if (!$lead) {
            return response()->json([
                'success' => false,
                'error' => 'Lead not found',
            ], 404);
        }

        DB::beginTransaction();

        try {
            if ($outcome === 'not_interested') {
                $reason = 'Not Interested';

                $lead->status = 'closed';
                $lead->next_followup_at = null;
                $lead->notes = $this->appendNote($lead->notes, '[' . now()->format('Y-m-d H:i:s') . '] ASM outcome: Not Interested');
                $lead->disableAutoUpdate();
                $lead->save();

                $prospect->update([
                    'verification_status' => 'rejected',
                    'lead_status' => 'cold',
                    'manager_remark' => $reason,
                    'rejection_reason' => $reason,
                    'verified_at' => now(),
                    'verified_by' => $user->id,
                ]);

                $this->deactivateLeadAssignments($lead);
                $task->markAsCompleted();
                $this->recordTaskOutcome($task, 'not_interested', $reason);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Lead marked as not interested and removed from ASM tasks.',
                    'outcome' => 'not_interested',
                ]);
            }

            if ($outcome === 'follow_up') {
                $nextAt = Carbon::parse($validated['next_datetime']);
                $remark = trim((string) ($validated['remark'] ?? ''));

                $followUpTask = Task::create([
                    'lead_id' => $lead->id,
                    'assigned_to' => $user->id,
                    'type' => 'phone_call',
                    'title' => "Follow-up call: {$lead->name}",
                    'description' => "Follow-up call task scheduled for {$nextAt->format('Y-m-d H:i')}.",
                    'status' => 'pending',
                    'scheduled_at' => $nextAt,
                    'created_by' => $user->id,
                    'notes' => $remark ?: "Follow-up scheduled for {$nextAt->format('Y-m-d H:i')}",
                ]);

                $lead->next_followup_at = $nextAt;
                $lead->notes = $this->appendNote($lead->notes, '[' . now()->format('Y-m-d H:i:s') . '] ASM outcome: Follow Up scheduled for ' . $nextAt->format('Y-m-d H:i:s'));
                $lead->save();

                $prospect->update([
                    'verification_status' => 'pending_verification',
                    'manager_remark' => $remark ?: "Follow-up scheduled for {$nextAt->format('Y-m-d H:i')}",
                ]);

                $task->markAsCompleted();
                $this->recordTaskOutcome($task, 'follow_up', $remark ?: null, $nextAt);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Follow-up task created successfully.',
                    'outcome' => 'follow_up',
                    'task' => $followUpTask,
                ]);
            }

            $junkRemark = trim((string) ($validated['remark'] ?? ''));

            $lead->status = 'junk';
            $lead->next_followup_at = null;
            $lead->notes = $this->appendNote($lead->notes, '[' . now()->format('Y-m-d H:i:s') . '] ASM outcome: Junk - ' . $junkRemark);
            $lead->disableAutoUpdate();
            $lead->save();

            $prospect->update([
                'verification_status' => 'rejected',
                'lead_status' => 'junk',
                'manager_remark' => $junkRemark,
                'rejection_reason' => $junkRemark,
                'verified_at' => now(),
                'verified_by' => $user->id,
            ]);

            $this->deactivateLeadAssignments($lead);
            $task->markAsCompleted();
            $this->recordTaskOutcome($task, 'junk', $junkRemark);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lead marked as junk and removed from the active ASM queue.',
                'outcome' => 'junk',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Submit Task Outcome Error', [
                'task_id' => $task->id,
                'outcome' => $outcome,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to submit task outcome: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getOrCreateTaskLeadAndProspect(Task $task, User $user, Request $request): array
    {
        $lead = $task->lead;
        if (!$lead) {
            return [null, null];
        }

        $prospect = $lead->prospects()->latest()->first();

        if (!$prospect) {
            $prospect = Prospect::create([
                'lead_id' => $lead->id,
                'customer_name' => $request->input('name', $lead->name),
                'phone' => $request->input('phone', $lead->phone),
                'manager_id' => $user->id,
                'assigned_manager' => $user->id,
                'created_by' => $user->id,
                'verification_status' => 'pending_verification',
            ]);
        }

        return [$lead, $prospect];
    }

    private function deactivateLeadAssignments(Lead $lead): void
    {
        $lead->assignments()
            ->where('is_active', true)
            ->update([
                'is_active' => false,
                'unassigned_at' => now(),
            ]);
    }

    private function recordTaskOutcome(Task $task, string $outcome, ?string $remark = null, ?Carbon $nextActionAt = null): void
    {
        $task->update([
            'outcome' => $outcome,
            'outcome_remark' => $remark,
            'outcome_recorded_at' => now(),
            'next_action_at' => $nextActionAt,
        ]);
    }

    private function appendNote(?string $existing, string $entry): string
    {
        $existing = trim((string) $existing);

        return $existing !== '' ? $existing . "\n\n" . $entry : $entry;
    }

    /**
     * Verify prospect from task (with full form data)
     */
    public function verifyProspectFromTask(Request $request, Task $task)
    {
        try {
            $user = $request->user();
            
            // Verify task is assigned to current user (admin/crm can view all)
            if ((int)$task->assigned_to !== (int)$user->id && !$user->isAdmin() && !$user->isCrm()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access to task',
                ], 403);
            }
            
            $lead = $task->lead;
            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'error' => 'Lead not found',
                ], 404);
            }
            
            // Get or find prospect
            $prospect = $lead->prospects()->latest()->first();
            
            if (!$prospect) {
                // Direct-assigned/imported leads may not have a prospect yet.
                // Create one so manager form submission can proceed normally.
                $prospect = Prospect::create([
                    'lead_id' => $lead->id,
                    'customer_name' => $request->input('name', $lead->name),
                    'phone' => $request->input('phone', $lead->phone),
                    'manager_id' => $user->id,
                    'assigned_manager' => $user->id,
                    'created_by' => $user->id,
                    'verification_status' => 'pending_verification',
                ]);
            }
            
            // Validate basic fields
            $validationRules = [
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'lead_status' => 'required|in:hot,warm,cold,junk',
                'lead_quality' => 'required|integer|between:1,5',
                'follow_up_required' => 'nullable|boolean',
                'follow_up_date' => 'nullable|required_if:follow_up_required,1|date',
            'interested_projects' => 'required|array|min:1',
            'interested_projects.*' => function ($attribute, $value, $fail) {
                // Allow both integer IDs and objects with name and is_custom
                if (is_int($value) || (is_array($value) && isset($value['name']) && isset($value['is_custom']))) {
                    return;
                }
                $fail('The ' . $attribute . ' must be either a project ID or a custom project object.');
            },
            ];
            
            // Get all fields for manager (for dynamic validation)
            $allFields = LeadFormField::active()
                ->visibleToRole('sales_manager')
                ->get();
            
            // Add dynamic field validation
            foreach ($allFields as $field) {
                if ($field->is_required) {
                    $rule = ['required'];
                } else {
                    $rule = ['nullable'];
                }
                
                switch ($field->field_type) {
                    case 'email':
                        $rule[] = 'email';
                        break;
                    case 'number':
                        $rule[] = 'numeric';
                        break;
                    case 'date':
                        $rule[] = 'date';
                        break;
                    case 'time':
                        $rule[] = 'date_format:H:i';
                        break;
                }
                
                $validationRules[$field->field_key] = $rule;
            }
            
            $validated = $request->validate($validationRules);
            
            DB::beginTransaction();
            
            try {
                // Update lead basic fields
                $lead->name = $validated['name'];
                $lead->phone = $validated['phone'];
                if ($request->has('email')) {
                    $lead->email = $request->input('email');
                }
                if ($request->has('address')) {
                    $lead->address = $request->input('address');
                }
                if ($request->has('city')) {
                    $lead->city = $request->input('city');
                }
                if ($request->has('state')) {
                    $lead->state = $request->input('state');
                }
                if ($request->has('pincode')) {
                    $lead->pincode = $request->input('pincode');
                }
                $lead->save();
                
                // Save dynamic form field values
                foreach ($allFields as $field) {
                    if ($request->has($field->field_key)) {
                        $value = $request->input($field->field_key);
                        if (!empty($value) || $field->is_required) {
                            $lead->setFormFieldValue($field->field_key, $value ?? '', $user->id);
                        }
                    }
                }
                
                // Save Customer Profiling fields (all optional)
                $customerProfilingFields = ['customer_job', 'industry_sector', 'buying_frequency', 'living_city', 'city_type'];
                foreach ($customerProfilingFields as $fieldKey) {
                    if ($request->has($fieldKey) && $request->input($fieldKey) !== null && $request->input($fieldKey) !== '') {
                        $lead->setFormFieldValue($fieldKey, $request->input($fieldKey), $user->id);
                    }
                }
                
                // Mark form as filled by manager
                $lead->form_filled_by_manager = true;
                $lead->save();
                
                // Map form values to prospect fields
                $budget = $request->input('budget');
                $preferredLocation = $request->input('preferred_location');
                $purposeRaw = $request->input('purpose');
                $possession = $request->input('possession');
                
                // Map purpose
                $purpose = $purposeRaw;
                if ($purposeRaw === 'End Use') {
                    $purpose = 'end_user';
                } elseif (in_array($purposeRaw, ['Short Term Investment', 'Long Term Investment', 'Rental Income', 'Investment + End Use'])) {
                    $purpose = 'investment';
                } elseif ($purposeRaw === 'N.A' || empty($purposeRaw)) {
                    $purpose = null;
                }
                
                // Get lead status and follow-up required flag
                $leadStatus = $validated['lead_status'];
                $leadQuality = $validated['lead_quality'];
                // Handle follow_up_required checkbox value (can be '1', '0', true, false, or 'true', 'false')
                $followUpRequiredValue = $request->input('follow_up_required', '0');
                $isFollowUpRequired = in_array($followUpRequiredValue, ['1', 'true', true, 1], true);
                $isFollowUp = $isFollowUpRequired; // Alias for consistency with other parts of the code
                
                // Update prospect - different logic for Follow Up Required vs normal verification
                if ($isFollowUpRequired) {
                    // Validate follow_up_date is present when follow_up_required is true (should already be validated, but double-check)
                    if (!isset($validated['follow_up_date']) || empty($validated['follow_up_date'])) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Validation failed',
                            'errors' => ['follow_up_date' => ['Follow Up Date is required when Follow Up Required is checked.']],
                        ], 422);
                    }
                    
                    // For Follow Up Required: Keep prospect as pending_verification, don't verify yet
                    $managerRemark = $request->input('manager_remark', '');
                    $followUpDateStr = $validated['follow_up_date'];
                    $followUpRemark = $managerRemark ? $managerRemark . ' | ' : '';
                    $followUpRemark .= 'Follow-up scheduled for ' . $followUpDateStr;
                    
                    $prospect->update([
                        'customer_name' => $validated['name'],
                        'phone' => $validated['phone'],
                        'budget' => $budget,
                        'preferred_location' => $preferredLocation,
                        'purpose' => $purpose,
                        'possession' => $possession,
                        'lead_status' => $leadStatus,
                        'lead_score' => $leadQuality, // Save lead quality to lead_score
                        'manager_remark' => $followUpRemark,
                        'verification_status' => 'pending_verification', // Keep as pending
                        'verified_at' => null,
                        'verified_by' => null,
                        'rejection_reason' => null,
                    ]);
                    
                    // Create follow-up calling task for the selected date and time
                    $followUpDate = \Carbon\Carbon::parse($validated['follow_up_date']); // Use provided datetime
                    
                    $followUpTask = Task::create([
                        'lead_id' => $lead->id,
                        'assigned_to' => $user->id,
                        'type' => 'phone_call',
                        'title' => "Follow-up call: {$validated['name']}",
                        'description' => "Follow-up call task scheduled for {$followUpDate->format('Y-m-d H:i')}. Prospect requires follow-up call on selected date and time.",
                        'notes' => $managerRemark ?: "Follow-up scheduled for {$followUpDate->format('Y-m-d H:i')}",
                        'status' => 'pending',
                        'scheduled_at' => $followUpDate,
                        'created_by' => $user->id,
                    ]);
                    
                    \Log::info('Follow-up task created for manager', [
                        'current_task_id' => $task->id,
                        'new_follow_up_task_id' => $followUpTask->id,
                        'prospect_id' => $prospect->id,
                        'lead_id' => $lead->id,
                        'manager_id' => $user->id,
                        'follow_up_date' => $followUpDate->format('Y-m-d H:i'),
                        'lead_quality' => $leadQuality,
                    ]);
                    
                    // Check if telecaller task should also be created
                    $createTelecallerTaskValue = $request->input('create_telecaller_task', false);
                    $createTelecallerTask = in_array($createTelecallerTaskValue, ['1', 'true', true, 1], true);
                    
                    if ($createTelecallerTask) {
                        // Get telecaller from prospect
                        $telecallerId = $prospect->telecaller_id ?? $prospect->created_by;
                        
                        if ($telecallerId) {
                            $telecaller = User::find($telecallerId);
                            
                            if ($telecaller) {
                                // Create TelecallerTask for telecaller
                                $telecallerTaskService = app(\App\Services\TelecallerTaskService::class);
                                $telecallerTask = $telecallerTaskService->createCallingTask(
                                    $lead,
                                    $telecaller,
                                    $user->id // Created by manager
                                );
                                
                                // Update scheduled_at to match follow-up date
                                $telecallerTask->update([
                                    'scheduled_at' => $followUpDate,
                                    'notes' => "Follow-up calling task created by manager. Scheduled for {$followUpDate->format('Y-m-d H:i')}."
                                ]);
                                
                                \Log::info('Follow-up telecaller task created', [
                                    'telecaller_task_id' => $telecallerTask->id,
                                    'telecaller_id' => $telecallerId,
                                    'lead_id' => $lead->id,
                                    'follow_up_date' => $followUpDate->format('Y-m-d H:i'),
                                ]);
                            }
                        }
                    }
                } else {
                    // For normal verification (no follow-up): Verify prospect normally
                    $prospect->update([
                        'customer_name' => $validated['name'],
                        'phone' => $validated['phone'],
                        'budget' => $budget,
                        'preferred_location' => $preferredLocation,
                        'purpose' => $purpose,
                        'possession' => $possession,
                        'lead_status' => $leadStatus,
                        'lead_score' => $leadQuality, // Save lead quality to lead_score
                        'manager_remark' => $request->input('manager_remark'),
                        'verification_status' => 'verified',
                        'verified_at' => now(),
                        'verified_by' => $user->id,
                        'rejection_reason' => null, // Clear rejection reason if was rejected before
                    ]);
                }
                
                // Sync interested projects (handle both IDs and custom project names)
                if ($request->has('interested_projects')) {
                    $interestedProjects = $request->input('interested_projects');
                    $projectIds = [];
                    
                    foreach ($interestedProjects as $project) {
                        if (is_int($project) || is_numeric($project)) {
                            // It's an ID
                            $projectIds[] = (int)$project;
                        } elseif (is_array($project) && isset($project['name']) && isset($project['is_custom']) && $project['is_custom']) {
                            // It's a custom project - create or find it in the database
                            $projectName = trim($project['name']);
                            if ($projectName) {
                                $projectModel = \App\Models\InterestedProjectName::firstOrCreate(
                                    ['name' => $projectName],
                                    [
                                        'slug' => \Illuminate\Support\Str::slug($projectName),
                                        'is_active' => true,
                                        'created_by' => $user->id,
                                    ]
                                );
                                $projectIds[] = $projectModel->id;
                            }
                        }
                    }
                    
                    if (!empty($projectIds)) {
                        $prospect->interestedProjects()->sync($projectIds);
                    }
                }
                
                // After verification, ensure lead is created/updated and status is set correctly
                if (!$isFollowUpRequired) {
                    // Ensure prospect status is saved as verified (double-check)
                    if ($prospect->verification_status !== 'verified') {
                        $prospect->verification_status = 'verified';
                        $prospect->verified_at = now();
                        $prospect->verified_by = $user->id;
                        $prospect->save();
                    }
                    
                    // Refresh prospect to get latest data
                    $prospect->refresh();
                    
                    // If prospect doesn't have a lead_id, create lead from prospect
                    if (!$prospect->lead_id) {
                        // Map prospect fields to lead fields
                        $leadData = [
                            'name' => $prospect->customer_name,
                            'phone' => $prospect->phone,
                            'email' => null,
                            'budget' => $prospect->budget,
                            'preferred_location' => $prospect->preferred_location,
                            'preferred_size' => $prospect->size,
                            'use_end_use' => $prospect->purpose === 'end_user' ? 'End User' : ($prospect->purpose === 'investment' ? '2nd Investments' : null),
                            'possession_status' => $prospect->possession,
                            'source' => 'call',
                            'status' => 'verified_prospect',
                            'created_by' => $user->id,
                        ];
                        
                        // Combine remarks in notes
                        $notes = [];
                        if ($prospect->remark) {
                            $notes[] = "Telecaller Remark: " . $prospect->remark;
                        }
                        if ($prospect->manager_remark) {
                            $notes[] = "Manager Remark: " . $prospect->manager_remark;
                        }
                        if (!empty($notes)) {
                            $leadData['notes'] = implode("\n\n", $notes);
                        }
                        
                        // Add requirements if available
                        if ($prospect->notes) {
                            $leadData['requirements'] = $prospect->notes;
                        }
                        
                        $lead = Lead::create($leadData);
                        $prospect->lead_id = $lead->id;
                        $prospect->save();
                        
                        // Assign lead to the manager who verified
                        $assignedTo = $prospect->manager_id ?? ($prospect->telecaller ? $prospect->telecaller->manager_id : null) ?? $user->id;
                        
                        // Deactivate existing assignments for this lead
                        LeadAssignment::where('lead_id', $lead->id)->update([
                            'is_active' => false,
                            'unassigned_at' => now()
                        ]);
                        
                        // Create new assignment
                        LeadAssignment::create([
                            'lead_id' => $lead->id,
                            'assigned_to' => $assignedTo,
                            'assigned_by' => $user->id,
                            'assignment_type' => 'primary',
                            'assigned_at' => now(),
                            'is_active' => true,
                        ]);
                        
                        // Fire LeadAssigned event
                        event(new \App\Events\LeadAssigned($lead, $assignedTo, $user->id));
                        
                        // Lead status is already set to 'verified_prospect' in creation
                    } else {
                        // If lead already exists, update its status to verified_prospect
                        $lead = Lead::find($prospect->lead_id);
                        if ($lead) {
                            // Enable auto-update if disabled, then update status
                            if (!$lead->canAutoUpdate()) {
                                $lead->enableAutoUpdate();
                            }
                            $lead->updateStatusIfAllowed('verified_prospect');
                            
                            // If updateStatusIfAllowed failed (shouldn't happen now), force update
                            if ($lead->status !== 'verified_prospect') {
                                $lead->status = 'verified_prospect';
                                $lead->save();
                            }
                        }
                    }
                    
                    // Final save to ensure all changes are persisted
                    $prospect->save();
                }
                
                // Mark current verification task as completed
                $task->markAsCompleted();
                
                // Send notification to telecaller when prospect is verified (not for Follow Up Required)
                if (!$isFollowUpRequired && $prospect->verification_status === 'verified' && $prospect->telecaller_id) {
                    try {
                        $telecaller = \App\Models\User::find($prospect->telecaller_id);
                        if ($telecaller) {
                            $notificationService = new NotificationService();
                            
                            $managerRemark = $request->input('manager_remark', '');
                            $remarkText = $managerRemark ? "\nManager Remark: {$managerRemark}" : '';
                            
                            $title = "Prospect Verified: {$lead->name}";
                            $message = "Manager {$user->name} verified prospect {$lead->name}. Status: " . ucfirst($leadStatus) . "{$remarkText}";
                            $actionUrl = url('/telecaller/verification-pending');
                            
                            $notificationData = [
                                'lead_id' => $lead->id,
                                'lead_name' => $lead->name,
                                'lead_phone' => $lead->phone,
                                'prospect_id' => $prospect->id,
                                'verification_status' => 'verified',
                                'lead_status' => $leadStatus,
                                'manager_remark' => $managerRemark,
                                'verified_at' => now()->toIso8601String(),
                                'manager_name' => $user->name,
                                'manager_id' => $user->id,
                            ];
                            
                            $notificationService->notifyNewVerification(
                                $telecaller,
                                'verified',
                                $title,
                                $message,
                                $actionUrl,
                                $notificationData
                            );
                            
                            \Log::info('Verification notification sent to telecaller', [
                                'telecaller_id' => $telecaller->id,
                                'prospect_id' => $prospect->id,
                                'lead_id' => $lead->id,
                            ]);
                        }
                    } catch (\Exception $e) {
                        // Log notification error but don't fail the verification
                        \Log::error('Failed to send verification notification to telecaller', [
                            'error' => $e->getMessage(),
                            'telecaller_id' => $prospect->telecaller_id,
                            'prospect_id' => $prospect->id,
                        ]);
                    }
                }
                
                // Fire event for telecaller achievement (if prospect is verified, count towards telecaller)
                // Only fire event if prospect is actually verified (not for Follow Up)
                if (!$isFollowUp) {
                    // This will be handled by existing event listeners if needed
                }
                
                // Final verification: Ensure prospect status is 'verified' before committing
                if (!$isFollowUpRequired) {
                    $prospect->refresh();
                    if ($prospect->verification_status !== 'verified') {
                        $prospect->verification_status = 'verified';
                        $prospect->verified_at = now();
                        $prospect->verified_by = $user->id;
                        $prospect->save();
                    }
                }
                
                DB::commit();
                
                // Get lead ID for logging
                $leadId = $prospect->lead_id ?? null;
                
                $logMessage = $isFollowUp 
                    ? 'Follow-up task created for prospect from manager task'
                    : 'Prospect verified successfully from manager task';
                    
                \Log::info($logMessage, [
                    'task_id' => $task->id,
                    'prospect_id' => $prospect->id,
                    'lead_id' => $leadId,
                    'manager_id' => $user->id,
                    'lead_status' => $leadStatus,
                    'is_follow_up' => $isFollowUp,
                    'follow_up_date' => $isFollowUp ? $validated['follow_up_date'] ?? null : null,
                    'prospect_verification_status' => $prospect->verification_status,
                ]);
                
                $responseMessage = $isFollowUp 
                    ? 'Follow-up task created successfully. Prospect will be called on the selected date.'
                    : 'Prospect verified successfully';
                
                // Refresh prospect one more time to get latest data
                $prospect->refresh();
                
                return response()->json([
                    'success' => true,
                    'message' => $responseMessage,
                    'prospect' => $prospect->fresh(['manager', 'telecaller', 'lead']),
                    'is_follow_up' => $isFollowUp,
                ], 200);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
            } catch (\Illuminate\Validation\ValidationException $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Verify Prospect From Task Error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'task_id' => $task->id,
                ]);
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to verify prospect: ' . $e->getMessage(),
                ], 500);
            }
        }

    /**
     * Reject prospect from task
     */
    public function rejectProspectFromTask(Request $request, Task $task)
    {
        try {
            $user = $request->user();
            
            // Verify task is assigned to current user (admin/crm can view all)
            if ((int)$task->assigned_to !== (int)$user->id && !$user->isAdmin() && !$user->isCrm()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access to task',
                ], 403);
            }
            
            $request->validate([
                'rejection_reason' => 'required|string|max:1000',
            ]);
            
            $lead = $task->lead;
            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'error' => 'Lead not found',
                ], 404);
            }
            
            $prospect = $lead->prospects()->latest()->first();
            
            if (!$prospect) {
                return response()->json([
                    'success' => false,
                    'error' => 'Prospect not found for this lead',
                ], 404);
            }
            
            DB::beginTransaction();
            
            try {
                // Update prospect as rejected
                $rejectionReason = $request->input('rejection_reason');
                $prospect->update([
                    'verification_status' => 'rejected',
                    'rejection_reason' => $rejectionReason,
                    'verified_by' => null,
                    'verified_at' => null,
                ]);
                
                // Mark task as completed
                $task->markAsCompleted();
                
                // Send notification to telecaller when prospect is rejected
                if ($prospect->telecaller_id) {
                    try {
                        $telecaller = \App\Models\User::find($prospect->telecaller_id);
                        if ($telecaller) {
                            $notificationService = new NotificationService();
                            
                            $title = "Prospect Rejected: {$lead->name}";
                            $message = "Manager {$user->name} rejected prospect {$lead->name}. Reason: {$rejectionReason}";
                            $actionUrl = url('/telecaller/verification-pending');
                            
                            $notificationData = [
                                'lead_id' => $lead->id,
                                'lead_name' => $lead->name,
                                'lead_phone' => $lead->phone,
                                'prospect_id' => $prospect->id,
                                'verification_status' => 'rejected',
                                'rejection_reason' => $rejectionReason,
                                'rejected_at' => now()->toIso8601String(),
                                'manager_name' => $user->name,
                                'manager_id' => $user->id,
                            ];
                            
                            $notificationService->notifyNewVerification(
                                $telecaller,
                                'rejected',
                                $title,
                                $message,
                                $actionUrl,
                                $notificationData
                            );
                            
                            \Log::info('Rejection notification sent to telecaller', [
                                'telecaller_id' => $telecaller->id,
                                'prospect_id' => $prospect->id,
                                'lead_id' => $lead->id,
                            ]);
                        }
                    } catch (\Exception $e) {
                        // Log notification error but don't fail the rejection
                        \Log::error('Failed to send rejection notification to telecaller', [
                            'error' => $e->getMessage(),
                            'telecaller_id' => $prospect->telecaller_id,
                            'prospect_id' => $prospect->id,
                        ]);
                    }
                }
                
                DB::commit();
                
                \Log::info('Prospect rejected from manager task', [
                    'task_id' => $task->id,
                    'prospect_id' => $prospect->id,
                    'lead_id' => $lead->id,
                    'manager_id' => $user->id,
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Prospect rejected successfully',
                    'prospect' => $prospect->fresh(['manager', 'telecaller']),
                ], 200);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Reject Prospect From Task Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'task_id' => $task->id,
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to reject prospect: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark prospect as CNP (Call Not Picked) and create retry task at selected time
     */
    public function markAsCNP(Request $request, Task $task)
    {
        try {
            $user = $request->user();
            
            // Verify task is assigned to current user (admin/crm can view all)
            if ((int)$task->assigned_to !== (int)$user->id && !$user->isAdmin() && !$user->isCrm()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access to task',
                ], 403);
            }
            
            // Validate retry time (retry_at OR retry_minutes)
            $request->validate([
                'retry_at' => 'nullable|date|after:now',
                'retry_minutes' => 'nullable|integer|min:1|max:10080', // Max 1 week (10080 minutes)
            ], [
                'retry_at.after' => 'Retry time must be in the future',
                'retry_minutes.max' => 'Retry time cannot be more than 1 week in the future',
            ]);
            
            $lead = $task->lead;
            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'error' => 'Lead not found',
                ], 404);
            }
            
            $prospect = $lead->prospects()->latest()->first();
            
            if (!$prospect) {
                return response()->json([
                    'success' => false,
                    'error' => 'Prospect not found for this lead',
                ], 404);
            }
            
            DB::beginTransaction();
            
            try {
                // Calculate scheduled time based on selection
                $retryScheduledAt = null;
                $timeDescription = '';
                
                if ($request->has('retry_at') && $request->retry_at) {
                    // Custom datetime provided
                    $retryScheduledAt = \Carbon\Carbon::parse($request->retry_at);
                    $timeDescription = $retryScheduledAt->format('d M Y, h:i A');
                } elseif ($request->has('retry_minutes') && $request->retry_minutes) {
                    // Quick option (minutes) provided
                    $retryScheduledAt = now()->addMinutes($request->retry_minutes);
                    $timeDescription = "in {$request->retry_minutes} minutes ({$retryScheduledAt->format('d M Y, h:i A')})";
                } else {
                    // Default: 2 hours later (backward compatibility)
                    $retryScheduledAt = now()->addHours(2);
                    $timeDescription = "in 2 hours ({$retryScheduledAt->format('d M Y, h:i A')})";
                }
                
                // Ensure scheduled time is in future
                if ($retryScheduledAt->isPast()) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'error' => 'Retry time must be in the future',
                    ], 422);
                }
                
                // Cancel the current task since a new retry task is being created
                // This ensures only one active task per lead (the new retry task)
                $cnpNote = "Call Not Picked - Rescheduled for {$timeDescription} on " . now()->format('Y-m-d H:i:s');
                $currentDescription = $task->description ?? '';
                $task->update([
                    'description' => $currentDescription . ($currentDescription ? "\n\n" : '') . $cnpNote,
                    'notes' => ($task->notes ?? '') . ($task->notes ? "\n\n" : '') . $cnpNote,
                    'status' => 'cancelled', // Cancel old task to avoid duplicates
                ]);
                
                // Create new task for selected time
                $retryTaskTitle = "Retry call: {$lead->name} (CNP rescheduled - {$retryScheduledAt->format('d M Y, h:i A')})";
                $retryTaskDescription = "Retry call {$timeDescription} - previous call not picked. Original task ID: {$task->id}";
                
                $retryTask = Task::create([
                    'lead_id' => $lead->id,
                    'assigned_to' => $user->id,
                    'type' => 'phone_call',
                    'title' => $retryTaskTitle,
                    'description' => $retryTaskDescription,
                    'status' => 'pending',
                    'scheduled_at' => $retryScheduledAt,
                    'created_by' => $user->id,
                    'notes' => "CNP retry task created from task #{$task->id} on " . now()->format('Y-m-d H:i:s') . " - Scheduled for {$timeDescription}",
                ]);
                
                // Prospect status remains pending_verification (no change)
                // This is already the case, so no update needed
                
                DB::commit();
                
                \Log::info('Prospect marked as CNP - Retry task created', [
                    'current_task_id' => $task->id,
                    'new_retry_task_id' => $retryTask->id,
                    'prospect_id' => $prospect->id,
                    'lead_id' => $lead->id,
                    'manager_id' => $user->id,
                    'retry_scheduled_at' => $retryScheduledAt->format('Y-m-d H:i:s'),
                    'retry_minutes' => $request->retry_minutes ?? null,
                    'retry_at' => $request->retry_at ?? null,
                ]);
                
                $successMessage = $request->has('retry_at') || $request->has('retry_minutes')
                    ? "Call Not Picked marked. New calling task created {$timeDescription}."
                    : 'Call Not Picked marked. New calling task created for 2 hours later.';
                
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                    'current_task' => $task->fresh(),
                    'retry_task' => $retryTask,
                    'retry_scheduled_at' => $retryScheduledAt->format('Y-m-d H:i:s'),
                    'time_description' => $timeDescription,
                ], 200);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Mark as CNP Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'task_id' => $task->id ?? null,
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to mark as CNP: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete a task
     */
    public function completeTask(Request $request, Task $task)
    {
        try {
            $user = $request->user();
            
            // Verify task is assigned to current user (admin/crm can view all)
            if ((int)$task->assigned_to !== (int)$user->id && !$user->isAdmin() && !$user->isCrm()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized access to task',
                ], 403);
            }
            
            // Check if task is already completed
            if ($task->status === 'completed') {
                return response()->json([
                    'success' => true,
                    'message' => 'Task already completed',
                    'data' => $task,
                ]);
            }
            
            // Mark task as completed
            $task->markAsCompleted();
            
            return response()->json([
                'success' => true,
                'message' => 'Task completed successfully',
                'data' => $task->fresh(),
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error completing task: ' . $e->getMessage(), [
                'task_id' => $task->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to complete task: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove all overdue tasks for the manager
     * Marks all overdue tasks as completed
     */
    public function removeAllOverdueTasks(Request $request)
    {
        try {
            $user = $request->user();
            
            // Get all overdue tasks assigned to this manager (more than 10 minutes old)
            $tenMinutesAgo = now()->subMinutes(10);
            $overdueTasks = Task::where('assigned_to', $user->id)
                ->where('type', 'phone_call')
                ->whereIn('status', ['pending', 'in_progress'])
                ->where('scheduled_at', '<', $tenMinutesAgo)
                ->get();
            
            $count = 0;
            foreach ($overdueTasks as $task) {
                $task->markAsCompleted();
                $count++;
            }
            
            \Log::info('Removed all overdue tasks for manager', [
                'manager_id' => $user->id,
                'count' => $count,
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Successfully removed {$count} overdue task(s)",
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            \Log::error('Remove All Overdue Tasks Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'manager_id' => $request->user()->id ?? null,
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to remove overdue tasks: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Schedule a call task for a lead
     */
    public function scheduleCallTask(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'lead_id' => 'required|exists:leads,id',
            'scheduled_at' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }
        
        // Check if scheduled_at is in the future (with 1 minute buffer for timezone issues)
        $scheduledAt = Carbon::parse($request->scheduled_at);
        if ($scheduledAt->isPast() && $scheduledAt->diffInMinutes(now()) < -1) {
            return response()->json([
                'success' => false,
                'message' => 'Scheduled time must be in the future',
                'errors' => ['scheduled_at' => ['The scheduled time must be in the future']],
            ], 422);
        }

        try {
            $lead = Lead::findOrFail($request->lead_id);
            $notes = $request->notes ?? null;
            
            Log::info('Schedule Call Task Request', [
                'user_id' => $user->id,
                'lead_id' => $request->lead_id,
                'scheduled_at' => $scheduledAt->toDateTimeString(),
                'user_role' => $user->role->slug ?? 'unknown',
            ]);

            // Check if user has permission to access this lead
            if (!$this->canAccessLead($user, $lead)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to create a task for this lead.',
                ], 403);
            }

            // Determine who the task should be assigned to
            // If lead is assigned to someone, assign task to them, otherwise assign to current user
            $assignedTo = $lead->activeAssignments->first()?->assigned_to ?? $user->id;

            // Create task based on assigned user's role (not current user's role)
            $assignedUser = User::with('role')->find($assignedTo);
            
            if ($assignedUser && $assignedUser->isTelecaller()) {
                // For telecallers, create TelecallerTask
                $telecallerTaskService = app(\App\Services\TelecallerTaskService::class);
                $task = $telecallerTaskService->createCallingTask($lead, $assignedUser, $user->id);
                
                // Update scheduled_at if provided
                if ($scheduledAt) {
                    $task->update(['scheduled_at' => $scheduledAt]);
                }
                
                // Add notes if provided
                if ($notes) {
                    $task->update(['notes' => $notes]);
                }

                Log::info('Call task scheduled (TelecallerTask)', [
                    'task_id' => $task->id,
                    'lead_id' => $lead->id,
                    'assigned_to' => $assignedTo,
                    'scheduled_at' => $scheduledAt,
                    'created_by' => $user->id,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Call task scheduled successfully',
                    'data' => $task->load(['lead', 'assignedTo']),
                ], 201);
            }

            // For sales managers, sales executives, and others, create Task
            $task = Task::create([
                'lead_id' => $lead->id,
                'assigned_to' => $assignedTo,
                'type' => 'phone_call',
                'title' => "Call lead: {$lead->name}",
                'description' => "Phone call task for lead: {$lead->name} ({$lead->phone})" . ($notes ? "\n\nNotes: {$notes}" : ''),
                'status' => 'pending',
                'scheduled_at' => $scheduledAt,
                'created_by' => $user->id,
                'notes' => $notes,
            ]);

            Log::info('Call task scheduled (Task)', [
                'task_id' => $task->id,
                'lead_id' => $lead->id,
                'assigned_to' => $assignedTo,
                'scheduled_at' => $scheduledAt,
                'created_by' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Call task scheduled successfully',
                'data' => $task->load(['lead', 'assignedTo', 'creator']),
            ], 201);

        } catch (\Exception $e) {
            Log::error('Schedule Call Task Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id ?? null,
                'lead_id' => $request->lead_id ?? null,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule call task: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if user can access a lead
     */
    private function canAccessLead($user, Lead $lead): bool
    {
        // Admin and CRM can see all leads
        if ($user->isAdmin() || $user->isCrm()) {
            return true;
        }

        // Check if lead is directly assigned to user
        if ($lead->activeAssignments()->where('assigned_to', $user->id)->exists()) {
            return true;
        }

        // Senior Manager, Manager, Assistant Sales Manager: team's leads
        if ($user->isSalesManager() || $user->isSeniorManager() || $user->isAssistantSalesManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            
            if ($teamMemberIds->isNotEmpty() && $lead->activeAssignments()->whereIn('assigned_to', $teamMemberIds)->where('is_active', true)->exists()) {
                return true;
            }
            
            if ($teamMemberIds->isNotEmpty()) {
                return $lead->prospects()
                    ->whereIn('telecaller_id', $teamMemberIds)
                    ->whereIn('verification_status', ['verified', 'approved'])
                    ->exists();
            }
        }

        // Sales Executive: only assigned leads or leads from their own prospects
        if ($user->isSalesExecutive()) {
            return $lead->activeAssignments()->where('assigned_to', $user->id)->exists() ||
                   $lead->prospects()->where('telecaller_id', $user->id)->exists();
        }
        return false;
    }
    /**
     * Get lead requirement form directly by lead ID (no task required)
     * Used by admin/crm from lead show page
     */
    public function getLeadRequirementForm(Request $request, \App\Models\Lead $lead)
    {
        try {
            $user = $request->user();
            if (
                !$user->isAdmin() &&
                !$user->isCrm() &&
                !$user->isSalesManager() &&
                !$user->isSeniorManager() &&
                !$user->isAssistantSalesManager()
            ) {
                return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
            }

            $prospect = $lead->prospects()->latest()->first();
            $hasProspect = $prospect && in_array($prospect->verification_status ?? '', ['pending', 'pending_verification']);

            $lead->load('formFieldValues');
            $existingValues = $lead->getFormFieldsArray();

            if ($prospect) {
                if (!isset($existingValues['purpose']) && $prospect->purpose) {
                    $existingValues['purpose'] = $prospect->purpose === 'end_user' ? 'End Use' : 'Short Term Investment';
                }
                if (!isset($existingValues['preferred_location']) && $prospect->preferred_location) {
                    $existingValues['preferred_location'] = $prospect->preferred_location;
                }
                if (!isset($existingValues['possession']) && $prospect->possession) {
                    $existingValues['possession'] = $prospect->possession;
                }
                if (!isset($existingValues['budget']) && $prospect->budget) {
                    $existingValues['budget'] = $prospect->budget;
                }
            }

            $visibleFields = \App\Models\LeadFormField::active()
                ->visibleToRole('sales_manager')
                ->orderBy('display_order')
                ->get();

            if ($visibleFields->isEmpty()) {
                $visibleFields = \App\Models\LeadFormField::active()->orderBy('display_order')->get();
            }

            $mappedFields = $visibleFields->map(function ($field) {
                return [
                    'key'                 => $field->field_key,
                    'field_key'           => $field->field_key,
                    'label'               => $field->field_label,
                    'field_label'         => $field->field_label,
                    'type'                => $field->field_type,
                    'field_type'          => $field->field_type,
                    'required'            => $field->is_required,
                    'is_required'         => $field->is_required,
                    'options'             => is_array($field->options) ? $field->options : (is_string($field->options) ? json_decode($field->options, true) ?? [] : []),
                    'dependent_field'     => $field->dependent_field,
                    'dependent_conditions'=> $field->dependent_conditions,
                    'placeholder'         => $field->placeholder,
                    'help_text'           => $field->help_text,
                    'display_order'       => $field->display_order,
                ];
            });

            return response()->json([
                'success'          => true,
                'lead_id'          => $lead->id,
                'lead_name'        => $lead->name,
                'lead_phone'       => $lead->phone,
                'lead_email'       => $lead->email,
                'prospect_id'      => $prospect?->id,
                'prospect_status'  => $prospect?->verification_status,
                'has_prospect'     => $hasProspect,
                'form_values'      => $existingValues,
                'form_fields'      => $mappedFields->values()->all(),
            ]);
        } catch (\Exception $e) {
            \Log::error('getLeadRequirementForm error', ['error' => $e->getMessage(), 'lead_id' => $lead->id ?? null]);
            return response()->json(['success' => false, 'error' => 'Failed to load form: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Save lead requirements directly by lead ID (no task required)
     * Used by admin/crm from lead show page
     */
    public function updateLeadRequirements(Request $request, \App\Models\Lead $lead)
    {
        $user = $request->user();
        if (
            !$user->isAdmin() &&
            !$user->isCrm() &&
            !$user->isSalesManager() &&
            !$user->isSeniorManager() &&
            !$user->isAssistantSalesManager()
        ) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'customer_name'      => 'required|string|max:255',
            'phone'              => 'required|string|max:20',
            'email'              => 'nullable|email|max:255',
            'preferred_location' => 'nullable|string|max:255',
            'budget'             => 'nullable|string|max:255',
            'possession'         => 'nullable|string|max:255',
            'purpose'            => 'nullable|string|max:255',
            'lead_quality'       => 'nullable|string|max:50',
            'lead_status'        => 'nullable|string|max:50',
            'form_fields'        => 'nullable|array',
        ]);

        \DB::beginTransaction();
        try {
            $lead->update([
                'name'               => $request->input('customer_name'),
                'phone'              => $request->input('phone'),
                'email'              => $request->input('email'),
                'preferred_location' => $request->input('preferred_location'),
                'budget'             => $request->input('budget'),
            ]);

            // Save dynamic form field values
            if ($request->has('form_fields') && is_array($request->input('form_fields'))) {
                foreach ($request->input('form_fields') as $key => $value) {
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value);
                    } elseif (is_bool($value)) {
                        $value = $value ? '1' : '0';
                    }
                    \App\Models\LeadFormFieldValue::updateOrCreate(
                        ['lead_id' => $lead->id, 'field_key' => $key],
                        ['field_value' => $value, 'updated_by' => $user->id]
                    );
                }
            }

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Lead requirements updated successfully',
                'lead_id' => $lead->id,
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('updateLeadRequirements error', ['error' => $e->getMessage(), 'lead_id' => $lead->id ?? null]);
            return response()->json(['success' => false, 'message' => 'Failed to save: ' . $e->getMessage()], 500);
        }
    }
}
