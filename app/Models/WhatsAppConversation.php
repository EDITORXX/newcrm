<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsAppConversation extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'whatsapp_conversations';

    protected $fillable = [
        'user_id',
        'phone_number',
        'contact_name',
        'lead_id',
    ];

    /**
     * Get the user that owns the conversation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the lead associated with this conversation (if phone matches)
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get all messages in this conversation
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'conversation_id')->orderBy('created_at', 'asc');
    }

    /**
     * Get the latest message in this conversation
     */
    public function getLatestMessage()
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Get unread message count
     */
    public function getUnreadCount(): int
    {
        return $this->messages()
            ->where('direction', 'received')
            ->where('status', '!=', 'read')
            ->count();
    }

    /**
     * Mark all received messages as read
     */
    public function markAsRead(): void
    {
        $this->messages()
            ->where('direction', 'received')
            ->where('status', '!=', 'read')
            ->update(['status' => 'read']);
    }
}
