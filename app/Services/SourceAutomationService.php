<?php

namespace App\Services;

use App\Events\LeadAssigned;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\SourceAutomationRule;
use App\Models\SourceAutomationRuleUser;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class SourceAutomationService
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Main entry point: find matching rule for a source and assign the lead.
     * Called from webhook controllers after lead creation.
     */
    public function assignFromSource(Lead $lead, string $source, ?int $fbFormId = null, ?int $googleSheetConfigId = null): bool
    {
        try {
            $rule = $this->findRule($source, $fbFormId, $googleSheetConfigId);
            if (!$rule) {
                Log::info("SourceAutomationService: no active rule found", [
                    'source'                  => $source,
                    'fb_form_id'              => $fbFormId,
                    'google_sheet_config_id'  => $googleSheetConfigId,
                    'lead_id'                 => $lead->id,
                ]);
                return false;
            }

            $userId = $this->pickUser($rule);

            if (!$userId && $rule->fallback_user_id) {
                $userId = $rule->fallback_user_id;
            }

            if (!$userId) {
                Log::warning("SourceAutomationService: no user available for rule", [
                    'rule_id' => $rule->id,
                    'lead_id' => $lead->id,
                ]);
                return false;
            }

            DB::beginTransaction();

            // Create LeadAssignment record
            $lead->assignments()->update(['is_active' => false, 'unassigned_at' => now()]);
            LeadAssignment::create([
                'lead_id'           => $lead->id,
                'assigned_to'       => $userId,
                'assigned_by'       => $rule->created_by,
                'assignment_type'   => 'primary',
                'assignment_method' => $rule->assignment_method,
                'assigned_at'       => now(),
                'is_active'         => true,
            ]);

            // Update lead's assigned_to field
            $lead->update(['assigned_to' => $userId]);

            // Increment daily counter for rule user
            $this->incrementRuleUserCount($rule, $userId);

            // Fire LeadAssigned event → CreateTelecallerTask listener auto-runs
            if (!$lead->is_blocked && $rule->auto_create_task) {
                try {
                    Event::dispatch(new LeadAssigned($lead, $userId, $rule->created_by));

                    // Send chatbot notification
                    $assignedUser = User::with('role')->find($userId);
                    if ($assignedUser) {
                        $leadUrl = route('leads.show', $lead->id);
                        $this->notificationService->notifyNewLead($assignedUser, $lead, $leadUrl);
                    }
                } catch (\Exception $e) {
                    Log::error("SourceAutomationService: event dispatch failed", [
                        'lead_id' => $lead->id,
                        'error'   => $e->getMessage(),
                    ]);
                }
            } else {
                // No task creation — just send notification
                try {
                    $assignedUser = User::with('role')->find($userId);
                    if ($assignedUser) {
                        $leadUrl = route('leads.show', $lead->id);
                        $this->notificationService->notifyNewLead($assignedUser, $lead, $leadUrl);
                    }
                } catch (\Exception $e) {
                    // Non-critical
                }
            }

            DB::commit();

            Log::info("SourceAutomationService: lead assigned", [
                'lead_id'    => $lead->id,
                'user_id'    => $userId,
                'rule_id'    => $rule->id,
                'source'     => $source,
                'method'     => $rule->assignment_method,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("SourceAutomationService: assignment failed", [
                'lead_id' => $lead->id,
                'source'  => $source,
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    /**
     * Find matching rule by priority:
     * 1. source + specific form/sheet exact match
     * 2. source match (no specific form/sheet)
     * 3. source = 'all' catch-all
     */
    protected function findRule(string $source, ?int $fbFormId = null, ?int $googleSheetConfigId = null): ?SourceAutomationRule
    {
        // Priority 1: exact match with specific form/sheet
        if ($fbFormId) {
            $rule = SourceAutomationRule::where('source', $source)
                ->where('fb_form_id', $fbFormId)
                ->where('is_active', true)
                ->first();
            if ($rule) return $rule;
        }

        if ($googleSheetConfigId) {
            $rule = SourceAutomationRule::where('source', $source)
                ->where('google_sheet_config_id', $googleSheetConfigId)
                ->where('is_active', true)
                ->first();
            if ($rule) return $rule;
        }

        // Priority 2: source match, no specific form/sheet
        $rule = SourceAutomationRule::where('source', $source)
            ->whereNull('fb_form_id')
            ->whereNull('google_sheet_config_id')
            ->where('is_active', true)
            ->first();
        if ($rule) return $rule;

        // Priority 3: catch-all rule
        return SourceAutomationRule::where('source', 'all')
            ->where('is_active', true)
            ->first();
    }

    /**
     * Pick a user based on the rule's assignment method.
     */
    protected function pickUser(SourceAutomationRule $rule): ?int
    {
        return match($rule->assignment_method) {
            'round_robin'     => $this->pickRoundRobin($rule),
            'first_available' => $this->pickFirstAvailable($rule),
            'percentage'      => $this->pickByPercentage($rule),
            'single_user'     => $rule->single_user_id,
            default           => null,
        };
    }

    /**
     * Round Robin: assign to user with least assigned_count_today (cycling).
     */
    protected function pickRoundRobin(SourceAutomationRule $rule): ?int
    {
        $ruleUsers = SourceAutomationRuleUser::where('rule_id', $rule->id)
            ->with('user')
            ->get()
            ->filter(fn($ru) => $ru->user && $ru->user->is_active)
            ->each(fn($ru) => $ru->resetIfNewDay())
            ->filter(fn($ru) => $ru->isWithinLimit())
            ->sortBy('assigned_count_today');

        if ($ruleUsers->isEmpty()) return null;

        // Check rule-level daily limit
        if ($rule->daily_limit) {
            $totalToday = $ruleUsers->sum('assigned_count_today');
            if ($totalToday >= $rule->daily_limit) return null;
        }

        return $ruleUsers->first()->user_id;
    }

    /**
     * First Available: user with fewest active lead assignments overall.
     */
    protected function pickFirstAvailable(SourceAutomationRule $rule): ?int
    {
        $ruleUsers = SourceAutomationRuleUser::where('rule_id', $rule->id)
            ->with('user')
            ->get()
            ->filter(fn($ru) => $ru->user && $ru->user->is_active)
            ->each(fn($ru) => $ru->resetIfNewDay())
            ->filter(fn($ru) => $ru->isWithinLimit());

        if ($ruleUsers->isEmpty()) return null;

        if ($rule->daily_limit) {
            $totalToday = $ruleUsers->sum('assigned_count_today');
            if ($totalToday >= $rule->daily_limit) return null;
        }

        $userIds = $ruleUsers->pluck('user_id')->toArray();

        // Count active assignments per user
        $counts = LeadAssignment::whereIn('assigned_to', $userIds)
            ->where('is_active', true)
            ->selectRaw('assigned_to, COUNT(*) as cnt')
            ->groupBy('assigned_to')
            ->pluck('cnt', 'assigned_to')
            ->toArray();

        // Pick user with minimum active assignments
        $bestUserId = null;
        $bestCount = PHP_INT_MAX;
        foreach ($userIds as $uid) {
            $cnt = $counts[$uid] ?? 0;
            if ($cnt < $bestCount) {
                $bestCount = $cnt;
                $bestUserId = $uid;
            }
        }

        return $bestUserId;
    }

    /**
     * Percentage: weighted random selection.
     */
    protected function pickByPercentage(SourceAutomationRule $rule): ?int
    {
        $ruleUsers = SourceAutomationRuleUser::where('rule_id', $rule->id)
            ->with('user')
            ->get()
            ->filter(fn($ru) => $ru->user && $ru->user->is_active && ($ru->percentage ?? 0) > 0)
            ->each(fn($ru) => $ru->resetIfNewDay())
            ->filter(fn($ru) => $ru->isWithinLimit());

        if ($ruleUsers->isEmpty()) return null;

        if ($rule->daily_limit) {
            $totalToday = $ruleUsers->sum('assigned_count_today');
            if ($totalToday >= $rule->daily_limit) return null;
        }

        $totalPercentage = $ruleUsers->sum('percentage');
        if ($totalPercentage <= 0) return null;

        $rand = mt_rand(0, (int)($totalPercentage * 100)) / 100;
        $cumulative = 0;

        foreach ($ruleUsers as $ru) {
            $cumulative += $ru->percentage;
            if ($rand <= $cumulative) {
                return $ru->user_id;
            }
        }

        return $ruleUsers->last()->user_id;
    }

    /**
     * Increment today's count for the selected user in this rule.
     */
    protected function incrementRuleUserCount(SourceAutomationRule $rule, int $userId): void
    {
        $ruleUser = SourceAutomationRuleUser::where('rule_id', $rule->id)
            ->where('user_id', $userId)
            ->first();

        if ($ruleUser) {
            $ruleUser->incrementCount();
        }
    }
}
