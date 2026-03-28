<?php

namespace App\Services;

use App\Events\AdminBroadcast;
use App\Events\FollowupNotification;
use App\Events\NewLeadNotification;
use App\Events\NewVerificationNotification;
use App\Jobs\SendFcmNotificationJob;
use App\Models\AppNotification;
use App\Models\BroadcastMessage;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\Role;
use App\Models\TelecallerTask;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    public function notifyNewLead(User $user, $lead, string $actionUrl): AppNotification
    {
        $message = "New lead assigned: {$lead->name}";

        return $this->createNotification(
            $user,
            AppNotification::TYPE_NEW_LEAD,
            'New Lead Assigned',
            $message,
            AppNotification::ACTION_LEAD,
            $actionUrl,
            [
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
            ],
            null,
            'new-lead-' . $lead->id
        );
    }

    public function notifyNewVerification(User $user, string $type, string $title, string $message, string $actionUrl, array $data = []): AppNotification
    {
        return $this->createNotification(
            $user,
            AppNotification::TYPE_NEW_VERIFICATION,
            $title,
            $message,
            AppNotification::ACTION_VERIFICATION,
            $actionUrl,
            array_merge([
                'verification_type' => $type,
            ], $data)
        );
    }

    public function notifyFollowup(User $user, $followup, string $actionUrl): AppNotification
    {
        $leadName = $followup->lead ? $followup->lead->name : 'Lead';
        $message = "Follow-up reminder: {$leadName}";
        if ($followup->scheduled_at) {
            $message .= ' at ' . $followup->scheduled_at->format('M d, Y h:i A');
        }

        return $this->createNotification(
            $user,
            AppNotification::TYPE_FOLLOWUP_REMINDER,
            'Follow-up Reminder',
            $message,
            AppNotification::ACTION_FOLLOWUP,
            $actionUrl,
            [
                'followup_id' => $followup->id,
                'lead_id' => $followup->lead_id,
                'lead_name' => $leadName,
                'scheduled_at' => $followup->scheduled_at ? $followup->scheduled_at->toIso8601String() : null,
            ],
            null,
            'followup-reminder-' . $followup->id
        );
    }

    public function notifyFollowupReminder(FollowUp $followup): Collection
    {
        $followup->loadMissing(['creator.manager', 'lead.activeAssignments.assignedTo.manager']);

        $actionUrl = $this->resolveFollowupActionUrl($followup);
        $notifications = collect();

        foreach ($this->resolveFollowupReminderRecipients($followup) as $user) {
            $notifications->push($this->notifyFollowup($user, $followup, $actionUrl));
        }

        return $notifications;
    }

    public function notifyOverdueTask(TelecallerTask $task): Collection
    {
        $task->loadMissing(['lead', 'assignedTo.manager']);

        $leadName = $task->lead?->name ?? 'Lead';
        $scheduledTime = $task->scheduled_at?->format('M d, Y h:i A') ?? 'scheduled time';
        $actionUrl = $this->resolveTaskActionUrl($task);

        return $this->createNotificationsForUsers(
            $this->resolveOverdueTaskRecipients($task),
            AppNotification::TYPE_TASK_OVERDUE,
            'Overdue Task',
            "Overdue task for {$leadName}. It was due at {$scheduledTime}.",
            AppNotification::ACTION_LEAD,
            $actionUrl,
            [
                'task_id' => $task->id,
                'lead_id' => $task->lead_id,
                'lead_name' => $leadName,
                'scheduled_at' => $task->scheduled_at?->toIso8601String(),
                'kind' => 'task_overdue',
            ],
            $task->id,
            'task-overdue-' . $task->id
        );
    }

    public function notifyOverdueFollowup(FollowUp $followup): Collection
    {
        $followup->loadMissing(['creator.manager', 'lead.activeAssignments.assignedTo.manager']);

        $leadName = $followup->lead?->name ?? 'Lead';
        $scheduledTime = $followup->scheduled_at?->format('M d, Y h:i A') ?? 'scheduled time';
        $actionUrl = $this->resolveFollowupActionUrl($followup);

        return $this->createNotificationsForUsers(
            $this->resolveOverdueFollowupRecipients($followup),
            AppNotification::TYPE_FOLLOWUP_OVERDUE,
            'Overdue Follow-up',
            "Overdue follow-up for {$leadName}. It was due at {$scheduledTime}.",
            AppNotification::ACTION_FOLLOWUP,
            $actionUrl,
            [
                'followup_id' => $followup->id,
                'lead_id' => $followup->lead_id,
                'lead_name' => $leadName,
                'scheduled_at' => $followup->scheduled_at?->toIso8601String(),
                'kind' => 'followup_overdue',
            ],
            null,
            'followup-overdue-' . $followup->id
        );
    }

    public function notifySiteVisit(User $user, $siteVisit, string $actionUrl): AppNotification
    {
        $leadName = $siteVisit->lead->name ?? ($siteVisit->customer_name ?? 'Lead');
        $message = "Hurry! Site visit scheduled for your lead {$leadName}";

        return $this->createNotification(
            $user,
            AppNotification::TYPE_SITE_VISIT,
            'Site Visit Scheduled',
            $message,
            AppNotification::ACTION_LEAD,
            $actionUrl,
            [
                'site_visit_id' => $siteVisit->id,
                'lead_id' => $siteVisit->lead_id,
                'lead_name' => $leadName,
                'scheduled_at' => $siteVisit->scheduled_at ? (string) $siteVisit->scheduled_at : null,
            ],
            null,
            'site-visit-' . $siteVisit->id
        );
    }

    public function notifyEligibleSiteVisitForIncentive(User $telecaller, $siteVisit, string $actionUrl): AppNotification
    {
        $leadName = $siteVisit->lead->name ?? ($siteVisit->customer_name ?? 'Lead');
        $message = "Your prospect '{$leadName}' site visit has been completed. Request incentive for this visit.";

        return $this->createNotification(
            $telecaller,
            AppNotification::TYPE_NEW_LEAD,
            'Eligible Site Visit for Incentive',
            $message,
            AppNotification::ACTION_LEAD,
            $actionUrl,
            [
                'site_visit_id' => $siteVisit->id,
                'lead_id' => $siteVisit->lead_id,
                'lead_name' => $leadName,
                'type' => 'site_visit_incentive_eligible',
            ]
        );
    }

    public function notifyMeeting(User $user, $meeting, string $actionUrl): AppNotification
    {
        $leadName = $meeting->lead->name ?? ($meeting->customer_name ?? 'Lead');
        $message = "Hurry! Meeting scheduled for your lead {$leadName}";

        return $this->createNotification(
            $user,
            AppNotification::TYPE_MEETING,
            'Meeting Scheduled',
            $message,
            AppNotification::ACTION_LEAD,
            $actionUrl,
            [
                'meeting_id' => $meeting->id,
                'lead_id' => $meeting->lead_id,
                'lead_name' => $leadName,
                'scheduled_at' => $meeting->scheduled_at ? (string) $meeting->scheduled_at : null,
            ],
            null,
            'meeting-' . $meeting->id
        );
    }

    public function notifyAdminsNewUser(User $newUser): array
    {
        $admins = User::whereHas('role', function ($q) {
            $q->where('slug', Role::ADMIN);
        })->where('is_active', true)->get();

        $actionUrl = url('/users');
        $title = 'New user created';
        $message = "New user created: {$newUser->name} ({$newUser->email})";

        $notifications = [];
        foreach ($admins as $admin) {
            $notifications[] = $this->createNotification(
                $admin,
                AppNotification::TYPE_NEW_USER,
                $title,
                $message,
                AppNotification::ACTION_USER,
                $actionUrl,
                [
                    'new_user_id' => $newUser->id,
                    'new_user_name' => $newUser->name,
                    'new_user_email' => $newUser->email,
                ]
            );
        }

        return $notifications;
    }

    public function sendBroadcast(User $sender, string $title, string $message, string $targetType = 'all_users', array $targetRoles = []): array
    {
        $broadcast = BroadcastMessage::create([
            'sender_id' => $sender->id,
            'title' => $title,
            'message' => $message,
            'target_type' => $targetType,
            'target_roles' => $targetType === 'role_based' ? $targetRoles : null,
        ]);

        $targetUsers = $this->getTargetUsers($targetType, $targetRoles);

        $notifications = [];
        foreach ($targetUsers as $user) {
            $notifications[] = AppNotification::create([
                'user_id' => $user->id,
                'type' => AppNotification::TYPE_ADMIN_BROADCAST,
                'title' => $title,
                'message' => $message,
                'action_type' => AppNotification::ACTION_BROADCAST,
                'action_url' => null,
                'data' => [
                    'broadcast_id' => $broadcast->id,
                    'sender_name' => $sender->name,
                ],
            ]);
        }

        event(new AdminBroadcast($broadcast, $notifications));

        return [
            'broadcast' => $broadcast,
            'notifications' => $notifications,
            'sent_to' => count($targetUsers),
        ];
    }

    public function notifyClosingVerificationPending($siteVisit, int $requestedByUserId): void
    {
        $crmUsers = User::whereHas('role', function ($query) {
            $query->where('slug', Role::CRM);
        })->where('is_active', true)->get();

        $requestedBy = User::find($requestedByUserId);
        $leadName = $siteVisit->lead->name ?? ($siteVisit->customer_name ?? 'Lead');
        $message = "New closing request pending verification for lead: {$leadName}";

        foreach ($crmUsers as $crmUser) {
            $this->createNotification(
                $crmUser,
                AppNotification::TYPE_NEW_LEAD,
                'Closing Verification Pending',
                $message,
                AppNotification::ACTION_LEAD,
                url('/crm/verifications'),
                [
                    'site_visit_id' => $siteVisit->id,
                    'lead_id' => $siteVisit->lead_id,
                    'lead_name' => $leadName,
                    'requested_by' => $requestedBy ? $requestedBy->name : 'Unknown',
                    'type' => 'closing_verification_pending',
                ]
            );
        }
    }

    public function notifyClosingVerified($siteVisit, int $verifiedByUserId): void
    {
        $requestedBy = $siteVisit->creator;
        if (!$requestedBy) {
            return;
        }

        $verifiedBy = User::find($verifiedByUserId);
        $leadName = $siteVisit->lead->name ?? ($siteVisit->customer_name ?? 'Lead');
        $message = "Your closing request for lead: {$leadName} has been verified. You can now request incentives.";

        $this->createNotification(
            $requestedBy,
            AppNotification::TYPE_NEW_LEAD,
            'Closing Verified',
            $message,
            AppNotification::ACTION_LEAD,
            url('/sales-manager/site-visits'),
            [
                'site_visit_id' => $siteVisit->id,
                'lead_id' => $siteVisit->lead_id,
                'lead_name' => $leadName,
                'verified_by' => $verifiedBy ? $verifiedBy->name : 'CRM',
                'type' => 'closing_verified',
            ]
        );
    }

    public function notifyClosingRejected($siteVisit, int $rejectedByUserId, string $reason): void
    {
        $requestedBy = $siteVisit->creator;
        if (!$requestedBy) {
            return;
        }

        $rejectedBy = User::find($rejectedByUserId);
        $leadName = $siteVisit->lead->name ?? ($siteVisit->customer_name ?? 'Lead');
        $message = "Your closing request for lead: {$leadName} has been rejected. Reason: {$reason}";

        $this->createNotification(
            $requestedBy,
            AppNotification::TYPE_NEW_LEAD,
            'Closing Rejected',
            $message,
            AppNotification::ACTION_LEAD,
            url('/sales-manager/site-visits'),
            [
                'site_visit_id' => $siteVisit->id,
                'lead_id' => $siteVisit->lead_id,
                'lead_name' => $leadName,
                'rejected_by' => $rejectedBy ? $rejectedBy->name : 'CRM',
                'rejection_reason' => $reason,
                'type' => 'closing_rejected',
            ]
        );
    }

    public function notifyIncentiveRequestPending($incentive): void
    {
        $financeManagers = User::whereHas('role', function ($query) {
            $query->where('slug', Role::FINANCE_MANAGER);
        })->where('is_active', true)->get();

        $requestedBy = $incentive->user;
        $siteVisit = $incentive->siteVisit;
        $leadName = $siteVisit->lead->name ?? ($siteVisit->customer_name ?? 'Lead');
        $message = "New incentive request from {$requestedBy->name} for lead: {$leadName} (Amount: â‚¹{$incentive->amount})";

        foreach ($financeManagers as $financeManager) {
            $this->createNotification(
                $financeManager,
                AppNotification::TYPE_NEW_LEAD,
                'Incentive Request Pending',
                $message,
                AppNotification::ACTION_LEAD,
                url('/finance-manager/incentives'),
                [
                    'incentive_id' => $incentive->id,
                    'site_visit_id' => $siteVisit->id,
                    'lead_id' => $siteVisit->lead_id,
                    'lead_name' => $leadName,
                    'requested_by' => $requestedBy->name,
                    'amount' => $incentive->amount,
                    'type' => 'incentive_request_pending',
                ]
            );
        }
    }

    public function notifyIncentiveApproved($incentive): void
    {
        $requestedBy = $incentive->user;
        $siteVisit = $incentive->siteVisit;
        $leadName = $siteVisit->lead->name ?? ($siteVisit->customer_name ?? 'Lead');
        $message = "Your incentive request for lead: {$leadName} has been approved. Amount: â‚¹{$incentive->amount}";

        $this->createNotification(
            $requestedBy,
            AppNotification::TYPE_NEW_LEAD,
            'Incentive Approved',
            $message,
            AppNotification::ACTION_LEAD,
            url('/sales-manager/site-visits'),
            [
                'incentive_id' => $incentive->id,
                'site_visit_id' => $siteVisit->id,
                'lead_id' => $siteVisit->lead_id,
                'lead_name' => $leadName,
                'amount' => $incentive->amount,
                'type' => 'incentive_approved',
            ]
        );
    }

    public function notifyIncentiveRejected($incentive, string $reason): void
    {
        $requestedBy = $incentive->user;
        $siteVisit = $incentive->siteVisit;
        $leadName = $siteVisit->lead->name ?? ($siteVisit->customer_name ?? 'Lead');
        $message = "Your incentive request for lead: {$leadName} has been rejected. Reason: {$reason}";

        $this->createNotification(
            $requestedBy,
            AppNotification::TYPE_NEW_LEAD,
            'Incentive Rejected',
            $message,
            AppNotification::ACTION_LEAD,
            url('/sales-manager/site-visits'),
            [
                'incentive_id' => $incentive->id,
                'site_visit_id' => $siteVisit->id,
                'lead_id' => $siteVisit->lead_id,
                'lead_name' => $leadName,
                'rejection_reason' => $reason,
                'type' => 'incentive_rejected',
            ]
        );
    }

    private function createNotificationsForUsers(
        Collection $users,
        string $type,
        string $title,
        string $message,
        ?string $actionType,
        ?string $actionUrl,
        array $data = [],
        ?int $telecallerTaskId = null,
        ?string $tag = null
    ): Collection {
        return $users->map(function (User $user) use ($type, $title, $message, $actionType, $actionUrl, $data, $telecallerTaskId, $tag) {
            return $this->createNotification(
                $user,
                $type,
                $title,
                $message,
                $actionType,
                $actionUrl,
                $data,
                $telecallerTaskId,
                $tag ? $tag . '-user-' . $user->id : null
            );
        });
    }

    private function createNotification(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionType,
        ?string $actionUrl,
        array $data = [],
        ?int $telecallerTaskId = null,
        ?string $fcmTag = null
    ): AppNotification {
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'telecaller_task_id' => $telecallerTaskId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_type' => $actionType,
            'action_url' => $actionUrl,
            'data' => $data,
        ]);

        $this->broadcastNotification($notification);
        $this->dispatchFcmNotification($user, $title, $message, $actionUrl, $fcmTag ?? $type . '-' . $notification->id);

        return $notification;
    }

    private function broadcastNotification(AppNotification $notification): void
    {
        if ($notification->type === AppNotification::TYPE_NEW_VERIFICATION) {
            event(new NewVerificationNotification($notification));
            return;
        }

        if (in_array($notification->type, [AppNotification::TYPE_FOLLOWUP_REMINDER, AppNotification::TYPE_FOLLOWUP_OVERDUE], true)) {
            event(new FollowupNotification($notification));
            return;
        }

        event(new NewLeadNotification($notification));
    }

    private function dispatchFcmNotification(User $user, string $title, string $message, ?string $actionUrl, string $tag): void
    {
        if (!$actionUrl) {
            return;
        }

        SendFcmNotificationJob::dispatch($user->id, $title, $message, $actionUrl, $tag);
    }

    private function resolveFollowupReminderRecipients(FollowUp $followup): Collection
    {
        return $this->uniqueUsers([
            $followup->creator,
            $this->resolveLeadOwner($followup->lead),
        ]);
    }

    private function resolveOverdueTaskRecipients(TelecallerTask $task): Collection
    {
        return $this->uniqueUsers([
            $task->assignedTo,
            $task->assignedTo?->manager,
        ]);
    }

    private function resolveOverdueFollowupRecipients(FollowUp $followup): Collection
    {
        $responsibleUser = $this->resolveLeadOwner($followup->lead) ?? $followup->creator;

        return $this->uniqueUsers([
            $responsibleUser,
            $responsibleUser?->manager,
        ]);
    }

    private function resolveLeadOwner(?Lead $lead): ?User
    {
        if (!$lead) {
            return null;
        }

        $lead->loadMissing('activeAssignments.assignedTo.manager');

        return optional($lead->activeAssignments->first())->assignedTo;
    }

    private function resolveTaskActionUrl(TelecallerTask $task): string
    {
        return url('/telecaller/tasks?status=pending&task_id=' . $task->id);
    }

    private function resolveFollowupActionUrl(FollowUp $followup): string
    {
        if ($followup->lead_id) {
            return url('/leads/' . $followup->lead_id);
        }

        return url('/leads');
    }

    private function uniqueUsers(array $users): Collection
    {
        return collect($users)
            ->filter(fn ($user) => $user instanceof User && $user->is_active)
            ->unique('id')
            ->values();
    }

    private function getTargetUsers(string $targetType, array $targetRoles = []): Collection
    {
        if ($targetType === 'all_users') {
            return User::where('is_active', true)->get();
        }

        if (!empty($targetRoles)) {
            $roleIds = Role::whereIn('slug', $targetRoles)->pluck('id');

            return User::where('is_active', true)
                ->whereIn('role_id', $roleIds)
                ->get();
        }

        return collect();
    }
}
