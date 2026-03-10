<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WeeklyChecking;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\StudentProfile;
use App\Models\User;
use App\Models\PeerRequest;
use App\Models\Chat;
use App\Models\PeerRating;
use App\Models\WeeklySummary;
use Illuminate\Support\Facades\Http;

class DashboardPoornimaController extends Controller
{
    public function dashboard(Request $request)
    {
        $userId = Auth::id();

        // Get last 4 weeks scores
        $survey_count = WeeklyChecking::where('user_id',$userId)->count();
        $weeklyScores = WeeklyChecking::getLast4WeeksScores($userId);
        $weightedScores = WeeklyChecking::calculateWeightedScores($weeklyScores);
        $trends = WeeklyChecking::detectTrend($weeklyScores);

        $report = [];
        foreach ($weightedScores as $area => $score) {
            $risk = WeeklyChecking::determineRisk($area, $score);
            $trend = $trends[$area];
            $priority = WeeklyChecking::getSuggestionPriority($risk, $trend);

            $report[] = [
                'area' => $area,
                'weekly_scores' => $weeklyScores[$area],
                'weighted_score' => $score,
                'risk_level' => $risk,
                'trend' => $trend,
                'suggestion_priority' => $priority,
            ];
        }

        // Sort by priority then area order
        $order = ['depression', 'stress', 'social_isolation', 'disengagement', 'openness'];
        usort(
            $report,
            fn($a, $b) =>
            $a['suggestion_priority'] <=> $b['suggestion_priority'] ?: array_search($a['area'], $order) - array_search($b['area'], $order)
        );

        // Quick stats
        $checkins_count = WeeklyChecking::where('user_id', $userId)->count();
        $lastWeekly = WeeklyChecking::where('user_id', $userId)->latest()->first();
        $previousWeekly = WeeklyChecking::where('user_id', $userId)->latest()->skip(1)->first();

        $current_mood = $lastWeekly->overall_mood ?? 0;
        $last_week_mood = $previousWeekly->mood ?? $current_mood;
        $mood_change = $current_mood - $last_week_mood;

        $peer_connections = PeerRequest::where('sender_id', $userId)->where('status','accepted')->count() + PeerRequest::where('receiver_id', $userId)->where('status','accepted')->count(); // example
        $support_level = 'Good'; // example

        // --- SUGGESTIONS ---
        $suggestionsText = [
            'depression' => [
                1 => "Reach out to the wellbeing center or a counselor for emotional support.",
                2 => "Talk to a friend or classmate you trust about how you’re feeling.",
                3 => "Take a short break or go for a short walk to refresh your mood.",
                4 => "Do one small activity you enjoy today (music, drawing, relaxing).",
            ],
            'stress' => [
                1 => "Try a 2–3 minute breathing exercise to calm down quickly.",
                2 => "Break your workload into smaller tasks and handle them one by one.",
                3 => "Take short breaks between study sessions to avoid build-up.",
                4 => "Plan your week with a simple checklist to reduce pressure.",
            ],
            'social_isolation' => [
                1 => "Join at least one simple, low-pressure campus activity this week.",
                2 => "Send a small friendly message to a classmate to start a conversation.",
                3 => "Sit with a familiar person before/after class to build connection slowly.",
                4 => "Attend one group event this month to stay socially active.",
            ],
            'disengagement' => [
                1 => "Talk to a lecturer or mentor about your workload or challenges.",
                2 => "Start with one small task today — completing one thing boosts momentum.",
                3 => "Review your weekly study goals and adjust them to stay consistent.",
                4 => "Try a 25-minute focused study session to build energy.",
            ],
            'openness' => [
                1 => "You don’t have to handle everything alone — reach out to someone you trust or a counselor.",
                2 => "If reaching out feels hard, start with a small step like messaging a peer or mentor.",
                3 => "Write down your feelings privately — it helps organize thoughts before seeking support.",
                4 => "Save one wellbeing support contact on your phone for future ease.",
            ],
        ];

        // Attach suggestion text to report
        foreach ($report as &$item) {
            $priority = $item['suggestion_priority'];
            $area = $item['area'];
            $item['suggestion'] = $suggestionsText[$area][$priority] ?? null;
        }

        // Sort suggestions by priority then area order
        $areaOrder = ['depression', 'stress', 'social_isolation', 'disengagement', 'openness'];
        usort(
            $report,
            fn($a, $b) =>
            $a['suggestion_priority'] <=> $b['suggestion_priority'] ?: array_search($a['area'], $areaOrder) - array_search($b['area'], $areaOrder)
        );

        // Journal-based risk data (from NLP analysis of journal entries)
        $journalSummary = WeeklySummary::where('user_id', $userId)
            ->orderByDesc('week_start')
            ->first();

        $journalRisk = null;
        if ($journalSummary) {
            $journalRisk = [
                'week_index'       => $journalSummary->week_index,
                'lri_score'        => round($journalSummary->lri_score, 2),
                'risk_level'       => $journalSummary->risk_level,
                'risk_color'       => $journalSummary->risk_color,
                'risk_message'     => $journalSummary->risk_message,
                'escalation_flag'  => $journalSummary->escalation_flag,
                'stress_score'     => round($journalSummary->stress_score, 4),
                'sentiment_score'  => round($journalSummary->sentiment_score, 4),
                'pronoun_ratio'    => round($journalSummary->pronoun_ratio, 4),
                'absolutist_score' => round($journalSummary->absolutist_score, 4),
                'withdrawal_score' => round($journalSummary->withdrawal_score, 4),
                'week_start'       => $journalSummary->week_start->format('M d, Y'),
                'week_end'         => $journalSummary->week_end->format('M d, Y'),
                'summary_text'     => $journalSummary->summary_text,
            ];
        }

        // Last 6 weeks LRI trend for chart
        $journalTrend = WeeklySummary::where('user_id', $userId)
            ->orderByDesc('week_start')
            ->take(6)
            ->get()
            ->sortBy('week_start')
            ->values()
            ->map(fn($s, $i) => [
                'label'      => 'Week #' . ($s->week_index ?? ($i + 1)),
                'lri_score'  => round($s->lri_score, 2),
                'risk_level' => $s->risk_level,
            ])->toArray();

        return view('dashboard-poornima', compact(
            'report',
            'checkins_count',
            'current_mood',
            'mood_change',
            'peer_connections',
            'support_level',
            'survey_count',
            'journalRisk',
            'journalTrend'
        ));
    }

