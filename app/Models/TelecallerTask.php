<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Illuminate\Database\Eloquent\SoftDeletes;

class TelecallerTask extends Model
{
    use HasFactory, SoftDeletes;

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
        'moved_to_pending_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'notification_sent_at' => 'datetime',
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
