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
        'detected_keywords',
        'context_snippet',
        'confidence_score',
    ];

    protected $casts = [
        'detected_keywords' => 'array',
        'confidence_score' => 'float',
    ];

    // Severity levels from uploaded document
    const SEVERITY_RED = 'red';      // Critical
    const SEVERITY_YELLOW = 'yellow'; // Concerning
    const SEVERITY_BLUE = 'blue';     // Warning

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
}