    public function riskLevel(Request $request)
    {
        $userId = Auth::id();

        $survey_count = WeeklyChecking::where('user_id',$userId)->count();
        $weeklyScores = WeeklyChecking::getLast4WeeksScores($userId);
        $weightedScores = WeeklyChecking::calculateWeightedScores($weeklyScores);
        $trends = WeeklyChecking::detectTrend($weeklyScores);

        $report = [];
        foreach ($weightedScores as $area => $score) {
            $risk = WeeklyChecking::determineRisk($area, $score);
            $trend = $trends[$area];
            $priority = WeeklyChecking::getSuggestionPriority($risk, $trend);

            $report[] = [
                'area' => $area,
                'weekly_scores' => $weeklyScores[$area],
                'weighted_score' => $score,
                'risk_level' => $risk,
                'trend' => $trend,
                'suggestion_priority' => $priority,
            ];
        }

        // Sort by priority then area order
        $order = ['depression', 'stress', 'social_isolation', 'disengagement', 'openness'];
        usort(
            $report,
            fn($a, $b) =>
            $a['suggestion_priority'] <=> $b['suggestion_priority'] ?: array_search($a['area'], $order) - array_search($b['area'], $order)
        );

        // Quick stats
        $checkins_count = WeeklyChecking::where('user_id', $userId)->count();
        $lastWeekly = WeeklyChecking::where('user_id', $userId)->latest()->first();
        $previousWeekly = WeeklyChecking::where('user_id', $userId)->latest()->skip(1)->first();

        $current_mood = $lastWeekly->overall_mood ?? 0;
        $last_week_mood = $previousWeekly->mood ?? $current_mood;
        $mood_change = $current_mood - $last_week_mood;

        $peer_connections = PeerRequest::where('sender_id', $userId)->where('status','accepted')->count() + PeerRequest::where('receiver_id', $userId)->where('status','accepted')->count(); // example
        $support_level = 'Good'; // example

        // --- SUGGESTIONS ---
        $suggestionsText = [
            'depression' => [
                1 => "Reach out to the wellbeing center or a counselor for emotional support.",
                2 => "Talk to a friend or classmate you trust about how you’re feeling.",
                3 => "Take a short break or go for a short walk to refresh your mood.",
                4 => "Do one small activity you enjoy today (music, drawing, relaxing).",
            ],
            'stress' => [
                1 => "Try a 2–3 minute breathing exercise to calm down quickly.",
                2 => "Break your workload into smaller tasks and handle them one by one.",
                3 => "Take short breaks between study sessions to avoid build-up.",
                4 => "Plan your week with a simple checklist to reduce pressure.",
            ],
            'social_isolation' => [
                1 => "Join at least one simple, low-pressure campus activity this week.",
                2 => "Send a small friendly message to a classmate to start a conversation.",
                3 => "Sit with a familiar person before/after class to build connection slowly.",
                4 => "Attend one group event this month to stay socially active.",
            ],
            'disengagement' => [
                1 => "Talk to a lecturer or mentor about your workload or challenges.",
                2 => "Start with one small task today — completing one thing boosts momentum.",
                3 => "Review your weekly study goals and adjust them to stay consistent.",
                4 => "Try a 25-minute focused study session to build energy.",
            ],
            'openness' => [
                1 => "You don’t have to handle everything alone — reach out to someone you trust or a counselor.",
                2 => "If reaching out feels hard, start with a small step like messaging a peer or mentor.",
                3 => "Write down your feelings privately — it helps organize thoughts before seeking support.",
                4 => "Save one wellbeing support contact on your phone for future ease.",
            ],
        ];

        // Attach suggestion text to report
        foreach ($report as &$item) {
            $priority = $item['suggestion_priority'];
            $area = $item['area'];
            $item['suggestion'] = $suggestionsText[$area][$priority] ?? null;
        }

        // Sort suggestions by priority then area order
        $areaOrder = ['depression', 'stress', 'social_isolation', 'disengagement', 'openness'];
        usort(
            $report,
            fn($a, $b) =>
            $a['suggestion_priority'] <=> $b['suggestion_priority'] ?: array_search($a['area'], $areaOrder) - array_search($b['area'], $areaOrder)
        );

        return view('dashboard.risk-level', compact(
            'report',
            'survey_count'
        ));
    }

