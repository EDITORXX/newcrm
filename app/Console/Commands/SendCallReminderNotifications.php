<?php

namespace App\Console\Commands;

use App\Models\TelecallerTask;
use App\Models\AppNotification;
use App\Jobs\SendBrowserNotificationJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendCallReminderNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telecaller:send-reminder-notifications';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send 10-minute reminder notifications for scheduled calls';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for calls due in next 10 minutes...');

        $now = now();
        $tenMinutesLater = $now->copy()->addMinutes(10);

        $tasksToNotify = TelecallerTask::where('status', '!=', 'completed')
            ->whereBetween('scheduled_at', [$now, $tenMinutesLater])
            ->whereNull('notification_sent_at')
            ->with(['lead', 'assignedTo'])
            ->get();

        $count = 0;
        foreach ($tasksToNotify as $task) {
            $leadName = $task->lead ? $task->lead->name : 'Unknown';
            $scheduledTime = $task->scheduled_at->format('h:i A');
            
            // Create in-app notification
            $notification = AppNotification::create([
                'user_id' => $task->assigned_to,
                'telecaller_task_id' => $task->id,
                'type' => 'call_reminder',
                'title' => 'Call Reminder',
                'message' => "You have a call scheduled with {$leadName} at {$scheduledTime}",
                'data' => [
                    'task_id' => $task->id,
                    'lead_id' => $task->lead_id,
                    'lead_name' => $leadName,
                    'lead_phone' => $task->lead ? $task->lead->phone : null,
                    'scheduled_at' => $task->scheduled_at->toIso8601String(),
                ],
            ]);

            // Queue browser notification
            SendBrowserNotificationJob::dispatch($task->assigned_to, [
                'title' => 'Call Reminder',
                'body' => "You have a call scheduled with {$leadName} at {$scheduledTime}",
                'icon' => '/favicon.ico',
                'data' => [
                    'task_id' => $task->id,
                    'notification_id' => $notification->id,
                    'url' => url('/telecaller/tasks?status=pending&task_id=' . $task->id),
                ],
            ]);

            // Mark notification as sent
            $task->update([
                'notification_sent_at' => now(),
            ]);

            $count++;
            
            Log::info("Sent call reminder notification", [
                'task_id' => $task->id,
                'user_id' => $task->assigned_to,
                'scheduled_at' => $task->scheduled_at,
            ]);
        }

        $this->info("Sent {$count} reminder notification(s).");
        
        return Command::SUCCESS;
    }
}
