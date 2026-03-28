<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;

class TelecallerTask extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (TelecallerTask $task) {
            $scheduledChanged = $task->isDirty('scheduled_at');
            $statusChanged = $task->isDirty('status');
            $completedChanged = $task->isDirty('completed_at');

            if (!$scheduledChanged && !$statusChanged && !$completedChanged) {
                return;
            }

            $activeStatuses = ['pending', 'in_progress', 'rescheduled'];
            $isActive = in_array($task->status, $activeStatuses, true) && $task->completed_at === null;
            $wasActive = in_array($task->getOriginal('status'), $activeStatuses, true) && $task->getOriginal('completed_at') === null;

            if ($isActive && ($scheduledChanged || !$wasActive)) {
                $task->notification_sent_at = null;
                $task->overdue_notified_at = null;
            }

            if (!$isActive) {
                $task->overdue_notified_at = null;
            }
        });
    }

    protected $fillable = [
        'lead_id',
        'meeting_id',
        'assigned_to',
        'task_type',
        'status',
        'scheduled_at',
        'completed_at',
        'outcome',
        'notes',
        'created_by',
        'notification_sent_at',
        'overdue_notified_at',
        'moved_to_pending_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'notification_sent_at' => 'datetime',
        'overdue_notified_at' => 'datetime',
        'moved_to_pending_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * Scope to get overdue tasks (more than 10 minutes old)
     */
    public function scopeOverdue($query)
    {
        $tenMinutesAgo = now()->subMinutes(10);
        return $query->where('status', '!=', 'completed')
            ->where('scheduled_at', '<', $tenMinutesAgo);
    }

    /**
     * Scope to get tasks due today
     */
    public function scopeDueToday($query)
    {
        return $query->whereDate('scheduled_at', today())
            ->where('status', '!=', 'completed');
    }

    /**
     * Scope to get urgent tasks (due within next hour)
     */
    public function scopeUrgent($query)
    {
        return $query->where('status', '!=', 'completed')
            ->whereBetween('scheduled_at', [now(), now()->addHour()]);
    }
}
