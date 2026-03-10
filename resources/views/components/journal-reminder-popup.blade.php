{{-- ═══════════════════════════════════════════════════════════════════════
     GLOBAL JOURNAL REMINDER POPUP — week_end based
     Included in every authenticated layout (app.blade.php, app-dashboard.blade.php).
     Logic:
       • Uses weekly_summaries.week_end as the gate (NOT created_at, NOT entry_date + 7).
       • Shows if: logged in AND (no summaries OR now >= last week_end).
       • sessionStorage key prevents re-showing after dismiss within the same session.
     ═══════════════════════════════════════════════════════════════════════ --}}

@auth
@php
    // Fetch the latest weekly summary's week_end for this user
    $__latestSummary = \App\Models\WeeklySummary::where('user_id', auth()->id())
                        ->orderByDesc('week_end')
                        ->first();
    $__weekEnd       = $__latestSummary?->week_end?->format('Y-m-d');  // "YYYY-MM-DD" or null
    $__weekIndex     = $__latestSummary?->week_index;
    $__onboardingDone = (bool) auth()->user()->onboarding_completed;
    $__todayEntry    = \App\Models\Journal::where('user_id', auth()->id())
                        ->where('entry_date', \Carbon\Carbon::today()->toDateString())->first();
    $__writtenToday  = (bool) $__todayEntry;

    // Check for duplicate — journal already exists in the upcoming week window
    $__duplicateInWeek = false;
    if ($__latestSummary) {
        $__newWeekStart = \Carbon\Carbon::parse($__latestSummary->week_end)->startOfDay();
        $__newWeekEnd   = $__newWeekStart->copy()->addDays(6);
        $__duplicateInWeek = \App\Models\Journal::where('user_id', auth()->id())
            ->whereBetween('entry_date', [$__newWeekStart->toDateString(), $__newWeekEnd->toDateString()])
            ->exists();
    }
@endphp

{{-- Modal (hidden by default; JS opens it when conditions are met) --}}
<div id="journalReminderModal"
     class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50 px-4"
     aria-modal="true" role="dialog" aria-labelledby="jrModalTitle">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 relative"
         style="animation: jrFadeIn 0.3s ease-out;">

        {{-- Close × --}}
        <button onclick="closeJournalReminder()"
            class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition"
            aria-label="Close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Icon + heading --}}
        <div class="text-center mb-6">
            <div class="text-5xl mb-3">📔</div>
            <h2 id="jrModalTitle" class="text-2xl font-bold text-gray-800 mb-2">
                Time to Check In
            </h2>
            <p id="jrMessage" class="text-gray-500 text-sm leading-relaxed"></p>
        </div>

        {{-- Last entry date (populated by JS) --}}
        <div id="jrLastEntryRow"
             class="hidden bg-gray-50 rounded-lg px-4 py-3 mb-6 text-sm text-center text-gray-500">
            Week ended: <span id="jrLastEntryLabel" class="font-medium text-gray-700"></span>
        </div>

        {{-- Buttons --}}
        <div class="flex gap-3">
            <button onclick="closeJournalReminder()"
                class="flex-1 border border-gray-300 text-gray-600 font-semibold py-2.5 rounded-lg
                       hover:bg-gray-50 transition text-sm">
                Remind Me Later
            </button>
            <a href="{{ route('journal.index') }}"
               onclick="closeJournalReminder()"
               class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2.5
                      rounded-lg transition text-sm text-center">
                Write Now ✍️
            </a>
        </div>
    </div>
</div>

<script>
(function () {
    // ── data injected from PHP (week_end based, NOT created_at) ────
    var ONBOARDING_DONE   = @json($__onboardingDone);   // true if onboarding is completed
    var WEEK_END          = @json($__weekEnd);          // "YYYY-MM-DD" or null (latest summary week_end)
    var WEEK_INDEX        = @json($__weekIndex);        // latest week_index or null
    var WRITTEN_TODAY     = @json($__writtenToday);      // true / false
    var DUPLICATE_IN_WEEK = @json($__duplicateInWeek);   // true if journal already exists in upcoming week

    // ── helpers ────────────────────────────────────────────────────
    function todayMidnight() {
        var d = new Date();
        d.setHours(0, 0, 0, 0);
        return d;
    }

    function shouldShow() {
        if (!ONBOARDING_DONE)  return false;  // new user hasn't finished onboarding yet
        if (WRITTEN_TODAY)     return false;  // already wrote today → no popup
        if (DUPLICATE_IN_WEEK) return false;  // already have journal for this week

        var today = todayMidnight();

        // No summaries ever → new user → show immediately
        if (!WEEK_END) return true;

        // Parse week_end and compare using startOfDay
        var parts   = WEEK_END.split('-').map(Number);
        var weekEnd = new Date(parts[0], parts[1] - 1, parts[2]); // local midnight
        weekEnd.setHours(0, 0, 0, 0);

        // Show when today >= week_end (week has ended)
        return today >= weekEnd;
    }

    function dismissedToday() {
        var key = 'journalPopupDismissed_' + new Date().toISOString().slice(0, 10);
        return sessionStorage.getItem(key) === '1';
    }

    // ── public: called by the "Remind Me Later" button ─────────────────
    window.closeJournalReminder = function () {
        var modal = document.getElementById('journalReminderModal');
        if (!modal) return;

        // Remember dismissal only for this session (not across logins)
        var key = 'journalPopupDismissed_' + new Date().toISOString().slice(0, 10);
        sessionStorage.setItem(key, '1');

        modal.style.transition = 'opacity 0.3s';
        modal.style.opacity    = '0';
        setTimeout(function () { modal.classList.add('hidden'); modal.style.opacity = ''; }, 300);
    };

    // ── on page load ───────────────────────────────────────────────────
    function init() {
        if (!shouldShow() || dismissedToday()) return;

        var modal    = document.getElementById('journalReminderModal');
        var msgEl    = document.getElementById('jrMessage');
        var infoRow  = document.getElementById('jrLastEntryRow');
        var dateEl   = document.getElementById('jrLastEntryLabel');

        if (!modal) return;

        // Fill message text
        if (!WEEK_END) {
            msgEl.textContent = "You haven't written a journal entry yet. Start today to begin tracking your wellbeing.";
        } else {
            var parts   = WEEK_END.split('-').map(Number);
            var weekEnd = new Date(parts[0], parts[1] - 1, parts[2]);
            var today   = todayMidnight();
            var diffDays = Math.floor((today - weekEnd) / 86400000);

            var weekLabel = WEEK_INDEX ? ' (Week #' + (WEEK_INDEX + 1) + ')' : '';
            msgEl.innerHTML = "Your previous week ended <strong style=\"color:#7c3aed\">" + diffDays
                            + " day" + (diffDays !== 1 ? "s" : "") + " ago</strong>."
                            + " Time to write your next journal entry" + weekLabel + "."
                            + " Writing regularly helps track your emotional wellbeing.";

            dateEl.textContent = weekEnd.toLocaleDateString(undefined, {
                weekday: 'long', year: 'numeric', month: 'short', day: 'numeric'
            });
            infoRow.classList.remove('hidden');
        }

        // Show modal
        modal.classList.remove('hidden');

        // Close on backdrop click
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeJournalReminder();
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init(); // DOM already ready
    }
})();
</script>

<style>
    @keyframes jrFadeIn {
        from { opacity: 0; transform: translateY(-16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>
@endauth
