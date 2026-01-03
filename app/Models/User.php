<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        // Academic & Demographic
        'university',
        'faculty',
        'al_stream',
        'al_subject_1',
        'al_grade_1',
        'al_subject_2',
        'al_grade_2',
        'al_subject_3',
        'al_grade_3',
        'al_subject_4',
        'al_grade_4',
        'al_subject_5',
        'al_grade_5',
        'al_results',
        'learning_style',
        'transition_confidence',
        // Social & Personality
        'social_preference',
        'introvert_extrovert_scale',
        'stress_level',
        'group_work_comfort',
        'communication_preferences',
        // Interests & Lifestyle
        'primary_motivator',
        'goal_clarity',
        'interests',
        'hobbies',
        'living_arrangement',
        'is_employed',
        // Wellbeing & Support
        'overwhelm_level',
        'peer_struggle',
        'ai_openness',
        'preferred_support_types',
        // Onboarding status
        'onboarding_completed',
        'onboarding_completed_at',
        'on_boarding_required'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'al_results' => 'array',
        'learning_style' => 'array',
        'communication_preferences' => 'array',
        'interests' => 'array',
        'hobbies' => 'array',
        'preferred_support_types' => 'array',
        'is_employed' => 'boolean',
        'onboarding_completed' => 'boolean',
    ];

    /**
     * Get the user's memories.
     */
    public function memories()
    {
        return $this->hasMany(Memory::class);
    }



    public function weeklyCheckings()
    {
        return $this->hasMany(WeeklyChecking::class);
    }

    public function profile()
    {
        return $this->hasOne(StudentProfile::class);
    }

    public function ratingsGiven()
    {
        return $this->hasMany(PeerRating::class, 'from_id');
    }

    public function ratingsReceived()
    {
        return $this->hasMany(PeerRating::class, 'to_id');
    }

    public function myRating()
    {
        $me = Auth::id();
        return PeerRating::where('from_id', $me)
                        ->where('to_id', $this->id)
                        ->value('rating') ?? 0;
    }

    public function isPeeredWith()
    {
        $me = Auth::id();
        $other = $this->id;

        return PeerRequest::where(function ($query) use ($me, $other) {
                $query->where(function ($q) use ($me, $other) {
                    $q->where('sender_id', $me)
                    ->where('receiver_id', $other);
                })
                ->orWhere(function ($q) use ($me, $other) {
                    $q->where('sender_id', $other)
                    ->where('receiver_id', $me);
                });
            })
            ->where('status', 'accepted')
            ->exists();
    }

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_members')
                    ->withTimestamps()
                    ->withPivot('joined_at');
    }

    public function adminGroups()
    {
        return $this->hasMany(Group::class, 'admin_id');
    }


}