    public function suggestions(Request $request)
    {
        $userId = Auth::id();

        $survey_count = WeeklyChecking::where('user_id',$userId)->count();
        $weeklyScores = WeeklyChecking::getLast4WeeksScores($userId);
        $weightedScores = WeeklyChecking::calculateWeightedScores($weeklyScores);
        $trends = WeeklyChecking::detectTrend($weeklyScores);

        $report = [];
        foreach ($weightedScores as $area => $score) {
            $risk = WeeklyChecking::determineRisk($area, $score);
            $trend = $trends[$area];
            $priority = WeeklyChecking::getSuggestionPriority($risk, $trend);

            $report[] = [
                'area' => $area,
                'weekly_scores' => $weeklyScores[$area],
                'weighted_score' => $score,
                'risk_level' => $risk,
                'trend' => $trend,
                'suggestion_priority' => $priority,
            ];
        }

        // Sort by priority then area order
        $order = ['depression', 'stress', 'social_isolation', 'disengagement', 'openness'];
        usort(
            $report,
            fn($a, $b) =>
            $a['suggestion_priority'] <=> $b['suggestion_priority'] ?: array_search($a['area'], $order) - array_search($b['area'], $order)
        );

        // Quick stats
        $checkins_count = WeeklyChecking::where('user_id', $userId)->count();
        $lastWeekly = WeeklyChecking::where('user_id', $userId)->latest()->first();
        $previousWeekly = WeeklyChecking::where('user_id', $userId)->latest()->skip(1)->first();

        $current_mood = $lastWeekly->overall_mood ?? 0;
        $last_week_mood = $previousWeekly->mood ?? $current_mood;
        $mood_change = $current_mood - $last_week_mood;

        $peer_connections = PeerRequest::where('sender_id', $userId)->where('status','accepted')->count() + PeerRequest::where('receiver_id', $userId)->where('status','accepted')->count(); // example
        $support_level = 'Good'; // example

        // --- SUGGESTIONS ---
        $suggestionsText = [
            'depression' => [
                1 => "Reach out to the wellbeing center or a counselor for emotional support.",
                2 => "Talk to a friend or classmate you trust about how you’re feeling.",
                3 => "Take a short break or go for a short walk to refresh your mood.",
                4 => "Do one small activity you enjoy today (music, drawing, relaxing).",
            ],
            'stress' => [
                1 => "Try a 2–3 minute breathing exercise to calm down quickly.",
                2 => "Break your workload into smaller tasks and handle them one by one.",
                3 => "Take short breaks between study sessions to avoid build-up.",
                4 => "Plan your week with a simple checklist to reduce pressure.",
            ],
            'social_isolation' => [
                1 => "Join at least one simple, low-pressure campus activity this week.",
                2 => "Send a small friendly message to a classmate to start a conversation.",
                3 => "Sit with a familiar person before/after class to build connection slowly.",
                4 => "Attend one group event this month to stay socially active.",
            ],
            'disengagement' => [
                1 => "Talk to a lecturer or mentor about your workload or challenges.",
                2 => "Start with one small task today — completing one thing boosts momentum.",
                3 => "Review your weekly study goals and adjust them to stay consistent.",
                4 => "Try a 25-minute focused study session to build energy.",
            ],
            'openness' => [
                1 => "You don’t have to handle everything alone — reach out to someone you trust or a counselor.",
                2 => "If reaching out feels hard, start with a small step like messaging a peer or mentor.",
                3 => "Write down your feelings privately — it helps organize thoughts before seeking support.",
                4 => "Save one wellbeing support contact on your phone for future ease.",
            ],
        ];

        // Attach suggestion text to report
        foreach ($report as &$item) {
            $priority = $item['suggestion_priority'];
            $area = $item['area'];
            $item['suggestion'] = $suggestionsText[$area][$priority] ?? null;
        }

        // Sort suggestions by priority then area order
        $areaOrder = ['depression', 'stress', 'social_isolation', 'disengagement', 'openness'];
        usort(
            $report,
            fn($a, $b) =>
            $a['suggestion_priority'] <=> $b['suggestion_priority'] ?: array_search($a['area'], $areaOrder) - array_search($b['area'], $areaOrder)
        );

        return view('dashboard.suggestions', compact(
            'report',
            'report',
            'checkins_count',
            'current_mood',
            'mood_change',
            'peer_connections',
            'support_level',
            'survey_count'
        ));
    }

