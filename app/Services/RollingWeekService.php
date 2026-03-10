<?php

namespace App\Services;

use App\Models\Journal;
use App\Models\WeeklySummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Personalized Rolling 7-Day Week Service
 *
 * Computes per-user 7-day window boundaries based on the date of their
 * first journal entry rather than fixed calendar weeks (Mon–Sun).
 *
 *   Window 0 (week_index 1)  →  firstJournalDate .. firstJournalDate + 6 days
 *   Window 1 (week_index 2)  →  firstJournalDate + 7 .. firstJournalDate + 13
 *   ...
 *
 * week_end is the authoritative gate for journal access control.
 * week_end = week_start + 6 days (always).
 */
class RollingWeekService
{
    /**
     * Get the date of the user's very first journal entry (start of day).
     * Returns null when no journals exist yet.
     */
    public function getFirstJournalDate(int $userId): ?Carbon
    {
        $first = Journal::where('user_id', $userId)
            ->orderBy('entry_date')
            ->first();

        return $first ? Carbon::parse($first->entry_date)->startOfDay() : null;
    }

    /**
     * Get the rolling week boundaries (week_start, week_end, week_index) for a given date.
     *
     * @return array{week_start: Carbon, week_end: Carbon, week_index: int}|null
     */
    public function getWeekInfoForDate(int $userId, Carbon $date): ?array
    {
        $firstDate = $this->getFirstJournalDate($userId);

        if (! $firstDate) {
            return null;
        }

        $daysDiff  = $firstDate->diffInDays($date->copy()->startOfDay(), false);
        $daysDiff  = max(0, $daysDiff); // clamp — dates before first journal → window 0
        $window    = (int) floor($daysDiff / 7);

        $weekStart = $firstDate->copy()->addDays($window * 7);
        $weekEnd   = $weekStart->copy()->addDays(6);  // week_end = week_start + 6 days

        $weekIndex = $window + 1; // 1-based

        Log::debug('RollingWeekService::getWeekInfoForDate', [
            'user_id'    => $userId,
            'date'       => $date->toDateString(),
            'first_date' => $firstDate->toDateString(),
            'week_start' => $weekStart->toDateString(),
            'week_end'   => $weekEnd->toDateString(),
            'week_index' => $weekIndex,
        ]);

        return [
            'week_start' => $weekStart,
            'week_end'   => $weekEnd,
            'week_index' => $weekIndex,
        ];
    }

    /**
     * Get the current rolling-week boundaries based on today's date.
     *
     * @return array{week_start: Carbon, week_end: Carbon, week_index: int}|null
     */
    public function getCurrentWeekInfo(int $userId): ?array
    {
        return $this->getWeekInfoForDate($userId, Carbon::today());
    }

    /**
     * Compute the next week_index for a user by reading the latest summary.
     * If no summary exists, returns 1.
     */
    public function getNextWeekIndex(int $userId): int
    {
        $latest = WeeklySummary::where('user_id', $userId)
            ->orderByDesc('week_index')
            ->first();

        return $latest ? ($latest->week_index + 1) : 1;
    }
}
