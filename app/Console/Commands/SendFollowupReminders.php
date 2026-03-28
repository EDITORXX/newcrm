<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use App\Models\FollowUp;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendFollowupReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:followup-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send follow-up reminder notifications to users';

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        parent::__construct();
        $this->notificationService = $notificationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();
        $windowStart = $now->copy()->addMinutes(5)->startOfMinute();
        $windowEnd = $windowStart->copy()->endOfMinute();

        $followups = FollowUp::whereBetween('scheduled_at', [$windowStart, $windowEnd])
            ->whereNull('completed_at')
            ->where('status', 'scheduled')
            ->whereNull('reminder_sent_at')
            ->with(['creator.manager', 'lead.activeAssignments.assignedTo.manager'])
            ->get();

        $notifiedCount = 0;

        foreach ($followups as $followup) {
            try {
                $notifications = $this->notificationService->notifyFollowupReminder($followup);
                $followup->forceFill(['reminder_sent_at' => $now])->save();
                $notifiedCount += $notifications->count();
            } catch (\Exception $e) {
                $this->error("Failed to send notification for follow-up {$followup->id}: " . $e->getMessage());
            }
        }

        $this->info("Sent {$notifiedCount} follow-up reminder notifications.");
        
        return 0;
    }
}
