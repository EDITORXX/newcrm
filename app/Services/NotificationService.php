<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\BroadcastMessage;
use App\Models\User;
use App\Models\Role;
use App\Events\NewLeadNotification;
use App\Events\NewVerificationNotification;
use App\Events\FollowupNotification;
use App\Events\AdminBroadcast;
use App\Jobs\SendFcmNotificationJob;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Notify user about new lead assignment
     */
    public function notifyNewLead(User $user, $lead, string $actionUrl): AppNotification
    {
        // Sales Executive UX: show one aggregated "New X leads allocated" notification
        if ($user->isSalesExecutive()) {
            $telecallerTasksUrl = url('/telecaller/tasks?status=pending');

            // Reuse a recent unread "new_lead" notification to aggregate counts
            $existing = AppNotification::where('user_id', $user->id)
                ->where('type', AppNotification::TYPE_NEW_LEAD)
                ->whereNull('read_at')
                ->where('created_at', '>=', now()->subHours(2))
                ->orderBy('created_at', 'desc')
                ->first();

            if ($existing) {
                $data = is_array($existing->data) ? $existing->data : [];
                $leadIds = isset($data['lead_ids']) && is_array($data['lead_ids']) ? $data['lead_ids'] : [];

                // Avoid double-counting the same lead (some flows call notifyNewLead twice)
                if (!in_array($lead->id, $leadIds, true)) {
                    $leadIds[] = $lead->id;
                }

                $leadCount = count($leadIds);
                $message = "New {$leadCount} lead" . ($leadCount === 1 ? '' : 's') . " allocated, please call and complete task";

                $existing->update([
                    'title' => 'New Leads Allocated',
                    'message' => $message,
                    'action_type' => AppNotification::ACTION_LEAD,
                    'action_url' => $telecallerTasksUrl,
                    'data' => array_merge($data, [
                        'lead_count' => $leadCount,
                        'lead_ids' => $leadIds,
                        'last_lead_id' => $lead->id,
                        'last_lead_name' => $lead->name,
                    ]),
                ]);

                return $existing->fresh();
            }

            $message = "New 1 lead allocated, please call and complete task";

            $notification = AppNotification::create([
                'user_id' => $user->id,
                'type' => AppNotification::TYPE_NEW_LEAD,
                'title' => 'New Leads Allocated',
                'message' => $message,
                'action_type' => AppNotification::ACTION_LEAD,
                'action_url' => $telecallerTasksUrl,
                'data' => [
                    'lead_count' => 1,
                    'lead_ids' => [$lead->id],
                    'last_lead_id' => $lead->id,
                    'last_lead_name' => $lead->name,
                ],
            ]);

            // Broadcast via Pusher (only on create)
            event(new NewLeadNotification($notification));

            // FCM Push (background/closed app)
            SendFcmNotificationJob::dispatch(
                $user->id,
                'New Leads Allocated',
                $message,
                $telecallerTasksUrl,
                'new-lead-' . $lead->id
            );

            return $notification;
        }

        // Non-telecaller: keep per-lead notification
        $message = "New lead assigned: {$lead->name}";

        $notification = AppNotification::create([
            'user_id' => $user->id,
            'type' => AppNotification::TYPE_NEW_LEAD,
            'title' => 'New Lead Assigned',
            'message' => $message,
            'action_type' => AppNotification::ACTION_LEAD,
            'action_url' => $actionUrl,
            'data' => [
                'lead_id' => $lead->id,
                'lead_name' => $lead->name,
            ],
        ]);

        // Broadcast via Pusher
        event(new NewLeadNotification($notification));

        // FCM Push (background/closed app)
        SendFcmNotificationJob::dispatch(
            $user->id,
            'New Lead Assigned',
            $message,
            $actionUrl,
            'new-lead-' . $lead->id
        );

        return $notification;
    }

    /**
     * Notify user about new verification pending
     */
    public function notifyNewVerification(User $user, string $type, string $title, string $message, string $actionUrl, array $data = []): AppNotification
    {
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'type' => AppNotification::TYPE_NEW_VERIFICATION,
            'title' => $title,
            'message' => $message,
            'action_type' => AppNotification::ACTION_VERIFICATION,
            'action_url' => $actionUrl,
            'data' => array_merge([
                'verification_type' => $type,
            ], $data),
        ]);

        // Broadcast via Pusher
        event(new NewVerificationNotification($notification));

        return $notification;
    }

    /**
     * Notify user about upcoming follow-up
     */
    public function notifyFollowup(User $user, $followup, string $actionUrl): AppNotification
    {
        $leadName = $followup->lead ? $followup->lead->name : 'Lead';
        $message = "Follow-up reminder: {$leadName}";
        if ($followup->scheduled_at) {
            $message .= " at " . $followup->scheduled_at->format('M d, Y h:i A');
        }
        
        $notification = AppNotification::create([
            'user_id' => $user->id,
            'type' => AppNotification::TYPE_FOLLOWUP_REMINDER,
            'title' => 'Follow-up Reminder',
            'message' => $message,
            'action_type' => AppNotification::ACTION_FOLLOWUP,
            'action_url' => $actionUrl,
            'data' => [
                'followup_id' => $followup->id,
                'lead_id' => $followup->lead_id,
                'lead_name' => $leadName,
                'scheduled_at' => $followup->scheduled_at ? $followup->scheduled_at->toIso8601String() : null,
            ],
        ]);

        // Broadcast via Pusher
        event(new FollowupNotification($notification));

        return $notification;
    }

    /**
     * Notify user about a new site visit scheduled (telecaller bot)
     */
    public function notifySiteVisit(User $user, $siteVisit, string $actionUrl): AppNotification
    {
        $leadName = $siteVisit->lead->name ?? ($siteVisit->customer_name ?? 'Lead');
        $message = "Hurry! Site visit scheduled for your lead {$leadName}";

        $notification = AppNotification::create([
            'user_id' => $user->id,
            'type' => AppNotification::TYPE_SITE_VISIT,
            'title' => 'Site Visit Scheduled',
            'message' => $message,
            'action_type' => AppNotification::ACTION_LEAD,
            'action_url' => $actionUrl,
            'data' => [
                'site_visit_id' => $siteVisit->id,
                'lead_id' => $siteVisit->lead_id,
                'lead_name' => $leadName,
                'scheduled_at' => $siteVisit->scheduled_at ? (string) $siteVisit->scheduled_at : null,
            ],
        ]);

        // No extra broadcast event type needed; reuse generic "notification.new" channel via DB polling/pusher
        // (Pusher for AppNotification is handled by events elsewhere in the app)
        return $notification;
    }

    /**
     * Notify Telecaller about eligible site visit for incentive
     */
    public function notifyEligibleSiteVisitForIncentive(User $telecaller, $siteVisit, string $actionUrl): AppNotification
    {
        $leadName = $siteVisit->lead->name ?? ($siteVisit->customer_name ?? 'Lead');
        $message = "Your prospect '{$leadName}' site visit has been completed. Request incentive for this visit.";

        $notification = AppNotification::create([
            'user_id' => $telecaller->id,
            'type' => AppNotification::TYPE_NEW_LEAD, // Using existing type, or create new type if needed
            'title' => 'Eligible Site Visit for Incentive',
            'message' => $message,
            'action_type' => AppNotification::ACTION_LEAD,
            'action_url' => $actionUrl,
            'data' => [
                'site_visit_id' => $siteVisit->id,
                'lead_id' => $siteVisit->lead_id,
                'lead_name' => $leadName,
                'type' => 'site_visit_incentive_eligible',
            ],
        ]);

        // Broadcast via Pusher
        event(new NewLeadNotification($notification));

        return $notification;
    }

    /**
     * Notify user about a meeting scheduled (telecaller bot)
     */
    public function notifyMeeting(User $user, $meeting, string $actionUrl): AppNotification
    {
        $leadName = $meeting->lead->name ?? ($meeting->customer_name ?? 'Lead');
        $message = "Hurry! Meeting scheduled for your lead {$leadName}";

        $notification = AppNotification::create([
            'user_id' => $user->id,
            'type' => AppNotification::TYPE_MEETING,
            'title' => 'Meeting Scheduled',
            'message' => $message,
            'action_type' => AppNotification::ACTION_LEAD,
            'action_url' => $actionUrl,
            'data' => [
                'meeting_id' => $meeting->id,
                'lead_id' => $meeting->lead_id,
                'lead_name' => $leadName,
                'scheduled_at' => $meeting->scheduled_at ? (string) $meeting->scheduled_at : null,
            ],
        ]);

        return $notification;
    }

    /**
     * Notify all admin users when a new user is created
     */
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
            $notifications[] = AppNotification::create([
                'user_id' => $admin->id,
                'type' => AppNotification::TYPE_NEW_USER,
                'title' => $title,
                'message' => $message,
                'action_type' => AppNotification::ACTION_USER,
                'action_url' => $actionUrl,
                'data' => [
                    'new_user_id' => $newUser->id,
                    'new_user_name' => $newUser->name,
                    'new_user_email' => $newUser->email,
                ],
            ]);
        }

        return $notifications;
    }

    /**
     * Send admin broadcast message to users
     */
    public function sendBroadcast(User $sender, string $title, string $message, string $targetType = 'all_users', array $targetRoles = []): array
    {
        // Create broadcast message record
        $broadcast = BroadcastMessage::create([
            'sender_id' => $sender->id,
            'title' => $title,
            'message' => $message,
            'target_type' => $targetType,
            'target_roles' => $targetType === 'role_based' ? $targetRoles : null,
        ]);

        // Get target users
        $targetUsers = $this->getTargetUsers($targetType, $targetRoles);

        $notifications = [];
        foreach ($targetUsers as $user) {
            $notification = AppNotification::create([
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

            $notifications[] = $notification;
        }

        // Broadcast via Pusher to all users
        event(new AdminBroadcast($broadcast, $notifications));

        return [
            'broadcast' => $broadcast,
            'notifications' => $notifications,
            'sent_to' => count($targetUsers),
        ];
    }

    /**
     * Get target users based on target type and roles
     */
    private function getTargetUsers(string $targetType, array $targetRoles = []): \Illuminate\Support\Collection
    {
        if ($targetType === 'all_users') {
            return User::where('is_active', true)->get();
        }

        // Role-based targeting
        if (!empty($targetRoles)) {
            $roleIds = Role::whereIn('slug', $targetRoles)->pluck('id');
            return User::where('is_active', true)
                ->whereIn('role_id', $roleIds)
                ->get();
        }

        return collect();
    }

    /**
     * Notify CRM about pending closing verification
     */
    public function notifyClosingVerificationPending($siteVisit, int $requestedByUserId): void
    {
        $crmUsers = User::whereHas('role', function ($query) {
            $query->where('slug', Role::CRM);
        })->where('is_active', true)->get();

        $requestedBy = User::find($requestedByUserId);
        $leadName = $siteVisit->lead->name ?? ($siteVisit->customer_name ?? 'Lead');
        $message = "New closing request pending verification for lead: {$leadName}";

        foreach ($crmUsers as $crmUser) {
            AppNotification::create([
                'user_id' => $crmUser->id,
                'type' => AppNotification::TYPE_NEW_LEAD,
                'title' => 'Closing Verification Pending',
                'message' => $message,
                'action_type' => AppNotification::ACTION_LEAD,
                'action_url' => url('/crm/verifications'),
                'data' => [
                    'site_visit_id' => $siteVisit->id,
                    'lead_id' => $siteVisit->lead_id,
                    'lead_name' => $leadName,
                    'requested_by' => $requestedBy ? $requestedBy->name : 'Unknown',
                    'type' => 'closing_verification_pending',
                ],
            ]);
        }
    }

    /**
     * Notify user that closing has been verified
     */
    public function notifyClosingVerified($siteVisit, int $verifiedByUserId): void
    {
        $requestedBy = $siteVisit->creator;
        if (!$requestedBy) {
            return;
        }

        $verifiedBy = User::find($verifiedByUserId);
        $leadName = $siteVisit->lead->name ?? ($siteVisit->customer_name ?? 'Lead');
        $message = "Your closing request for lead: {$leadName} has been verified. You can now request incentives.";

        $notification = AppNotification::create([
            'user_id' => $requestedBy->id,
            'type' => AppNotification::TYPE_NEW_LEAD,
            'title' => 'Closing Verified',
            'message' => $message,
            'action_type' => AppNotification::ACTION_LEAD,
            'action_url' => url('/sales-manager/site-visits'),
            'data' => [
                'site_visit_id' => $siteVisit->id,
                'lead_id' => $siteVisit->lead_id,
                'lead_name' => $leadName,
                'verified_by' => $verifiedBy ? $verifiedBy->name : 'CRM',
                'type' => 'closing_verified',
            ],
        ]);

        event(new NewLeadNotification($notification));
    }

    /**
     * Notify user that closing has been rejected
     */
    public function notifyClosingRejected($siteVisit, int $rejectedByUserId, string $reason): void
    {
        $requestedBy = $siteVisit->creator;
        if (!$requestedBy) {
            return;
        }

        $rejectedBy = User::find($rejectedByUserId);
        $leadName = $siteVisit->lead->name ?? ($siteVisit->customer_name ?? 'Lead');
        $message = "Your closing request for lead: {$leadName} has been rejected. Reason: {$reason}";

        $notification = AppNotification::create([
            'user_id' => $requestedBy->id,
            'type' => AppNotification::TYPE_NEW_LEAD,
            'title' => 'Closing Rejected',
            'message' => $message,
            'action_type' => AppNotification::ACTION_LEAD,
            'action_url' => url('/sales-manager/site-visits'),
            'data' => [
                'site_visit_id' => $siteVisit->id,
                'lead_id' => $siteVisit->lead_id,
                'lead_name' => $leadName,
                'rejected_by' => $rejectedBy ? $rejectedBy->name : 'CRM',
                'rejection_reason' => $reason,
                'type' => 'closing_rejected',
            ],
        ]);

        event(new NewLeadNotification($notification));
    }

    /**
     * Notify Finance Manager about pending incentive request
     */
    public function notifyIncentiveRequestPending($incentive): void
    {
        $financeManagers = User::whereHas('role', function ($query) {
            $query->where('slug', Role::FINANCE_MANAGER);
        })->where('is_active', true)->get();

        $requestedBy = $incentive->user;
        $siteVisit = $incentive->siteVisit;
        $leadName = $siteVisit->lead->name ?? ($siteVisit->customer_name ?? 'Lead');
        $message = "New incentive request from {$requestedBy->name} for lead: {$leadName} (Amount: ₹{$incentive->amount})";

        foreach ($financeManagers as $financeManager) {
            $notification = AppNotification::create([
                'user_id' => $financeManager->id,
                'type' => AppNotification::TYPE_NEW_LEAD,
                'title' => 'Incentive Request Pending',
                'message' => $message,
                'action_type' => AppNotification::ACTION_LEAD,
                'action_url' => url('/finance-manager/incentives'),
                'data' => [
                    'incentive_id' => $incentive->id,
                    'site_visit_id' => $siteVisit->id,
                    'lead_id' => $siteVisit->lead_id,
                    'lead_name' => $leadName,
                    'requested_by' => $requestedBy->name,
                    'amount' => $incentive->amount,
                    'type' => 'incentive_request_pending',
                ],
            ]);

            event(new NewLeadNotification($notification));
        }
    }

    /**
     * Notify user that incentive has been approved
     */
    public function notifyIncentiveApproved($incentive): void
    {
        $requestedBy = $incentive->user;
        $siteVisit = $incentive->siteVisit;
        $leadName = $siteVisit->lead->name ?? ($siteVisit->customer_name ?? 'Lead');
        $message = "Your incentive request for lead: {$leadName} has been approved. Amount: ₹{$incentive->amount}";

        $notification = AppNotification::create([
            'user_id' => $requestedBy->id,
            'type' => AppNotification::TYPE_NEW_LEAD,
            'title' => 'Incentive Approved',
            'message' => $message,
            'action_type' => AppNotification::ACTION_LEAD,
            'action_url' => url('/sales-manager/site-visits'),
            'data' => [
                'incentive_id' => $incentive->id,
                'site_visit_id' => $siteVisit->id,
                'lead_id' => $siteVisit->lead_id,
                'lead_name' => $leadName,
                'amount' => $incentive->amount,
                'type' => 'incentive_approved',
            ],
        ]);

        event(new NewLeadNotification($notification));
    }

    /**
     * Notify user that incentive has been rejected
     */
    public function notifyIncentiveRejected($incentive, string $reason): void
    {
        $requestedBy = $incentive->user;
        $siteVisit = $incentive->siteVisit;
        $leadName = $siteVisit->lead->name ?? ($siteVisit->customer_name ?? 'Lead');
        $message = "Your incentive request for lead: {$leadName} has been rejected. Reason: {$reason}";

        $notification = AppNotification::create([
            'user_id' => $requestedBy->id,
            'type' => AppNotification::TYPE_NEW_LEAD,
            'title' => 'Incentive Rejected',
            'message' => $message,
            'action_type' => AppNotification::ACTION_LEAD,
            'action_url' => url('/sales-manager/site-visits'),
            'data' => [
                'incentive_id' => $incentive->id,
                'site_visit_id' => $siteVisit->id,
                'lead_id' => $siteVisit->lead_id,
                'lead_name' => $leadName,
                'rejection_reason' => $reason,
                'type' => 'incentive_rejected',
            ],
        ]);

        event(new NewLeadNotification($notification));
    }
}
