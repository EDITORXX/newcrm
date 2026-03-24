<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'assigned_to',
        'type',
        'title',
        'description',
        'status',
        'priority',
        'scheduled_at',
        'due_date',
        'completed_at',
        'created_by',
        'notes',
        'recurrence_pattern',
        'recurrence_end_date',
        'rescheduled_from',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'recurrence_pattern' => 'array',
        'recurrence_end_date' => 'datetime',
        'rescheduled_from' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TaskActivity::class);
    }

    public function attachments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    /**
     * Mark task as in progress
     */
    public function markAsInProgress(): void
    {
        $this->update([
            'status' => 'in_progress',
        ]);
    }

    /**
     * Mark task as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Check if task is overdue
     * A task is overdue if scheduled_at is more than 10 minutes in the past and status is pending or in_progress
     */
    public function isOverdue(): bool
    {
        if (!$this->scheduled_at) {
            return false;
        }

        // Task is overdue if scheduled_at is more than 10 minutes ago
        $tenMinutesAgo = now()->subMinutes(10);
        
        return $this->scheduled_at->lt($tenMinutesAgo)
            && in_array($this->status, ['pending', 'in_progress']);
    }

    /**
     * Check if task is recurring
     */
    public function isRecurring(): bool
    {
        return !empty($this->recurrence_pattern);
    }

    /**
     * Get next occurrence date based on recurrence pattern
     */
    public function getNextOccurrenceDate(): ?\Carbon\Carbon
    {
        if (!$this->isRecurring() || !$this->scheduled_at) {
            return null;
        }

        $pattern = $this->recurrence_pattern;
        $currentDate = $this->scheduled_at->copy();

        switch ($pattern['frequency'] ?? null) {
            case 'daily':
                return $currentDate->addDay();
            case 'weekly':
                $days = $pattern['days'] ?? []; // e.g., [1,3,5] for Mon, Wed, Fri
                if (empty($days)) {
                    return $currentDate->addWeek();
                }
                // Find next matching day
                $nextDate = $currentDate->copy()->addDay();
                for ($i = 0; $i < 7; $i++) {
                    if (in_array($nextDate->dayOfWeek, $days)) {
                        return $nextDate;
                    }
                    $nextDate->addDay();
                }
                return null;
            case 'monthly':
                $dayOfMonth = $pattern['day_of_month'] ?? $currentDate->day;
                $nextDate = $currentDate->copy()->addMonth()->day($dayOfMonth);
                if (!$nextDate->isValid()) {
                    $nextDate = $currentDate->copy()->addMonth()->lastOfMonth();
                }
                return $nextDate;
            case 'yearly':
                return $currentDate->addYear();
            default:
                return null;
        }
    }

    /**
     * Check if recurrence should continue
     */
    public function shouldContinueRecurring(): bool
    {
        if (!$this->isRecurring()) {
            return false;
        }

        // Check end date
        if ($this->recurrence_end_date && now()->greaterThan($this->recurrence_end_date)) {
            return false;
        }

        // Check occurrence count
        if (isset($this->recurrence_pattern['count'])) {
            $count = $this->recurrence_pattern['count'] ?? null;
            if ($count !== null) {
                // This would need to track generated occurrences - simplified version
                return true;
            }
        }

        return true;
    }
}
