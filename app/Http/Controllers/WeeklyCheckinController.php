<?php

namespace App\Http\Controllers;

use App\Models\WeeklyCheckin;
use App\Models\KpiSnapshot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeeklyCheckinController extends Controller
{
    public function showForm()
    {
        // Use a rolling 7-day rule: if the most recent check-in was within the last 7 days,
        // don't show the form again.
        $last = WeeklyCheckin::where('user_id', Auth::id())
            ->orderBy('week_start', 'desc')
            ->first();

        if ($last && Carbon::parse($last->week_start)->gte(Carbon::now()->subDays(7))) {
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

        // ✅ KPI creation handled by Dashboard controller

        return redirect()->route('dashboard')
            ->with('success', 'Weekly check-in submitted successfully.');
    }
}
