<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'user_id',
        'role',
        'content',
        'metadata',
        'has_crisis_flags',
    ];

    protected $casts = [
        'metadata' => 'array',
        'has_crisis_flags' => 'boolean',
    ];

    /**
     * Get the conversation that owns the message.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user that sent the message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the crisis flags for the message.
     */
    public function crisisFlags(): HasMany
    {
        return $this->hasMany(CrisisFlag::class);
    }

    /**
     * Get the embeddings for the message.
     */
    public function embeddings(): HasMany
    {
        return $this->hasMany(ConversationEmbedding::class);
    }

    /**
     * Scope a query to only include user messages.
     */
    public function scopeUserMessages($query)
    {
        return $query->where('role', 'user');
    }

    /**
     * Scope a query to only include assistant messages.
     */
    public function scopeAssistantMessages($query)
    {
        return $query->where('role', 'assistant');
    }

    /**
     * Scope a query to only include messages with crisis flags.
     */
    public function scopeWithCrisisFlags($query)
    {
        return $query->where('has_crisis_flags', true);
    }

    /**
     * Check if message is from user.
     */
    public function isUserMessage(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if message is from assistant.
     */
    public function isAssistantMessage(): bool
    {
        return $this->role === 'assistant';
    }

    /**
     * Get formatted timestamp for display.
     */
    public function getFormattedTime(): string
    {
        return $this->created_at->format('g:i A');
    }
}
