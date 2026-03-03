<?php

namespace App\Services;

use App\Models\Journal;
use App\Models\WeeklySummary;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service to combine weekly journal entries and orchestrate AI analysis.
 * Uses personalized rolling 7-day weeks (via RollingWeekService) instead of calendar weeks.
 */
class WeeklySummaryService
{
    protected AIService $aiService;
    protected RiskAssessmentService $riskAssessment;
    protected RollingWeekService $rollingWeek;

    public function __construct(
        AIService $aiService,
        RiskAssessmentService $riskAssessment,
        RollingWeekService $rollingWeek,
    ) {
        $this->aiService = $aiService;
        $this->riskAssessment = $riskAssessment;
        $this->rollingWeek = $rollingWeek;
    }

    // ─────────────────────────────────────────────────────────
    //  Batch Processing (scheduler / cron)
    // ─────────────────────────────────────────────────────────

    /**
     * Process all users' current rolling-week journals — intended for scheduler use.
     */
    public function processAllUsers(): array
    {
        $userIds = Journal::whereDate('entry_date', '>=', Carbon::now()->subDays(7))
            ->distinct()
            ->pluck('user_id');

        $results = ['processed' => 0, 'skipped' => 0, 'failed' => 0];

        foreach ($userIds as $userId) {
            try {
                $weekInfo = $this->rollingWeek->getCurrentWeekInfo($userId);

                if (! $weekInfo) {
                    $results['skipped']++;
                    continue;
                }

                $summary = $this->processUserWeek(
                    $userId,
                    $weekInfo['week_start'],
                    $weekInfo['week_end'],
                    $weekInfo['week_index'],
                );

                if ($summary) {
                    $results['processed']++;
                } else {
                    $results['skipped']++;
                }
            } catch (\Exception $e) {
                $results['failed']++;
                Log::error("Weekly summary processing failed for user {$userId}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Weekly summary batch processing complete', $results);
        return $results;
    }

    /**
     * Process a single user's rolling-week journals (only creates NEW, skips existing).
     * Used by the scheduler to avoid re-processing.
     */
    public function processUserWeek(int $userId, Carbon $weekStart, Carbon $weekEnd, ?int $weekIndex = null): ?WeeklySummary
    {
        // Skip if summary already exists for this rolling week
        $existing = WeeklySummary::where('user_id', $userId)
            ->where('week_start', $weekStart->toDateString())
            ->first();

        if ($existing) {
            Log::info("Weekly summary already exists for user {$userId}, week starting {$weekStart->toDateString()}");
            return $existing;
        }

        // Auto-compute week_index if not provided
        if ($weekIndex === null) {
            $weekIndex = $this->rollingWeek->getNextWeekIndex($userId);
        }

        return $this->buildAndSaveSummary($userId, $weekStart, $weekEnd, weekIndex: $weekIndex);
    }

    // ─────────────────────────────────────────────────────────
    //  On-Demand Recalculation (journal save / dashboard view)
    // ─────────────────────────────────────────────────────────

    /**
     * Recalculate weekly summary for a specific user and rolling week.
     * Uses updateOrCreate so it overwrites any existing summary for the same week.
     * Called after journal create/update/delete.
     */
    public function recalculateUserWeek(int $userId, Carbon $weekStart, Carbon $weekEnd, ?int $weekIndex = null): ?WeeklySummary
    {
        // Auto-compute week_index if not provided
        if ($weekIndex === null) {
            $existing = WeeklySummary::where('user_id', $userId)
                ->where('week_start', $weekStart->toDateString())
                ->first();

            $weekIndex = $existing
                ? $existing->week_index
                : $this->rollingWeek->getNextWeekIndex($userId);
        }

        return $this->buildAndSaveSummary($userId, $weekStart, $weekEnd, weekIndex: $weekIndex, forceRecalculate: true);
    }

    /**
     * Lazy-evaluate the current rolling week's summary for dashboard display.
     * Returns existing if available, otherwise calculates fresh.
     */
    public function getOrCalculateCurrentWeek(int $userId): ?WeeklySummary
    {
        $weekInfo = $this->rollingWeek->getCurrentWeekInfo($userId);

        if (! $weekInfo) {
            return null; // user has no journals at all
        }

        $weekStart = $weekInfo['week_start'];
        $weekEnd   = $weekInfo['week_end'];

        // Check for existing summary
        $existing = WeeklySummary::where('user_id', $userId)
            ->where('week_start', $weekStart->toDateString())
            ->first();

        // If exists and no new journals since last update, return cached
        if ($existing) {
            $latestJournal = Journal::where('user_id', $userId)
                ->whereBetween('entry_date', [$weekStart, $weekEnd])
                ->latest('updated_at')
                ->first();

            // Re-calculate only if journals were modified after last summary update
            if (! $latestJournal || $latestJournal->updated_at->lte($existing->updated_at)) {
                return $existing;
            }

            Log::info("Journals updated since last summary — recalculating for user {$userId}, week starting {$weekStart->toDateString()}");
        }

        // Calculate or recalculate
        return $this->buildAndSaveSummary($userId, $weekStart, $weekEnd, forceRecalculate: true);
    }

    // ─────────────────────────────────────────────────────────
    //  Core Analysis Engine
    // ─────────────────────────────────────────────────────────

    /**
     * Build and save (or update) the weekly summary.
     *
     * @param  bool  $forceRecalculate  If true, uses updateOrCreate to overwrite existing
     */
    protected function buildAndSaveSummary(
        int $userId,
        Carbon $weekStart,
        Carbon $weekEnd,
        ?int $weekIndex = null,
        bool $forceRecalculate = false,
    ): ?WeeklySummary {
        // Retrieve all journal entries for the rolling week
        $journals = Journal::where('user_id', $userId)
            ->whereBetween('entry_date', [$weekStart, $weekEnd])
            ->orderBy('entry_date')
            ->get();

        if ($journals->isEmpty()) {
            // If recalculating and all journals are deleted, remove the summary
            if ($forceRecalculate) {
                WeeklySummary::where('user_id', $userId)
                    ->where('week_start', $weekStart->toDateString())
                    ->delete();

                Log::info("Removed weekly summary for user {$userId} — no journals remain for week starting {$weekStart->toDateString()}");
            }
            return null;
        }

        // Combine into summary text
        $summaryText = $this->combineJournals($journals);

        if (empty(trim($summaryText))) {
            return null;
        }

        // Call AI service for analysis
        $analysis = $this->aiService->analyze($summaryText);
        $aiAvailable = $analysis !== null;

        if (! $aiAvailable) {
            Log::warning("AI analysis failed for user {$userId}, using fallback scoring");
            $analysis = $this->fallbackAnalysis();
        }

        // Check for risk escalation (longitudinal trend)
        $escalationFlag = $this->riskAssessment->checkEscalation($userId, $analysis['lri_score']);

        // If escalating, bump risk level up by one tier
        $riskLevel = $analysis['risk_level'];
        if ($escalationFlag) {
            $riskLevel = $this->riskAssessment->escalateRiskLevel($riskLevel);
        }

        // Save within a transaction — updateOrCreate prevents duplicates
        return DB::transaction(function () use (
            $userId, $summaryText, $analysis, $riskLevel, $escalationFlag,
            $weekStart, $weekEnd, $aiAvailable, $forceRecalculate
        ) {
            $data = [
                'summary_text'     => $summaryText,
                'stress_score'     => $analysis['stress_probability'],
                'sentiment_score'  => $analysis['sentiment_score'],
                'pronoun_ratio'    => $analysis['pronoun_ratio'],
                'absolutist_score' => $analysis['absolutist_score'],
                'withdrawal_score' => $analysis['withdrawal_score'],
                'lri_score'        => $analysis['lri_score'],
                'risk_level'       => $riskLevel,
                'escalation_flag'  => $escalationFlag,
                'week_start'       => $weekStart->toDateString(),
                'week_end'         => $weekEnd->toDateString(),
                'week_index'       => $weekIndex ?? $this->rollingWeek->getNextWeekIndex($userId),
            ];

            $summary = WeeklySummary::updateOrCreate(
                [
                    'user_id'    => $userId,
                    'week_start' => $weekStart->toDateString(),
                ],
                $data
            );

            $action = $summary->wasRecentlyCreated ? 'created' : 'updated';

            Log::info("Weekly summary {$action}", [
                'user_id'      => $userId,
                'summary_id'   => $summary->id,
                'week_index'   => $summary->week_index,
                'week_start'   => $weekStart->toDateString(),
                'week_end'     => $weekEnd->toDateString(),
                'lri_score'    => $summary->lri_score,
                'risk_level'   => $summary->risk_level,
                'escalated'    => $escalationFlag,
                'ai_available' => $aiAvailable,
            ]);

            return $summary;
        });
    }

    // ─────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────

    /**
     * Combine multiple journal entries into a single summary text.
     */
    protected function combineJournals($journals): string
    {
        return $journals
            ->map(fn ($j) => "[{$j->entry_date->format('l, M d')}] {$j->content}")
            ->implode("\n\n");
    }

    /**
     * Provide fallback scores when AI service is unavailable.
     */
    protected function fallbackAnalysis(): array
    {
        return [
            'stress_probability' => 0.0,
            'sentiment_score'    => 0.5,
            'pronoun_ratio'      => 0.0,
            'absolutist_score'   => 0.0,
            'withdrawal_score'   => 0.0,
            'lri_score'          => 10.0,
            'risk_level'         => 'Low',
        ];
    }
}
