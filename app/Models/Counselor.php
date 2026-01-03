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
        'hospital',
    ];

    protected $casts = [
        // No remaining cast columns
    ];

    // Counselor categories - Full display names stored in database
    const CATEGORY_ACADEMIC = 'Academic & Study Support';
    const CATEGORY_MENTAL_HEALTH = 'Mental Health & Wellness';
    const CATEGORY_SOCIAL = 'Social Integration & Peer Relationships';
    const CATEGORY_CRISIS = 'Crisis & Emergency Intervention';
    const CATEGORY_CAREER = 'Career Guidance & Future Planning';
    const CATEGORY_RELATIONSHIP = 'Relationship & Love Affairs';
    const CATEGORY_FAMILY = 'Family & Home-Related Issues';
    const CATEGORY_PHYSICAL = 'Physical Health & Lifestyle';
    const CATEGORY_FINANCIAL = 'Financial Wellness';
    const CATEGORY_PERSONAL_DEVELOPMENT = 'Extracurricular & Personal Development';

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get the display label for the counselor's category.
     * Since categories are now stored as full names, just return the category.
     */
    public function getCategoryLabel(): string
    {
        return $this->category;
    }
}
