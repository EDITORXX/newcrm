<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;

class FollowUp extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (FollowUp $followUp) {
            $scheduledChanged = $followUp->isDirty('scheduled_at');
            $statusChanged = $followUp->isDirty('status');
            $completedChanged = $followUp->isDirty('completed_at');

            if (!$scheduledChanged && !$statusChanged && !$completedChanged) {
                return;
            }

            $isScheduledOpen = $followUp->status === 'scheduled' && $followUp->completed_at === null;
            $wasScheduledOpen = $followUp->getOriginal('status') === 'scheduled' && $followUp->getOriginal('completed_at') === null;

            if ($isScheduledOpen && ($scheduledChanged || !$wasScheduledOpen)) {
                $followUp->reminder_sent_at = null;
                $followUp->overdue_notified_at = null;
            }

            if (!$isScheduledOpen) {
                $followUp->overdue_notified_at = null;
            }
        });
    }

    protected $fillable = [
        'lead_id',
        'created_by',
        'type',
        'notes',
        'scheduled_at',
        'reminder_sent_at',
        'overdue_notified_at',
        'completed_at',
        'status',
        'outcome',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'reminder_sent_at' => 'datetime',
        'overdue_notified_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
