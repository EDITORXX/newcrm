<?php

namespace App\Services;

use App\Models\Target;
use App\Models\User;
use App\Models\Prospect;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TargetService
{
    /**
     * Set or update targets for a user for a specific month
     */
    public function setTargetsForUser(int $userId, string $month, array $targets): Target
    {
        // Parse month (format: YYYY-MM)
        $targetMonth = Carbon::parse($month . '-01')->startOfMonth();

        return Target::updateOrCreate(
            [
                'user_id' => $userId,
                'target_month' => $targetMonth,
            ],
            [
                'target_visits' => $targets['target_visits'] ?? 0,
                'target_meetings' => $targets['target_meetings'] ?? 0,
                'target_closers' => $targets['target_closers'] ?? 0,
                'target_prospects_extract' => $targets['target_prospects_extract'] ?? 0,
                'target_prospects_verified' => $targets['target_prospects_verified'] ?? 0,
                'target_calls' => $targets['target_calls'] ?? 0,
                'manager_target_calculation_logic' => $targets['manager_target_calculation_logic'] ?? null,
                'manager_junior_scope' => $targets['manager_junior_scope'] ?? null,
                'incentive_per_closer' => $targets['incentive_per_closer'] ?? null,
                'incentive_per_visit' => $targets['incentive_per_visit'] ?? null,
            ]
        );
    }

    /**
     * Get target progress for a user for a specific month
     */
    public function getTargetProgress(int $userId, ?string $month = null): ?array
    {
        if (!$month) {
            $month = now()->format('Y-m');
        }

        $targetMonth = Carbon::parse($month . '-01')->startOfMonth();
        
        $target = Target::where('user_id', $userId)
            ->where('target_month', $targetMonth)
            ->first();

        if (!$target) {
            return null;
        }

        return [
            'target' => $target,
            'progress' => $target->getProgressData(),
        ];
    }

    /**
     * Get all users with targets for a specific month
     */
    public function getUsersWithTargets(string $month): array
    {
        $targetMonth = Carbon::parse($month . '-01')->startOfMonth();

        $targets = Target::where('target_month', $targetMonth)
            ->with('user.role')
            ->get();

        $result = [];
        foreach ($targets as $target) {
            $result[] = [
                'target' => $target,
                'user' => $target->user,
                'progress' => $target->getProgressData(),
            ];
        }

        return $result;
    }

    /**
     * Get team targets progress for a manager
     */
    public function getTeamTargetsProgress(int $managerId, ?string $month = null): array
    {
        if (!$month) {
            $month = now()->format('Y-m');
        }

        $targetMonth = Carbon::parse($month . '-01')->startOfMonth();

        // Get all team members (sales executives under this manager)
        $teamMembers = User::where('manager_id', $managerId)
            ->whereHas('role', function($q) {
                $q->where('slug', 'sales_executive');
            })
            ->pluck('id');

        $targets = Target::whereIn('user_id', $teamMembers)
            ->where('target_month', $targetMonth)
            ->with(['user.role'])
            ->get();

        // Calculate team totals
        $teamMeetingsTarget = $targets->sum('target_meetings');
        $teamVisitsTarget = $targets->sum('target_visits');
        $teamClosersTarget = $targets->sum('target_closers');

        $teamMeetingsAchieved = 0;
        $teamVisitsAchieved = 0;
        $teamClosersAchieved = 0;

        foreach ($targets as $target) {
            $meetingsProgress = $target->getAchievementProgress('meetings');
            $visitsProgress = $target->getAchievementProgress('visits');
            $closersProgress = $target->getAchievementProgress('closers');

            $teamMeetingsAchieved += $meetingsProgress['achieved'];
            $teamVisitsAchieved += $visitsProgress['achieved'];
            $teamClosersAchieved += $closersProgress['achieved'];
        }

        $teamMeetingsPercentage = $teamMeetingsTarget > 0 ? min(100, round(($teamMeetingsAchieved / $teamMeetingsTarget) * 100, 2)) : 0;
        $teamVisitsPercentage = $teamVisitsTarget > 0 ? min(100, round(($teamVisitsAchieved / $teamVisitsTarget) * 100, 2)) : 0;
        $teamClosersPercentage = $teamClosersTarget > 0 ? min(100, round(($teamClosersAchieved / $teamClosersTarget) * 100, 2)) : 0;

        // Build individual team member data
        $teamMembersData = [];
        foreach ($targets as $target) {
            if (!$target->user) {
                continue; // Skip if user not found
            }
            
            // Ensure role is loaded
            if (!$target->user->relationLoaded('role')) {
                $target->user->load('role');
            }
            
            $meetingsProgress = $target->getAchievementProgress('meetings');
            $visitsProgress = $target->getAchievementProgress('visits');
            $closersProgress = $target->getAchievementProgress('closers');
            
            $teamMembersData[] = [
                'user_id' => $target->user_id,
                'user_name' => $target->user->name ?? 'N/A',
                'user_role' => $target->user->role->slug ?? 'N/A',
                'user_role_name' => $target->user->role->name ?? 'N/A',
                'targets' => [
                    'meetings' => $meetingsProgress,
                    'visits' => $visitsProgress,
                    'closers' => $closersProgress,
                ],
            ];
        }

        // Return structured data
        return [
            'team_totals' => [
                'meetings' => [
                    'target' => $teamMeetingsTarget,
                    'achieved' => $teamMeetingsAchieved,
                    'percentage' => $teamMeetingsPercentage,
                ],
                'visits' => [
                    'target' => $teamVisitsTarget,
                    'achieved' => $teamVisitsAchieved,
                    'percentage' => $teamVisitsPercentage,
                ],
                'closers' => [
                    'target' => $teamClosersTarget,
                    'achieved' => $teamClosersAchieved,
                    'percentage' => $teamClosersPercentage,
                ],
            ],
            'team_members' => $teamMembersData,
        ];
    }

    /**
     * Get system-wide target overview for admin/CRM
     */
    public function getSystemOverview(?string $month = null): array
    {
        if (!$month) {
            $month = now()->format('Y-m');
        }

        $targetMonth = Carbon::parse($month . '-01')->startOfMonth();

        $targets = Target::where('target_month', $targetMonth)
            ->with('user.role')
            ->get();

        $totalTargets = [
            'prospects_extract' => 0,
            'prospects_verified' => 0,
            'calls' => 0,
        ];

        $totalActuals = [
            'prospects_extract' => 0,
            'prospects_verified' => 0,
            'calls' => 0,
        ];

        foreach ($targets as $target) {
            $progress = $target->getProgressData();
            
            $totalTargets['prospects_extract'] += $target->target_prospects_extract;
            $totalTargets['prospects_verified'] += $target->target_prospects_verified;
            $totalTargets['calls'] += $target->target_calls;

            $totalActuals['prospects_extract'] += $progress['prospects_extract']['actual'];
            $totalActuals['prospects_verified'] += $progress['prospects_verified']['actual'];
            $totalActuals['calls'] += $progress['calls']['actual'];
        }

        $percentages = [];
        foreach ($totalTargets as $key => $targetValue) {
            $percentages[$key] = $targetValue > 0 
                ? round(($totalActuals[$key] / $targetValue) * 100, 2) 
                : 0;
        }

        return [
            'month' => $month,
            'total_users' => $targets->count(),
            'targets' => $totalTargets,
            'actuals' => $totalActuals,
            'percentages' => $percentages,
            'details' => $targets->map(function($target) {
                return [
                    'user' => ($target->user && $target->user->name) ? $target->user->name : 'Unknown',
                    'progress' => $target->getProgressData(),
                ];
            }),
        ];
    }

    /**
     * Bulk set targets for multiple users
     */
    public function bulkSetTargets(array $userIds, string $month, array $targets): array
    {
        $results = [];
        
        DB::beginTransaction();
        try {
            foreach ($userIds as $userId) {
                $target = $this->setTargetsForUser($userId, $month, $targets);
                $results[] = $target;
            }
            
            DB::commit();
            return $results;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

