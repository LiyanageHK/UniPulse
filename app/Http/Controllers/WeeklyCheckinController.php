<?php

namespace App\Http\Controllers;

use App\Models\WeeklyCheckin;
use App\Models\KpiSnapshot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\WeeklyChecking;
//use Auth;

class WeeklyCheckinController extends Controller
{
    public function showForm()
{
    $user = Auth::user();
    $now = Carbon::now();

    // 1. Skip weekly check-in during the first 7 days after onboarding
    $onboardingDate = Carbon::parse($user->created_at);

    if ($onboardingDate->diffInDays($now) < 7) {
        return redirect()->route('dashboard');
    }

    // 2. Enforce rolling 7-day rule after onboarding week
    $lastCheckin = WeeklyCheckin::where('user_id', $user->id)
        ->orderBy('week_start', 'desc')
        ->first();

    if ($lastCheckin && Carbon::parse($lastCheckin->week_start)->gte($now->subDays(7))) {
        return redirect()->route('dashboard')
            ->with('info', 'You have already submitted your weekly check-in recently.');
    }

    return view('weekly_checkin.form');
}

    public function submitForm(Request $request)
    {
        // Store as Y-m-d string to match DB's date column and avoid timezone precision issues
        $weekStart = Carbon::now()->startOfWeek()->toDateString();

        $data = $request->validate([
            'mood' => 'required|integer|min:1|max:5',
            'tense' => 'required|integer|min:1|max:5',
            'overwhelmed' => 'required|integer|min:1|max:5',
            'worry' => 'required|integer|min:1|max:5',
            'sleep_trouble' => 'required|integer|min:1|max:5',
            'openness_to_mentor' => 'required|integer|min:1|max:5',
            'knowledge_of_support' => 'required|integer|min:1|max:5',
            'peer_connection' => 'required|integer|min:1|max:5',
            'peer_interaction' => 'required|integer|min:1|max:5',
            'group_participation' => 'nullable|string',
            'feel_left_out' => 'required|integer|min:1|max:5',
            'no_one_to_talk' => 'required|integer|min:1|max:5',
            'university_belonging' => 'required|integer|min:1|max:5',
            'meaningful_connections' => 'required|integer|min:1|max:5',
            'studies_interesting' => 'required|integer|min:1|max:5',
            'academic_confidence' => 'required|integer|min:1|max:5',
            'workload_management' => 'required|integer|min:1|max:5',
            'no_energy' => 'required|integer|min:1|max:5',
            'low_pleasure' => 'required|integer|min:1|max:5',
            'feeling_down' => 'required|integer|min:1|max:5',
            'emotionally_drained' => 'required|integer|min:1|max:5',
            'hard_to_stay_focused' => 'required|integer|min:1|max:5',
            'just_through_motions' => 'required|integer|min:1|max:5'
        ]);

        $data['user_id'] = Auth::id();
        $data['week_start'] = $weekStart;

        WeeklyCheckin::create($data);

         WeeklyChecking::create([
                'user_id'               => Auth::id(),
                'overall_mood'          => $request->mood,
                'felt_supported'        => $request->university_belonging,
                'emotion_description'   => "",
                'trouble_sleeping'      => $request->sleep_trouble,
                'hard_to_focus'         => $request->hard_to_stay_focused,
                'open_to_counselor'     => $request->openness_to_mentor,
                'know_access_support'   => $request->knowledge_of_support,
                'feeling_tense'         => $request->tense,
                'worrying'              => $request->worry,
                'interact_peers'        => $request->peer_connection,
                'keep_up_workload'      => $request->workload_management,
                'group_activities'      => $request->group_participation ? json_encode($request->group_participation) : null,
                'academic_challenges'   => $request->academic_confidence ? json_encode($request->academic_confidence) : null,
                'feel_left_out'         => $request->feel_left_out,
                'no_one_to_talk'        => $request->no_one_to_talk,
                'no_energy'             => $request->no_energy,
                'little_pleasure'       => $request->low_pleasure,
                'feeling_down'          => $request->feeling_down,
                'emotionally_drained'   => $request->emotionally_drained,
                'going_through_motions' => $request->just_through_motions,
            ]);

        $numericStress = round((
            ($data['tense'] + $data['worry'] + $data['sleep_trouble']) / 3
        ));

        $peerStruggle = round(
            ($data['feel_left_out'] + $data['no_one_to_talk']) / 2
        );

        // Map numeric stress score to label
if ($numericStress >= 4.0) {
    $stressLabel = 'High';
} elseif ($numericStress >= 2.5) {
    $stressLabel = 'Moderate';
} else {
    $stressLabel = 'Low';
}

        // 3️ Update USER profile (latest state) — note: transition_confidence is onboarding-owned and should not be modified here
        Auth::user()->update([
            'stress_level' => $stressLabel,
            'overwhelm_level' => $data['overwhelmed'],
            'peer_struggle' => $peerStruggle,
            'group_work_comfort' => $data['peer_interaction'],
            'last_checkin_at' => now(),
        ]);


        //  KPI creation handled by Dashboard controller

        return redirect()->route('dashboard')
            ->with('success', 'Weekly check-in submitted successfully.');
    }
}
