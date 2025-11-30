<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KpiSnapshot extends Model
{
    protected $fillable = [
        'user_id',
        'week_start',
        'motivation_kpi',
        'social_kpi',
        'emotional_kpi'
    ];
}

