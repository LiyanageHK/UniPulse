<?php

namespace App\Services;

use App\Models\Counselor;
use App\Models\CrisisFlag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CounselorMatchingService
{
    /**
     * Find matching counselor based on crisis category.
     * Uses direct category mapping (no specialization matching).
     */
    public function findMatchingCounselor(string $crisisCategory, ?string $city = null): ?Counselor
    {
        // Get counselors matching the crisis category
        $query = Counselor::available();

        // Get counselors that match the crisis category
        $matchingCounselors = $query->get()->filter(function ($counselor) use ($crisisCategory) {
            return $counselor->matchesCrisisCategory($crisisCategory);
        });

        if ($matchingCounselors->isNotEmpty()) {
            // Prefer city match if provided
            if ($city) {
                $cityMatch = $matchingCounselors->filter(function ($counselor) use ($city) {
                    return stripos($counselor->city, $city) !== false || $counselor->city === 'All Cities';
                })->first();
                
                if ($cityMatch) {
                    return $cityMatch;
                }
            }

            // Prefer online counselors
            $onlineMatch = $matchingCounselors->where('offers_online', true)->first();
            if ($onlineMatch) {
                return $onlineMatch;
            }

            // Return any matching counselor
            return $matchingCounselors->first();
        }

        // Fallback: Return any available counselor (preferably crisis category)
        $crisisCounselor = Counselor::available()
            ->where('category', Counselor::CATEGORY_CRISIS)
            ->first();
        
        return $crisisCounselor ?? Counselor::available()->first();
    }

    /**
     * Get counselors by crisis flag category.
     */
    public function getCounselorsByCrisisCategory(string $crisisCategory): Collection
    {
        return Counselor::available()
            ->get()
            ->filter(function ($counselor) use ($crisisCategory) {
                return $counselor->matchesCrisisCategory($crisisCategory);
            });
    }

    /**
     * Get recommended counselors for a user based on crisis flags.
     */
    public function getRecommendedCounselors(int $userId, ?string $city = null, int $limit = 3): Collection
    {
        // Get user's recent crisis flags to understand their needs
        $recentFlags = CrisisFlag::where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        $categories = $recentFlags->pluck('category')->unique();

        // Get all available counselors
        $counselors = Counselor::available()->get();

        // Filter by city if provided
        if ($city) {
            $counselors = $counselors->filter(function ($counselor) use ($city) {
                return stripos($counselor->city, $city) !== false 
                    || $counselor->city === 'All Cities' 
                    || $counselor->offers_online;
            });
        }

        // Score and filter based on category match
        $scoredCounselors = $counselors->map(function ($counselor) use ($categories) {
            $score = 0;

            // Check matches for each crisis category
            foreach ($categories as $category) {
                if ($counselor->matchesCrisisCategory($category)) {
                    $score += 10;
                }
            }

            // Crisis category counselors are preferred for emergencies
            if ($counselor->category === Counselor::CATEGORY_CRISIS) {
                $score += 5;
            }

            // Online availability bonus
            if ($counselor->offers_online) {
                $score += 3;
            }

            return [
                'counselor' => $counselor,
                'score' => $score,
                'match_reason' => $this->getMatchReason($counselor, $categories),
            ];
        });

        return $scoredCounselors
            ->sortByDesc('score')
            ->take($limit)
            ->map(fn($item) => [
                'id' => $item['counselor']->id,
                'name' => $item['counselor']->name,
                'title' => $item['counselor']->title,
                'category' => $this->getCategoryLabel($item['counselor']->category),
                'city' => $item['counselor']->city,
                'email' => $item['counselor']->email,
                'phone' => $item['counselor']->phone,
                'office_location' => $item['counselor']->office_location,
                'offers_online' => $item['counselor']->offers_online,
                'online_booking_url' => $item['counselor']->online_booking_url,
                'match_reason' => $item['match_reason'],
                'score' => $item['score'],
            ]);
    }

    /**
     * Get match reason for display to user.
     */
    protected function getMatchReason(Counselor $counselor, Collection $categories): string
    {
        foreach ($categories as $category) {
            if ($counselor->matchesCrisisCategory($category)) {
                return $this->getCategoryLabel($counselor->category) . ' specialist';
            }
        }

        return $this->getCategoryLabel($counselor->category) . ' support';
    }

    /**
     * Get display label for a counselor category.
     */
    protected function getCategoryLabel(string $category): string
    {
        return match($category) {
            Counselor::CATEGORY_ACADEMIC => 'Academic & Study Support',
            Counselor::CATEGORY_MENTAL_HEALTH => 'Mental Health & Wellness',
            Counselor::CATEGORY_SOCIAL => 'Social Integration & Peer Relationships',
            Counselor::CATEGORY_CRISIS => 'Crisis & Emergency Intervention',
            Counselor::CATEGORY_CAREER => 'Career Guidance & Future Planning',
            Counselor::CATEGORY_RELATIONSHIP => 'Relationship & Love Affairs',
            Counselor::CATEGORY_FAMILY => 'Family & Home-Related Issues',
            Counselor::CATEGORY_PHYSICAL => 'Physical Health & Lifestyle',
            Counselor::CATEGORY_FINANCIAL => 'Financial Wellness',
            Counselor::CATEGORY_PERSONAL_DEVELOPMENT => 'Extracurricular & Personal Development',
            default => ucfirst(str_replace('_', ' ', $category)),
        };
    }

    /**
     * Get counselors by category.
     */
    public function getCounselorsByCategory(string $category): Collection
    {
        return Counselor::byCategory($category)
            ->available()
            ->get();
    }

    /**
     * Get counselors by city.
     */
    public function getCounselorsByCity(string $city): Collection
    {
        return Counselor::inCity($city)
            ->available()
            ->get();
    }
}
