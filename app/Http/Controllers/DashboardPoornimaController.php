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

        return view('dashboard-poornima', compact(
            'report',
            'checkins_count',
            'current_mood',
            'mood_change',
            'peer_connections',
            'support_level',
            'survey_count'
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


        $user = Auth::user();
        $myProfile = $user->profile;

        if (!$myProfile) {
            return back()->with('error', 'Please complete your profile first.');
        }

        // Fetch ALL other users with profiles
        $profiles = StudentProfile::where('user_id', '!=', $user->id)->get();

        $matches = [];

        foreach ($profiles as $profile) {
            $score = 0;
            $total = 0;

            // --------- SIMPLE MATCHING ALGORITHM ----------

            // AL stream (20 points)
            $total += 20;
            if ($profile->al_stream === $myProfile->al_stream) {
                $score += 20;
            }

            // Learning style (20 points)
            $total += 20;
            if ($profile->learning_style === $myProfile->learning_style) {
                $score += 20;
            }

            // Social setting (10 points)
            $total += 10;
            if ($profile->social_setting === $myProfile->social_setting) {
                $score += 10;
            }

            // Communication preferences (10 points)
            $total += 10;
            $mine_comms = json_decode($myProfile->communication_methods, true) ?: [];
            $other_comms = json_decode($profile->communication_methods, true) ?: [];

            $common_comms = array_intersect($mine_comms, $other_comms);
            if (count($common_comms) > 0) {
                $score += 10;
            }

            // Interests (20 points)
            $total += 20;
            $mine_interests = json_decode($myProfile->top_interests, true) ?: [];
            $other_interests = json_decode($profile->top_interests, true) ?: [];

            $common_interests = array_intersect($mine_interests, $other_interests);
            if (count($common_interests) > 0) {
                $score += 20;
            }

            // Stress level closeness (10 points)
            $total += 10;
            if ($profile->stress_level === $myProfile->stress_level) {
                $score += 10;
            }

            $percentage = round(($score / $total) * 100);

            $myRating = PeerRating::where('from_id', $user->id)
                ->where('to_id', $profile->user_id)
                ->value('rating') ?? 0;

            $matches[] = [
                'user' => $profile->user,
                'profile' => $profile,
                'percentage' => $percentage,
                'my_rating' => $profile->user->myRating(),
                'isPeeredWith' => $profile->user->isPeeredWith()
            ];
        }

        usort($matches, function ($a, $b) {
            // First, prioritize by my_rating (descending)
            if ($a['my_rating'] !== $b['my_rating']) {
                return $b['my_rating'] <=> $a['my_rating'];
            }
            // Then, sort by percentage (descending)
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
                $decoded = json_decode($otherUser->profile->top_interests, true);
                $interests = is_array($decoded) ? $decoded : [];
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
