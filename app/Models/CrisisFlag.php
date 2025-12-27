<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CrisisFlag extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'conversation_id',
        'user_id',
        'severity',
        'category',
        'detected_keywords',
        'context_snippet',
        'confidence_score',
        'escalated',
        'escalated_at',
        'reviewed',
        'reviewed_by',
        'reviewed_at',
        'reviewer_notes',
    ];

    protected $casts = [
        'detected_keywords' => 'array',
        'confidence_score' => 'float',
        'escalated' => 'boolean',
        'escalated_at' => 'datetime',
        'reviewed' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    // Severity levels from uploaded document
    const SEVERITY_RED = 'red';      // Critical
    const SEVERITY_YELLOW = 'yellow'; // Concerning
    const SEVERITY_BLUE = 'blue';     // Warning

    // Categories for counselor matching
    const CATEGORY_SUICIDE_RISK = 'suicide_risk';
    const CATEGORY_SELF_HARM = 'self_harm';
    const CATEGORY_DEPRESSION = 'depression';
    const CATEGORY_ANXIETY = 'anxiety';
    const CATEGORY_HOPELESSNESS = 'hopelessness';
    const CATEGORY_STRESS = 'stress';
    const CATEGORY_LONELINESS = 'loneliness';

    /**
     * Get the message that owns the crisis flag.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the conversation that owns the crisis flag.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user that owns the crisis flag.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the reviewer of the flag.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the crisis alert for this flag.
     */
    public function alert(): HasOne
    {
        return $this->hasOne(CrisisAlert::class);
    }

    /**
     * Scope a query to only include red flags.
     */
    public function scopeRed($query)
    {
        return $query->where('severity', self::SEVERITY_RED);
    }

    /**
     * Scope a query to only include yellow flags.
     */
    public function scopeYellow($query)
    {
        return $query->where('severity', self::SEVERITY_YELLOW);
    }

    /**
     * Scope a query to only include blue flags.
     */
    public function scopeBlue($query)
    {
        return $query->where('severity', self::SEVERITY_BLUE);
    }

    /**
     * Scope a query to only include escalated flags.
     */
    public function scopeEscalated($query)
    {
        return $query->where('escalated', true);
    }

    /**
     * Scope a query to only include unreviewed flags.
     */
    public function scopeUnreviewed($query)
    {
        return $query->where('reviewed', false);
    }

    /**
     * Check if flag is red (critical).
     */
    public function isRed(): bool
    {
        return $this->severity === self::SEVERITY_RED;
    }

    /**
     * Check if flag is yellow (concerning).
     */
    public function isYellow(): bool
    {
        return $this->severity === self::SEVERITY_YELLOW;
    }

    /**
     * Check if flag is blue (warning).
     */
    public function isBlue(): bool
    {
        return $this->severity === self::SEVERITY_BLUE;
    }

    /**
     * Get severity label.
     */
    public function getSeverityLabel(): string
    {
        return match($this->severity) {
            self::SEVERITY_RED => 'Critical',
            self::SEVERITY_YELLOW => 'Concerning',
            self::SEVERITY_BLUE => 'Warning',
            default => 'Unknown',
        };
    }

    /**
     * Get category label.
     */
    public function getCategoryLabel(): string
    {
        return ucwords(str_replace('_', ' ', $this->category));
    }
}
