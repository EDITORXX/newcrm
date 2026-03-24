<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Illuminate\Database\Eloquent\SoftDeletes;

class SiteVisit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'created_by',
        'assigned_to',
        'property_name',
        'property_address',
        'scheduled_at',
        'completed_at',
        'status',
        'visit_notes',
        'feedback',
        'rating',
        // Verification fields
        'verification_status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'lead_status',
        // Closer fields
        'closer_status',
        'converted_to_closer_at',
        'closer_verified_by',
        'closer_verified_at',
        'closer_rejection_reason',
        // Form fields
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
        'visited_projects',
        'tentative_closing_time',
        'completion_proof_photos',
        'closer_request_proof_photos',
        'is_dead',
        'dead_reason',
        'marked_dead_at',
        'marked_dead_by',
        // Reschedule fields
        'rescheduled_at',
        'rescheduled_by',
        'reschedule_reason',
        // Visit type and reminder fields
        'visit_type',
        'visit_sequence',
        'reminder_enabled',
        'reminder_minutes',
        'reminder_task_id',
        'reschedule_count',
        'is_rescheduled',
        'incentive_amount',
        // KYC fields for closing
        'nominee_name',
        'second_customer_name',
        'customer_dob',
        'pan_card',
        'aadhaar_card_no',
        'kyc_documents',
        // Closing verification fields
        'closing_verification_status',
        'closing_verified_by',
        'closing_verified_at',
        'closing_rejection_reason',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
        'converted_to_closer_at' => 'datetime',
        'closer_verified_at' => 'datetime',
        'marked_dead_at' => 'datetime',
        'date_of_visit' => 'date',
        'rating' => 'integer',
        'photos' => 'array',
        'completion_proof_photos' => 'array',
        'closer_request_proof_photos' => 'array',
        'kyc_documents' => 'array',
        'pan_card' => 'encrypted',
        'aadhaar_card_no' => 'encrypted',
        'customer_dob' => 'date',
        'closing_verified_at' => 'datetime',
        'is_dead' => 'boolean',
        'rescheduled_at' => 'datetime',
        'is_rescheduled' => 'boolean',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
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

    public function closerVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closer_verified_by');
    }

    public function closingVerifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closing_verified_by');
    }

    public function rescheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rescheduled_by');
    }

    public function incentives(): HasMany
    {
        return $this->hasMany(Incentive::class);
    }

    /**
     * Check if site visit is rescheduled
     */
    public function isRescheduled(): bool
    {
        return $this->is_rescheduled === true;
    }

    /**
     * Mark site visit as verified
     */
    public function verify(int $userId, ?string $notes = null, ?string $leadStatus = null): void
    {
        $this->verification_status = 'verified';
        $this->verified_at = now();
        $this->verified_by = $userId;
        
        if ($notes) {
            $this->visit_notes = ($this->visit_notes ? $this->visit_notes . "\n" : '') . $notes;
        }
        
        if ($leadStatus) {
            $this->lead_status = $leadStatus;
        }
        
        $this->save();
    }

    /**
     * Mark site visit as rejected
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
     * Check if site visit is verified
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Check if site visit is pending verification
     */
    public function isPendingVerification(): bool
    {
        return $this->status === 'completed' && $this->verification_status === 'pending';
    }

    /**
     * Mark site visit as completed
     */
    public function markAsCompleted(): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->verification_status = 'pending'; // Goes for verification
        $this->save();
    }

    /**
     * Convert site visit to closer
     */
    public function convertToCloser(): void
    {
        if ($this->verification_status !== 'verified') {
            throw new \Exception('Site visit must be verified before converting to closer.');
        }

        $this->closer_status = 'pending';
        $this->converted_to_closer_at = now();
        $this->save();
    }

    /**
     * Verify closer
     */
    public function verifyCloser(int $userId, ?string $notes = null, ?string $leadStatus = null): void
    {
        if ($this->closer_status !== 'pending') {
            throw new \Exception('Closer must be pending before verification.');
        }

        $this->closer_status = 'verified';
        $this->closer_verified_at = now();
        $this->closer_verified_by = $userId;

        // Update lead status to closed when closer is verified
        if ($this->lead_id) {
            $lead = Lead::find($this->lead_id);
            if ($lead) {
                $lead->update(['status' => 'closed']);
            }
        }

        if ($notes) {
            $this->visit_notes = ($this->visit_notes ? $this->visit_notes . "\n" : '') . "Closer: " . $notes;
        }

        if ($leadStatus) {
            $this->lead_status = $leadStatus;
        }

        $this->save();
    }

    /**
     * Reject closer
     */
    public function rejectCloser(int $userId, string $reason): void
    {
        if ($this->closer_status !== 'pending') {
            throw new \Exception('Closer must be pending before rejection.');
        }

        $this->closer_status = 'rejected';
        $this->closer_verified_at = now();
        $this->closer_verified_by = $userId;
        $this->closer_rejection_reason = $reason;
        $this->save();
    }

    /**
     * Check if closer is verified
     */
    public function isCloserVerified(): bool
    {
        return $this->closer_status === 'verified';
    }

    /**
     * Check if closing is verified by CRM
     */
    public function isClosingVerified(): bool
    {
        return $this->closing_verification_status === 'verified';
    }

    /**
     * Verify closing (CRM only)
     */
    public function verifyClosing(int $userId, ?string $notes = null): void
    {
        if ($this->closing_verification_status !== 'pending') {
            throw new \Exception('Closing must be pending before verification.');
        }

        $this->closing_verification_status = 'verified';
        $this->closing_verified_at = now();
        $this->closing_verified_by = $userId;
        
        // After closing verification, set closer_status to verified so incentive can be requested
        $this->closer_status = 'verified';
        $this->closer_verified_at = now();
        $this->closer_verified_by = $userId;
        
        if ($notes) {
            $this->visit_notes = ($this->visit_notes ? $this->visit_notes . "\n" : '') . "Closing Verification: " . $notes;
        }
        
        $this->save();
    }

    /**
     * Reject closing (CRM only)
     */
    public function rejectClosing(int $userId, string $reason): void
    {
        if ($this->closing_verification_status !== 'pending') {
            throw new \Exception('Closing must be pending before rejection.');
        }

        $this->closing_verification_status = 'rejected';
        $this->closing_verified_at = now();
        $this->closing_verified_by = $userId;
        $this->closing_rejection_reason = $reason;
        $this->save();
    }

    /**
     * Mark site visit as dead
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
                $lead->markAsDead($userId, $reason, 'site_visit');
            }
        }
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
     * Get the reminder task for this site visit
     */
    public function reminderTask(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'reminder_task_id');
    }
}

