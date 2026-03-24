<?php

namespace App\Http\Controllers\Api\Crm;

use App\Http\Controllers\Controller;
use App\Models\CrmAssignment;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\Meeting;
use App\Models\Prospect;
use App\Models\SiteVisit;
use App\Models\TelecallerTask;
use App\Models\User;
use App\Models\TelecallerDailyLimit;
use App\Models\TelecallerProfile;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get date range based on filter type
     */
    private function getDateRange($dateRange, ?Request $request = null)
    {
        $today = Carbon::today();
        
        switch ($dateRange) {
            case 'today':
                return [$today->copy()->startOfDay(), $today->copy()->endOfDay()];
            case 'yesterday':
                $yesterday = $today->copy()->subDay();
                return [$yesterday->startOfDay(), $yesterday->endOfDay()];
            case 'this_week':
                return [$today->copy()->startOfWeek(), $today->copy()->endOfWeek()];
            case 'this_month':
                return [$today->copy()->startOfMonth(), $today->copy()->endOfMonth()];
            case 'this_year':
                return [$today->copy()->startOfYear(), $today->copy()->endOfYear()];
            case 'custom':
                if ($request && $request->has('start_date') && $request->has('end_date')) {
                    $start = Carbon::parse($request->get('start_date'))->startOfDay();
                    $end = Carbon::parse($request->get('end_date'))->endOfDay();
                    return [$start, $end];
                }
                return [null, null];
            case 'till_date':
            case 'all_time':
            default:
                return [null, null];
        }
    }

    /**
     * Get top 4 stats cards
     */
    public function getStats(Request $request)
    {
        $dateRange = $request->get('date_range', 'all_time');
        [$startDate, $endDate] = $this->getDateRange($dateRange, $request);

        // Total Assigned Leads: Count of all active LeadAssignment records
        $totalAssignedQuery = LeadAssignment::where('is_active', true);
        if ($startDate && $endDate) {
            $totalAssignedQuery->whereBetween('assigned_at', [$startDate, $endDate]);
        }
        $totalAssigned = $totalAssignedQuery->count();

        // Called Leads: Count of all completed TelecallerTask records
        $calledQuery = TelecallerTask::where('status', 'completed');
        if ($startDate && $endDate) {
            $calledQuery->whereBetween('completed_at', [$startDate, $endDate]);
        }
        $called = $calledQuery->count();

        // Interested: Count of verified/approved prospects
        $interestedQuery = Prospect::whereIn('verification_status', ['verified', 'approved']);
        if ($startDate && $endDate) {
            $interestedQuery->whereBetween('verified_at', [$startDate, $endDate]);
        }
        $interested = $interestedQuery->count();

        // Not Interested: Sum of called_not_interested in CrmAssignment + rejected prospects
        $notInterestedCrmQuery = CrmAssignment::where('call_status', 'called_not_interested');
        if ($startDate && $endDate) {
            $notInterestedCrmQuery->whereBetween('assigned_at', [$startDate, $endDate]);
        }
        $notInterestedCrm = $notInterestedCrmQuery->count();

        $notInterestedProspectsQuery = Prospect::where('verification_status', 'rejected');
        if ($startDate && $endDate) {
            $notInterestedProspectsQuery->whereBetween('verified_at', [$startDate, $endDate]);
        }
        $notInterestedProspects = $notInterestedProspectsQuery->count();

        $notInterested = $notInterestedCrm + $notInterestedProspects;

        return response()->json([
            'total_assigned' => $totalAssigned,
            'called' => $called,
            'not_interested' => $notInterested,
            'interested' => $interested,
        ]);
    }

    /**
     * Roles for Sales Executive Performance filter (exclude Admin, CRM)
     */
    public function getPerformanceFilterRoles()
    {
        $roles = Role::where('is_active', true)
            ->whereNotIn('slug', [Role::ADMIN, Role::CRM])
            ->get(['id', 'name', 'slug']);
        return response()->json($roles);
    }

    /**
     * Get telecaller performance stats
     */
    public function getTelecallerStats(Request $request)
    {
        try {
            $dateRange = $request->get('date_range', 'this_month');
            [$startDate, $endDate] = $this->getDateRange($dateRange, $request);
            $roleSlug = $request->get('role_slug', 'all');

            // Sab users dikhane chahiye except Admin, CRM aur Sale Head
            $users = User::with('role')
                ->whereHas('role', function ($q) {
                    $q->whereNotIn('slug', [Role::ADMIN, Role::CRM]);
                })
                ->get()
                ->filter(function ($user) {
                    if ($user->role->slug === Role::SALES_MANAGER && $user->manager_id === null) {
                        return false; // Sale Head - exclude
                    }
                    return true;
                });

            // Filter by role if selected
            if ($roleSlug && $roleSlug !== 'all') {
                $users = $users->filter(function ($user) use ($roleSlug) {
                    return $user->role->slug === $roleSlug;
                });
            }

            $users = $users->values();

            if ($users->isEmpty()) {
                return response()->json([]);
            }

            $result = [];

            foreach ($users as $telecaller) {
                try {
                    $userId = $telecaller->id;

                    // assigned = LeadAssignment count
                    $assignedQuery = LeadAssignment::where('assigned_to', $userId)->where('is_active', true);
                    if ($startDate && $endDate) {
                        $assignedQuery->whereBetween('assigned_at', [$startDate, $endDate]);
                    }
                    $assigned = $assignedQuery->count();

                    // follow_up = FollowUp by this user (created_by), date on scheduled_at or created_at
                    $followUpQuery = FollowUp::where('created_by', $userId);
                    if ($startDate && $endDate) {
                        $followUpQuery->where(function ($q) use ($startDate, $endDate) {
                            $q->whereBetween('scheduled_at', [$startDate, $endDate])
                                ->orWhereBetween('created_at', [$startDate, $endDate]);
                        });
                    }
                    $follow_up = $followUpQuery->count();

                    // meetings = Meeting assigned_to this user
                    $meetingsQuery = Meeting::where('assigned_to', $userId);
                    if ($startDate && $endDate) {
                        $meetingsQuery->whereBetween('scheduled_at', [$startDate, $endDate]);
                    }
                    $meetings = $meetingsQuery->count();

                    // visits = SiteVisit assigned_to this user
                    $visitsQuery = SiteVisit::where('assigned_to', $userId);
                    if ($startDate && $endDate) {
                        $visitsQuery->whereBetween('scheduled_at', [$startDate, $endDate]);
                    }
                    $visits = $visitsQuery->count();

                    // closer = SiteVisit assigned_to, closer_status = verified
                    $closerQuery = SiteVisit::where('assigned_to', $userId)->where('closer_status', 'verified');
                    if ($startDate && $endDate) {
                        $closerQuery->whereBetween('closer_verified_at', [$startDate, $endDate]);
                    }
                    $closer = $closerQuery->count();

                    // pending_tasks = TelecallerTask assigned_to, status pending or rescheduled
                    $pendingTasksQuery = TelecallerTask::where('assigned_to', $userId)
                        ->whereIn('status', ['pending', 'rescheduled']);
                    $pending_tasks = $pendingTasksQuery->count();

                    // overdue_tasks = same but scheduled_at < now
                    $overdueTasksQuery = TelecallerTask::where('assigned_to', $userId)
                        ->whereIn('status', ['pending', 'rescheduled'])
                        ->where('scheduled_at', '<', now());
                    $overdue_tasks = $overdueTasksQuery->count();

                    $result[] = [
                        'telecaller_id' => $userId,
                        'telecaller_name' => $telecaller->name,
                        'username' => $telecaller->name,
                        'assigned' => $assigned,
                        'follow_up' => $follow_up,
                        'meetings' => $meetings,
                        'visits' => $visits,
                        'closer' => $closer,
                        'pending_tasks' => $pending_tasks,
                        'overdue_tasks' => $overdue_tasks,
                    ];
                } catch (\Exception $e) {
                    Log::error('Error processing telecaller stats for user ' . $telecaller->id . ': ' . $e->getMessage());
                    continue;
                }
            }

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error in getTelecallerStats: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load telecaller stats: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get user-wise leads allocated but not yet responded (no call outcome).
     * Same "remaining" logic as admin getLeadsPendingResponseByUser.
     */
    public function getLeadsPendingResponse(Request $request)
    {
        try {
            $dateRange = $request->get('date_range', 'this_month');
            [$startDate, $endDate] = $this->getDateRange($dateRange, $request);

            $users = User::with('role')
                ->where('is_active', true)
                ->whereHas('role', function ($q) {
                    $q->whereNotIn('slug', [Role::ADMIN, Role::CRM]);
                })
                ->get()
                ->filter(function ($user) {
                    if ($user->role && $user->role->slug === Role::SALES_MANAGER && $user->manager_id === null) {
                        return false;
                    }
                    return true;
                })
                ->values();

            if ($users->isEmpty()) {
                return response()->json(['data' => [], 'server_now' => now()->toIso8601String()]);
            }

            $result = [];

            foreach ($users as $user) {
                $userId = $user->id;

                $leadIdsWithCalls = DB::table('telecaller_tasks')
                    ->where('assigned_to', $userId)
                    ->where('status', 'completed')
                    ->distinct()
                    ->pluck('lead_id')
                    ->merge(
                        DB::table('crm_assignments')
                            ->where('assigned_to', $userId)
                            ->where(function ($q) {
                                $q->where('cnp_count', '>', 0)
                                    ->orWhere('call_status', '!=', 'pending');
                            })
                            ->distinct()
                            ->pluck('lead_id')
                    )
                    ->unique()
                    ->values();

                $assignmentsQuery = LeadAssignment::where('assigned_to', $userId)
                    ->where('is_active', true)
                    ->with('lead:id,name,phone');

                if ($leadIdsWithCalls->isNotEmpty()) {
                    $assignmentsQuery->whereNotIn('lead_id', $leadIdsWithCalls);
                }
                if ($startDate && $endDate) {
                    $assignmentsQuery->whereBetween('assigned_at', [$startDate, $endDate]);
                }

                $assignments = $assignmentsQuery->orderBy('assigned_at', 'desc')->get();

                $leads = [];
                foreach ($assignments as $a) {
                    $lead = $a->lead;
                    if (!$lead) {
                        continue;
                    }
                    $leads[] = [
                        'lead_id' => $lead->id,
                        'name' => $lead->name,
                        'phone' => $lead->phone,
                        'assigned_at' => $a->assigned_at?->toIso8601String(),
                    ];
                }

                $result[] = [
                    'user_id' => $userId,
                    'user_name' => $user->name,
                    'pending_count' => count($leads),
                    'leads' => $leads,
                ];
            }

            usort($result, function ($a, $b) {
                return $b['pending_count'] - $a['pending_count'];
            });

            return response()->json([
                'data' => $result,
                'server_now' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getLeadsPendingResponse: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get user-wise average lead response time (assign to first response) for the date range.
     * Same logic as Admin getAverageResponseTimeByUser.
     */
    public function getAverageResponseTime(Request $request)
    {
        try {
            $dateRange = $request->get('date_range', 'this_month');
            [$startDate, $endDate] = $this->getDateRange($dateRange, $request);

            $users = User::with('role')
                ->where('is_active', true)
                ->whereHas('role', function ($q) {
                    $q->whereNotIn('slug', [Role::ADMIN, Role::CRM]);
                })
                ->get()
                ->filter(function ($user) {
                    if ($user->role && $user->role->slug === Role::SALES_MANAGER && $user->manager_id === null) {
                        return false;
                    }
                    return true;
                })
                ->values();

            if ($users->isEmpty()) {
                return response()->json(['data' => [], 'server_now' => now()->toIso8601String()]);
            }

            $result = [];

            foreach ($users as $user) {
                $userId = $user->id;

                $assignmentsQuery = LeadAssignment::where('assigned_to', $userId)
                    ->where('is_active', true);

                if ($startDate && $endDate) {
                    $assignmentsQuery->whereBetween('assigned_at', [$startDate, $endDate]);
                }

                $assignments = $assignmentsQuery->get();
                $responseMinutesList = [];

                foreach ($assignments as $a) {
                    $assignedAt = $a->assigned_at;
                    $leadId = $a->lead_id;

                    $taskFirst = TelecallerTask::where('lead_id', $leadId)
                        ->where('assigned_to', $userId)
                        ->where('status', 'completed')
                        ->min('completed_at');

                    $crmFirst = DB::table('crm_assignments')
                        ->where('lead_id', $leadId)
                        ->where('assigned_to', $userId)
                        ->where(function ($q) {
                            $q->where('cnp_count', '>', 0)
                                ->orWhere('call_status', '!=', 'pending');
                        })
                        ->selectRaw('MIN(COALESCE(called_at, updated_at)) as first_at')
                        ->value('first_at');

                    $firstResponse = null;
                    if ($taskFirst && $crmFirst) {
                        $firstResponse = Carbon::parse($taskFirst)->lt(Carbon::parse($crmFirst)) ? $taskFirst : $crmFirst;
                    } elseif ($taskFirst) {
                        $firstResponse = $taskFirst;
                    } elseif ($crmFirst) {
                        $firstResponse = $crmFirst;
                    }

                    if (!$firstResponse) {
                        continue;
                    }

                    $firstResponseCarbon = Carbon::parse($firstResponse);
                    if ($firstResponseCarbon->lt($assignedAt)) {
                        continue;
                    }

                    $responseMinutesList[] = (int) round($assignedAt->diffInMinutes($firstResponseCarbon));
                }

                $avgMinutes = count($responseMinutesList) > 0
                    ? array_sum($responseMinutesList) / count($responseMinutesList)
                    : 0;
                $result[] = [
                    'user_id' => $userId,
                    'user_name' => $user->name,
                    'avg_response_minutes' => count($responseMinutesList) > 0 ? round($avgMinutes, 1) : 0,
                    'responded_count' => count($responseMinutesList),
                ];
            }

            usort($result, function ($a, $b) {
                $cmp = (int) ($a['avg_response_minutes'] <=> $b['avg_response_minutes']);
                return $cmp !== 0 ? $cmp : strcasecmp($a['user_name'] ?? '', $b['user_name'] ?? '');
            });

            return response()->json([
                'data' => $result,
                'server_now' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getAverageResponseTime: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get daily prospects with filters and pagination
     */
    public function getDailyProspects(Request $request)
    {
        $dateRange = $request->get('date_range', 'all_time');
        [$startDate, $endDate] = $this->getDateRange($dateRange, $request);
        
        $query = Prospect::with(['createdBy', 'assignedManager', 'assignment']);

        // Date filter
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id !== 'all') {
            $query->where('created_by', $request->user_id);
        }

        // Filter by verification status
        if ($request->has('verification_status') && $request->verification_status !== 'all') {
            $query->where('verification_status', $request->verification_status);
        }

        // Get total count before pagination
        $total = $query->count();

        // Pagination
        $perPage = $request->get('per_page', 50);
        $page = $request->get('page', 1);
        
        $prospects = $query->latest()
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // Calculate response time and format data
        $formattedProspects = $prospects->map(function($prospect) {
            $responseTime = null;
            if ($prospect->verified_at && $prospect->created_at) {
                $seconds = $prospect->verified_at->diffInSeconds($prospect->created_at);
                $responseTime = $this->formatResponseTime($seconds);
            }

            return [
                'id' => $prospect->id,
                'customer_name' => $prospect->customer_name,
                'phone' => $prospect->phone,
                'budget' => $prospect->budget,
                'preferred_location' => $prospect->preferred_location,
                'size' => $prospect->size,
                'purpose' => $prospect->purpose,
                'possession' => $prospect->possession,
                'notes' => $prospect->notes,
                'employee_remark' => $prospect->employee_remark,
                'manager_remark' => $prospect->manager_remark,
                'verification_status' => $prospect->verification_status,
                'verified_at' => $prospect->verified_at?->format('Y-m-d H:i:s'),
                'created_at' => $prospect->created_at->format('Y-m-d H:i:s'),
                'created_by_name' => $prospect->createdBy->name ?? null,
                'assigned_manager_name' => $prospect->assignedManager->name ?? null,
                'response_time' => $responseTime,
                'response_time_seconds' => $prospect->verified_at && $prospect->created_at 
                    ? $prospect->verified_at->diffInSeconds($prospect->created_at) 
                    : null,
            ];
        });

        // Stats
        $statsQuery = Prospect::query();
        if ($startDate && $endDate) {
            $statsQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        if ($request->has('user_id') && $request->user_id !== 'all') {
            $statsQuery->where('created_by', $request->user_id);
        }

        $stats = [
            'total' => (clone $statsQuery)->count(),
            'pending_verification' => (clone $statsQuery)->where('verification_status', 'pending_verification')->count(),
            'verified' => (clone $statsQuery)->where('verification_status', 'verified')->count(),
            'rejected' => (clone $statsQuery)->where('verification_status', 'rejected')->count(),
        ];

        // Stats by user
        $statsByUserQuery = Prospect::query()
            ->select('created_by', DB::raw('COUNT(*) as count'))
            ->groupBy('created_by')
            ->with('createdBy');
        
        if ($startDate && $endDate) {
            $statsByUserQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $statsByUser = $statsByUserQuery->get()->map(function($item) {
            return [
                'user_id' => $item->created_by,
                'username' => $item->createdBy->name ?? 'Unknown',
                'count' => $item->count,
            ];
        });

        // Daily breakdown
        $dailyBreakdownQuery = Prospect::query()
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('GROUP_CONCAT(DISTINCT CONCAT(createdBy.name, ":", COUNT(*)) SEPARATOR ", ") as users')
            )
            ->groupBy('date', 'created_by')
            ->with('createdBy');
        
        if ($startDate && $endDate) {
            $dailyBreakdownQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        // Simplified daily breakdown - group by date only
        $dailyBreakdown = DB::table('prospects')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->when($startDate && $endDate, function($q) use ($startDate, $endDate) {
                return $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function($item) {
                // Get users for this date
                $users = Prospect::whereDate('created_at', $item->date)
                    ->select('created_by', DB::raw('COUNT(*) as user_count'))
                    ->groupBy('created_by')
                    ->with('createdBy')
                    ->get()
                    ->map(function($u) {
                        return ($u->createdBy->name ?? 'Unknown') . ':' . $u->user_count;
                    })
                    ->toArray();

                return [
                    'date' => $item->date,
                    'count' => $item->count,
                    'users' => $users,
                ];
            });

        return response()->json([
            'data' => $formattedProspects,
            'stats' => $stats,
            'stats_by_user' => $statsByUser,
            'daily_breakdown' => $dailyBreakdown,
            'pagination' => [
                'current_page' => (int) $page,
                'per_page' => (int) $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
                'from' => (($page - 1) * $perPage) + 1,
                'to' => min($page * $perPage, $total),
            ],
        ]);
    }

    /**
     * Format response time in human readable format
     */
    private function formatResponseTime($seconds)
    {
        if ($seconds < 60) {
            return $seconds . ' seconds';
        } elseif ($seconds < 3600) {
            $minutes = floor($seconds / 60);
            return $minutes . ' minute' . ($minutes > 1 ? 's' : '');
        } elseif ($seconds < 86400) {
            $hours = floor($seconds / 3600);
            $minutes = floor(($seconds % 3600) / 60);
            $result = $hours . ' hour' . ($hours > 1 ? 's' : '');
            if ($minutes > 0) {
                $result .= ' ' . $minutes . ' minute' . ($minutes > 1 ? 's' : '');
            }
            return $result;
        } else {
            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            $result = $days . ' day' . ($days > 1 ? 's' : '');
            if ($hours > 0) {
                $result .= ' ' . $hours . ' hour' . ($hours > 1 ? 's' : '');
            }
            return $result;
        }
    }
}
