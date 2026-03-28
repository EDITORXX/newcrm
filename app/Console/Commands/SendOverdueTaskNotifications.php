<?php

namespace App\Console\Commands;

use App\Models\TelecallerTask;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendOverdueTaskNotifications extends Command
{
    protected $signature = 'notifications:overdue-tasks';

    protected $description = 'Send overdue task notifications to assigned users and their managers';

    public function __construct(protected NotificationService $notificationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $now = now();
        $repeatAfter = $now->copy()->subMinutes(30);

        $tasks = TelecallerTask::whereNull('completed_at')
            ->whereIn('status', ['pending', 'in_progress', 'rescheduled'])
            ->where('scheduled_at', '<', $now)
            ->where(function ($query) use ($repeatAfter) {
                $query->whereNull('overdue_notified_at')
                    ->orWhere('overdue_notified_at', '<=', $repeatAfter);
            })
            ->with(['lead', 'assignedTo.manager'])
            ->get();

        $notifiedCount = 0;

        foreach ($tasks as $task) {
            try {
                $notifications = $this->notificationService->notifyOverdueTask($task);
                $task->forceFill(['overdue_notified_at' => $now])->save();
                $notifiedCount += $notifications->count();
            } catch (\Exception $e) {
                $this->error("Failed to send overdue notification for task {$task->id}: " . $e->getMessage());
            }
        }

        $this->info("Sent {$notifiedCount} overdue task notification(s).");

        return self::SUCCESS;
    }
}
