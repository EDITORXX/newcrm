<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\SiteVisit;
use App\Models\FollowUp;
use App\Models\User;
use App\Models\Incentive;
use App\Models\Target;
use App\Services\TargetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    protected $targetService;

    public function __construct(TargetService $targetService)
    {
        $this->targetService = $targetService;
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $data = [
            'stats' => $this->getStats($user),
            'recent_leads' => $this->getRecentLeads($user),
            'upcoming_followups' => $this->getUpcomingFollowups($user),
            'upcoming_site_visits' => $this->getUpcomingSiteVisits($user),
        ];

        // Add target progress for telecallers
        if ($user->isTelecaller()) {
            $targetProgress = $this->targetService->getTargetProgress($user->id);
            $data['targets'] = $targetProgress;
            $data['incentives'] = $this->getTelecallerIncentives($user);
            $data['incentive_potential'] = $this->getTelecallerIncentivePotential($user);
        }

        // Add team target progress for managers
        if ($user->isSalesManager() || $user->isAssistantSalesManager()) {
            $teamProgress = $this->targetService->getTeamTargetsProgress($user->id);
            $data['team_targets'] = $teamProgress;
            $data['manager_targets'] = $this->getManagerTargetsVsAchievements($user);
            $data['incentives'] = $this->getManagerIncentives($user);
            $data['incentive_potential'] = $this->getManagerIncentivePotential($user);
        }

        // Add Sales Head specific data
        if ($user->isSalesHead()) {
            $data['sales_head_data'] = $this->getSalesHeadData($user);
        }

        // Add system overview for admin/CRM
        if ($user->isAdmin() || $user->isCrm()) {
            $overview = $this->targetService->getSystemOverview();
            $data['target_overview'] = $overview;
        }

        return response()->json($data);
    }

    /**
     * Get Manager/Sales Executive incentives (closer incentives)
     */
    private function getManagerIncentives(User $user)
    {
        $incentives = Incentive::where('user_id', $user->id)
            ->where('type', 'closer')
            ->with(['siteVisit.lead', 'salesHeadVerifiedBy', 'crmVerifiedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pending = $incentives->where('status', 'pending_sales_head')
            ->concat($incentives->where('status', 'pending_crm'))
            ->values();
        
        $verified = $incentives->where('status', 'verified')->values();
        $rejected = $incentives->where('status', 'rejected')->values();

        $totalEarned = $verified->sum('amount');
        $pendingAmount = $pending->sum('amount');

        return [
            'pending' => $pending->map(function ($inc) {
                return [
                    'id' => $inc->id,
                    'amount' => $inc->amount,
                    'status' => $inc->status,
                    'site_visit' => $inc->siteVisit ? [
                        'id' => $inc->siteVisit->id,
                        'customer_name' => $inc->siteVisit->customer_name ?? ($inc->siteVisit->lead->name ?? 'N/A'),
                    ] : null,
                    'created_at' => $inc->created_at ? $inc->created_at->toIso8601String() : null,
                ];
            }),
            'verified' => $verified->map(function ($inc) {
                return [
                    'id' => $inc->id,
                    'amount' => $inc->amount,
                    'status' => $inc->status,
                    'site_visit' => $inc->siteVisit ? [
                        'id' => $inc->siteVisit->id,
                        'customer_name' => $inc->siteVisit->customer_name ?? ($inc->siteVisit->lead->name ?? 'N/A'),
                    ] : null,
                    'sales_head_verified_at' => $inc->sales_head_verified_at ? $inc->sales_head_verified_at->toIso8601String() : null,
                    'crm_verified_at' => $inc->crm_verified_at ? $inc->crm_verified_at->toIso8601String() : null,
                    'created_at' => $inc->created_at ? $inc->created_at->toIso8601String() : null,
                ];
            }),
            'rejected' => $rejected->map(function ($inc) {
                return [
                    'id' => $inc->id,
                    'amount' => $inc->amount,
                    'status' => $inc->status,
                    'rejection_reason' => $inc->rejection_reason,
                    'site_visit' => $inc->siteVisit ? [
                        'id' => $inc->siteVisit->id,
                        'customer_name' => $inc->siteVisit->customer_name ?? ($inc->siteVisit->lead->name ?? 'N/A'),
                    ] : null,
                    'created_at' => $inc->created_at ? $inc->created_at->toIso8601String() : null,
                ];
            }),
            'total_earned' => $totalEarned,
            'pending_amount' => $pendingAmount,
        ];
    }

    /**
     * Get Telecaller incentives (site visit incentives)
     */
    private function getTelecallerIncentives(User $user)
    {
        $incentives = Incentive::where('user_id', $user->id)
            ->where('type', 'site_visit')
            ->with(['siteVisit.lead', 'salesHeadVerifiedBy', 'crmVerifiedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pending = $incentives->where('status', 'pending_sales_head')
            ->concat($incentives->where('status', 'pending_crm'))
            ->values();
        
        $verified = $incentives->where('status', 'verified')->values();
        $rejected = $incentives->where('status', 'rejected')->values();

        $totalEarned = $verified->sum('amount');
        $pendingAmount = $pending->sum('amount');

        return [
            'pending' => $pending->map(function ($inc) {
                return [
                    'id' => $inc->id,
                    'amount' => $inc->amount,
                    'status' => $inc->status,
                    'site_visit' => $inc->siteVisit ? [
                        'id' => $inc->siteVisit->id,
                        'customer_name' => $inc->siteVisit->customer_name ?? ($inc->siteVisit->lead->name ?? 'N/A'),
                    ] : null,
                    'created_at' => $inc->created_at ? $inc->created_at->toIso8601String() : null,
                ];
            }),
            'verified' => $verified->map(function ($inc) {
                return [
                    'id' => $inc->id,
                    'amount' => $inc->amount,
                    'status' => $inc->status,
                    'site_visit' => $inc->siteVisit ? [
                        'id' => $inc->siteVisit->id,
                        'customer_name' => $inc->siteVisit->customer_name ?? ($inc->siteVisit->lead->name ?? 'N/A'),
                    ] : null,
                    'sales_head_verified_at' => $inc->sales_head_verified_at ? $inc->sales_head_verified_at->toIso8601String() : null,
                    'crm_verified_at' => $inc->crm_verified_at ? $inc->crm_verified_at->toIso8601String() : null,
                    'created_at' => $inc->created_at ? $inc->created_at->toIso8601String() : null,
                ];
            }),
            'rejected' => $rejected->map(function ($inc) {
                return [
                    'id' => $inc->id,
                    'amount' => $inc->amount,
                    'status' => $inc->status,
                    'rejection_reason' => $inc->rejection_reason,
                    'site_visit' => $inc->siteVisit ? [
                        'id' => $inc->siteVisit->id,
                        'customer_name' => $inc->siteVisit->customer_name ?? ($inc->siteVisit->lead->name ?? 'N/A'),
                    ] : null,
                    'created_at' => $inc->created_at ? $inc->created_at->toIso8601String() : null,
                ];
            }),
            'total_earned' => $totalEarned,
            'pending_amount' => $pendingAmount,
        ];
    }

    /**
     * Get Manager/Sales Executive incentive potential (target_closers × incentive_per_closer)
     */
    private function getManagerIncentivePotential(User $user)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        $target = Target::where('user_id', $user->id)
            ->whereYear('target_month', $currentMonth->year)
            ->whereMonth('target_month', $currentMonth->month)
            ->first();

        if (!$target || !$target->incentive_per_closer || $target->incentive_per_closer <= 0) {
            return [
                'potential' => 0,
                'target_closers' => $target->target_closers ?? 0,
                'incentive_per_closer' => 0,
            ];
        }

        $targetClosers = $target->target_closers ?? 0;
        $incentivePerCloser = $target->incentive_per_closer;
        $potential = $targetClosers * $incentivePerCloser;

        return [
            'potential' => $potential,
            'target_closers' => $targetClosers,
            'incentive_per_closer' => $incentivePerCloser,
        ];
    }

    /**
     * Get Telecaller incentive potential (target_visits × incentive_per_visit)
     */
    private function getTelecallerIncentivePotential(User $user)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        $target = Target::where('user_id', $user->id)
            ->whereYear('target_month', $currentMonth->year)
            ->whereMonth('target_month', $currentMonth->month)
            ->first();

        if (!$target || !$target->incentive_per_visit || $target->incentive_per_visit <= 0) {
            return [
                'potential' => 0,
                'target_visits' => $target->target_visits ?? 0,
                'incentive_per_visit' => 0,
            ];
        }

        $targetVisits = $target->target_visits ?? 0;
        $incentivePerVisit = $target->incentive_per_visit;
        $potential = $targetVisits * $incentivePerVisit;

        return [
            'potential' => $potential,
            'target_visits' => $targetVisits,
            'incentive_per_visit' => $incentivePerVisit,
        ];
    }

    /**
     * Get Manager's own target vs achievements
     */
    private function getManagerTargetsVsAchievements(User $user)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        
        $target = Target::where('user_id', $user->id)
            ->whereYear('target_month', $currentMonth->year)
            ->whereMonth('target_month', $currentMonth->month)
            ->first();

        if (!$target) {
            return [
                'meetings' => ['target' => 0, 'achieved' => 0, 'percentage' => 0],
                'visits' => ['target' => 0, 'achieved' => 0, 'percentage' => 0],
                'closers' => ['target' => 0, 'achieved' => 0, 'percentage' => 0],
            ];
        }

        $meetingsProgress = $target->getAchievementProgress('meetings');
        $visitsProgress = $target->getAchievementProgress('visits');
        $closersProgress = $target->getAchievementProgress('closers');

        return [
            'meetings' => $meetingsProgress,
            'visits' => $visitsProgress,
            'closers' => $closersProgress,
        ];
    }

    private function getStats($user)
    {
        $leadQuery = Lead::query();
        $siteVisitQuery = SiteVisit::query();
        $followUpQuery = FollowUp::query();

        // Apply role-based filtering
        if ($user->isSalesExecutive() || $user->isAssistantSalesManager()) {
            $leadIds = Lead::whereHas('activeAssignments', function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            })->pluck('id');

            $leadQuery->whereIn('id', $leadIds);
            $siteVisitQuery->where('assigned_to', $user->id);
            $followUpQuery->where('created_by', $user->id);
        } elseif ($user->isSalesHead()) {
            // Sales Head sees all team data including nested teams
            $allTeamMemberIds = $user->getAllTeamMemberIds();
            if (!empty($allTeamMemberIds)) {
                $leadIds = Lead::whereHas('activeAssignments', function ($q) use ($allTeamMemberIds) {
                    $q->whereIn('assigned_to', $allTeamMemberIds);
                })->pluck('id');

                $leadQuery->whereIn('id', $leadIds);
                $siteVisitQuery->whereIn('assigned_to', $allTeamMemberIds);
                $followUpQuery->whereIn('created_by', $allTeamMemberIds);
            } else {
                // No team members, return empty results
                $leadQuery->whereRaw('1 = 0');
                $siteVisitQuery->whereRaw('1 = 0');
                $followUpQuery->whereRaw('1 = 0');
            }
        } elseif ($user->isSalesManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            // Only get leads from verified prospects of team members
            $leadIds = Lead::whereHas('prospects', function ($q) use ($teamMemberIds) {
                $q->whereIn('telecaller_id', $teamMemberIds)
                  ->whereIn('verification_status', ['verified', 'approved']);
            })->pluck('id');

            $leadQuery->whereIn('id', $leadIds);
            $siteVisitQuery->whereIn('assigned_to', $teamMemberIds);
            $followUpQuery->whereIn('created_by', $teamMemberIds);
        }

        return [
            'total_leads' => $leadQuery->count(),
            'new_leads' => (clone $leadQuery)->where('status', 'new')->count(),
            'qualified_leads' => (clone $leadQuery)->where('status', 'qualified')->count(),
            'closed_won' => (clone $leadQuery)->where('status', 'closed_won')->count(),
            'upcoming_site_visits' => $siteVisitQuery->where('status', 'scheduled')
                ->where('scheduled_at', '>=', now())
                ->count(),
            'pending_followups' => $followUpQuery->where('status', 'scheduled')
                ->where('scheduled_at', '>=', now())
                ->count(),
        ];
    }

    private function getRecentLeads($user, $limit = 5)
    {
        $query = Lead::with(['creator', 'activeAssignments.assignedTo']);

        if ($user->isSalesExecutive() || $user->isAssistantSalesManager()) {
            $query->whereHas('activeAssignments', function ($q) use ($user) {
                $q->where('assigned_to', $user->id);
            });
        } elseif ($user->isSalesHead()) {
            $allTeamMemberIds = $user->getAllTeamMemberIds();
            if (!empty($allTeamMemberIds)) {
                $query->whereHas('activeAssignments', function ($q) use ($allTeamMemberIds) {
                    $q->whereIn('assigned_to', $allTeamMemberIds);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->isSalesManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            $query->whereHas('activeAssignments', function ($q) use ($teamMemberIds) {
                $q->whereIn('assigned_to', $teamMemberIds);
            });
        }

        return $query->latest()->limit($limit)->get();
    }

    private function getUpcomingFollowups($user, $limit = 5)
    {
        $query = FollowUp::with(['lead', 'creator'])
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now());

        if ($user->isSalesExecutive() || $user->isAssistantSalesManager()) {
            $query->where('created_by', $user->id);
        } elseif ($user->isSalesHead()) {
            $allTeamMemberIds = $user->getAllTeamMemberIds();
            if (!empty($allTeamMemberIds)) {
                $query->whereIn('created_by', $allTeamMemberIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->isSalesManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            $query->whereIn('created_by', $teamMemberIds);
        }

        return $query->orderBy('scheduled_at')->limit($limit)->get();
    }

    private function getUpcomingSiteVisits($user, $limit = 5)
    {
        $query = SiteVisit::with(['lead', 'assignedTo'])
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now());

        if ($user->isSalesExecutive() || $user->isAssistantSalesManager()) {
            $query->where('assigned_to', $user->id);
        } elseif ($user->isSalesHead()) {
            $allTeamMemberIds = $user->getAllTeamMemberIds();
            if (!empty($allTeamMemberIds)) {
                $query->whereIn('assigned_to', $allTeamMemberIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->isSalesManager()) {
            $teamMemberIds = $user->teamMembers()->pluck('id');
            $query->whereIn('assigned_to', $teamMemberIds);
        }

        return $query->orderBy('scheduled_at')->limit($limit)->get();
    }

    /**
     * Get Sales Head specific data
     */
    private function getSalesHeadData($user)
    {
        $allTeamMemberIds = $user->getAllTeamMemberIds();
        
        // Get all Senior Managers
        $salesManagers = User::where('manager_id', $user->id)
            ->whereHas('role', function($q) {
                $q->where('slug', 'sales_manager');
            })
            ->count();

        // Get all Sales Executives
        $salesExecutives = User::whereIn('manager_id', array_merge([$user->id], User::where('manager_id', $user->id)->pluck('id')->toArray()))
            ->whereHas('role', function($q) {
                $q->where('slug', 'sales_executive');
            })
            ->count();

        // Get all Sales Executives (team)
        $telecallers = User::whereIn('manager_id', array_merge([$user->id], $allTeamMemberIds))
            ->whereHas('role', function($q) {
                $q->where('slug', \App\Models\Role::SALES_EXECUTIVE);
            })
            ->count();

        return [
            'total_managers' => $salesManagers,
            'total_executives' => $salesExecutives,
            'total_telecallers' => $telecallers,
            'pending_verifications' => Lead::where('needs_verification', true)->count(),
        ];
    }
}
