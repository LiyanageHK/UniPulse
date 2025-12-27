<?php

namespace App\Services;

use App\Models\Counselor;
use App\Models\CrisisFlag;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CounselorMatchingService
{
    /**
     * Find matching counselor based on crisis category and location.
     */
    public function findMatchingCounselor(string $crisisCategory, ?string $city = null): ?Counselor
    {
        // Start with available counselors
        $query = Counselor::available();

        // Filter by city if provided
        if ($city) {
            // Try exact city match first
            $counselor = $query->clone()->inCity($city)->first();
            if ($counselor) {
                return $this->selectBestMatch(
                    $query->clone()->inCity($city)->get(),
                    $crisisCategory
                );
            }
        }

        // Try online counselors
        $onlineCounselors = Counselor::available()->offersOnline()->get();
        if ($onlineCounselors->isNotEmpty()) {
            return $this->selectBestMatch($onlineCounselors, $crisisCategory);
        }

        // Fallback to any available counselor
        $anyCounselor = Counselor::available()->first();
        
        // If no counselors available, return null (system will alert via email)
        return $anyCounselor;
    }

    /**
     * Select best matching counselor from a collection based on specialization.
     */
    protected function selectBestMatch(Collection $counselors, string $crisisCategory): ?Counselor
    {
        // Score each counselor
        $scored = $counselors->map(function ($counselor) use ($crisisCategory) {
            $score = 0;

            // Check if counselor matches crisis category
            if ($counselor->matchesCrisisCategory($crisisCategory)) {
                $score += 10;
            }

            // Mental health counselors are preferred for all crisis situations
            if ($counselor->category === Counselor::CATEGORY_MENTAL_HEALTH) {
                $score += 5;
            }

            // Online availability is a plus
            if ($counselor->offers_online) {
                $score += 2;
            }

            return [
                'counselor' => $counselor,
                'score' => $score,
            ];
        });

        // Get highest scoring counselor
        $best = $scored->sortByDesc('score')->first();

        return $best ? $best['counselor'] : null;
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

        // Build query
        $query = Counselor::available();

        // Filter by city if provided
        if ($city) {
            $query->where(function ($q) use ($city) {
                $q->inCity($city)->orWhere('offers_online', true);
            });
        }

        // Get all matching counselors
        $counselors = $query->get();

        // Score and filter
        $scoredCounselors = $counselors->map(function ($counselor) use ($categories) {
            $score = 0;

            // Check matches for each category
            foreach ($categories as $category) {
                if ($counselor->matchesCrisisCategory($category)) {
                    $score += 10;
                }
            }

            // Mental health counselors are preferred
            if ($counselor->category === Counselor::CATEGORY_MENTAL_HEALTH) {
                $score += 5;
            }

            // Online availability
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
                'specializations' => $item['counselor']->specializations,
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
        $matches = [];

        foreach ($categories as $category) {
            if ($counselor->matchesCrisisCategory($category)) {
                $matches[] = $this->getCategoryLabel($category);
            }
        }

        if (empty($matches)) {
            return 'General mental health support';
        }

        return 'Specializes in: ' . implode(', ', array_unique($matches));
    }

    /**
     * Get display label for a crisis category.
     */
    protected function getCategoryLabel(string $category): string
    {
        return match($category) {
            CrisisFlag::CATEGORY_SUICIDE_RISK => 'Suicide Prevention',
            CrisisFlag::CATEGORY_SELF_HARM => 'Self-Harm Support',
            CrisisFlag::CATEGORY_DEPRESSION => 'Depression',
            CrisisFlag::CATEGORY_ANXIETY => 'Anxiety',
            CrisisFlag::CATEGORY_STRESS => 'Stress Management',
            CrisisFlag::CATEGORY_LONELINESS => 'Loneliness & Social Support',
            CrisisFlag::CATEGORY_HOPELESSNESS => 'Hope & Resilience',
            CrisisFlag::CATEGORY_GENERAL => 'General Mental Health',
            default => ucfirst(str_replace('_', ' ', $category)),
        };
    }

    /**
     * Search for counselors via web (fallback when no local match).
     */
    public function searchCounselorsOnline(string $category, string $city): array
    {
        // This would integrate with a web search API or external counselor directory
        // For now, returning a placeholder

        try {
            // Example: Google Custom Search API or Mental Health Organization API
            // $results = Http::get('https://api.counselordirectory.com/search', [
            //     'category' => $category,
            //     'location' => $city,
            //     'country' => 'LK',
            // ]);

            Log::info('Web search for counselors: ' . $category . ' in ' . $city);

            // Placeholder return
            return [
                [
                    'name' => 'National Mental Health Center',
                    'phone' => '011-2882254',
                    'location' => $city,
                    'type' => 'Government Facility',
                    'services' => ['Crisis intervention', 'Counseling', 'Psychiatric care'],
                ],
                [
                    'name' => 'Private Counseling Services',
                    'phone' => 'Search online for local providers',
                    'location' => $city,
                    'type' => 'Private Practice',
                    'services' => ['Individual counseling', 'Therapy'],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Online counselor search failed: ' . $e->getMessage());
            return [];
        }
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
