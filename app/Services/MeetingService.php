<?php

namespace App\Services;

use App\Models\Meeting;
use App\Models\TelecallerTask;
use App\Models\User;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MeetingService
{
    /**
     * Create meeting with optional pre-meeting reminder task
     */
    public function createMeetingWithReminder(array $data, User $creator): Meeting
    {
        DB::beginTransaction();
        
        try {
            // Set creator and initial status
            $data['created_by'] = $creator->id;
            $data['assigned_to'] = $data['assigned_to'] ?? $creator->id;
            $data['status'] = $data['status'] ?? 'scheduled';
            $data['verification_status'] = $data['verification_status'] ?? 'pending';
            
            // Get lead's customer name and phone if not provided
            if (!empty($data['lead_id'])) {
                $lead = Lead::find($data['lead_id']);
                if ($lead) {
                    $data['customer_name'] = $data['customer_name'] ?? $lead->name;
                    $data['phone'] = $data['phone'] ?? $lead->phone;
                } else {
                    throw new \Exception("Lead with ID {$data['lead_id']} not found");
                }
            }
            
            // Validate required fields
            if (empty($data['customer_name'])) {
                throw new \Exception("Customer name is required");
            }
            if (empty($data['phone'])) {
                throw new \Exception("Phone number is required");
            }
            if (empty($data['scheduled_at'])) {
                throw new \Exception("Scheduled date and time is required");
            }
            
            // Validate location for offline meetings (already validated in controller, but double-check)
            if (($data['meeting_mode'] ?? 'offline') === 'offline' && (empty($data['location']) || trim($data['location']) === '')) {
                throw new \Exception("Location is required for offline meetings");
            }
            
            // Set date_of_visit from scheduled_at (required field)
            if (!empty($data['scheduled_at'])) {
                $scheduledAt = Carbon::parse($data['scheduled_at']);
                $data['date_of_visit'] = $data['date_of_visit'] ?? $scheduledAt->format('Y-m-d');
            } else {
                // Fallback to today if scheduled_at is not provided (shouldn't happen due to validation)
                $data['date_of_visit'] = $data['date_of_visit'] ?? now()->format('Y-m-d');
            }
            
            // Create meeting
            $meeting = Meeting::create($data);

            if (!empty($lead)) {
                $lead->updateStatusIfAllowed('meeting_scheduled');
            }
            
            // Create pre-meeting reminder task if enabled
            if (!empty($data['reminder_enabled']) && !empty($data['scheduled_at'])) {
                $reminderMinutes = $data['reminder_minutes'] ?? 5;
                $scheduledAt = Carbon::parse($data['scheduled_at']);
                $reminderTime = $scheduledAt->copy()->subMinutes($reminderMinutes);
                
                // Only create task if reminder time is in the future
                if ($reminderTime->isFuture()) {
                    $assignedUser = User::with('role')->find($meeting->assigned_to);
                    
                    // Check if assigned user is Senior Manager - use Task model, otherwise TelecallerTask
                    if ($assignedUser && ($assignedUser->isSalesManager() || $assignedUser->isSalesHead() || $assignedUser->isAssistantSalesManager())) {
                        // Create Task for Senior Manager/Head/Executive
                        // Note: pre_meeting_call_task_id only references TelecallerTask, so we don't set it for Task model
                        $task = \App\Models\Task::create([
                            'lead_id' => $meeting->lead_id,
                            'assigned_to' => $meeting->assigned_to,
                            'type' => 'phone_call',
                            'status' => 'pending',
                            'scheduled_at' => $reminderTime,
                            'title' => "Pre-meeting reminder call - {$meeting->customer_name}",
                            'description' => "Pre-meeting reminder call for meeting scheduled at " . $scheduledAt->format('Y-m-d H:i'),
                            'notes' => "Pre-meeting reminder call for meeting scheduled at " . $scheduledAt->format('Y-m-d H:i') . " | Meeting ID: {$meeting->id}",
                            'created_by' => $creator->id,
                        ]);
                        // Don't set pre_meeting_call_task_id for Task model (it only references TelecallerTask)
                    } else {
                        // Create TelecallerTask for Telecallers
                        $task = TelecallerTask::create([
                            'lead_id' => $meeting->lead_id,
                            'meeting_id' => $meeting->id,
                            'assigned_to' => $meeting->assigned_to,
                            'task_type' => 'pre_meeting_reminder',
                            'status' => 'pending',
                            'scheduled_at' => $reminderTime,
                            'notes' => "Pre-meeting reminder call for meeting scheduled at " . $scheduledAt->format('Y-m-d H:i'),
                            'created_by' => $creator->id,
                        ]);
                        
                        // Link TelecallerTask to meeting (only TelecallerTask can be referenced by pre_meeting_call_task_id)
                        $meeting->pre_meeting_call_task_id = $task->id;
                        $meeting->save();
                    }
                }
            }
            
            DB::commit();
            
            return $meeting->fresh(['lead', 'preMeetingCallTask', 'assignedTo']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create meeting with reminder', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);
            throw $e;
        }
    }
    
    /**
     * Handle pre-meeting call completion
     */
    public function handlePreCallComplete(Meeting $meeting, string $action, ?string $notes = null, ?int $userId = null): array
    {
        DB::beginTransaction();
        
        try {
            // Mark calling task as completed if exists
            if ($meeting->pre_meeting_call_task_id) {
                $task = TelecallerTask::find($meeting->pre_meeting_call_task_id);
                if ($task && $task->status !== 'completed') {
                    $task->status = 'completed';
                    $task->completed_at = now();
                    $task->outcome = $action;
                    $task->notes = ($task->notes ? $task->notes . "\n" : '') . ($notes ?? "Call completed with action: $action");
                    $task->save();
                }
            }
            
            $result = ['action' => $action, 'meeting_id' => $meeting->id];
            
            // Handle based on action
            switch ($action) {
                case 'confirm':
                    $meeting->confirmMeeting();
                    $result['message'] = 'Meeting confirmed! Customer will join.';
                    $result['status'] = 'confirmed';
                    break;
                    
                case 'cancel':
                    $meeting->cancelMeeting($userId ?? auth()->id(), 'Customer cancelled via pre-meeting call');
                    $result['message'] = 'Meeting has been cancelled.';
                    $result['status'] = 'cancelled';
                    break;
                    
                case 'reschedule':
                    // Return instructions for frontend to handle reschedule flow
                    $result['message'] = 'Please reschedule the meeting.';
                    $result['status'] = 'pending_reschedule';
                    $result['require_reschedule'] = true;
                    break;
                    
                default:
                    throw new \InvalidArgumentException("Invalid action: $action");
            }
            
            DB::commit();
            
            return $result;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to handle pre-call complete', [
                'error' => $e->getMessage(),
                'meeting_id' => $meeting->id,
                'action' => $action,
            ]);
            throw $e;
        }
    }
    
    /**
     * Cancel old meeting and create new one (reschedule)
     */
    public function cancelAndReschedule(Meeting $oldMeeting, array $newData, User $user): Meeting
    {
        DB::beginTransaction();
        
        try {
            // Cancel old meeting
            $oldMeeting->cancelMeeting($user->id, 'Rescheduled');
            
            // Prepare new meeting data based on old meeting
            $newMeetingData = [
                'lead_id' => $oldMeeting->lead_id,
                'customer_name' => $oldMeeting->customer_name,
                'phone' => $oldMeeting->phone,
                'assigned_to' => $oldMeeting->assigned_to,
                'meeting_mode' => $newData['meeting_mode'] ?? $oldMeeting->meeting_mode,
                'meeting_link' => $newData['meeting_link'] ?? $oldMeeting->meeting_link,
                'location' => $newData['location'] ?? $oldMeeting->location,
                'scheduled_at' => $newData['scheduled_at'],
                'reminder_enabled' => $newData['reminder_enabled'] ?? $oldMeeting->reminder_enabled,
                'reminder_minutes' => $newData['reminder_minutes'] ?? $oldMeeting->reminder_minutes,
                'meeting_notes' => $newData['meeting_notes'] ?? "Rescheduled from " . $oldMeeting->scheduled_at->format('Y-m-d H:i'),
                'original_meeting_id' => $oldMeeting->id,
            ];
            
            // Get next meeting sequence for this lead
            if ($oldMeeting->lead_id) {
                $newMeetingData['meeting_sequence'] = $newData['meeting_sequence'] ?? $this->getLeadMeetingSequence($oldMeeting->lead_id);
            }
            
            // Create new meeting with reminder
            $newMeeting = $this->createMeetingWithReminder($newMeetingData, $user);
            
            DB::commit();
            
            return $newMeeting;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reschedule meeting', [
                'error' => $e->getMessage(),
                'old_meeting_id' => $oldMeeting->id,
            ]);
            throw $e;
        }
    }
    
    /**
     * Get next meeting sequence number for a lead
     */
    public function getLeadMeetingSequence(int $leadId): int
    {
        $maxSequence = Meeting::where('lead_id', $leadId)
            ->where('status', '!=', 'cancelled')
            ->max('meeting_sequence');
            
        return ($maxSequence ?? 0) + 1;
    }
}
