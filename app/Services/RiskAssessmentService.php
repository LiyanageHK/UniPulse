<?php

namespace App\Services;

use App\Models\WeeklySummary;
use Illuminate\Support\Facades\Log;

/**
 * Service for risk classification, longitudinal trend analysis,
 * escalation logic, and trend detection.
 */
class RiskAssessmentService
{
    /**
     * Risk level hierarchy (lowest to highest).
     */
    protected array $riskHierarchy = ['Low', 'Moderate', 'High'];

    /**
     * Number of previous weeks to consider for escalation analysis.
     */
    protected int $trendWeeks = 3;

    // ─────────────────────────────────────────────────────────
    //  Escalation Logic
    // ─────────────────────────────────────────────────────────

    /**
     * Check if a user's risk should be escalated based on longitudinal trend.
     *
     * Rule: If LRI has been increasing for 2 or more consecutive weeks, escalate.
     *
     * @param  int    $userId     User ID
     * @param  float  $currentLri Current week's LRI score
     * @return bool   Whether risk should be escalated
     */
    public function checkEscalation(int $userId, float $currentLri): bool
    {
        $previousSummaries = WeeklySummary::where('user_id', $userId)
            ->orderByDesc('week_start')
            ->take($this->trendWeeks)
            ->get();

        if ($previousSummaries->count() < 2) {
            return false;
        }

        // Build chronological LRI sequence: oldest → newest → current
        $lriSequence = $previousSummaries->sortBy('week_start')
            ->pluck('lri_score')
            ->push($currentLri)
            ->values()
            ->toArray();

        // Check for 2+ consecutive increases at the end of the sequence
        $consecutiveIncreases = 0;
        for ($i = count($lriSequence) - 1; $i >= 1; $i--) {
            if ($lriSequence[$i] > $lriSequence[$i - 1]) {
                $consecutiveIncreases++;
            } else {
                break;
            }
        }

        $escalate = $consecutiveIncreases >= 2;

        if ($escalate) {
            Log::warning("Risk escalation triggered for user {$userId}", [
                'lri_sequence'          => $lriSequence,
                'consecutive_increases' => $consecutiveIncreases,
            ]);
        }

        return $escalate;
    }

    /**
     * Escalate risk level by one tier.
     */
    public function escalateRiskLevel(string $currentLevel): string
    {
        $index = array_search($currentLevel, $this->riskHierarchy);

        if ($index === false) {
            return 'Moderate'; // Default fallback
        }

        $newIndex = min($index + 1, count($this->riskHierarchy) - 1);
        return $this->riskHierarchy[$newIndex];
    }

    // ─────────────────────────────────────────────────────────
    //  Trend Data for Charts
    // ─────────────────────────────────────────────────────────

    /**
     * Get risk trend data for the last N weeks (for dashboard chart).
     *
     * @param  int  $userId
     * @param  int  $weeks  Number of weeks to fetch (default 6)
     * @return array Trend data with week labels, LRI scores, and levels
     */
    public function getTrendData(int $userId, int $weeks = 6): array
    {
        $summaries = WeeklySummary::where('user_id', $userId)
            ->whereExists(function ($q) {
                $q->select(\DB::raw(1))
                  ->from('journals')
                  ->whereColumn('journals.user_id', 'weekly_summaries.user_id')
                  ->whereColumn('journals.entry_date', '>=', 'weekly_summaries.week_start')
                  ->whereColumn('journals.entry_date', '<=', 'weekly_summaries.week_end');
            })
            ->orderByDesc('week_start')
            ->take($weeks)
            ->get()
            ->sortBy('week_start')
            ->values();

        $labels = [];
        $scores = [];
        $levels = [];
        $ids    = [];
        $weekIndexes = [];
        $weekNum = 1;

        foreach ($summaries as $summary) {
            $labels[] = 'Week #' . ($summary->week_index ?? $weekNum);
            $scores[] = round($summary->lri_score, 2);
            $levels[] = $summary->risk_level;
            $ids[]    = $summary->id;
            $weekIndexes[] = $summary->week_index ?? $weekNum;
            $weekNum++;
        }

        return [
            'labels'       => $labels,
            'scores'       => $scores,
            'levels'       => $levels,
            'ids'          => $ids,
            'week_indexes' => $weekIndexes,
            'has_data'     => $summaries->isNotEmpty(),
        ];
    }

    // ─────────────────────────────────────────────────────────
    //  Trend Detection (↑ ↓ →)
    // ─────────────────────────────────────────────────────────

