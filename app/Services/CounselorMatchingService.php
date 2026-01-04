<?php

namespace App\Services;

use App\Models\Counselor;
use App\Models\CrisisFlag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CounselorMatchingService
{
    /**
     * Find a crisis counselor for immediate support.
     */
    public function findCrisisCounselor(): ?Counselor
    {
        return Counselor::where('category', Counselor::CATEGORY_CRISIS)->first()
            ?? Counselor::first();
    }

    /**
     * Get counselors by their category.
     */
    public function getCounselorsByCategory(string $category): Collection
    {
        return Counselor::byCategory($category)->get();
    }

    /**
     * Get recommended counselors for a user.
     */
    public function getRecommendedCounselors(int $userId, int $limit = 3): Collection
    {
        // Get all counselors, prioritizing crisis counselors
        $counselors = Counselor::all();

        $scoredCounselors = $counselors->map(function ($counselor) {
            $score = 0;

            // Crisis category counselors are preferred
            if ($counselor->category === Counselor::CATEGORY_CRISIS) {
                $score += 10;
            }

            // Mental health counselors next
            if ($counselor->category === Counselor::CATEGORY_MENTAL_HEALTH) {
                $score += 5;
            }

            return [
                'counselor' => $counselor,
                'score' => $score,
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
                'hospital' => $item['counselor']->hospital,
                'score' => $item['score'],
            ]);
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
}
