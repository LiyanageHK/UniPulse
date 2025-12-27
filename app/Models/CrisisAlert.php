<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrisisAlert extends Model
{
    use HasFactory;

    protected $fillable = [
        'crisis_flag_id',
        'user_id',
        'counselor_id',
        'status',
        'priority',
        'email_sent',
        'email_sent_at',
        'sms_sent',
        'sms_sent_at',
        'resources_sent',
        'resources_sent_at',
        'acknowledged_at',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'email_sent' => 'boolean',
        'email_sent_at' => 'datetime',
        'sms_sent' => 'boolean',
        'sms_sent_at' => 'datetime',
        'resources_sent' => 'array',
        'resources_sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ACKNOWLEDGED = 'acknowledged';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_RESOLVED = 'resolved';

    const PRIORITY_CRITICAL = 'critical';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_MEDIUM = 'medium';

    /**
     * Get the crisis flag that owns the alert.
     */
    public function crisisFlag(): BelongsTo
    {
        return $this->belongsTo(CrisisFlag::class);
    }

    /**
     * Get the user (student) that owns the alert.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the counselor assigned to the alert.
     */
    public function counselor(): BelongsTo
    {
        return $this->belongsTo(Counselor::class);
    }

    /**
     * Scope a query to only include pending alerts.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include acknowledged alerts.
     */
    public function scopeAcknowledged($query)
    {
        return $query->where('status', self::STATUS_ACKNOWLEDGED);
    }

    /**
     * Scope a query to only include resolved alerts.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    /**
     * Scope a query to only include critical alerts.
     */
    public function scopeCritical($query)
    {
        return $query->where('priority', self::PRIORITY_CRITICAL);
    }

    /**
     * Mark alert as acknowledged.
     */
    public function acknowledge(): void
    {
        $this->update([
            'status' => self::STATUS_ACKNOWLEDGED,
            'acknowledged_at' => now(),
        ]);
    }

    /**
     * Mark alert as resolved.
     */
    public function resolve(string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Mark resources as sent.
     */
    public function markResourcesSent(array $resources): void
    {
        $this->update([
            'resources_sent' => $resources,
            'resources_sent_at' => now(),
        ]);
    }
}
