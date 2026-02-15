<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Memory extends Model
{
    use HasFactory;

    protected $table = 'user_memories';

    protected $fillable = [
        'user_id',
        'source_conversation_id',
        'source_message_id',
        'category',
        'memory_key',
        'memory_value',
        'importance_score',
        'last_referenced_at',
        'embedding',
        'embedding_model',
        'embedding_dimensions',
    ];

    protected $casts = [
        'importance_score' => 'float',
        'last_referenced_at' => 'datetime',
        'embedding' => 'array',
        'embedding_dimensions' => 'integer',
    ];

    /**
     * Memory categories
     */
    const CATEGORY_PERSONAL_INFO = 'personal_info';
    const CATEGORY_ACADEMIC = 'academic';
    const CATEGORY_GOALS = 'goals';
    const CATEGORY_PREFERENCES = 'preferences';
    const CATEGORY_EMOTIONAL = 'emotional';
    const CATEGORY_RELATIONSHIPS = 'relationships';
    const CATEGORY_HEALTH = 'health';
    const CATEGORY_EXPERIENCES = 'experiences';

    /**
     * Get all valid categories
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_PERSONAL_INFO,
            self::CATEGORY_ACADEMIC,
            self::CATEGORY_GOALS,
            self::CATEGORY_PREFERENCES,
            self::CATEGORY_EMOTIONAL,
            self::CATEGORY_RELATIONSHIPS,
            self::CATEGORY_HEALTH,
            self::CATEGORY_EXPERIENCES,
        ];
    }

    /**
     * Get the user that owns the memory.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the conversation where this memory was extracted.
     */
    public function sourceConversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'source_conversation_id');
    }

    /**
     * Get the message where this memory was extracted.
     */
    public function sourceMessage(): BelongsTo
    {
        return $this->belongsTo(Message::class, 'source_message_id');
    }

    /**
     * Scope: Filter by category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope: Filter by minimum importance
     */
    public function scopeImportant($query, float $minImportance = 0.7)
    {
        return $query->where('importance_score', '>=', $minImportance);
    }

    /**
     * Scope: Recently referenced memories
     */
    public function scopeRecentlyUsed($query, int $days = 30)
    {
        return $query->where('last_referenced_at', '>=', now()->subDays($days));
    }

    /**
     * Update last referenced timestamp
     */
    public function markAsReferenced(): void
    {
        $this->update(['last_referenced_at' => now()]);
    }

    /**
     * Get formatted memory for display
     */
    public function getFormattedAttribute(): string
    {
        $categoryLabel = str_replace('_', ' ', ucfirst($this->category));
        return "[{$categoryLabel}] {$this->memory_value}";
    }

    /**
     * Get human-readable category name
     */
    public function getCategoryNameAttribute(): string
    {
        return str_replace('_', ' ', ucwords($this->category));
    }
}
