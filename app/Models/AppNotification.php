<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'telecaller_task_id',
        'type',
        'title',
        'message',
        'data',
        'action_type',
        'action_url',
        'read_at',
        'clicked_at',
    ];

    // Notification types
    public const TYPE_CALL_REMINDER = 'call_reminder';
    public const TYPE_NEW_LEAD = 'new_lead';
    public const TYPE_NEW_VERIFICATION = 'new_verification';
    public const TYPE_FOLLOWUP_REMINDER = 'followup_reminder';
    public const TYPE_ADMIN_BROADCAST = 'admin_broadcast';
    public const TYPE_SITE_VISIT = 'site_visit';
    public const TYPE_MEETING = 'meeting';
    public const TYPE_NEW_USER = 'new_user';

    // Action types
    public const ACTION_LEAD = 'lead';
    public const ACTION_USER = 'user';
    public const ACTION_VERIFICATION = 'verification';
    public const ACTION_FOLLOWUP = 'followup';
    public const ACTION_BROADCAST = 'broadcast';

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
        'clicked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function telecallerTask(): BelongsTo
    {
        return $this->belongsTo(TelecallerTask::class);
    }

    /**
     * Scope to get unread notifications
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope to get recent notifications
     */
    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('created_at', 'desc')->limit($limit);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(): void
    {
        if (!$this->read_at) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Mark notification as clicked
     */
    public function markAsClicked(): void
    {
        if (!$this->clicked_at) {
            $this->update(['clicked_at' => now()]);
        }
        $this->markAsRead();
    }
}
