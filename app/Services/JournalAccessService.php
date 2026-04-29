<?php

namespace App\Services;

use App\Models\Journal;
use App\Models\WeeklySummary;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Determines whether a user is allowed to post a new journal entry.
 *
 * Rules (strictly based on weekly_summaries.week_end):
 *  1. If NO weekly summary exists → allow (new user).
 *  2. If Carbon::now()->startOfDay() >= latest summary.week_end->startOfDay() → allow.
 *  3. Otherwise → deny (current rolling week has not ended yet).
 *  4. Prevent duplicate journal within the same week_index.
 *
 * DOES NOT USE: created_at, startOfWeek(), calendar-based weeks.
 */
class JournalAccessService
{
    /**
     * Check whether the user may submit a new journal entry.
     *
     * @return array{
     *     allowed: bool,
     *     reason: string,
     *     week_end: ?string,
     *     next_allowed: ?string,
     *     days_remaining: ?int,
     *     current_week_index: ?int,
     *     duplicate: bool,
     * }
     */
    public function canPostJournal(int $userId): array
    {
        $now     = Carbon::now(config('app.timezone'))->startOfDay();
        $latest  = $this->getLatestSummary($userId);

        // ── Debug logging ──────────────────────────────────────────
        Log::info('JournalAccessService::canPostJournal', [
            'user_id'          => $userId,
            'now_startOfDay'   => $now->toDateTimeString(),
            'latest_week_end'  => $latest?->week_end?->toDateString(),
            'latest_week_index'=> $latest?->week_index,
        ]);

        // 1. New user — no summaries at all → allow
        if (! $latest) {
            Log::info("Journal access: ALLOWED (new user, no summaries)", ['user_id' => $userId]);

            return [
                'allowed'            => true,
                'reason'             => 'new_user',
                'week_end'           => null,
                'next_allowed'       => null,
                'days_remaining'     => null,
                'current_week_index' => 1,
                'duplicate'          => false,
            ];
        }

        $weekEnd = Carbon::parse($latest->week_end)->startOfDay();
        $comparison = $now->gte($weekEnd);

        Log::info('JournalAccessService date comparison', [
            'user_id'        => $userId,
            'now'            => $now->toDateString(),
            'week_end'       => $weekEnd->toDateString(),
            'now_gte_weekEnd'=> $comparison,
        ]);

        // 2. Current rolling week has NOT ended → deny
        if (! $comparison) {
            $daysRemaining = $now->diffInDays($weekEnd, false);

            Log::info("Journal access: DENIED (week in progress)", [
                'user_id'        => $userId,
                'days_remaining' => $daysRemaining,
            ]);

            return [
                'allowed'            => false,
                'reason'             => 'week_in_progress',
                'week_end'           => $weekEnd->toDateString(),
                'next_allowed'       => $weekEnd->toDateString(),
                'days_remaining'     => max(0, $daysRemaining),
                'current_week_index' => $latest->week_index,
                'duplicate'          => false,
            ];
        }

        // 3. Week ended → compute new week index
        $nextWeekIndex = ($latest->week_index ?? 0) + 1;

        // 4. Duplicate guard: check if a journal already exists for the new week window
        $newWeekStart = $weekEnd->copy();                    // new week starts on old week_end
        $newWeekEnd   = $newWeekStart->copy()->addDays(6);   // week_end = week_start + 6

        $duplicateExists = Journal::where('user_id', $userId)
            ->whereBetween('entry_date', [
                $newWeekStart->toDateString(),
                $newWeekEnd->toDateString(),
            ])
            ->exists();

        if ($duplicateExists) {
            Log::info("Journal access: DENIED (duplicate in same week_index)", [
                'user_id'    => $userId,
                'week_index' => $nextWeekIndex,
                'week_start' => $newWeekStart->toDateString(),
                'week_end'   => $newWeekEnd->toDateString(),
            ]);

            return [
                'allowed'            => false,
                'reason'             => 'duplicate_week',
                'week_end'           => $newWeekEnd->toDateString(),
                'next_allowed'       => $newWeekEnd->toDateString(),
                'days_remaining'     => max(0, $now->diffInDays($newWeekEnd, false)),
                'current_week_index' => $nextWeekIndex,
                'duplicate'          => true,
            ];
        }

        Log::info("Journal access: ALLOWED (week ended)", [
            'user_id'        => $userId,
            'next_week_index'=> $nextWeekIndex,
        ]);

        return [
            'allowed'            => true,
            'reason'             => 'week_ended',
            'week_end'           => $weekEnd->toDateString(),
            'next_allowed'       => null,
            'days_remaining'     => null,
            'current_week_index' => $nextWeekIndex,
            'duplicate'          => false,
        ];
    }

    /**
     * Determine whether the journal reminder modal should be shown on the dashboard.
     * Shows when: no summaries (new user) OR now >= latest week_end.
     */
    public function shouldShowJournalModal(int $userId): bool
    {
        $access = $this->canPostJournal($userId);

        return $access['allowed'];
    }

    /**
     * Get the next allowed posting date for the user (null if allowed now or new user).
     */
    public function getNextAllowedDate(int $userId): ?Carbon
    {
        $access = $this->canPostJournal($userId);

        return $access['next_allowed']
            ? Carbon::parse($access['next_allowed'])
            : null;
    }

    /**
     * Get the current or next week_index for the user.
     */
    public function getCurrentWeekIndex(int $userId): int
    {
        $access = $this->canPostJournal($userId);

        return $access['current_week_index'] ?? 1;
    }

    /**
     * Fetch the latest weekly summary for a user, ordered by week_end DESC.
     */
    protected function getLatestSummary(int $userId): ?WeeklySummary
    {
        return WeeklySummary::where('user_id', $userId)
            ->orderByDesc('week_end')
            ->first();
    }
}
