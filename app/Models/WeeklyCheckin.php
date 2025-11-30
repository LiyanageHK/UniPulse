<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeeklyCheckin extends Model
{
    protected $fillable = [
        'user_id', 'week_start',
        'mood','tense','overwhelmed','worry','sleep_trouble','openness_to_mentor','knowledge_of_support',
        'peer_connection','peer_interaction','group_participation','feel_left_out','no_one_to_talk','university_belonging','meaningful_connections',
        'studies_interesting','academic_confidence','workload_management',
        'no_energy','low_pleasure','feeling_down','emotionally_drained','hard_to_stay_focused','just_through_motions'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