    /**
     * Compare current week LRI with previous week and return trend indicator.
     *
     * @return array{direction: string, symbol: string, label: string, delta: float|null}
     */
    public function detectTrend(int $userId): array
    {
        $summaries = WeeklySummary::where('user_id', $userId)
            ->orderByDesc('week_start')
            ->take(2)
            ->get();

        if ($summaries->count() < 2) {
            return [
                'direction' => 'stable',
                'symbol'    => '→',
                'label'     => 'Not enough data',
                'delta'     => null,
            ];
        }

        $current  = $summaries->first()->lri_score;
        $previous = $summaries->last()->lri_score;
        $delta    = round($current - $previous, 2);

        // Use a ±0.02 threshold for "stable" (0–1 scale)
        if ($delta > 0.02) {
            return [
                'direction' => 'increasing',
                'symbol'    => '↑',
                'label'     => 'Increasing Risk',
                'delta'     => $delta,
            ];
        }

        if ($delta < -0.02) {
            return [
                'direction' => 'decreasing',
                'symbol'    => '↓',
                'label'     => 'Decreasing Risk',
                'delta'     => $delta,
            ];
        }

        return [
            'direction' => 'stable',
            'symbol'    => '→',
            'label'     => 'Stable',
            'delta'     => $delta,
        ];
    }

    // ─────────────────────────────────────────────────────────
    //  Latest Risk Profile
    // ─────────────────────────────────────────────────────────

    /**
     * Get the latest weekly summary with full risk context.
     */
    public function getLatestRiskProfile(int $userId): ?array
    {
        $latest = WeeklySummary::where('user_id', $userId)
            ->orderByDesc('week_start')
            ->first();

        if (!$latest) {
            return null;
        }

        return [
            'summary_id'      => $latest->id,
            'week_index'       => $latest->week_index,
            'lri_score'        => round($latest->lri_score, 2),
            'risk_level'       => $latest->risk_level,
            'risk_color'       => $latest->risk_color,
            'risk_message'     => $latest->risk_message,
            'escalation_flag'  => $latest->escalation_flag,
            'stress_score'     => round($latest->stress_score, 4),
            'sentiment_score'  => round($latest->sentiment_score, 4),
            'pronoun_ratio'    => round($latest->pronoun_ratio, 4),
            'absolutist_score' => round($latest->absolutist_score, 4),
            'withdrawal_score' => round($latest->withdrawal_score, 4),
            'week_start'       => $latest->week_start->format('Y-m-d'),
            'week_end'         => $latest->week_end->format('Y-m-d'),
            'created_at'       => $latest->created_at->toDateTimeString(),
        ];
    }

    // ─────────────────────────────────────────────────────────
    //  Risk History (for history view)
    // ─────────────────────────────────────────────────────────

    /**
     * Get paginated risk history sorted by week_start DESC.
     *
     * @param  int  $userId
     * @param  int  $limit   Number of records per page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getRiskHistory(int $userId, int $limit = 10)
    {
        return WeeklySummary::where('user_id', $userId)
            ->orderByDesc('week_start')
            ->select([
                'id', 'week_index', 'week_start', 'week_end', 'lri_score', 'risk_level',
                'escalation_flag', 'stress_score', 'sentiment_score',
                'pronoun_ratio', 'absolutist_score', 'withdrawal_score',
                'created_at',
            ])
            ->paginate($limit);
    }

    /**
     * Get a specific weekly summary by ID for the given user.
     */
    public function getWeeklyReport(int $userId, int $summaryId): ?WeeklySummary
    {
        return WeeklySummary::where('user_id', $userId)
            ->where('id', $summaryId)
            ->first();
    }

    // ─────────────────────────────────────────────────────────
    //  Static Classification Helpers
    // ─────────────────────────────────────────────────────────

    /**
     * Determine risk level from an LRI score.
     */
    public static function classifyRisk(float $lriScore): string
    {
        if ($lriScore >= 0.6) return 'High';
        if ($lriScore >= 0.3) return 'Moderate';
        return 'Low';
    }

    /**
     * Get the interpretation message for a risk level.
     */
    public static function getRiskMessage(string $riskLevel): string
    {
        return match ($riskLevel) {
            'Low'      => 'Emotionally stable.',
            'Moderate' => 'Mild stress indicators.',
            'High'     => 'High stress signals detected.',
            default    => 'No data available.',
        };
    }

    /**
     * Get the badge color for a risk level.
     */
    public static function getRiskColor(string $riskLevel): string
    {
        return match ($riskLevel) {
            'Low'      => 'green',
            'Moderate' => 'yellow',
            'High'     => 'red',
            default    => 'gray',
        };
    }
}
