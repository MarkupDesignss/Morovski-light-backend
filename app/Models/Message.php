<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'text',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Mark the message as read.
     */
    public function markAsRead(): void
    {
        if (is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    /**
     * Check if the message has been read.
     */
    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    /**
     * Scope to get unread messages for a specific user in a conversation.
     */
    public function scopeUnreadForUser($query, int $conversationId, int $userId)
    {
        return $query->where('conversation_id', $conversationId)
                     ->where('sender_id', '!=', $userId)
                     ->whereNull('read_at');
    }
}