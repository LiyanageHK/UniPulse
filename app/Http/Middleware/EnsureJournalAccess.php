<?php

namespace App\Http\Middleware;

use App\Services\JournalAccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that blocks journal submission if the current rolling week
 * has not yet ended (based on weekly_summaries.week_end).
 *
 * Applied to the journal.store route to enforce the rule at the HTTP layer
 * before the controller even runs.
 */
class EnsureJournalAccess
{
    protected JournalAccessService $journalAccess;

    public function __construct(JournalAccessService $journalAccess)
    {
        $this->journalAccess = $journalAccess;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $userId = Auth::id();

        if (! $userId) {
            return $next($request);
        }

        $access = $this->journalAccess->canPostJournal($userId);

        Log::info('EnsureJournalAccess middleware check', [
            'user_id' => $userId,
            'allowed' => $access['allowed'],
            'reason'  => $access['reason'],
        ]);

        if ($access['allowed']) {
            return $next($request);
        }

        // ── Denied — build user-friendly message ──────────────────
        if ($access['reason'] === 'duplicate_week') {
            $message = "You have already submitted a journal entry for Week #{$access['current_week_index']}. "
                     . "Your next entry will be available from {$access['next_allowed']}.";
        } else {
            $daysLeft = $access['days_remaining'] ?? 0;
            $message  = "Your current week has not ended yet. "
                      . "You can write your next journal entry in {$daysLeft} day(s) "
                      . "(available from {$access['next_allowed']}).";
        }

        // For AJAX / JSON requests
        if ($request->expectsJson()) {
            return response()->json([
                'error'          => $message,
                'allowed'        => false,
                'reason'         => $access['reason'],
                'next_allowed'   => $access['next_allowed'],
                'days_remaining' => $access['days_remaining'],
            ], 403);
        }

        return redirect()->back()
            ->withErrors(['content' => $message])
            ->withInput();
    }
}
