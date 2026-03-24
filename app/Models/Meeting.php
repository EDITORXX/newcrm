<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;

class Meeting extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'prospect_id',
        'created_by',
        'assigned_to',
        'customer_name',
        'phone',
        'employee',
        'occupation',
        'date_of_visit',
        'project',
        'budget_range',
        'team_leader',
        'property_type',
        'payment_mode',
        'tentative_period',
        'lead_type',
        'photos',
        'scheduled_at',
        'completed_at',
        'status',
        'verification_status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'meeting_notes',
        'feedback',
        'rating',
        'completion_proof_photos',
        'is_dead',
        'dead_reason',
        'marked_dead_at',
        'marked_dead_by',
        // Reschedule fields
        'rescheduled_at',
        'rescheduled_by',
        'reschedule_reason',
        'reschedule_count',
        'is_rescheduled',
        'converted_to_site_visit_id',
        'is_converted',
        // Simplified meeting fields
        'meeting_sequence',
        'meeting_mode',
        'meeting_link',
        'location',
        'reminder_enabled',
        'reminder_minutes',
        'pre_meeting_call_task_id',
        'customer_confirmation_status',
        'original_meeting_id',
    ];

    protected $casts = [
        'photos' => 'array',
        'completion_proof_photos' => 'array',
        'date_of_visit' => 'date',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
        'marked_dead_at' => 'datetime',
        'rating' => 'integer',
        'is_dead' => 'boolean',
        'rescheduled_at' => 'datetime',
        'is_rescheduled' => 'boolean',
        'is_converted' => 'boolean',
        'reminder_enabled' => 'boolean',
        'meeting_sequence' => 'integer',
        'reminder_minutes' => 'integer',
    ];

    // Relationships
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function markedDeadBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_dead_by');
    }

    public function rescheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rescheduled_by');
    }

    public function convertedToSiteVisit(): BelongsTo
    {
        return $this->belongsTo(SiteVisit::class, 'converted_to_site_visit_id');
    }

    public function preMeetingCallTask(): BelongsTo
    {
        return $this->belongsTo(TelecallerTask::class, 'pre_meeting_call_task_id');
    }

    public function originalMeeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class, 'original_meeting_id');
    }

    /**
     * Check if meeting is rescheduled
     */
    public function isRescheduled(): bool
    {
        return $this->is_rescheduled === true;
    }

    /**
     * Mark meeting as dead
     */
    public function markAsDead(int $userId, string $reason): void
    {
        $this->is_dead = true;
        $this->dead_reason = $reason;
        $this->marked_dead_at = now();
        $this->marked_dead_by = $userId;
        $this->save();

        // Also mark associated lead as dead if exists
        if ($this->lead_id) {
            $lead = Lead::find($this->lead_id);
            if ($lead) {
                $lead->markAsDead($userId, $reason, 'meeting');
            }
        }
    }

    /**
     * Mark meeting as verified
     */
    public function verify(int $userId, ?string $notes = null): void
    {
        $this->verification_status = 'verified';
        $this->verified_at = now();
        $this->verified_by = $userId;
        
        if ($notes) {
            $this->meeting_notes = ($this->meeting_notes ? $this->meeting_notes . "\n" : '') . $notes;
        }
        
        $this->save();
    }

    /**
     * Mark meeting as rejected
     */
    public function reject(int $userId, string $reason): void
    {
        $this->verification_status = 'rejected';
        $this->verified_at = now();
        $this->verified_by = $userId;
        $this->rejection_reason = $reason;
        $this->save();
    }

    /**
     * Check if meeting is verified
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Check if meeting is pending verification
     */
    public function isPendingVerification(): bool
    {
        return $this->status === 'completed' && $this->verification_status === 'pending';
    }

    /**
     * Mark meeting as completed
     */
    public function markAsCompleted(): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->verification_status = 'pending'; // Goes for verification
        $this->save();
    }

    /**
     * Get photos URLs
     */
    public function getPhotosUrlsAttribute(): array
    {
        if (!$this->photos || !is_array($this->photos)) {
            return [];
        }

        return array_map(function ($photo) {
            if (filter_var($photo, FILTER_VALIDATE_URL)) {
                return $photo;
            }
            return asset('storage/' . $photo);
        }, $this->photos);
    }

    /**
     * Cancel meeting
     */
    public function cancelMeeting(int $userId, string $reason = 'Cancelled by user'): void
    {
        $this->status = 'cancelled';
        $this->customer_confirmation_status = 'cancelled';
        $this->meeting_notes = ($this->meeting_notes ? $this->meeting_notes . "\n" : '') . "Cancelled: $reason";
        $this->save();
    }

    /**
     * Confirm meeting (customer will join)
     */
    public function confirmMeeting(): void
    {
        $this->customer_confirmation_status = 'confirmed';
        $this->save();
    }
}
