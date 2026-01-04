<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentProfile extends Model
{
    protected $fillable = [
        'user_id',
        'university',
        'university_other',
        'faculty',
        'faculty_other',
        'al_stream',
        'al_stream_other',
        'al_result_subject1',
        'al_result_subject2',
        'al_result_subject3',
        'al_result_english',
        'al_result_gk',
        'learning_style',
        'confidence',
        'social_setting',
        'intro_extro',
        'stress_level',
        'group_comfort',
        'communication_methods',
        'motivation',
        'clear_goal',
        'top_interests',
        'hobbies',
        'living_arrangement',
        'employed',
        'overwhelmed',
        'struggle_connect',
        'ai_platform_support',
        'support_types',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
