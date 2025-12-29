<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'guest_name',
        'guest_email',
        'content',
        'rating',
        'status',
        'llm_validation_score',
        'llm_validation_notes',
        'show_name',
        'approved_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'llm_validation_score' => 'integer',
        'llm_validation_notes' => 'array',
        'show_name' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the user who submitted this feedback.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for approved feedback only.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for pending feedback.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for feedback with high ratings (4+).
     */
    public function scopeHighRated($query)
    {
        return $query->where('rating', '>=', 4);
    }

    /**
     * Get display name for the feedback.
     */
    public function getDisplayNameAttribute(): string
    {
        if (!$this->show_name) {
            return 'Anonymous User';
        }
        
        // Check if it's a registered user
        if ($this->user) {
            return $this->user->name;
        }
        
        // Guest user
        if ($this->guest_name) {
            return $this->guest_name;
        }
        
        return 'Anonymous User';
    }

    /**
     * Get display initial for avatar.
     */
    public function getDisplayInitialAttribute(): string
    {
        if (!$this->show_name) {
            return 'A';
        }
        
        // Check if it's a registered user
        if ($this->user) {
            return strtoupper(substr($this->user->name, 0, 1));
        }
        
        // Guest user
        if ($this->guest_name) {
            return strtoupper(substr($this->guest_name, 0, 1));
        }
        
        return 'G';
    }

    /**
     * Check if this is a guest feedback.
     */
    public function isGuest(): bool
    {
        return is_null($this->user_id);
    }

    /**
     * Check if feedback can be auto-approved.
     */
    public function canAutoApprove(): bool
    {
        return $this->rating >= 4 && $this->llm_validation_score >= 70;
    }
}
