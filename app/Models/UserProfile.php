<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'is_absent',
        'absent_reason',
        'absent_until',
    ];

    protected $casts = [
        'is_absent' => 'boolean',
        'absent_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if user is currently absent
     */
    public function isCurrentlyAbsent(): bool
    {
        if (!$this->is_absent) {
            return false;
        }

        // If absent_until is set and has passed, user is no longer absent
        if ($this->absent_until && $this->absent_until->isPast()) {
            return false;
        }

        return true;
    }
}