    public function survey(Request $request)
    {
        return view('survey');
    }

    public function surveyStore(Request $request)
    {
        $request->validate([
            'overall_mood'          => 'required|integer|min:1|max:5',
            'felt_supported'        => 'required|integer|min:1|max:5',
            'emotion_description'   => 'nullable|string|max:1000',
            'trouble_sleeping'      => 'required|integer|min:1|max:5',
            'hard_to_focus'         => 'required|integer|min:1|max:5',
            'open_to_counselor'     => 'required|integer|min:1|max:5',
            'know_access_support'   => 'required|integer|min:1|max:5',
            'feeling_tense'         => 'required|integer|min:1|max:5',
            'worrying'              => 'required|integer|min:1|max:5',
            'interact_peers'        => 'required|integer|min:1|max:5',
            'keep_up_workload'      => 'required|integer|min:1|max:5',
            'group_activities'      => 'nullable|array',
            'academic_challenges'   => 'nullable|array',
            'feel_left_out'         => 'required|integer|min:1|max:5',
            'no_one_to_talk'        => 'required|integer|min:1|max:5',
            'no_energy'             => 'required|integer|min:1|max:5',
            'little_pleasure'       => 'required|integer|min:1|max:5',
            'feeling_down'          => 'required|integer|min:1|max:5',
            'emotionally_drained'   => 'required|integer|min:1|max:5',
            'going_through_motions' => 'required|integer|min:1|max:5',
        ]);

        DB::beginTransaction();

        try {

            WeeklyChecking::create([
                'user_id'               => Auth::id(),
                'overall_mood'          => $request->overall_mood,
                'felt_supported'        => $request->felt_supported,
                'emotion_description'   => $request->emotion_description,
                'trouble_sleeping'      => $request->trouble_sleeping,
                'hard_to_focus'         => $request->hard_to_focus,
                'open_to_counselor'     => $request->open_to_counselor,
                'know_access_support'   => $request->know_access_support,
                'feeling_tense'         => $request->feeling_tense,
                'worrying'              => $request->worrying,
                'interact_peers'        => $request->interact_peers,
                'keep_up_workload'      => $request->keep_up_workload,
                'group_activities'      => $request->group_activities ? json_encode($request->group_activities) : null,
                'academic_challenges'   => $request->academic_challenges ? json_encode($request->academic_challenges) : null,
                'feel_left_out'         => $request->feel_left_out,
                'no_one_to_talk'        => $request->no_one_to_talk,
                'no_energy'             => $request->no_energy,
                'little_pleasure'       => $request->little_pleasure,
                'feeling_down'          => $request->feeling_down,
                'emotionally_drained'   => $request->emotionally_drained,
                'going_through_motions' => $request->going_through_motions,
            ]);

            DB::commit();

            return redirect()->route('survey-success')->with('success', 'Your weekly check-in has been saved!');
        } catch (\Exception $e) {

            DB::rollBack();
            return back()->withErrors([
                'error' => 'Something went wrong. Please try again.'
            ]);
        }
    }

