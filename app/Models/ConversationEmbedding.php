<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationEmbedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'conversation_id',
        'message_id',
        'type',
        'content',
        'summary',
        'embedding',
        'topic',
        'keywords',
        'importance_score',
        'model',
        'dimensions',
    ];

    protected $casts = [
        'embedding' => 'array',
        'keywords' => 'array',
        'importance_score' => 'float',
        'dimensions' => 'integer',
    ];

    const TYPE_MESSAGE = 'message';
    const TYPE_PROFILE = 'profile';
    const TYPE_SUMMARY = 'summary';

    /**
     * Get the user that owns the embedding.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the conversation that owns the embedding.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the message that owns the embedding.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Scope a query to only include message embeddings.
     */
    public function scopeMessages($query)
    {
        return $query->where('type', self::TYPE_MESSAGE);
    }

    /**
     * Scope a query to only include profile embeddings.
     */
    public function scopeProfiles($query)
    {
        return $query->where('type', self::TYPE_PROFILE);
    }

    /**
     * Scope a query to only include summary embeddings.
     */
    public function scopeSummaries($query)
    {
        return $query->where('type', self::TYPE_SUMMARY);
    }

    /**
     * Scope a query to order by importance.
     */
    public function scopeByImportance($query)
    {
        return $query->orderBy('importance_score', 'desc');
    }

    /**
     * Calculate cosine similarity with another embedding.
     */
    public function cosineSimilarity(array $otherEmbedding): float
    {
        $embedding1 = $this->embedding;
        $embedding2 = $otherEmbedding;

        if (count($embedding1) !== count($embedding2)) {
            return 0.0;
        }

        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        for ($i = 0; $i < count($embedding1); $i++) {
            $dotProduct += $embedding1[$i] * $embedding2[$i];
            $magnitude1 += $embedding1[$i] * $embedding1[$i];
            $magnitude2 += $embedding2[$i] * $embedding2[$i];
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }
}
