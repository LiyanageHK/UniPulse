<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Services\JournalAccessService;
use App\Services\WeeklySummaryService;
use App\Services\RollingWeekService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class JournalController extends Controller
{
    protected WeeklySummaryService $weeklySummaryService;
    protected RollingWeekService $rollingWeekService;
    protected JournalAccessService $journalAccessService;

    public function __construct(
        WeeklySummaryService $weeklySummaryService,
        RollingWeekService $rollingWeekService,
        JournalAccessService $journalAccessService,
    ) {
        $this->weeklySummaryService = $weeklySummaryService;
        $this->rollingWeekService = $rollingWeekService;
        $this->journalAccessService = $journalAccessService;
    }

    /**
     * Show the journal writing page.
     */
    public function index()
    {
        $userId = Auth::id();

        // Check access using week_end (NOT created_at or calendar weeks)
        $access = $this->journalAccessService->canPostJournal($userId);

        // Today's entry (pre-fills the form if already written today)
        $todayEntry = Journal::where('user_id', $userId)
            ->where('entry_date', Carbon::today()->toDateString())
            ->first();

        // Most recent entry
        $lastEntry = Journal::where('user_id', $userId)
            ->orderByDesc('entry_date')
            ->first();

        // Paginated list of all entries
        $journals = Journal::where('user_id', $userId)
            ->orderByDesc('entry_date')
            ->paginate(10);

        return view('journal.index', compact(
            'journals',
            'todayEntry',
            'lastEntry',
            'access',
        ));
    }

    /**
     * Store or update today's journal entry and recalculate the current week's summary.
     *
     * Access control is enforced by the EnsureJournalAccess middleware AND validated here.
     * Uses weekly_summaries.week_end — NOT created_at or calendar-based weeks.
     */
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|min:10|max:5000',
        ]);

        $userId = Auth::id();

        // ── Strict week_end access check ────────────────────────────
        $access = $this->journalAccessService->canPostJournal($userId);

        Log::info('JournalController::store access check', [
            'user_id'    => $userId,
            'allowed'    => $access['allowed'],
            'reason'     => $access['reason'],
            'week_end'   => $access['week_end'],
            'now'        => Carbon::now()->startOfDay()->toDateString(),
        ]);

        if (! $access['allowed']) {
            $nextDate = $access['next_allowed'] ?? 'N/A';
            $daysLeft = $access['days_remaining'] ?? 0;

            $message = $access['duplicate']
                ? "You have already submitted a journal entry for Week #{$access['current_week_index']}. Next entry available from {$nextDate}."
                : "Your current week has not ended yet. You can write your next journal entry in {$daysLeft} day(s) (available from {$nextDate}).";

            return redirect()->back()
                ->withErrors(['content' => $message])
                ->withInput();
        }
        // ────────────────────────────────────────────────────────────

        $journal = Journal::updateOrCreate(
            [
                'user_id'    => $userId,
                'entry_date' => Carbon::today()->toDateString(),
            ],
            [
                'content' => $request->input('content'),
            ]
        );

        // Trigger lazy weekly summary recalculation for the rolling week
        try {
            $entryDate = Carbon::parse($journal->entry_date);
            $weekInfo  = $this->rollingWeekService->getWeekInfoForDate($userId, $entryDate);

            if ($weekInfo) {
                Log::info('JournalController: triggering summary recalculation', [
                    'user_id'    => $userId,
                    'week_index' => $weekInfo['week_index'],
                    'week_start' => $weekInfo['week_start']->toDateString(),
                    'week_end'   => $weekInfo['week_end']->toDateString(),
                ]);

                $this->weeklySummaryService->recalculateUserWeek(
                    $userId,
                    $weekInfo['week_start'],
                    $weekInfo['week_end'],
                    $weekInfo['week_index'],
                );
            }
        } catch (\Exception $e) {
            Log::error('Weekly summary recalculation failed after journal save', [
                'user_id' => $userId,
                'error'   => $e->getMessage(),
            ]);
            // Don't fail the journal save — summary will be recalculated on dashboard visit
        }

        // Determine where to redirect after saving
        $redirectTo = $request->input('redirect_to');

        // If this is the user's first journal entry, go to risk dashboard
        $totalJournals = Journal::where('user_id', $userId)->count();

        if ($redirectTo === 'dashboard') {
            return redirect()
                ->route('dashboard')
                ->with('success', $totalJournals === 1
                    ? 'Your first journal entry was saved! Your risk profile will be generated soon.'
                    : 'Journal entry saved for today.');
        }

        return redirect()
            ->route('risk-dashboard.index')
            ->with('success', $totalJournals === 1
                ? 'Your first journal entry was saved! Here is your initial risk profile.'
                : 'Journal entry saved successfully.');
    }

    /**
     * Show a specific journal entry.
     */
    public function show(int $id)
    {
        $journal = Journal::where('user_id', Auth::id())
            ->findOrFail($id);

        return view('journal.show', compact('journal'));
    }

    /**
     * Delete a journal entry and recalculate the affected week.
     */
    public function destroy(int $id)
    {
        $journal = Journal::where('user_id', Auth::id())
            ->findOrFail($id);

        $entryDate = Carbon::parse($journal->entry_date);
        $weekInfo  = $this->rollingWeekService->getWeekInfoForDate(Auth::id(), $entryDate);

        $journal->delete();

        // If no journals remain for that week, delete the summary; otherwise recalculate
        try {
            if ($weekInfo) {
                $remaining = Journal::where('user_id', Auth::id())
                    ->whereBetween('entry_date', [
                        $weekInfo['week_start']->toDateString(),
                        $weekInfo['week_end']->toDateString(),
                    ])
                    ->exists();

                if ($remaining) {
                    $this->weeklySummaryService->recalculateUserWeek(
                        Auth::id(),
                        $weekInfo['week_start'],
                        $weekInfo['week_end'],
                        $weekInfo['week_index'],
                    );
                } else {
                    \App\Models\WeeklySummary::where('user_id', Auth::id())
                        ->where('week_start', $weekInfo['week_start']->toDateString())
                        ->delete();
                }
            }
        } catch (\Exception $e) {
            Log::error('Weekly summary recalculation failed after journal delete', [
                'user_id' => Auth::id(),
                'error'   => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('risk-dashboard.index')
            ->with('success', 'Journal entry deleted.');
    }
}
