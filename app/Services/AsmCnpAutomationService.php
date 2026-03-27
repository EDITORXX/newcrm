<?php

namespace App\Services;

use App\Models\AsmCnpAutomationAudit;
use App\Models\AsmCnpAutomationConfig;
use App\Models\AsmCnpAutomationState;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AsmCnpAutomationService
{
    public function getConfig(): AsmCnpAutomationConfig
    {
        return AsmCnpAutomationConfig::query()
            ->with(['poolUsers.user.role', 'overrides'])
            ->firstOrFail();
    }

    public function isFreshLeadTask(Task $task): bool
    {
        if ($task->type !== 'phone_call') {
            return false;
        }

        $task->loadMissing('lead.prospects');
        $lead = $task->lead;

        if (!$lead || $this->leadHasProgressed($lead)) {
            return false;
        }

        return !$lead->prospects->isNotEmpty();
    }

    public function handleFreshLeadCnp(Task $task, User $user): array
    {
        if (!$this->isFreshLeadTask($task)) {
            throw new \RuntimeException('Fresh lead CNP automation is not applicable to this task.');
        }

        $config = $this->getConfig();
        if (!$config->is_enabled || !$config->is_active) {
            throw new \RuntimeException('ASM CNP automation is disabled.');
        }

        $task->loadMissing('lead.activeAssignments');
        $lead = $task->lead;

        return DB::transaction(function () use ($config, $task, $user, $lead) {
            $assignment = $lead->activeAssignments()
                ->where('assigned_to', $user->id)
                ->latest('assigned_at')
                ->first();

            if (!$assignment) {
                throw new \RuntimeException('Active lead assignment not found for this user.');
            }

            $state = AsmCnpAutomationState::query()
                ->where('lead_id', $lead->id)
                ->where('lead_assignment_id', $assignment->id)
                ->where('status', 'active')
                ->latest('id')
                ->first();

            if (!$state) {
                $state = AsmCnpAutomationState::create([
                    'lead_id' => $lead->id,
                    'lead_assignment_id' => $assignment->id,
                    'config_id' => $config->id,
                    'original_assigned_to' => $user->id,
                    'current_assigned_to' => $user->id,
                    'cnp_count' => 0,
                    'assignment_started_at' => $assignment->assigned_at ?? $assignment->created_at ?? now(),
                    'status' => 'active',
                    'transfer_eligible' => true,
                ]);
            }

            $cnpCount = (int) $state->cnp_count + 1;
            $now = now();
            $maxAttempts = max(1, (int) $config->max_cnp_attempts);
            $retryTask = null;

            $taskNote = 'Call not picked (auto CNP) handled on ' . $now->format('Y-m-d H:i:s');
            $task->update([
                'status' => 'cancelled',
                'notes' => trim(($task->notes ?? '') . PHP_EOL . $taskNote),
                'description' => trim(($task->description ?? '') . PHP_EOL . $taskNote),
                'outcome' => 'cnp',
                'outcome_recorded_at' => $now,
            ]);

            $eligibleForTransferAt = Carbon::parse($state->assignment_started_at ?? $assignment->assigned_at ?? $now)
                ->addHours((int) $config->transfer_threshold_hours);

            $nextRetryAt = null;
            if ($cnpCount < $maxAttempts) {
                $nextRetryAt = $now->copy()->addMinutes((int) $config->retry_delay_minutes);
                $retryTask = $this->findExistingPendingTask($lead->id, $user->id, $nextRetryAt)
                    ?? Task::create([
                        'lead_id' => $lead->id,
                        'assigned_to' => $user->id,
                        'type' => 'phone_call',
                        'title' => 'Fresh lead retry call: ' . $lead->name,
                        'description' => 'Auto-created CNP retry task for fresh lead after attempt #' . $cnpCount,
                        'status' => 'pending',
                        'scheduled_at' => $nextRetryAt,
                        'created_by' => $user->id,
                        'notes' => 'ASM fresh lead CNP automation retry task',
                    ]);
            }

            $state->update([
                'config_id' => $config->id,
                'current_assigned_to' => $user->id,
                'last_retry_task_id' => $retryTask?->id,
                'cnp_count' => $cnpCount,
                'first_cnp_at' => $state->first_cnp_at ?? $now,
                'last_cnp_at' => $now,
                'next_retry_at' => $nextRetryAt,
                'eligible_for_transfer_at' => $eligibleForTransferAt,
                'transfer_eligible' => true,
                'status' => 'active',
                'cancel_reason' => null,
                'cancelled_at' => null,
                'transferred_at' => null,
                'last_processed_at' => $now,
            ]);

            $this->createAudit($state, 'retry_created', [
                'from_user_id' => $user->id,
                'task_id' => $retryTask?->id,
                'message' => $retryTask
                    ? 'Auto retry task created after CNP #' . $cnpCount
                    : 'Retry cap reached after CNP #' . $cnpCount . ', waiting for transfer check.',
                'meta' => [
                    'retry_delay_minutes' => (int) $config->retry_delay_minutes,
                    'transfer_threshold_hours' => (int) $config->transfer_threshold_hours,
                    'eligible_for_transfer_at' => optional($eligibleForTransferAt)->toDateTimeString(),
                ],
            ]);

            return [
                'state' => $state->fresh(),
                'retry_task' => $retryTask,
                'message' => $retryTask
                    ? 'Call Not Picked marked. Retry task has been auto-created.'
                    : 'Call Not Picked marked. Retry cap reached; transfer automation will handle next assignment.',
            ];
        });
    }

    public function cancelLeadAutomation(?Lead $lead, string $reason): void
    {
        if (!$lead) {
            return;
        }

        $states = AsmCnpAutomationState::query()
            ->where('lead_id', $lead->id)
            ->where('status', 'active')
            ->get();

        foreach ($states as $state) {
            $state->update([
                'status' => 'cancelled',
                'transfer_eligible' => false,
                'cancel_reason' => $reason,
                'cancelled_at' => now(),
                'last_processed_at' => now(),
            ]);

            $this->createAudit($state, 'cancelled', [
                'from_user_id' => $state->current_assigned_to,
                'message' => $reason,
            ]);
        }
    }

    public function processDueTransfers(): array
    {
        $config = $this->getConfig();
        if (!$config->is_enabled || !$config->is_active) {
            return ['processed' => 0, 'transferred' => 0, 'cancelled' => 0, 'skipped' => 0];
        }

        $states = AsmCnpAutomationState::query()
            ->with(['lead.activeAssignments', 'lead.prospects', 'leadAssignment', 'currentAssignee.role', 'config.overrides', 'config.poolUsers.user.role'])
            ->where('status', 'active')
            ->where('transfer_eligible', true)
            ->where('cnp_count', '>=', (int) $config->max_cnp_attempts)
            ->whereNotNull('eligible_for_transfer_at')
            ->where('eligible_for_transfer_at', '<=', now())
            ->get();

        $stats = ['processed' => 0, 'transferred' => 0, 'cancelled' => 0, 'skipped' => 0];

        foreach ($states as $state) {
            $stats['processed']++;
            $lead = $state->lead;

            if (!$lead || $this->leadHasProgressed($lead)) {
                $this->cancelState($state, 'Lead moved out of fresh lead CNP flow.');
                $stats['cancelled']++;
                continue;
            }

            $activeAssignment = $lead->activeAssignments()
                ->where('assigned_to', $state->current_assigned_to)
                ->latest('assigned_at')
                ->first();

            if (!$activeAssignment || ($state->lead_assignment_id && (int) $activeAssignment->id !== (int) $state->lead_assignment_id)) {
                $this->cancelState($state, 'Lead assignment changed before CNP transfer.');
                $stats['cancelled']++;
                continue;
            }

            $targetUser = $this->resolveTransferTarget($config, $state->current_assigned_to);
            if (!$targetUser) {
                $this->markSkipped($state, 'No valid CNP transfer target available.');
                $stats['skipped']++;
                continue;
            }

            DB::transaction(function () use ($state, $lead, $activeAssignment, $targetUser, $config) {
                $activeAssignment->update([
                    'is_active' => false,
                    'unassigned_at' => now(),
                    'notes' => trim(($activeAssignment->notes ?? '') . PHP_EOL . 'Auto transferred by ASM CNP automation'),
                ]);

                $newAssignment = LeadAssignment::create([
                    'lead_id' => $lead->id,
                    'assigned_to' => $targetUser->id,
                    'assigned_by' => auth()->id() ?? $config->updated_by ?? $config->created_by ?? 1,
                    'assignment_type' => 'primary',
                    'assignment_method' => 'cnp_auto_transfer',
                    'notes' => 'Auto-transferred after max fresh-lead CNP attempts.',
                    'assigned_at' => now(),
                    'is_active' => true,
                ]);

                $newTask = $this->findExistingPendingTask($lead->id, $targetUser->id)
                    ?? Task::create([
                        'lead_id' => $lead->id,
                        'assigned_to' => $targetUser->id,
                        'type' => 'phone_call',
                        'title' => 'Fresh lead call: ' . $lead->name,
                        'description' => 'Assigned by ASM fresh-lead CNP automation.',
                        'status' => 'pending',
                        'scheduled_at' => now(),
                        'created_by' => $config->updated_by ?? $config->created_by ?? $targetUser->id,
                        'notes' => 'Auto-created for new assignee after CNP transfer.',
                    ]);

                $state->update([
                    'lead_assignment_id' => $newAssignment->id,
                    'current_assigned_to' => $targetUser->id,
                    'last_retry_task_id' => $newTask->id,
                    'status' => 'transferred',
                    'transfer_eligible' => false,
                    'transferred_at' => now(),
                    'last_processed_at' => now(),
                ]);

                $this->createAudit($state, 'transferred', [
                    'from_user_id' => $activeAssignment->assigned_to,
                    'to_user_id' => $targetUser->id,
                    'task_id' => $newTask->id,
                    'message' => 'Lead auto-transferred after max fresh-lead CNP attempts.',
                    'meta' => [
                        'new_assignment_id' => $newAssignment->id,
                        'routing' => $config->fallback_routing,
                    ],
                ]);
            });

            $stats['transferred']++;
        }

        return $stats;
    }

    protected function cancelState(AsmCnpAutomationState $state, string $reason): void
    {
        $state->update([
            'status' => 'cancelled',
            'transfer_eligible' => false,
            'cancel_reason' => $reason,
            'cancelled_at' => now(),
            'last_processed_at' => now(),
        ]);

        $this->createAudit($state, 'cancelled', [
            'from_user_id' => $state->current_assigned_to,
            'message' => $reason,
        ]);
    }

    protected function markSkipped(AsmCnpAutomationState $state, string $reason): void
    {
        $state->update([
            'status' => 'skipped',
            'transfer_eligible' => false,
            'cancel_reason' => $reason,
            'last_processed_at' => now(),
        ]);

        $this->createAudit($state, 'skipped', [
            'from_user_id' => $state->current_assigned_to,
            'message' => $reason,
        ]);
    }

    protected function resolveTransferTarget(AsmCnpAutomationConfig $config, int $fromUserId): ?User
    {
        $override = $config->overrides
            ->first(fn ($item) => $item->is_active && (int) $item->from_user_id === $fromUserId);

        if ($override) {
            $target = User::query()->with('role')
                ->whereKey($override->to_user_id)
                ->where('is_active', true)
                ->first();

            if ($target && $target->id !== $fromUserId && $target->isAssistantSalesManager()) {
                return $target;
            }
        }

        $poolUsers = $config->poolUsers
            ->filter(function ($poolUser) use ($fromUserId) {
                return $poolUser->is_active
                    && $poolUser->user
                    && $poolUser->user->is_active
                    && $poolUser->user->id !== $fromUserId
                    && $poolUser->user->isAssistantSalesManager();
            })
            ->values();

        if ($poolUsers->isEmpty()) {
            return null;
        }

        $lastUserId = $config->last_round_robin_user_id;
        $next = $this->pickRoundRobinUser($poolUsers, $lastUserId);

        if ($next) {
            $config->update(['last_round_robin_user_id' => $next->id]);
        }

        return $next;
    }

    protected function pickRoundRobinUser(Collection $poolUsers, ?int $lastUserId): ?User
    {
        if ($poolUsers->isEmpty()) {
            return null;
        }

        if (!$lastUserId) {
            return $poolUsers->first()->user;
        }

        $index = $poolUsers->search(fn ($item) => (int) $item->user_id === (int) $lastUserId);
        if ($index === false) {
            return $poolUsers->first()->user;
        }

        $nextIndex = ($index + 1) % $poolUsers->count();
        return $poolUsers->get($nextIndex)?->user;
    }

    protected function findExistingPendingTask(int $leadId, int $userId, ?Carbon $scheduledAt = null): ?Task
    {
        $query = Task::query()
            ->where('lead_id', $leadId)
            ->where('assigned_to', $userId)
            ->where('type', 'phone_call')
            ->whereIn('status', ['pending', 'in_progress']);

        if ($scheduledAt) {
            $query->whereBetween('scheduled_at', [
                $scheduledAt->copy()->subMinutes(1),
                $scheduledAt->copy()->addMinutes(1),
            ]);
        }

        return $query->latest('id')->first();
    }

    protected function leadHasProgressed(Lead $lead): bool
    {
        $lead->loadMissing(['prospects', 'meetings', 'siteVisits', 'followUps']);

        if ($lead->is_dead) {
            return true;
        }

        if ($lead->prospects->isNotEmpty()) {
            return true;
        }

        if ($lead->meetings->isNotEmpty() || $lead->siteVisits->isNotEmpty() || $lead->followUps->isNotEmpty()) {
            return true;
        }

        return in_array($lead->status, [
            'verified_prospect',
            'meeting_scheduled',
            'meeting_completed',
            'visit_scheduled',
            'visit_done',
            'revisited_scheduled',
            'revisited_completed',
            'follow_up',
            'closed',
            'dead',
            'not_interested',
            'on_hold',
        ], true);
    }

    protected function createAudit(AsmCnpAutomationState $state, string $action, array $payload = []): void
    {
        AsmCnpAutomationAudit::create([
            'state_id' => $state->id,
            'lead_id' => $state->lead_id,
            'config_id' => $state->config_id,
            'from_user_id' => $payload['from_user_id'] ?? null,
            'to_user_id' => $payload['to_user_id'] ?? null,
            'task_id' => $payload['task_id'] ?? null,
            'cnp_count' => $state->cnp_count,
            'action' => $action,
            'message' => $payload['message'] ?? null,
            'meta' => $payload['meta'] ?? null,
            'acted_at' => now(),
        ]);
    }
}
