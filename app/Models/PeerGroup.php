<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeerGroup extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'cluster_id',
        'user_id',
        'purpose',
        'group_name',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * The user assigned to this peer group.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: filter by purpose.
     */
    public function scopeForPurpose($query, string $purpose)
    {
        return $query->where('purpose', $purpose);
    }

    /**
     * Get groups organized by cluster for a given purpose.
     */
    public static function getGroupedByCluster(string $purpose)
    {
        return static::where('purpose', $purpose)
            ->with('user')
            ->orderBy('cluster_id')
            ->get()
            ->groupBy('cluster_id');
    }
}
