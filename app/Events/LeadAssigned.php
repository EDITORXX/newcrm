<?php

namespace App\Events;

use App\Models\Lead;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LeadAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $lead;
    public $assignedTo;
    public $assignedBy;

    public function __construct(Lead $lead, int $assignedTo, int $assignedBy)
    {
        $this->lead = $lead->load(['creator', 'activeAssignments.assignedTo']);
        $this->assignedTo = $assignedTo;
        $this->assignedBy = $assignedBy;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->assignedTo),
            new PrivateChannel('user.' . $this->assignedBy),
        ];
    }

    public function broadcastAs(): string
    {
        return 'lead.assigned';
    }

    public function broadcastWith(): array
    {
        return [
            'lead' => $this->lead,
            'assigned_to' => $this->assignedTo,
            'assigned_by' => $this->assignedBy,
            'message' => 'A new lead has been assigned to you.',
        ];
    }
}