    public function surveySuccess(Request $request)
    {
        return view('survey-success');
    }

    public function weeklyCheckings(Request $request)
    {
        $query = WeeklyChecking::where('user_id', Auth::id());

        if ($request->filled('week')) {
            $query->whereRaw('WEEK(created_at, 1) = ?', [$request->week]); // WEEK(date,1) for ISO week
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $from = Carbon::parse($request->from_date)->startOfDay();
            $to = Carbon::parse($request->to_date)->endOfDay();

            $query->whereBetween('created_at', [$from, $to]);
        }

        $checkings = $query->orderBy('id', 'desc')->get();

        return view('dashboard.weekly-checkings', compact('checkings'));
    }

    public function weeklyCheckingsView(Request $request)
    {
        $checking = WeeklyChecking::find($request->id);
        return view('dashboard.weekly-checking-view', compact('checking'));
    }

    public function profileView($id)
    {
        $user = User::with('profile')->findOrFail($id);

        return view('dashboard.view-profile', compact('user'));
    }

    public function peerMatchings(Request $request)
    {
        $user      = Auth::user();
        $myProfile = $user->profile;

        if (!$myProfile) {
            return back()->with('error', 'Please complete your profile first.');
        }

        // Fetch all other profiles
        $profiles = StudentProfile::where('user_id', '!=', $user->id)->get();

        // Pre-load latest weekly check-in for every involved user in ONE query
        // to avoid N+1 inside the loop.
        $allUserIds = $profiles->pluck('user_id')->push($user->id)->unique()->values();

        $latestCheckins = WeeklyChecking::whereIn('user_id', $allUserIds)
            ->select('user_id', 'overall_mood', 'feel_left_out', 'created_at')
            ->orderByDesc('created_at')
            ->get()
            ->unique('user_id')          // keep only the most-recent row per user
            ->keyBy('user_id');          // index by user_id for O(1) lookup

        $myCheckin = $latestCheckins->get($user->id);

        $matches = [];

        foreach ($profiles as $profile) {
            $score = 0.0;

            // ── 1. Interest & Hobby Match (25%) ──────────────────────────────
            $mine_interests  = is_array($myProfile->top_interests)
                ? $myProfile->top_interests
                : (json_decode($myProfile->top_interests, true) ?: []);
            $other_interests = is_array($profile->top_interests)
                ? $profile->top_interests
                : (json_decode($profile->top_interests, true) ?: []);

            $commonInterests = count(array_intersect($mine_interests, $other_interests));
            $maxInterests    = max(count($mine_interests), 1);
            $score += ($commonInterests / $maxInterests) * 15; // proportional, max 15%

            if ($profile->learning_style === $myProfile->learning_style) {
                $score += 10; // learning style = 10%
            }

            // ── 2. Academic Compatibility (20%) ──────────────────────────────
            if ($profile->faculty === $myProfile->faculty) {
                $score += 10;
            }
            if ($profile->al_stream === $myProfile->al_stream) {
                $score += 10;
            }

            // ── 3. Personality Compatibility (20%) ───────────────────────────
            if ((int) $profile->intro_extro === (int) $myProfile->intro_extro) {
                $score += 10; // introvert-extrovert scale exact match
            }
            if ($profile->stress_level === $myProfile->stress_level) {
                $score += 10;
            }

            // ── 4. Emotional & Wellbeing Alignment (15%) ─────────────────────
            // Onboard form – overwhelmed easily by academic tasks (5%)
            if ((int) $profile->overwhelmed === (int) $myProfile->overwhelmed) {
                $score += 5;
            }

            // Latest weekly check-in (10%)
            $otherCheckin = $latestCheckins->get($profile->user_id);
            if ($myCheckin && $otherCheckin) {
                // Overall weekly mood – first question (5%)
                $scoret = $score;
                if ((int) $myCheckin->overall_mood <= (int) $otherCheckin->overall_mood) {
                    $score += 5;
                }
               
                //dd($myCheckin->overall_mood, $otherCheckin->overall_mood);
                // Feel left out / disconnected – Social & Academic Behavior (5%)
                if ((int) $myCheckin->feel_left_out === (int) $otherCheckin->feel_left_out) {
                    $score += 5;
                }
            }

            // ── 5. Communication Preference (10%) ────────────────────────────
            $mine_comms  = is_array($myProfile->communication_methods)
                ? $myProfile->communication_methods
                : (json_decode($myProfile->communication_methods, true) ?: []);
            $other_comms = is_array($profile->communication_methods)
                ? $profile->communication_methods
                : (json_decode($profile->communication_methods, true) ?: []);

            if (count(array_intersect($mine_comms, $other_comms)) > 0) {
                $score += 10;
            }

            // ── 6. Social Preferences (10%) ──────────────────────────────────
            if ($profile->social_setting === $myProfile->social_setting) {
                $score += 10;
            }

            // Score is already out of 100
            $percentage = (int) round($score);

            $matches[] = [
                'user'        => $profile->user,
                'profile'     => $profile,
                'percentage'  => $percentage,
                'my_rating'   => $profile->user->myRating(),
                'isPeeredWith' => $profile->user->isPeeredWith(),
            ];
        }

        usort($matches, function ($a, $b) {
            if ($a['my_rating'] !== $b['my_rating']) {
                return $b['my_rating'] <=> $a['my_rating'];
            }
            return $b['percentage'] <=> $a['percentage'];
        });

        return view('dashboard.peer-matching', compact('matches'));
    }

