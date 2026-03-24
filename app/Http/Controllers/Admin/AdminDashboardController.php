<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\User;
use App\Models\Project;
use App\Models\ActivityLog;
use App\Models\ImportBatch;
use App\Models\SiteVisit;
use App\Models\Meeting;
use App\Models\Role;
use App\Models\LeadAssignment;
use App\Models\CrmAssignment;
use App\Models\TelecallerTask;
use App\Models\Prospect;
use App\Services\TargetService;
use App\Services\CallLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    protected $targetService;
    protected $callLogService;

    public function __construct(TargetService $targetService, CallLogService $callLogService)
    {
        $this->targetService = $targetService;
        $this->callLogService = $callLogService;
    }

    public function dashboard()
    {
        $user = auth()->user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        return view('admin.dashboard');
    }

    public function profile()
    {
        $user = auth()->user();
        
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized access');
        }

        return view('admin.profile');
    }

    public function getDashboardData(Request $request)
    {
        try {
            $user = auth()->user();
            
            if (!$user || !$user->isAdmin()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Get date range from request
            $dateRange = $this->getDateRange($request);

            try {
                $targetOverview = $this->targetService->getSystemOverview();
            } catch (\Exception $e) {
                Log::warning('Failed to get target overview: ' . $e->getMessage());
                $targetOverview = [
                    'month' => now()->format('Y-m'),
                    'total_users' => 0,
                    'targets' => ['prospects_extract' => 0, 'prospects_verified' => 0, 'calls' => 0],
                    'actuals' => ['prospects_extract' => 0, 'prospects_verified' => 0, 'calls' => 0],
                    'percentages' => ['prospects_extract' => 0, 'prospects_verified' => 0, 'calls' => 0],
                    'details' => [],
                ];
            }

            // Get visits/meetings filter from request
            $visitsMeetingsFilter = $request->get('visits_meetings_filter', 'this_month');

            $data = [
                'system_stats' => $this->getSystemStats($dateRange),
                'user_stats' => $this->getUserStats($dateRange),
                'lead_stats' => $this->getLeadStats($dateRange),
                'project_stats' => $this->getProjectStats($dateRange),
                'activity_summary' => $this->getActivitySummary($dateRange),
                'system_health' => $this->getSystemHealth($dateRange),
                'target_overview' => $targetOverview,
                'recent_leads' => $this->getRecentLeads(10, $dateRange),
                'recent_activities' => $this->getRecentActivities(20, $dateRange),
                'agents_visits_meetings' => $this->getAgentsVisitsVsMeetings($dateRange),
                'property_segments' => $this->getPropertySegments($dateRange),
                'telecaller_performance' => $this->getTelecallerPerformance($dateRange),
                'leads_pending_response' => $this->getLeadsPendingResponseByUser($dateRange),
                'average_response_time_by_user' => $this->getAverageResponseTimeByUser($dateRange),
                'user_visits_meetings' => $this->getUserVisitsMeetingsData($visitsMeetingsFilter),
                'call_statistics' => $this->getCallStatistics($dateRange),
                'server_now' => now()->toIso8601String(),
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            Log::error('Admin Dashboard Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $request->user()?->id,
            ]);
            
            return response()->json([
                'error' => 'Failed to load dashboard data',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred while loading dashboard data.',
            ], 500);
        }
    }

    /**
     * Get date range from request
     */
    private function getDateRange(Request $request): array
    {
        $filter = $request->get('filter', 'month'); // Default to 'month'
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // If custom dates provided, use them
        if ($startDate && $endDate) {
            return [
                'start_date' => Carbon::parse($startDate)->startOfDay(),
                'end_date' => Carbon::parse($endDate)->endOfDay(),
            ];
        }

        // Calculate based on filter type
        switch ($filter) {
            case 'today':
                return [
                    'start_date' => Carbon::today()->startOfDay(),
                    'end_date' => Carbon::today()->endOfDay(),
                ];
            case 'week':
                return [
                    'start_date' => Carbon::now()->startOfWeek()->startOfDay(),
                    'end_date' => Carbon::now()->endOfWeek()->endOfDay(),
                ];
            case 'month':
                return [
                    'start_date' => Carbon::now()->startOfMonth()->startOfDay(),
                    'end_date' => Carbon::now()->endOfMonth()->endOfDay(),
                ];
            case 'year':
                return [
                    'start_date' => Carbon::now()->startOfYear()->startOfDay(),
                    'end_date' => Carbon::now()->endOfYear()->endOfDay(),
                ];
            default:
                return [
                    'start_date' => Carbon::now()->startOfMonth()->startOfDay(),
                    'end_date' => Carbon::now()->endOfMonth()->endOfDay(),
                ];
        }
    }

    private function getSystemStats(?array $dateRange = null)
    {
        $query = Lead::query();
        $visitsQuery = SiteVisit::query();
        $meetingsQuery = Meeting::query()
            ->where('is_converted', false); // Exclude converted meetings
        $closersQuery = SiteVisit::where('closer_status', 'verified');
        $deadQuery = Lead::where('is_dead', true);

        if ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
            $visitsQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
            $meetingsQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
            $closersQuery->whereBetween('closer_verified_at', [$dateRange['start_date'], $dateRange['end_date']]);
            $deadQuery->whereBetween('marked_dead_at', [$dateRange['start_date'], $dateRange['end_date']]);
        }

        return [
            'total_leads' => $query->count(),
            'total_visits' => $visitsQuery->count(),
            'total_meetings' => $meetingsQuery->count(),
            'total_closers' => $closersQuery->count(),
            'total_dead' => $deadQuery->count(),
        ];
    }

    private function getUserStats(?array $dateRange = null)
    {
        $usersQuery = User::where('is_active', true);
        
        if ($dateRange) {
            $usersQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
        }

        $usersByRole = $usersQuery->with('role')
            ->get()
            ->groupBy(function($user) {
                return $user->role->slug ?? 'no_role';
            })
            ->map(function($group) {
                return $group->count();
            });

        $totalQuery = User::where('is_active', true);
        $newQuery = User::where('is_active', true);
        $active24hQuery = User::where('is_active', true);

        if ($dateRange) {
            $totalQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
            $newQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
            $active24hQuery->whereBetween('updated_at', [$dateRange['start_date'], $dateRange['end_date']]);
        } else {
            $newQuery->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year);
            $active24hQuery->where('updated_at', '>=', Carbon::now()->subDay());
        }

        return [
            'total' => $totalQuery->count(),
            'by_role' => [
                'admin' => $usersByRole->get('admin', 0),
                'crm' => $usersByRole->get('crm', 0),
                'sales_manager' => $usersByRole->get('sales_manager', 0),
                'sales_executive' => $usersByRole->get('sales_executive', 0),
                'telecaller' => $usersByRole->get('sales_executive', 0), // merged into sales_executive
            ],
            'new_this_month' => $newQuery->count(),
            'active_24h' => $active24hQuery->count(),
        ];
    }

    private function getLeadStats(?array $dateRange = null)
    {
        $query = Lead::query();
        
        if ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
        }

        $leadsByStatus = (clone $query)->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $newTodayQuery = Lead::query();
        $newWeekQuery = Lead::query();
        $newMonthQuery = Lead::query();

        if ($dateRange) {
            $newTodayQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
            $newWeekQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
            $newMonthQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
        } else {
            $newTodayQuery->whereDate('created_at', Carbon::today());
            $newWeekQuery->where('created_at', '>=', Carbon::now()->startOfWeek());
            $newMonthQuery->whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year);
        }

        return [
            'total' => $query->count(),
            'by_status' => [
                'new' => $leadsByStatus->get('new', 0),
                'contacted' => $leadsByStatus->get('contacted', 0),
                'connected' => $leadsByStatus->get('connected', 0),
                'verified_prospect' => $leadsByStatus->get('verified_prospect', 0),
                'meeting_scheduled' => $leadsByStatus->get('meeting_scheduled', 0),
                'meeting_completed' => $leadsByStatus->get('meeting_completed', 0),
                'visit_scheduled' => $leadsByStatus->get('visit_scheduled', 0),
                'visit_done' => $leadsByStatus->get('visit_done', 0),
                'revisited_scheduled' => $leadsByStatus->get('revisited_scheduled', 0),
                'revisited_completed' => $leadsByStatus->get('revisited_completed', 0),
                'closed' => $leadsByStatus->get('closed', 0),
                'dead' => $leadsByStatus->get('dead', 0),
                'on_hold' => $leadsByStatus->get('on_hold', 0),
            ],
            'new_today' => $newTodayQuery->count(),
            'new_this_week' => $newWeekQuery->count(),
            'new_this_month' => $newMonthQuery->count(),
        ];
    }

    private function getProjectStats(?array $dateRange = null)
    {
        $query = Project::query();
        
        if ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
        }

        return [
            'total' => $query->count(),
            'active' => (clone $query)->where('is_active', true)->count(),
            'inactive' => (clone $query)->where('is_active', false)->count(),
        ];
    }

    private function getActivitySummary(?array $dateRange = null)
    {
        $activitiesQuery = ActivityLog::with('user');
        
        if ($dateRange) {
            $activitiesQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
        }

        $recentActivities = $activitiesQuery->latest()
            ->limit(50)
            ->get();

        $activitiesByType = $recentActivities->groupBy('action')
            ->map(function($group) {
                return $group->count();
            });

        $mostActiveUsersQuery = ActivityLog::select('user_id', DB::raw('count(*) as count'));
        
        if ($dateRange) {
            $mostActiveUsersQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
        } else {
            $mostActiveUsersQuery->where('created_at', '>=', Carbon::now()->subDays(7));
        }

        $mostActiveUsers = $mostActiveUsersQuery->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->with('user')
            ->get();

        return [
            'recent_count' => $recentActivities->count(),
            'by_type' => $activitiesByType->toArray(),
            'most_active_users' => $mostActiveUsers->map(function($log) {
                return [
                    'user_id' => $log->user_id,
                    'user_name' => ($log->user && $log->user->name) ? $log->user->name : 'Unknown',
                    'count' => $log->count ?? 0,
                ];
            }),
        ];
    }

    private function getSystemHealth(?array $dateRange = null)
    {
        $pendingVerificationsQuery = Lead::where('needs_verification', true);
        $activeAutomationsQuery = \Illuminate\Database\Eloquent\Collection::make();
        $pendingImportsQuery = ImportBatch::whereIn('status', ['pending', 'processing']);
        $failedImportsQuery = ImportBatch::where('status', 'failed');

        if ($dateRange) {
            $pendingVerificationsQuery->whereBetween('verification_requested_at', [$dateRange['start_date'], $dateRange['end_date']]);
            $pendingImportsQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
            $failedImportsQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
        } else {
            $failedImportsQuery->where('created_at', '>=', Carbon::now()->subDays(7));
        }

        return [
            'pending_verifications' => $pendingVerificationsQuery->count(),
            'active_automations'    => 0,
            'pending_imports' => $pendingImportsQuery->count(),
            'failed_imports' => $failedImportsQuery->count(),
        ];
    }

    private function getRecentLeads($limit = 10, ?array $dateRange = null)
    {
        $query = Lead::with(['creator', 'activeAssignments.assignedTo']);
        
        if ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
        }

        return $query->latest()
            ->limit($limit)
            ->get()
            ->map(function($lead) {
                return [
                    'id' => $lead->id,
                    'name' => $lead->name ?? 'N/A',
                    'phone' => $lead->phone ?? 'N/A',
                    'status' => $lead->status ?? 'N/A',
                    'created_at' => $lead->created_at ? $lead->created_at->format('Y-m-d H:i:s') : 'N/A',
                    'created_by' => ($lead->creator && $lead->creator->name) ? $lead->creator->name : 'System',
                ];
            })
            ->filter(function($lead) {
                return $lead !== null;
            })
            ->values();
    }

    private function getRecentActivities($limit = 20, ?array $dateRange = null)
    {
        $query = ActivityLog::with('user');
        
        if ($dateRange) {
            $query->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
        }

        return $query->latest()
            ->limit($limit)
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->id,
                    'user_name' => ($log->user && $log->user->name) ? $log->user->name : 'System',
                    'action' => $log->action ?? 'N/A',
                    'description' => $log->description ?? 'N/A',
                    'created_at' => $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : 'N/A',
                ];
            })
            ->filter(function($log) {
                return $log !== null;
            })
            ->values();
    }

    /**
     * Get agents visits vs meetings data for Senior Managers and Sales Executives
     */
    private function getAgentsVisitsVsMeetings(?array $dateRange = null): array
    {
        // Get Senior Manager and Sales Executive roles
        $salesManagerRole = Role::where('slug', 'sales_manager')->first();
        $salesExecutiveRole = Role::where('slug', 'sales_executive')->first();

        if (!$salesManagerRole || !$salesExecutiveRole) {
            return [];
        }

        // Get all active Senior Managers and Sales Executives
        $agents = User::where('is_active', true)
            ->whereIn('role_id', [$salesManagerRole->id, $salesExecutiveRole->id])
            ->with('role')
            ->get();

        $result = [];

        foreach ($agents as $agent) {
            // Count meetings assigned to this agent
            $meetingsQuery = Meeting::where('assigned_to', $agent->id)
                ->where('is_converted', false); // Exclude converted meetings
            if ($dateRange) {
                $meetingsQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
            }
            $meetingsCount = $meetingsQuery->count();

            // Count site visits assigned to this agent
            $visitsQuery = SiteVisit::where('assigned_to', $agent->id);
            if ($dateRange) {
                $visitsQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
            }
            $visitsCount = $visitsQuery->count();

            // Count closers (verified closers) assigned to this agent
            $closersQuery = SiteVisit::where('assigned_to', $agent->id)
                ->where('closer_status', 'verified');
            if ($dateRange) {
                $closersQuery->whereBetween('closer_verified_at', [$dateRange['start_date'], $dateRange['end_date']]);
            }
            $closersCount = $closersQuery->count();

            $result[] = [
                'agent_id' => $agent->id,
                'agent_name' => $agent->name,
                'role' => $agent->role ? $agent->role->name : 'Unknown',
                'role_slug' => $agent->role ? $agent->role->slug : 'unknown',
                'meetings' => $meetingsCount,
                'visits' => $visitsCount,
                'closers' => $closersCount,
            ];
        }

        // Sort by total activity (meetings + visits + closers) descending
        usort($result, function($a, $b) {
            $totalA = $a['meetings'] + $a['visits'] + $a['closers'];
            $totalB = $b['meetings'] + $b['visits'] + $b['closers'];
            return $totalB - $totalA;
        });

        return $result;
    }

    /**
     * Get property segments distribution
     */
    private function getPropertySegments(?array $dateRange = null): array
    {
        $plotQuery = Lead::where('property_type', 'plot');
        $commercialQuery = Lead::where('property_type', 'commercial');
        $residentialQuery = Lead::whereIn('property_type', ['apartment', 'villa']);
        $otherQuery = Lead::where(function($query) {
            $query->where('property_type', 'other')
                  ->orWhereNull('property_type');
        });

        if ($dateRange) {
            $plotQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
            $commercialQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
            $residentialQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
            $otherQuery->whereBetween('created_at', [$dateRange['start_date'], $dateRange['end_date']]);
        }

        return [
            'plot' => $plotQuery->count(),
            'commercial' => $commercialQuery->count(),
            'residential' => $residentialQuery->count(),
            'other' => $otherQuery->count(),
        ];
    }

    /**
     * Get telecaller performance metrics - Same logic as CRM API
     */
    private function getTelecallerPerformance(?array $dateRange = null): array
    {
        $salesExecutiveRole = Role::where('slug', Role::SALES_EXECUTIVE)->first();
        
        if (!$salesExecutiveRole) {
            Log::warning('Sales Executive role not found');
            return [];
        }

        $telecallers = User::where('is_active', true)
            ->where('role_id', $salesExecutiveRole->id)
            ->get();

        if ($telecallers->isEmpty()) {
            Log::info('No active telecallers found');
            return [];
        }
        
        Log::info('Found ' . $telecallers->count() . ' telecallers for performance calculation');

        $result = [];
        $startDate = $dateRange['start_date'] ?? null;
        $endDate = $dateRange['end_date'] ?? null;

        foreach ($telecallers as $telecaller) {
            try {
                $userId = $telecaller->id;

                // Allocated - use LeadAssignment table for actual assigned count
                $allocatedQuery = LeadAssignment::where('assigned_to', $userId)
                    ->where('is_active', true);
                if ($startDate && $endDate) {
                    $allocatedQuery->whereBetween('assigned_at', [$startDate, $endDate]);
                }
                $allocated = $allocatedQuery->count();

                // Base query for CrmAssignment stats
                $baseQuery = CrmAssignment::where('assigned_to', $userId);
                if ($startDate && $endDate) {
                    $baseQuery->whereBetween('assigned_at', [$startDate, $endDate]);
                }

                // Remaining: Leads where call has NOT been made yet
                $leadIdsWithCalls = DB::table('telecaller_tasks')
                    ->where('assigned_to', $userId)
                    ->where('status', 'completed')
                    ->distinct()
                    ->pluck('lead_id')
                    ->merge(
                        DB::table('crm_assignments')
                            ->where('assigned_to', $userId)
                            ->where(function($q) {
                                $q->where('cnp_count', '>', 0)
                                  ->orWhere('call_status', '!=', 'pending');
                            })
                            ->distinct()
                            ->pluck('lead_id')
                    )
                    ->unique()
                    ->values();
                
                $remainingQuery = LeadAssignment::where('assigned_to', $userId)
                    ->where('is_active', true);
                
                if ($leadIdsWithCalls->isNotEmpty()) {
                    $remainingQuery->whereNotIn('lead_id', $leadIdsWithCalls);
                }
                
                if ($startDate && $endDate) {
                    $remainingQuery->whereBetween('assigned_at', [$startDate, $endDate]);
                }
                $remaining = $remainingQuery->count();

                // Called: Count of completed TelecallerTask records (actual calls made)
                $calledQuery = TelecallerTask::where('assigned_to', $userId)
                    ->where('status', 'completed');
                if ($startDate && $endDate) {
                    $calledQuery->whereBetween('completed_at', [$startDate, $endDate]);
                }
                $called = $calledQuery->count();

                // Interested: Approved prospects created by this telecaller
                $interestedQuery = Prospect::where('telecaller_id', $userId)
                    ->where('verification_status', 'approved');
                if ($startDate && $endDate) {
                    $interestedQuery->whereBetween('verified_at', [$startDate, $endDate]);
                }
                $interested = $interestedQuery->count();

                // Not Interested: Sum of called_not_interested in CrmAssignment + rejected prospects
                $notInterestedCrm = (clone $baseQuery)
                    ->where('call_status', 'called_not_interested')
                    ->count();
                
                $notInterestedProspectsQuery = Prospect::where('telecaller_id', $userId)
                    ->where('verification_status', 'rejected');
                if ($startDate && $endDate) {
                    $notInterestedProspectsQuery->whereBetween('verified_at', [$startDate, $endDate]);
                }
                $notInterestedProspects = $notInterestedProspectsQuery->count();
                
                $notInterested = $notInterestedCrm + $notInterestedProspects;

                // CNP: Pending calls with cnp_count > 0 (call later status)
                $cnp = (clone $baseQuery)
                    ->where('call_status', 'pending')
                    ->where('cnp_count', '>', 0)
                    ->count();

                $result[] = [
                    'telecaller_id' => $userId,
                    'telecaller_name' => $telecaller->name,
                    'allocated' => $allocated ?? 0,
                    'called' => $called ?? 0,
                    'remaining' => $remaining ?? 0,
                    'interested' => $interested ?? 0,
                    'not_interested' => $notInterested ?? 0,
                    'cnp' => $cnp ?? 0,
                    'follow_up' => 0, // Not used in CRM dashboard
                ];
            } catch (\Exception $e) {
                \Log::error('Error processing telecaller stats for user ' . $telecaller->id . ': ' . $e->getMessage());
                continue;
            }
        }

        // Sort by allocated (descending)
        usort($result, function($a, $b) {
            return $b['allocated'] - $a['allocated'];
        });

        return $result;
    }

    /**
     * Get user-wise leads that are allocated but not yet responded (no call outcome).
     * Same "remaining" logic as getTelecallerPerformance. Includes all users except Admin, CRM, Sales Head.
     */
    private function getLeadsPendingResponseByUser(?array $dateRange = null): array
    {
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
            return [];
        }

        $startDate = $dateRange['start_date'] ?? null;
        $endDate = $dateRange['end_date'] ?? null;
        $result = [];

        foreach ($users as $user) {
            $userId = $user->id;

            // Lead IDs where user has already responded (completed task or CrmAssignment with outcome)
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

        // Sort by pending_count descending
        usort($result, function ($a, $b) {
            return $b['pending_count'] - $a['pending_count'];
        });

        return $result;
    }

    /**
     * Get user-wise average lead response time (assign to first response) for the date range.
     * Only includes Sales Executives who have at least one responded lead in the period.
     */
    private function getAverageResponseTimeByUser(?array $dateRange = null): array
    {
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
            return [];
        }

        $startDate = $dateRange['start_date'] ?? null;
        $endDate = $dateRange['end_date'] ?? null;
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

        return $result;
    }

    /**
     * Get user visits and meetings data with date filters
     */
    private function getUserVisitsMeetingsData(string $filter = 'this_month'): array
    {
        // Get Senior Manager and Sales Executive roles
        $salesManagerRole = Role::where('slug', 'sales_manager')->first();
        $salesExecutiveRole = Role::where('slug', 'sales_executive')->first();

        if (!$salesManagerRole || !$salesExecutiveRole) {
            return [
                'users' => [],
                'summary' => [
                    'total_users' => 0,
                    'total_visits' => 0,
                    'total_meetings' => 0,
                ],
            ];
        }

        // Get all active Senior Managers and Sales Executives
        $users = User::where('is_active', true)
            ->whereIn('role_id', [$salesManagerRole->id, $salesExecutiveRole->id])
            ->with('role')
            ->get();

        // Calculate date range based on filter
        $dateRange = $this->getVisitsMeetingsDateRange($filter);

        $result = [];
        $totalVisits = 0;
        $totalMeetings = 0;

        foreach ($users as $user) {
            // Count visits assigned to this user within date range
            $visitsQuery = SiteVisit::where('assigned_to', $user->id);
            if ($dateRange) {
                $visitsQuery->whereBetween('scheduled_at', [$dateRange['start_date'], $dateRange['end_date']]);
            }
            $visitsCount = $visitsQuery->count();

            // Count meetings assigned to this user within date range
            $meetingsQuery = Meeting::where('assigned_to', $user->id)
                ->where('is_converted', false); // Exclude converted meetings
            if ($dateRange) {
                $meetingsQuery->whereBetween('scheduled_at', [$dateRange['start_date'], $dateRange['end_date']]);
            }
            $meetingsCount = $meetingsQuery->count();

            $totalVisits += $visitsCount;
            $totalMeetings += $meetingsCount;

            $result[] = [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'role' => $user->role ? $user->role->name : 'Unknown',
                'role_slug' => $user->role ? $user->role->slug : 'unknown',
                'visits_count' => $visitsCount,
                'meetings_count' => $meetingsCount,
                'total' => $visitsCount + $meetingsCount,
            ];
        }

        // Sort by total (descending)
        usort($result, function($a, $b) {
            return $b['total'] - $a['total'];
        });

        return [
            'users' => $result,
            'summary' => [
                'total_users' => count($result),
                'total_visits' => $totalVisits,
                'total_meetings' => $totalMeetings,
            ],
        ];
    }

    /**
     * Get date range for visits/meetings filter
     */
    private function getCallStatistics(?array $dateRange = null): array
    {
        try {
            // Determine date range for call statistics
            $dateRangeStr = 'today';
            if ($dateRange) {
                $start = Carbon::parse($dateRange['start']);
                $end = Carbon::parse($dateRange['end']);
                $now = Carbon::now();
                
                if ($start->isToday() && $end->isToday()) {
                    $dateRangeStr = 'today';
                } elseif ($start->isCurrentWeek() && $end->isCurrentWeek()) {
                    $dateRangeStr = 'this_week';
                } elseif ($start->isCurrentMonth() && $end->isCurrentMonth()) {
                    $dateRangeStr = 'this_month';
                }
            }
            
            $stats = $this->callLogService->getSystemCallStatistics($dateRangeStr);
            
            // Get recent calls (last 10)
            $recentCalls = \App\Models\CallLog::with(['lead:id,name,phone', 'user:id,name', 'telecaller:id,name'])
                ->orderBy('start_time', 'desc')
                ->limit(10)
                ->get()
                ->map(function($call) {
                    return [
                        'id' => $call->id,
                        'phone_number' => $call->phone_number,
                        'lead_name' => $call->lead->name ?? 'N/A',
                        'user_name' => $call->callerUser->name ?? 'N/A',
                        'duration' => $call->formatted_duration,
                        'call_type' => $call->call_type_label,
                        'status' => $call->status_label,
                        'outcome' => $call->call_outcome_label,
                        'start_time' => $call->start_time ? $call->start_time->format('Y-m-d H:i:s') : null,
                    ];
                });
            
            $stats['recent_calls'] = $recentCalls;
            
            return $stats;
        } catch (\Exception $e) {
            Log::error('Failed to get call statistics: ' . $e->getMessage());
            return [
                'total_calls' => 0,
                'completed_calls' => 0,
                'total_duration' => 0,
                'formatted_duration' => '0s',
                'average_duration' => 0,
                'formatted_average_duration' => '0s',
                'connection_rate' => 0,
                'calls_by_role' => [],
                'top_users' => [],
                'outcome_distribution' => [],
                'recent_calls' => [],
            ];
        }
    }

    private function getVisitsMeetingsDateRange(string $filter): ?array
    {
        switch ($filter) {
            case 'today':
                return [
                    'start_date' => Carbon::today()->startOfDay(),
                    'end_date' => Carbon::today()->endOfDay(),
                ];
            case 'tomorrow':
                return [
                    'start_date' => Carbon::tomorrow()->startOfDay(),
                    'end_date' => Carbon::tomorrow()->endOfDay(),
                ];
            case 'this_weekend':
                $now = Carbon::now();
                // Get this week's Saturday and Sunday
                if ($now->dayOfWeek === Carbon::SATURDAY) {
                    // Today is Saturday, use today and tomorrow
                    $saturday = $now->copy()->startOfDay();
                    $sunday = $now->copy()->addDay()->endOfDay();
                } elseif ($now->dayOfWeek === Carbon::SUNDAY) {
                    // Today is Sunday, use yesterday and today
                    $saturday = $now->copy()->subDay()->startOfDay();
                    $sunday = $now->copy()->endOfDay();
                } else {
                    // Get upcoming weekend
                    $saturday = $now->copy()->next(Carbon::SATURDAY)->startOfDay();
                    $sunday = $saturday->copy()->addDay()->endOfDay();
                }
                
                return [
                    'start_date' => $saturday,
                    'end_date' => $sunday,
                ];
            case 'this_month':
                return [
                    'start_date' => Carbon::now()->startOfMonth()->startOfDay(),
                    'end_date' => Carbon::now()->endOfMonth()->endOfDay(),
                ];
            default:
                return [
                    'start_date' => Carbon::now()->startOfMonth()->startOfDay(),
                    'end_date' => Carbon::now()->endOfMonth()->endOfDay(),
                ];
        }
    }
}

