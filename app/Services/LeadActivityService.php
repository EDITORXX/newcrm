<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\ActivityLog;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class LeadActivityService
{
    /**
     * Get complete timeline of all activities for a lead
     */
    public function getTimeline(Lead $lead): Collection
    {
        $activities = collect();

        // 1. Lead Created
        if ($lead->created_at) {
            $activities->push([
                'type' => 'created',
                'title' => 'Lead Created',
                'description' => "Lead '{$lead->name}' was created",
                'user' => $lead->creator,
                'timestamp' => $lead->created_at,
                'icon' => 'fa-plus-circle',
                'color' => '#10b981', // green
                'metadata' => [
                    'source' => $lead->source,
                    'status' => $lead->status,
                ],
            ]);
        }

        // 2. Activity Logs
        $activityLogs = ActivityLog::where('model_type', 'Lead')
            ->where('model_id', $lead->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        $taskIdsFromActivityLog = collect();
        $telecallerTaskIdsFromActivityLog = collect();

        foreach ($activityLogs as $log) {
            $entry = [
                'type' => $this->getActivityType($log->action),
                'title' => $this->getActivityTitle($log),
                'description' => $log->description ?? $this->getActivityDescription($log),
                'user' => $log->user,
                'timestamp' => $log->created_at,
                'icon' => $this->getActivityIcon($log->action),
                'color' => $this->getActivityColor($log->action),
                'metadata' => [
                    'old_values' => $log->old_values,
                    'new_values' => $log->new_values,
                    'action' => $log->action,
                ],
            ];
            $activities->push($entry);

            // Track task_ids already in timeline (from ActivityLog) to avoid duplicate in step 9
            if ($log->action === 'task_created' && $log->new_values && isset($log->new_values['task_id'])) {
                $taskId = $log->new_values['task_id'];
                $model = $log->new_values['task_model'] ?? null;
                if ($model === 'Task') {
                    $taskIdsFromActivityLog->push($taskId);
                } elseif ($model === 'TelecallerTask') {
                    $telecallerTaskIdsFromActivityLog->push($taskId);
                } else {
                    $taskIdsFromActivityLog->push($taskId);
                    $telecallerTaskIdsFromActivityLog->push($taskId);
                }
            }
        }

        // 3. Lead Assignments
        foreach ($lead->assignments()->with(['assignedTo', 'assignedBy'])->orderBy('assigned_at', 'desc')->get() as $assignment) {
            $activities->push([
                'type' => 'assigned',
                'title' => 'Lead Assigned',
                'description' => $assignment->is_active 
                    ? "Assigned to {$assignment->assignedTo->name}"
                    : "Unassigned from {$assignment->assignedTo->name}",
                'user' => $assignment->assignedBy,
                'timestamp' => $assignment->assigned_at ?? $assignment->created_at,
                'icon' => $assignment->is_active ? 'fa-user-plus' : 'fa-user-minus',
                'color' => $assignment->is_active ? '#3b82f6' : '#ef4444',
                'metadata' => [
                    'assigned_to' => $assignment->assignedTo->name,
                    'is_active' => $assignment->is_active,
                ],
            ]);
        }

        // 4. Call Logs
        foreach ($lead->callLogs()->with('user')->orderBy('created_at', 'desc')->get() as $callLog) {
            $callType = $callLog->call_type === 'incoming' ? 'Inbound' : 'Outbound';
            $duration = $callLog->duration ? $this->formatDuration($callLog->duration) : 'N/A';
            
            $activities->push([
                'type' => 'call',
                'title' => "{$callType} Call",
                'description' => "{$callType} call with {$lead->name}. Duration: {$duration}",
                'user' => $callLog->user,
                'timestamp' => $callLog->created_at,
                'icon' => $callLog->call_type === 'incoming' ? 'fa-phone-alt' : 'fa-phone',
                'color' => $callLog->call_type === 'incoming' ? '#10b981' : '#3b82f6',
                'metadata' => [
                    'direction' => $callLog->direction,
                    'duration' => $callLog->duration,
                    'recording_url' => $callLog->recording_url,
                    'status' => $callLog->status,
                ],
            ]);
        }

        // 5. Site Visits
        foreach ($lead->siteVisits()->with(['creator', 'assignedTo', 'verifiedBy', 'closingVerifiedBy', 'rescheduledBy', 'incentives.user'])->orderBy('created_at', 'desc')->get() as $siteVisit) {
            // Site Visit Created event
            $projectsText = $siteVisit->project ? ". Projects: " . $siteVisit->project : '';
            $activities->push([
                'type' => 'site_visit_created',
                'title' => 'Site Visit Created',
                'description' => "Site visit scheduled for {$lead->name}" . 
                    ($siteVisit->scheduled_at ? " on " . $siteVisit->scheduled_at->format('M d, Y h:i A') : '') .
                    $projectsText,
                'user' => $siteVisit->creator,
                'timestamp' => $siteVisit->created_at,
                'icon' => 'fa-calendar-plus',
                'color' => '#3b82f6', // blue
                'metadata' => [
                    'scheduled_at' => $siteVisit->scheduled_at,
                    'project' => $siteVisit->project,
                    'status' => $siteVisit->status,
                ],
            ]);

            if ($siteVisit->is_rescheduled && $siteVisit->rescheduled_at) {
                $activities->push([
                    'type' => 'site_visit_rescheduled',
                    'title' => 'Site Visit Rescheduled',
                    'description' => "Site visit rescheduled for {$lead->name}" .
                        ($siteVisit->scheduled_at ? " to " . $siteVisit->scheduled_at->format('M d, Y h:i A') : '') .
                        ($siteVisit->reschedule_reason ? ". Reason: {$siteVisit->reschedule_reason}" : ''),
                    'user' => $siteVisit->rescheduledBy ?? $siteVisit->creator,
                    'timestamp' => $siteVisit->rescheduled_at,
                    'icon' => 'fa-calendar-day',
                    'color' => '#f59e0b',
                    'metadata' => [
                        'scheduled_at' => $siteVisit->scheduled_at,
                        'reschedule_reason' => $siteVisit->reschedule_reason,
                        'reschedule_count' => $siteVisit->reschedule_count,
                    ],
                ]);
            }

            // Site Visit Completed event (only if status is completed)
            if ($siteVisit->status === 'completed' && $siteVisit->completed_at) {
                $projectsVisitedText = $siteVisit->project ? ". Projects visited: " . $siteVisit->project : '';
                $activities->push([
                    'type' => 'site_visit_completed',
                    'title' => 'Site Visit Completed',
                    'description' => "Site visit completed for {$lead->name}" . $projectsVisitedText,
                    'user' => $siteVisit->creator,
                    'timestamp' => $siteVisit->completed_at,
                    'icon' => 'fa-check-circle',
                    'color' => '#10b981', // green
                    'metadata' => [
                        'completed_at' => $siteVisit->completed_at,
                        'project' => $siteVisit->project,
                        'rating' => $siteVisit->rating,
                    ],
                ]);
            }

            // If verified, add verification activity
            if ($siteVisit->verified_at && $siteVisit->verifiedBy) {
                $activities->push([
                    'type' => 'site_visit_verified',
                    'title' => 'Site Visit Verified',
                    'description' => "Site visit verified by {$siteVisit->verifiedBy->name}",
                    'user' => $siteVisit->verifiedBy,
                    'timestamp' => $siteVisit->verified_at,
                    'icon' => 'fa-check-circle',
                    'color' => '#10b981',
                    'metadata' => [
                        'verification_status' => $siteVisit->verification_status,
                    ],
                ]);
            }

            if ($siteVisit->closing_verification_status === 'pending' && $siteVisit->converted_to_closer_at) {
                $activities->push([
                    'type' => 'close_requested',
                    'title' => 'Close Requested',
                    'description' => "Close request submitted for {$lead->name}",
                    'user' => $siteVisit->creator,
                    'timestamp' => $siteVisit->converted_to_closer_at,
                    'icon' => 'fa-file-signature',
                    'color' => '#2563eb',
                    'metadata' => [
                        'closing_verification_status' => $siteVisit->closing_verification_status,
                    ],
                ]);
            }

            if ($siteVisit->closing_verified_at && $siteVisit->closingVerifiedBy) {
                $activities->push([
                    'type' => 'close_verified',
                    'title' => 'Close Verified',
                    'description' => "Close verified by {$siteVisit->closingVerifiedBy->name}",
                    'user' => $siteVisit->closingVerifiedBy,
                    'timestamp' => $siteVisit->closing_verified_at,
                    'icon' => 'fa-stamp',
                    'color' => '#16a34a',
                    'metadata' => [
                        'closing_verification_status' => $siteVisit->closing_verification_status,
                    ],
                ]);
            }

            if (!empty($siteVisit->kyc_documents) && $siteVisit->updated_at) {
                $activities->push([
                    'type' => 'kyc_submitted',
                    'title' => 'KYC Submitted',
                    'description' => "KYC documents submitted for {$lead->name}",
                    'user' => $siteVisit->creator,
                    'timestamp' => $siteVisit->updated_at,
                    'icon' => 'fa-id-card',
                    'color' => '#7c3aed',
                    'metadata' => [
                        'kyc_documents_count' => is_array($siteVisit->kyc_documents) ? count($siteVisit->kyc_documents) : 0,
                    ],
                ]);
            }

            foreach ($siteVisit->incentives as $incentive) {
                $activities->push([
                    'type' => 'incentive_submitted',
                    'title' => 'Incentive Submitted',
                    'description' => "Incentive request of ₹{$incentive->amount} submitted",
                    'user' => $incentive->user,
                    'timestamp' => $incentive->created_at,
                    'icon' => 'fa-money-bill-wave',
                    'color' => '#b45309',
                    'metadata' => [
                        'status' => $incentive->status,
                        'amount' => $incentive->amount,
                        'type' => $incentive->type,
                    ],
                ]);
            }
        }

        // 6. Follow-ups
        foreach ($lead->followUps()->with('creator')->orderBy('created_at', 'desc')->get() as $followUp) {
            $activities->push([
                'type' => 'followup',
                'title' => 'Follow-up ' . ucfirst($followUp->status),
                'description' => "Follow-up {$followUp->status}" . 
                    ($followUp->scheduled_at ? " scheduled for " . $followUp->scheduled_at->format('M d, Y h:i A') : ''),
                'user' => $followUp->creator,
                'timestamp' => $followUp->created_at,
                'icon' => $this->getFollowUpIcon($followUp->status),
                'color' => $this->getFollowUpColor($followUp->status),
                'metadata' => [
                    'type' => $followUp->type,
                    'status' => $followUp->status,
                    'scheduled_at' => $followUp->scheduled_at,
                    'completed_at' => $followUp->completed_at,
                ],
            ]);
        }

        // 6.5. Next Follow-up (upcoming scheduled follow-up)
        $nextFollowUp = $lead->followUps()
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>', now())
            ->orderBy('scheduled_at', 'asc')
            ->with('creator')
            ->first();

        if ($nextFollowUp) {
            $activities->push([
                'type' => 'next_followup',
                'title' => 'Next Follow-up Scheduled',
                'description' => "Follow-up scheduled for " . $nextFollowUp->scheduled_at->format('M d, Y h:i A'),
                'user' => $nextFollowUp->creator,
                'timestamp' => $nextFollowUp->created_at,
                'icon' => 'fa-calendar-check',
                'color' => '#f59e0b', // amber
                'metadata' => [
                    'scheduled_at' => $nextFollowUp->scheduled_at,
                    'type' => $nextFollowUp->type,
                ],
            ]);
        }

        // 7. Meetings
        foreach ($lead->meetings()->with(['creator', 'assignedTo', 'verifiedBy', 'rescheduledBy'])->orderBy('created_at', 'desc')->get() as $meeting) {
            $activities->push([
                'type' => 'meeting',
                'title' => 'Meeting ' . ucfirst($meeting->status),
                'description' => "Meeting {$meeting->status}" . 
                    ($meeting->scheduled_at ? " scheduled for " . $meeting->scheduled_at->format('M d, Y h:i A') : ''),
                'user' => $meeting->creator,
                'timestamp' => $meeting->created_at,
                'icon' => $this->getMeetingIcon($meeting->status),
                'color' => $this->getMeetingColor($meeting->status),
                'metadata' => [
                    'status' => $meeting->status,
                    'scheduled_at' => $meeting->scheduled_at,
                    'verification_status' => $meeting->verification_status,
                    'customer_name' => $meeting->customer_name,
                ],
            ]);

            if ($meeting->is_rescheduled && $meeting->rescheduled_at) {
                $activities->push([
                    'type' => 'meeting_rescheduled',
                    'title' => 'Meeting Rescheduled',
                    'description' => "Meeting rescheduled for {$lead->name}" .
                        ($meeting->scheduled_at ? " to " . $meeting->scheduled_at->format('M d, Y h:i A') : '') .
                        ($meeting->reschedule_reason ? ". Reason: {$meeting->reschedule_reason}" : ''),
                    'user' => $meeting->rescheduledBy ?? $meeting->creator,
                    'timestamp' => $meeting->rescheduled_at,
                    'icon' => 'fa-calendar-day',
                    'color' => '#f59e0b',
                    'metadata' => [
                        'scheduled_at' => $meeting->scheduled_at,
                        'reschedule_reason' => $meeting->reschedule_reason,
                        'reschedule_count' => $meeting->reschedule_count,
                    ],
                ]);
            }

            // If verified, add verification activity
            if ($meeting->verified_at && $meeting->verifiedBy) {
                $activities->push([
                    'type' => 'meeting_verified',
                    'title' => 'Meeting Verified',
                    'description' => "Meeting verified by {$meeting->verifiedBy->name}",
                    'user' => $meeting->verifiedBy,
                    'timestamp' => $meeting->verified_at,
                    'icon' => 'fa-check-circle',
                    'color' => '#10b981',
                    'metadata' => [
                        'verification_status' => $meeting->verification_status,
                    ],
                ]);
            }
        }

        // 8. Prospects
        foreach ($lead->prospects()->with(['createdBy', 'verifiedBy'])->orderBy('created_at', 'desc')->get() as $prospect) {
            $activities->push([
                'type' => 'prospect',
                'title' => 'Prospect Created',
                'description' => "Prospect created for {$lead->name}" . 
                    ($prospect->lead_score ? " with lead score: {$prospect->lead_score}/5" : ''),
                'user' => $prospect->createdBy,
                'timestamp' => $prospect->created_at,
                'icon' => 'fa-user-check',
                'color' => '#8b5cf6',
                'metadata' => [
                    'verification_status' => $prospect->verification_status,
                    'lead_score' => $prospect->lead_score,
                ],
            ]);

            // If verified, add verification activity
            if ($prospect->verified_at && $prospect->verifiedBy) {
                $activities->push([
                    'type' => 'prospect_verified',
                    'title' => 'Prospect Verified',
                    'description' => "Prospect verified by {$prospect->verifiedBy->name}",
                    'user' => $prospect->verifiedBy,
                    'timestamp' => $prospect->verified_at,
                    'icon' => 'fa-check-circle',
                    'color' => '#10b981',
                    'metadata' => [
                        'verification_status' => $prospect->verification_status,
                    ],
                ]);
            }
        }

        // 9. Tasks Created (for this lead) – skip if already in timeline from ActivityLog (step 2)
        $tasks = \App\Models\Task::where('lead_id', $lead->id)
            ->with(['assignedTo', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($tasks as $task) {
            if ($taskIdsFromActivityLog->contains($task->id)) {
                continue;
            }
            $activities->push([
                'type' => 'task_created',
                'title' => 'Calling Task Created',
                'description' => "Calling task created for {$lead->name}" .
                    ($task->assignedTo ? " (Assigned to {$task->assignedTo->name})" : ''),
                'user' => $task->creator,
                'timestamp' => $task->created_at,
                'icon' => 'fa-phone',
                'color' => '#3b82f6', // blue
                'metadata' => [
                    'task_id' => $task->id,
                    'task_type' => $task->type,
                    'assigned_to' => $task->assignedTo ? $task->assignedTo->name : null,
                    'status' => $task->status,
                ],
            ]);
        }

        $telecallerTasks = \App\Models\TelecallerTask::where('lead_id', $lead->id)
            ->with(['assignedTo', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($telecallerTasks as $task) {
            if ($telecallerTaskIdsFromActivityLog->contains($task->id)) {
                continue;
            }
            $activities->push([
                'type' => 'task_created',
                'title' => 'Calling Task Created',
                'description' => "Calling task created for {$lead->name}" .
                    ($task->assignedTo ? " (Assigned to {$task->assignedTo->name})" : ''),
                'user' => $task->createdBy ?? $task->assignedTo,
                'timestamp' => $task->created_at,
                'icon' => 'fa-phone',
                'color' => '#3b82f6', // blue
                'metadata' => [
                    'task_id' => $task->id,
                    'task_type' => $task->task_type,
                    'assigned_to' => $task->assignedTo ? $task->assignedTo->name : null,
                    'status' => $task->status,
                ],
            ]);
        }

        // 10. Status Changes (from lead history or activity logs)
        // This is already covered in ActivityLog, but we can add explicit status change tracking
        if ($lead->marked_dead_at) {
            $activities->push([
                'type' => 'status_changed',
                'title' => 'Lead Marked as Dead',
                'description' => "Lead marked as dead. Reason: {$lead->dead_reason}",
                'user' => $lead->markedDeadBy,
                'timestamp' => $lead->marked_dead_at,
                'icon' => 'fa-times-circle',
                'color' => '#ef4444',
                'metadata' => [
                    'status' => 'dead',
                    'reason' => $lead->dead_reason,
                    'stage' => $lead->dead_at_stage,
                ],
            ]);
        }

        // Sort by timestamp (newest first)
        return $activities->sortByDesc('timestamp')->values();
    }

    private function getActivityType(string $action): string
    {
        return match($action) {
            'created' => 'created',
            'updated' => 'updated',
            'deleted' => 'deleted',
            'assigned' => 'assigned',
            'task_created' => 'task_created',
            default => 'activity',
        };
    }

    private function getActivityTitle($log): string
    {
        if ($log->action === 'task_created') {
            return 'Calling Task Created';
        }
        
        if ($log->old_values && $log->new_values && isset($log->old_values['status']) && isset($log->new_values['status'])) {
            return 'Status Changed';
        }
        
        return ucfirst(str_replace('_', ' ', $log->action));
    }

    private function getActivityDescription($log): string
    {
        if ($log->old_values && $log->new_values) {
            if (isset($log->old_values['status']) && isset($log->new_values['status'])) {
                return "Status changed from '{$log->old_values['status']}' to '{$log->new_values['status']}'";
            }
            
            $changes = [];
            foreach ($log->new_values as $key => $value) {
                if (isset($log->old_values[$key]) && $log->old_values[$key] != $value) {
                    $changes[] = "{$key}: {$log->old_values[$key]} → {$value}";
                }
            }
            
            return !empty($changes) ? implode(', ', $changes) : 'Updated';
        }
        
        return ucfirst($log->action);
    }

    private function getActivityIcon(string $action): string
    {
        return match($action) {
            'created' => 'fa-plus-circle',
            'updated' => 'fa-edit',
            'deleted' => 'fa-trash',
            'assigned' => 'fa-user-plus',
            'task_created' => 'fa-phone',
            default => 'fa-info-circle',
        };
    }

    private function getActivityColor(string $action): string
    {
        return match($action) {
            'created' => '#10b981',
            'updated' => '#3b82f6',
            'deleted' => '#ef4444',
            'assigned' => '#8b5cf6',
            'task_created' => '#3b82f6',
            default => '#6b7280',
        };
    }

    private function getSiteVisitIcon(string $status): string
    {
        return match($status) {
            'scheduled' => 'fa-calendar-alt',
            'completed' => 'fa-check-circle',
            'cancelled' => 'fa-times-circle',
            default => 'fa-map-marker-alt',
        };
    }

    private function getSiteVisitColor(string $status): string
    {
        return match($status) {
            'scheduled' => '#3b82f6',
            'completed' => '#10b981',
            'cancelled' => '#ef4444',
            default => '#6b7280',
        };
    }

    private function getFollowUpIcon(string $status): string
    {
        return match($status) {
            'scheduled' => 'fa-calendar-check',
            'completed' => 'fa-check-circle',
            'missed' => 'fa-exclamation-circle',
            'cancelled' => 'fa-times-circle',
            default => 'fa-clock',
        };
    }

    private function getFollowUpColor(string $status): string
    {
        return match($status) {
            'scheduled' => '#3b82f6',
            'completed' => '#10b981',
            'missed' => '#f59e0b',
            'cancelled' => '#ef4444',
            default => '#6b7280',
        };
    }

    private function getMeetingIcon(string $status): string
    {
        return match($status) {
            'scheduled' => 'fa-calendar-alt',
            'completed' => 'fa-check-circle',
            'cancelled' => 'fa-times-circle',
            default => 'fa-handshake',
        };
    }

    private function getMeetingColor(string $status): string
    {
        return match($status) {
            'scheduled' => '#3b82f6',
            'completed' => '#10b981',
            'cancelled' => '#ef4444',
            default => '#6b7280',
        };
    }

    private function formatDuration(int $seconds): string
    {
        $minutes = floor($seconds / 60);
        $secs = $seconds % 60;
        
        if ($minutes > 0) {
            return "{$minutes}m {$secs}s";
        }
        
        return "{$secs}s";
    }
}
