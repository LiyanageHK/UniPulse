<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Counselor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'category',
        'specializations',
        'bio',
        'email',
        'phone',
        'office_location',
        'city',
        'region',
        'university',
        'is_available',
        'availability_schedule',
        'offers_online',
        'online_booking_url',
    ];

    protected $casts = [
        'specializations' => 'array',
        'is_available' => 'boolean',
        'availability_schedule' => 'array',
        'offers_online' => 'boolean',
    ];

    // Counselor categories matching crisis flag categories
    const CATEGORY_MENTAL_HEALTH = 'mental_health';
    const CATEGORY_ACADEMIC = 'academic';
    const CATEGORY_CAREER = 'career';
    const CATEGORY_FINANCIAL = 'financial';

    /**
     * Get the crisis alerts for this counselor.
     */
    public function crisisAlerts(): HasMany
    {
        return $this->hasMany(CrisisAlert::class);
    }

    /**
     * Scope a query to only include available counselors.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope a query to filter by city.
     */
    public function scopeInCity($query, string $city)
    {
        return $query->where('city', 'like', '%' . $city . '%');
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by specialization.
     */
    public function scopeWithSpecialization($query, string $specialization)
    {
        return $query->whereJsonContains('specializations', $specialization);
    }

    /**
     * Scope a query to only include online counselors.
     */
    public function scopeOffersOnline($query)
    {
        return $query->where('offers_online', true);
    }

    /**
     * Get formatted contact information.
     */
    public function getFormattedContact(): array
    {
        return array_filter([
            'email' => $this->email,
            'phone' => $this->phone,
            'office' => $this->office_location,
            'online' => $this->online_booking_url,
        ]);
    }

    /**
     * Check if counselor matches a crisis flag category.
     */
    public function matchesCrisisCategory(string $crisisCategory): bool
    {
        // Map crisis categories to counselor specializations
        $categoryMap = [
            CrisisFlag::CATEGORY_SUICIDE_RISK => ['suicide_prevention', 'crisis_intervention', 'mental_health'],
            CrisisFlag::CATEGORY_SELF_HARM => ['self_harm', 'crisis_intervention', 'mental_health'],
            CrisisFlag::CATEGORY_DEPRESSION => ['depression', 'mental_health', 'mood_disorders'],
            CrisisFlag::CATEGORY_ANXIETY => ['anxiety', 'mental_health', 'stress_management'],
            CrisisFlag::CATEGORY_HOPELESSNESS => ['depression', 'mental_health', 'crisis_intervention'],
            CrisisFlag::CATEGORY_STRESS => ['stress_management', 'academic_counseling', 'mental_health'],
            CrisisFlag::CATEGORY_LONELINESS => ['social_support', 'mental_health', 'peer_support'],
        ];

        $relevantSpecs = $categoryMap[$crisisCategory] ?? [];
        
        return !empty(array_intersect($this->specializations ?? [], $relevantSpecs));
    }
}
