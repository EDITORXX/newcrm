<?php

namespace App\Listeners;

use App\Events\LeadAssigned;
use App\Services\NotificationService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendNewLeadNotification implements ShouldQueue
{
    use InteractsWithQueue;

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function handle(LeadAssigned $event): void
    {
        $assignedUser = User::find($event->assignedTo);

        if ($assignedUser) {
            // Create notification via NotificationService
            $actionUrl = url('/leads/' . $event->lead->id);
            
            // For sales executives, use tasks URL
            if ($assignedUser->isSalesExecutive()) {
                $task = \App\Models\TelecallerTask::where('lead_id', $event->lead->id)
                    ->where('assigned_to', $assignedUser->id)
                    ->first();
                if ($task) {
                    $actionUrl = url('/telecaller/tasks?status=pending&task_id=' . $task->id);
                }
            }
            
            $this->notificationService->notifyNewLead($assignedUser, $event->lead, $actionUrl);
        }
    }
}
