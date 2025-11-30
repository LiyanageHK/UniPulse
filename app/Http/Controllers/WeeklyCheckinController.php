<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WeeklyCheckin;
use App\Models\KpiSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class WeeklyCheckinController extends Controller
{
    public function showForm()
    {
        $user = Auth::user();
        $weekStart = Carbon::now()->startOfWeek();

        $existing = WeeklyCheckin::where('user_id', $user->id)
                    ->where('week_start', $weekStart)
                    ->first();

        if ($existing) {
            return redirect()->route('dashboard')->with('info', 'You have already submitted this week\'s check-in.');
        }

        return view('weekly_checkin.form');
    }

    public function submitForm(Request $request)
    {
        $user = Auth::user();
        $weekStart = Carbon::now()->startOfWeek();

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
            'just_through_motions' => 'required|integer|min:1|max:5',
        ]);

        $data['user_id'] = $user->id;
        $data['week_start'] = $weekStart;

        $checkin = WeeklyCheckin::create($data);

        // ===== Calculate KPIs from check-in data =====
        $motivation = (
            $data['studies_interesting'] + 
            $data['academic_confidence'] + 
            $data['workload_management'] +
            (6 - $data['no_energy']) +
            (6 - $data['hard_to_stay_focused'])
        ) / 5;

        $social = (
            $data['peer_connection'] +
            $data['peer_interaction'] +
            $data['university_belonging'] +
            $data['meaningful_connections'] +
            (6 - $data['feel_left_out']) +
            (6 - $data['no_one_to_talk'])
        ) / 6;

        $emotional = (
            $data['mood'] + 
            (6 - $data['tense']) + 
            (6 - $data['overwhelmed']) + 
            (6 - $data['worry']) +
            (6 - $data['feeling_down']) +
            $data['openness_to_mentor']
        ) / 6;

        // ===== CREATE NEW SNAPSHOT (don't update existing) =====
        // Check if snapshot already exists for this week
        $existingSnapshot = KpiSnapshot::where('user_id', $user->id)
            ->where('week_start', $weekStart)
            ->first();

        if ($existingSnapshot) {
            // If snapshot exists for this week, DELETE it and create new one
            $existingSnapshot->delete();
        }

        // Create NEW snapshot for this week
        KpiSnapshot::create([
            'user_id' => $user->id,
            'week_start' => $weekStart,
            'motivation_kpi' => $motivation,
            'social_kpi' => $social,
            'emotional_kpi' => $emotional
        ]);

        return redirect()->route('dashboard')->with('success', 'Weekly check-in submitted and KPIs updated.');
    }
}