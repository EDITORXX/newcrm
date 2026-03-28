<?php

namespace App\Console\Commands;

use App\Models\FollowUp;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendOverdueFollowupNotifications extends Command
{
    protected $signature = 'notifications:overdue-followups';

    protected $description = 'Send overdue follow-up notifications to the responsible user and manager';

    public function __construct(protected NotificationService $notificationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $now = now();
        $repeatAfter = $now->copy()->subMinutes(30);

        $followups = FollowUp::whereNull('completed_at')
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<', $now)
            ->where(function ($query) use ($repeatAfter) {
                $query->whereNull('overdue_notified_at')
                    ->orWhere('overdue_notified_at', '<=', $repeatAfter);
            })
            ->with(['creator.manager', 'lead.activeAssignments.assignedTo.manager'])
            ->get();

        $notifiedCount = 0;

        foreach ($followups as $followup) {
            try {
                $notifications = $this->notificationService->notifyOverdueFollowup($followup);
                $followup->forceFill(['overdue_notified_at' => $now])->save();
                $notifiedCount += $notifications->count();
            } catch (\Exception $e) {
                $this->error("Failed to send overdue notification for follow-up {$followup->id}: " . $e->getMessage());
            }
        }

        $this->info("Sent {$notifiedCount} overdue follow-up notification(s).");

        return self::SUCCESS;
    }
}
