<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'initial_topic',
        'status',
        'message_count',
        'crisis_flags_count',
        'highest_severity_flag',
        'metadata',
        'last_message_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_message_at' => 'datetime',
        'message_count' => 'integer',
        'crisis_flags_count' => 'integer',
    ];

    /**
     * Get the user that owns the conversation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the messages for the conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the crisis flags for the conversation.
     */
    public function crisisFlags(): HasMany
    {
        return $this->hasMany(CrisisFlag::class);
    }

    /**
     * Get the embeddings for the conversation.
     */
    public function embeddings(): HasMany
    {
        return $this->hasMany(ConversationEmbedding::class);
    }

    /**
     * Scope a query to only include active conversations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include archived conversations.
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope a query to only include conversations with crisis flags.
     */
    public function scopeWithCrisisFlags($query)
    {
        return $query->where('crisis_flags_count', '>', 0);
    }

    /**
     * Check if conversation has any crisis flags.
     */
    public function hasCrisisFlags(): bool
    {
        return $this->crisis_flags_count > 0;
    }

    /**
     * Check if conversation has red flags.
     */
    public function hasRedFlags(): bool
    {
        return $this->highest_severity_flag === 'red';
    }

    /**
     * Get the severity level color class for UI.
     */
    public function getSeverityColorClass(): string
    {
        return match($this->highest_severity_flag) {
            'red' => 'text-red-600 bg-red-50',
            'yellow' => 'text-yellow-600 bg-yellow-50',
            'blue' => 'text-blue-600 bg-blue-50',
            default => 'text-gray-600 bg-gray-50',
        };
    }

    /**
     * Update the conversation's crisis flag statistics.
     */
    public function updateCrisisStats(): void
    {
        $this->crisis_flags_count = $this->crisisFlags()->count();
        
        // Get the highest severity flag
        $highestFlag = $this->crisisFlags()
            ->orderByRaw("FIELD(severity, 'red', 'yellow', 'blue')")
            ->first();
        
        $this->highest_severity_flag = $highestFlag?->severity;
        $this->save();
    }
}
