<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LeadDownloadRequest extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'uuid',
        'requested_by',
        'reviewed_by',
        'status',
        'format',
        'actual_format',
        'filters',
        'fields',
        'exported_records_count',
        'file_disk',
        'file_path',
        'file_name',
        'file_mime',
        'admin_note',
        'rejection_reason',
        'reviewed_at',
        'approved_at',
        'rejected_at',
        'processed_at',
        'expires_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'fields' => 'array',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'processed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $request) {
            if (empty($request->uuid)) {
                $request->uuid = (string) Str::uuid();
            }
        });
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isDownloadReady(): bool
    {
        return in_array($this->status, [self::STATUS_APPROVED, self::STATUS_COMPLETED], true)
            && !empty($this->file_path)
            && (!$this->expires_at || $this->expires_at->isFuture());
    }

    public function statusLabel(): string
    {
        return ucfirst(str_replace('_', ' ', $this->status));
    }
}
