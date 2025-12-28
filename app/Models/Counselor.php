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

    // Counselor categories matching the provided data
    const CATEGORY_ACADEMIC = 'academic';                    // Academic & Study Support
    const CATEGORY_MENTAL_HEALTH = 'mental_health';          // Mental Health & Wellness
    const CATEGORY_SOCIAL = 'social';                        // Social Integration & Peer Relationships
    const CATEGORY_CRISIS = 'crisis';                        // Crisis & Emergency Intervention
    const CATEGORY_CAREER = 'career';                        // Career Guidance & Future Planning
    const CATEGORY_RELATIONSHIP = 'relationship';            // Relationship & Love Affairs
    const CATEGORY_FAMILY = 'family';                        // Family & Home-Related Issues
    const CATEGORY_PHYSICAL = 'physical';                    // Physical Health & Lifestyle (Psych Focus)
    const CATEGORY_FINANCIAL = 'financial';                  // Financial Wellness
    const CATEGORY_PERSONAL_DEVELOPMENT = 'personal_development'; // Extracurricular & Personal Development

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
     * Uses direct category mapping (no specialization matching).
     */
    public function matchesCrisisCategory(string $crisisCategory): bool
    {
        // Map crisis categories to counselor categories
        $categoryMap = [
            CrisisFlag::CATEGORY_SUICIDE_RISK => self::CATEGORY_CRISIS,
            CrisisFlag::CATEGORY_SELF_HARM => self::CATEGORY_CRISIS,
            CrisisFlag::CATEGORY_DEPRESSION => self::CATEGORY_MENTAL_HEALTH,
            CrisisFlag::CATEGORY_ANXIETY => self::CATEGORY_MENTAL_HEALTH,
            CrisisFlag::CATEGORY_HOPELESSNESS => self::CATEGORY_MENTAL_HEALTH,
            CrisisFlag::CATEGORY_STRESS => self::CATEGORY_ACADEMIC,
            CrisisFlag::CATEGORY_LONELINESS => self::CATEGORY_SOCIAL,
        ];

        $matchingCategory = $categoryMap[$crisisCategory] ?? self::CATEGORY_MENTAL_HEALTH;
        
        return $this->category === $matchingCategory;
    }

    /**
     * Get the display label for the counselor's category.
     */
    public function getCategoryLabel(): string
    {
        return match($this->category) {
            self::CATEGORY_ACADEMIC => 'Academic & Study Support',
            self::CATEGORY_MENTAL_HEALTH => 'Mental Health & Wellness',
            self::CATEGORY_SOCIAL => 'Social Integration & Peer Relationships',
            self::CATEGORY_CRISIS => 'Crisis & Emergency Intervention',
            self::CATEGORY_CAREER => 'Career Guidance & Future Planning',
            self::CATEGORY_RELATIONSHIP => 'Relationship & Love Affairs',
            self::CATEGORY_FAMILY => 'Family & Home-Related Issues',
            self::CATEGORY_PHYSICAL => 'Physical Health & Lifestyle',
            self::CATEGORY_FINANCIAL => 'Financial Wellness',
            self::CATEGORY_PERSONAL_DEVELOPMENT => 'Extracurricular & Personal Development',
            default => ucfirst(str_replace('_', ' ', $this->category)),
        };
    }
}
