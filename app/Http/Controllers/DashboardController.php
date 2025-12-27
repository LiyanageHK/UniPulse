<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WeeklyCheckin;
use App\Models\KpiSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Redirect if onboarding not completed
        if (!$user->onboarding_completed) {
            return redirect()->route('onboarding.step1');
        }

        $weekStart = Carbon::now()->startOfWeek();
        
        // STEP 1: Check if user has ANY KPI snapshots at all
        $hasAnySnapshots = KpiSnapshot::where('user_id', $user->id)->exists();
        
        // If user has no snapshots at all, create initial one from onboarding data
        if (!$hasAnySnapshots) {
            $kpiData = $this->calculateKPIsFromOnboarding($user);
            
            KpiSnapshot::create([
                'user_id' => $user->id,
                'week_start' => $weekStart,
                'motivation_kpi' => $kpiData['motivationScore'],
                'social_kpi' => $kpiData['socialScore'],
                'emotional_kpi' => $kpiData['emotionalScore']
            ]);
        }

        // STEP 2: Check for current week data
        $checkin = WeeklyCheckin::where('user_id', $user->id)
            ->where('week_start', $weekStart)
            ->first();

        $currentSnapshot = KpiSnapshot::where('user_id', $user->id)
            ->where('week_start', $weekStart)
            ->first();

        // STEP 3: Calculate current KPIs for UI (but don't create DB records unnecessarily)
        if ($checkin) {
            // Use check-in data for UI (latest data)
            $kpiData = $this->calculateKPIsFromCheckin($checkin);
            
            // If no snapshot exists for this week, create one
            if (!$currentSnapshot) {
                KpiSnapshot::create([
                    'user_id' => $user->id,
                    'week_start' => $weekStart,
                    'motivation_kpi' => $kpiData['motivationScore'],
                    'social_kpi' => $kpiData['socialScore'],
                    'emotional_kpi' => $kpiData['emotionalScore']
                ]);
            }
        } 
        elseif ($currentSnapshot) {
            // Use existing snapshot for current week
            $kpiData = [
                'motivationScore' => $currentSnapshot->motivation_kpi,
                'socialScore' => $currentSnapshot->social_kpi,
                'emotionalScore' => $currentSnapshot->emotional_kpi,
                'motivationInterpretation' => $this->interpretMotivation($currentSnapshot->motivation_kpi),
                'socialInterpretation' => $this->interpretSocial($currentSnapshot->social_kpi),
                'emotionalInterpretation' => $this->interpretEmotional($currentSnapshot->emotional_kpi)
            ];
        }
        else {
            // No check-in and no snapshot for current week - use onboarding data for UI only
            $kpiData = $this->calculateKPIsFromOnboarding($user);
            // Don't create snapshot - we only want snapshots for actual data submissions
        }

        // STEP 4: Get ALL KPI history for charts
        $kpiHistory = KpiSnapshot::where('user_id', $user->id)
            ->orderBy('week_start', 'asc')
            ->get();

        return view('dashboard', array_merge($kpiData, [
            'user' => $user,
            'kpiHistory' => $kpiHistory
        ]));
    }

    private function calculateKPIsFromCheckin($checkin)
    {
        // Motivation KPI from check-in
        $motivationScore = round((
            $checkin->studies_interesting +
            $checkin->academic_confidence +
            $checkin->workload_management +
            (6 - $checkin->no_energy) +
            (6 - $checkin->hard_to_stay_focused)
        ) / 5, 2);

        // Social Inclusion KPI from check-in
        $socialScore = round((
            $checkin->peer_connection +
            $checkin->peer_interaction +
            $checkin->university_belonging +
            $checkin->meaningful_connections +
            (6 - $checkin->feel_left_out) +
            (6 - $checkin->no_one_to_talk)
        ) / 6, 2);

        // Emotional Status KPI from check-in
        $emotionalScore = round((
            $checkin->mood + 
            (6 - $checkin->tense) + 
            (6 - $checkin->overwhelmed) + 
            (6 - $checkin->worry) +
            (6 - $checkin->feeling_down) +
            $checkin->openness_to_mentor
        ) / 6, 2);

        return [
            'motivationScore' => $motivationScore,
            'socialScore' => $socialScore,
            'emotionalScore' => $emotionalScore,
            'motivationInterpretation' => $this->interpretMotivation($motivationScore),
            'socialInterpretation' => $this->interpretSocial($socialScore),
            'emotionalInterpretation' => $this->interpretEmotional($emotionalScore)
        ];
    }

    private function calculateKPIsFromOnboarding($user)
    {
        // Your existing onboarding KPI calculation logic
        $goalClarity = $user->goal_clarity ?? 3;
        $transitionConfidence = $user->transition_confidence ?? 3;

        $motivatorScores = [
            'Academic growth' => 5,
            'Career opportunities' => 4,
            'Experiences and exposure' => 3,
            'Friends and connections' => 2,
        ];
        $primaryMotivatorScore = $motivatorScores[$user->primary_motivator] ?? 3;

        $alResults = is_array($user->al_results) ? $user->al_results : json_decode($user->al_results ?? '[]', true);
        $gradeScores = ['A' => 5, 'B' => 4, 'C' => 3, 'S' => 2, 'F' => 1];
        $grades = array_map(fn($row) => $gradeScores[$row['grade']] ?? 0, array_values($alResults ?? []));
        rsort($grades);
        $academicPerformance = count($grades) >= 3 ? array_sum(array_slice($grades, 0, 3)) / 3 : (count($grades) > 0 ? array_sum($grades) / count($grades) : 3);

        $employmentAdjustment = $user->is_employed ? -0.5 : 0;
        $motivationScore = round(($goalClarity + $transitionConfidence + $academicPerformance + $primaryMotivatorScore + $employmentAdjustment) / 4, 2);

        // Social KPI
        $socialPreferenceScores = ['Large Groups'=>5,'Small Groups'=>4,'1-on-1'=>3,'Online-only'=>1];
        $socialPreferenceScore = $socialPreferenceScores[$user->social_preference] ?? 3;
        $introvertScore = ($user->introvert_extrovert_scale ?? 5)/10*5;
        $groupComfortScore = $user->group_work_comfort ?? 3;

        $livingArrangementScores = ['Hostel'=>5,'Boarding'=>4,'Home'=>3,'Other'=>2];
        $livingArrangementScore = $livingArrangementScores[$user->living_arrangement] ?? 2;

        $communicationVariety = count($user->preferred_support_types ?? []);
        $communicationScore = $communicationVariety>=3?5:($communicationVariety==2?4:($communicationVariety==1?3:1));

        $socialScore = round(($socialPreferenceScore+$groupComfortScore+$communicationScore+$livingArrangementScore+$introvertScore)/5, 2);

        // Emotional KPI
        $stressScore = ['Low'=>5,'Moderate'=>3,'High'=>1][$user->stress_level] ?? 3;
        $overwhelmScore = 6 - ($user->overwhelm_level ?? 3);
        $peerStruggleScore = 6 - ($user->peer_struggle ?? 3);
        $emotionalScore = round(($stressScore + $overwhelmScore + $peerStruggleScore + $goalClarity)/4, 2);

        return [
            'motivationScore' => $motivationScore,
            'socialScore' => $socialScore,
            'emotionalScore' => $emotionalScore,
            'motivationInterpretation' => $this->interpretMotivation($motivationScore),
            'socialInterpretation' => $this->interpretSocial($socialScore),
            'emotionalInterpretation' => $this->interpretEmotional($emotionalScore)
        ];
    }

    private function interpretMotivation($score)
    {
        return $score >= 4.0 ? 'High' : ($score >= 2.5 ? 'Moderate' : 'Low');
    }

    private function interpretSocial($score)
    {
        return $score >= 4.0 ? 'Integrated' : ($score >= 2.5 ? 'Moderate' : 'Isolated');
    }

    private function interpretEmotional($score)
    {
        return $score >= 4.0 ? 'Stable' : ($score >= 2.5 ? 'Moderate' : 'At-risk');
    }
}