<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeeklySummary extends Model
{
    protected $fillable = [
        'user_id',
        'summary_text',
        'stress_score',
        'sentiment_score',
        'pronoun_ratio',
        'absolutist_score',
        'withdrawal_score',
        'lri_score',
        'risk_level',
        'escalation_flag',
        'week_start',
        'week_end',
        'week_index',
    ];

    protected $casts = [
        'stress_score'     => 'float',
        'sentiment_score'  => 'float',
        'pronoun_ratio'    => 'float',
        'absolutist_score' => 'float',
        'withdrawal_score' => 'float',
        'lri_score'        => 'float',
        'escalation_flag'  => 'boolean',
        'week_start'       => 'date',
        'week_end'         => 'date',
        'week_index'       => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the color associated with the risk level.
     */
    public function getRiskColorAttribute(): string
    {
        return match ($this->risk_level) {
            'Low'      => 'green',
            'Moderate' => 'yellow',
            'High'     => 'red',
            default    => 'gray',
        };
    }

    /**
     * Get interpretation message for the risk level.
     */
    public function getRiskMessageAttribute(): string
    {
        return match ($this->risk_level) {
            'Low'      => 'Emotionally stable.',
            'Moderate' => 'Mild stress indicators.',
            'High'     => 'High stress signals detected.',
            default    => 'No data available.',
        };
    }
}
