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
        $oneHourLater = $now->copy()->addHour();
        $thirtyMinutesLater = $now->copy()->addMinutes(30);

        // Get follow-ups scheduled in next hour
        $followups = FollowUp::where('scheduled_at', '>=', $now)
            ->where('scheduled_at', '<=', $oneHourLater)
            ->whereNull('completed_at')
            ->where('status', 'scheduled')
            ->with(['creator', 'lead'])
            ->get();

        $notifiedCount = 0;

        foreach ($followups as $followup) {
            $user = $followup->creator;
            if (!$user) {
                continue;
            }

            // Check if notification already sent (to avoid duplicates)
            $existingNotification = \App\Models\AppNotification::where('user_id', $user->id)
                ->where('type', \App\Models\AppNotification::TYPE_FOLLOWUP_REMINDER)
                ->whereJsonContains('data->followup_id', $followup->id)
                ->where('created_at', '>=', $now->copy()->subHour())
                ->first();

            if ($existingNotification) {
                continue; // Already notified
            }

            $actionUrl = url('/follow-ups/' . $followup->id);
            
            try {
                $this->notificationService->notifyFollowup($user, $followup, $actionUrl);
                $notifiedCount++;
            } catch (\Exception $e) {
                $this->error("Failed to send notification for follow-up {$followup->id}: " . $e->getMessage());
            }
        }

        $this->info("Sent {$notifiedCount} follow-up reminder notifications.");
        
        return 0;
    }
}
