<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'title',
        'message',
        'target_type',
        'target_roles',
        'read_by',
    ];

    protected $casts = [
        'target_roles' => 'array',
        'read_by' => 'array',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Check if user has read this broadcast
     */
    public function isReadBy(int $userId): bool
    {
        return in_array($userId, $this->read_by ?? []);
    }

    /**
     * Mark broadcast as read by user
     */
    public function markAsReadBy(int $userId): void
    {
        $readBy = $this->read_by ?? [];
        if (!in_array($userId, $readBy)) {
            $readBy[] = $userId;
            $this->update(['read_by' => $readBy]);
        }
    }
}
