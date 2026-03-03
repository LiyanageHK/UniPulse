<?php

namespace App\Http\Controllers;

use App\Services\RiskAssessmentService;
use App\Services\WeeklySummaryService;
use App\Services\RollingWeekService;
use App\Services\AIService;
use App\Models\WeeklySummary;
use App\Models\Journal;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RiskDashboardController extends Controller
{
    protected RiskAssessmentService $riskAssessment;
    protected WeeklySummaryService $weeklySummary;
    protected RollingWeekService $rollingWeek;
    protected AIService $aiService;

    public function __construct(
        RiskAssessmentService $riskAssessment,
        WeeklySummaryService $weeklySummary,
        RollingWeekService $rollingWeek,
        AIService $aiService,
    ) {
        $this->riskAssessment = $riskAssessment;
        $this->weeklySummary = $weeklySummary;
        $this->rollingWeek = $rollingWeek;
        $this->aiService = $aiService;
    }

    // ─────────────────────────────────────────────────────────
    //  Dashboard Index (lazy evaluation of current week)
    // ─────────────────────────────────────────────────────────

    /**
     * Display the risk dashboard with current week risk, summary details,
     * 6-week trend chart, and trend indicator.
     */
    public function index()
    {
        $userId = Auth::id();

        // Lazy-evaluate: get or calculate current week's summary
        try {
            $currentSummary = $this->weeklySummary->getOrCalculateCurrentWeek($userId);
        } catch (\Exception $e) {
            Log::error('Dashboard: failed to calculate current week summary', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);
            $currentSummary = null;
        }

        // Build risk profile from current summary
        $riskProfile = $currentSummary
            ? $this->buildRiskProfile($currentSummary)
            : null;

        // Last 6 weeks trend data for chart
        $trendData = $this->riskAssessment->getTrendData($userId, 6);

        // Trend detection (↑ ↓ →)
        $trend = $this->riskAssessment->detectTrend($userId);

        // AI service health check
        $aiHealthy = $this->aiService->isHealthy();

        // Journal data for the embedded journal section
        $todayEntry = Journal::where('user_id', $userId)
            ->where('entry_date', Carbon::today()->toDateString())
            ->first();

        $lastEntry = Journal::where('user_id', $userId)
            ->orderByDesc('entry_date')
            ->first();

        $journals = Journal::where('user_id', $userId)
            ->orderByDesc('entry_date')
            ->paginate(10);

        return view('risk-dashboard.index', compact(
            'riskProfile',
            'trendData',
            'trend',
            'aiHealthy',
            'todayEntry',
            'lastEntry',
            'journals',
        ));
    }

    // ─────────────────────────────────────────────────────────
    //  Risk History View
    // ─────────────────────────────────────────────────────────

    /**
     * Delete a weekly summary record belonging to the authenticated user.
     */
    public function destroySummary(int $id)
    {
        WeeklySummary::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail()
            ->delete();

        return redirect()->route('risk-dashboard.index')
            ->with('success', 'Weekly summary deleted.');
    }

    /**
     * Display paginated risk history — past weekly reports.
     */
    public function history()
    {
        $userId = Auth::id();

        $history = $this->riskAssessment->getRiskHistory($userId, 10);

        return view('risk-dashboard.history', compact('history'));
    }

    /**
     * Show a single weekly report in detail.
     */
    public function showReport(int $id)
    {
        $userId = Auth::id();

        $report = $this->riskAssessment->getWeeklyReport($userId, $id);

        if (!$report) {
            abort(404, 'Weekly report not found.');
        }

        return view('risk-dashboard.report', compact('report'));
    }

    // ─────────────────────────────────────────────────────────
    //  API Endpoints
    // ─────────────────────────────────────────────────────────

    /**
     * API endpoint: return latest risk data as JSON.
     */
    public function apiLatest()
    {
        $userId = Auth::id();

        $riskProfile = $this->riskAssessment->getLatestRiskProfile($userId);
        $trendData = $this->riskAssessment->getTrendData($userId, 6);
        $trend = $this->riskAssessment->detectTrend($userId);

        return response()->json([
            'risk_profile' => $riskProfile,
            'trend'        => $trendData,
            'trend_direction' => $trend,
        ]);
    }

    /**
     * API endpoint: return risk history as JSON (for AJAX/chart consumers).
     */
    public function apiHistory()
    {
        $userId = Auth::id();

        $history = WeeklySummary::where('user_id', $userId)
            ->orderByDesc('week_start')
            ->take(6)
            ->get()
            ->map(fn ($s) => [
                'id'              => $s->id,
                'week_index'      => $s->week_index,
                'week_start'      => $s->week_start->format('Y-m-d'),
                'week_end'        => $s->week_end->format('Y-m-d'),
                'lri_score'       => round($s->lri_score, 2),
                'risk_level'      => $s->risk_level,
                'risk_color'      => $s->risk_color,
                'escalation_flag' => $s->escalation_flag,
            ]);

        return response()->json(['history' => $history]);
    }

    // ─────────────────────────────────────────────────────────
    //  Manual Trigger (Test/Debug)
    // ─────────────────────────────────────────────────────────

    /**
     * Manually trigger weekly summary recalculation for the current user.
     */
    public function testProcessWeekly()
    {
        $userId = Auth::id();

        $weekInfo = $this->rollingWeek->getCurrentWeekInfo($userId);

        if (! $weekInfo) {
            return redirect()->route('risk-dashboard.index')
                ->with('warning', 'No journal entries found. Write your first journal entry to start tracking.');
        }

        try {
            $summary = $this->weeklySummary->recalculateUserWeek(
                $userId,
                $weekInfo['week_start'],
                $weekInfo['week_end'],
                $weekInfo['week_index'],
            );

            if ($summary) {
                return redirect()->route('risk-dashboard.index')
                    ->with('success', "Weekly summary generated! LRI: {$summary->lri_score} — Risk: {$summary->risk_level}");
            }

            return redirect()->route('risk-dashboard.index')
                ->with('warning', 'No journal entries found for this rolling week.');
        } catch (\Exception $e) {
            Log::error('Manual weekly summary trigger failed', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);

            return redirect()->route('risk-dashboard.index')
                ->with('error', 'Processing failed: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────────
    //  Private Helpers
    // ─────────────────────────────────────────────────────────

    /**
     * Build a structured risk profile array from a WeeklySummary model.
     */
    private function buildRiskProfile(WeeklySummary $summary): array
    {
        return [
            'summary_id'      => $summary->id,
            'week_index'       => $summary->week_index,
            'lri_score'        => round($summary->lri_score, 2),
            'risk_level'       => $summary->risk_level,
            'risk_color'       => $summary->risk_color,
            'risk_message'     => $summary->risk_message,
            'escalation_flag'  => $summary->escalation_flag,
            'stress_score'     => round($summary->stress_score, 4),
            'sentiment_score'  => round($summary->sentiment_score, 4),
            'pronoun_ratio'    => round($summary->pronoun_ratio, 4),
            'absolutist_score' => round($summary->absolutist_score, 4),
            'withdrawal_score' => round($summary->withdrawal_score, 4),
            'week_start'       => $summary->week_start->format('Y-m-d'),
            'week_end'         => $summary->week_end->format('Y-m-d'),
            'created_at'       => $summary->created_at->toDateTimeString(),
        ];
    }
}
