<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelecallerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'max_pending_leads',
        'is_absent',
        'absent_reason',
        'absent_until',
    ];

    protected $casts = [
        'is_absent' => 'boolean',
        'max_pending_leads' => 'integer',
        'absent_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if telecaller is currently absent
     */
    public function isCurrentlyAbsent(): bool
    {
        if (!$this->is_absent) {
            return false;
        }

        // If absent_until is set and has passed, telecaller is no longer absent
        if ($this->absent_until && $this->absent_until->isPast()) {
            return false;
        }

        return true;
    }
}