    public function myConnections()
    {
        $userId = auth()->id();

        // Get accepted connections (as sender or receiver)
        $connections = PeerRequest::where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)
                ->orWhere('receiver_id', $userId);
        })
        ->where('status', 'accepted')
        ->get();

        $formatted = [];

        foreach ($connections as $conn) {
            // Identify "other" user
            $otherUser = $conn->sender_id == $userId
                ? $conn->receiver
                : $conn->sender;

            $interests = [];
            if ($otherUser->profile && $otherUser->profile->top_interests) {
                $raw = $otherUser->profile->top_interests;
                $interests = is_array($raw) ? $raw : (json_decode($raw, true) ?: []);
            }

            $formatted[] = [
                'user' => $otherUser,
                'profile' => $otherUser->profile,
                'rating' => $otherUser->myRating(),
                'stress_level' => $otherUser->profile->stress_level ?? null,
                'interests' => $interests,
            ];
        }

        return view('dashboard.peer-connections', [
            'connections' => $formatted
        ]);
    }


    public function sendRequest($receiverId)
    {
        $senderId = Auth::id();

        if (PeerRequest::where('sender_id', $senderId)->where('receiver_id', $receiverId)->exists()) {
            return back();
        }

        PeerRequest::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId
        ]);

        return back()->with('success', 'Request sent!');
    }

    public function acceptRequest($requestId)
    {
        $req = PeerRequest::findOrFail($requestId);

        $req->update(['status' => 'accepted']);

        Chat::create([
            'user1_id' => $req->sender_id,
            'user2_id'  => $req->receiver_id
        ]);

        return back()->with('success', 'Request accepted!');
    }

    public function rejectRequest($requestId)
    {
        $req = PeerRequest::findOrFail($requestId);

        $req->update(['status' => 'rejected']);

        return back()->with('success', 'Request rejected!');
    }

    public function cancelRequest($requestId)
    {
        $req = PeerRequest::where('id', $requestId)
                          ->where('sender_id', Auth::id())
                          ->where('status', 'pending')
                          ->firstOrFail();

        $req->delete();

        return back()->with('success', 'Request cancelled.');
    }

    public function chat()
    {

        $userId = Auth::id();
        $chats = Chat::where('user1_id', $userId)
            ->orWhere('user2_id', $userId)
            ->with(['user1', 'user2'])
            ->get();

        if ($chats->count() <= 0) {
            return redirect()->back();
        }

        $supabaseUrl = env('SUPABASE_URL');
        $supabaseKey = env('SUPABASE_ANON_KEY');

        return view('dashboard.chats', compact('chats', 'supabaseUrl', 'supabaseKey'));
    }

    public function viewRequests()
    {
        $requests = PeerRequest::where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->with('sender')
            ->get();


        return view('dashboard.incoming', compact('requests'));
    }

    public function validateChatAccess($chatId)
    {
        $userId = Auth::id();
        $chat = Chat::where('id', $chatId)
            ->where(function ($query) use ($userId) {
                $query->where('user1_id', $userId)
                    ->orWhere('user2_id', $userId);
            })
            ->first();

        if (!$chat) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'authorized' => true,
            'chat_id' => $chat->id,
            'user_id' => $userId
        ]);
    }

    public function peerRating(Request $request, $to_id)
    {

        $rating = PeerRating::updateOrCreate(
            [
                'from_id' => Auth::id(),  // condition
                'to_id'   => $to_id
            ],
            [
                'rating' => $request->rating // update/create data
            ]
        );

        return redirect()->back();
    }


}
