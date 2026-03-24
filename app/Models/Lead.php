<?php

namespace App\Models;

use App\Events\LeadStatusUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'pincode',
        'source',
        'status',
        'property_type',
        'budget_min',
        'budget_max',
        'budget',
        'requirements',
        'notes',
        'created_by',
        'last_contacted_at',
        'next_followup_at',
        'preferred_location',
        'preferred_size',
        'preferred_projects',
        'use_end_use',
        'possession_status',
        'cnp_count',
        'is_blocked',
        'blocked_reason',
        'blocked_at',
        'is_dead',
        'dead_reason',
        'dead_at_stage',
        'marked_dead_at',
        'marked_dead_by',
        'needs_verification',
        'verification_requested_by',
        'verification_requested_at',
        'verified_by',
        'verified_at',
        'verification_notes',
        'pending_manager_id',
        'status_auto_update_enabled',
        'form_filled_by_telecaller',
        'form_filled_by_executive',
        'form_filled_by_manager',
    ];

    protected $casts = [
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
        'investment' => 'decimal:2',
        'last_contacted_at' => 'datetime',
        'next_followup_at' => 'datetime',
        'marked_dead_at' => 'datetime',
        'is_dead' => 'boolean',
        'needs_verification' => 'boolean',
        'verification_requested_at' => 'datetime',
        'verified_at' => 'datetime',
        'status_auto_update_enabled' => 'boolean',
        'form_filled_by_telecaller' => 'boolean',
        'form_filled_by_executive' => 'boolean',
        'form_filled_by_manager' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function markedDeadBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'marked_dead_by');
    }

    public function verificationRequestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verification_requested_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function pendingManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pending_manager_id');
    }

    /**
     * Mark lead as dead
     */
    public function markAsDead(int $userId, string $reason, ?string $stage = null): void
    {
        $this->is_dead = true;
        $this->dead_reason = $reason;
        $this->dead_at_stage = $stage;
        $this->marked_dead_at = now();
        $this->marked_dead_by = $userId;
        $this->status = 'dead';
        $this->disableAutoUpdate();
        $this->save();
    }

    /**
     * Disable auto-update for status
     */
    public function disableAutoUpdate(): void
    {
        $this->status_auto_update_enabled = false;
    }

    /**
     * Enable auto-update for status
     */
    public function enableAutoUpdate(): void
    {
        $this->status_auto_update_enabled = true;
    }

    /**
     * Check if auto-update is allowed
     */
    public function canAutoUpdate(): bool
    {
        return $this->status_auto_update_enabled === true;
    }

    /**
     * Update status only if auto-update is enabled
     */
    public function updateStatusIfAllowed(string $newStatus): bool
    {
        if (!$this->canAutoUpdate()) {
            return false;
        }

        $oldStatus = $this->status;
        $this->status = $newStatus;
        $this->save();

        // Fire event if status changed
        // Wrap in try-catch to handle broadcasting errors (Pusher may not be configured)
        if ($oldStatus !== $newStatus) {
            try {
                event(new LeadStatusUpdated($this, $oldStatus, $newStatus));
            } catch (\Exception $e) {
                // Broadcasting errors (like Pusher) shouldn't stop the status update
                // Log but continue - the status update is successful even if broadcast fails
                Log::warning("Broadcasting error in LeadStatusUpdated (non-critical): " . $e->getMessage());
            }
        }

        return true;
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LeadAssignment::class);
    }

    public function activeAssignments(): HasMany
    {
        return $this->hasMany(LeadAssignment::class)->where('is_active', true);
    }

    public function siteVisits(): HasMany
    {
        return $this->hasMany(SiteVisit::class);
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(FollowUp::class);
    }

    public function getAssignedUsersAttribute()
    {
        return $this->activeAssignments->pluck('assignedTo');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(TelecallerTask::class);
    }

    public function managerTasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function pendingTasks(): HasMany
    {
        return $this->hasMany(TelecallerTask::class)->where('status', 'pending');
    }

    public function prospects(): HasMany
    {
        return $this->hasMany(Prospect::class);
    }

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }

    public function callLogs(): HasMany
    {
        return $this->hasMany(CallLog::class);
    }

    /**
     * Scope to get leads for a specific telecaller
     */
    public function scopeForTelecaller($query, $userId)
    {
        return $query->whereHas('activeAssignments', function ($q) use ($userId) {
            $q->where('assigned_to', $userId);
        });
    }

    /**
     * Scope to get hot leads (high CNP count or recently contacted)
     */
    public function scopeHotLeads($query)
    {
        return $query->where(function ($q) {
            $q->where('cnp_count', '>=', 3)
              ->orWhere(function ($subQ) {
                  $subQ->whereNotNull('last_contacted_at')
                       ->whereDate('last_contacted_at', today());
              });
        });
    }

    /**
     * Scope to get leads pending contact
     */
    public function scopePendingContact($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('last_contacted_at')
              ->orWhere('status', 'new');
        });
    }

    /**
     * Get WhatsApp conversations for this lead
     */
    public function whatsappConversations(): HasMany
    {
        return $this->hasMany(WhatsAppConversation::class);
    }

    /**
     * Get all form field values for this lead
     */
    public function formFieldValues(): HasMany
    {
        return $this->hasMany(LeadFormFieldValue::class);
    }

    /**
     * Get value for a specific form field
     */
    public function getFormFieldValue(string $fieldKey): ?string
    {
        $fieldValue = $this->formFieldValues()->where('field_key', $fieldKey)->first();
        return $fieldValue ? $fieldValue->field_value : null;
    }

    /**
     * Set value for a specific form field
     */
    public function setFormFieldValue(string $fieldKey, $value, ?int $userId = null): LeadFormFieldValue
    {
        return LeadFormFieldValue::updateOrCreate(
            [
                'lead_id' => $this->id,
                'field_key' => $fieldKey,
            ],
            [
                'field_value' => $value,
                'filled_by_user_id' => $userId ?? auth()->id(),
                'filled_at' => now(),
            ]
        );
    }

    /**
     * Get all form field values as key-value array
     */
    public function getFormFieldsArray(): array
    {
        return $this->formFieldValues()->pluck('field_value', 'field_key')->toArray();
    }
}
