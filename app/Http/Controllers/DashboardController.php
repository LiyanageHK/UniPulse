<?php

namespace App\Http\Controllers;

use App\Models\WeeklyCheckin;
use App\Models\KpiSnapshot;
use App\Services\AiRecommender;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Normalize to Y-m-d for consistent date comparisons
        $weekStart = Carbon::now()->startOfWeek()->toDateString();

        // ✅ STEP 1 — Redirect if onboarding not completed
        if (!$user->onboarding_completed) {
            return redirect()->route('onboarding.step1');
        }

        // ✅ STEP 2 — Require a weekly check-in: if last check-in is older than 7 days,
        // redirect the user to complete a new one (rolling 7-day policy).
        $last = WeeklyCheckin::where('user_id', $user->id)
            ->orderBy('week_start', 'desc')
            ->first();

        if (!$last || Carbon::parse($last->week_start)->lt(Carbon::now()->subDays(7))) {
            return redirect()->route('weekly.checkin')
                ->with('info', 'Please complete a weekly check-in.');
        }

        // Use the most recent checkin for KPI calculations
        $checkin = $last;

        // ✅ STEP 3 — Calculate KPI for current week from the weekly checkin
        $kpiData = $this->calculateKPIsFromCheckin($checkin);

        // ✅ STEP 4 — Save current week snapshot
        KpiSnapshot::updateOrCreate(
            [
                'user_id' => $user->id,
                'week_start' => $weekStart,
            ],
            [
                'motivation_kpi' => $kpiData['motivationScore'],
                'social_kpi' => $kpiData['socialScore'],
                'emotional_kpi' => $kpiData['emotionalScore'],
            ]
        );

        // ✅ STEP 5 — Ensure first KPI snapshot exists for history
        $hasAnySnapshots = KpiSnapshot::where('user_id', $user->id)->exists();
        if (!$hasAnySnapshots) {
            KpiSnapshot::create([
                'user_id' => $user->id,
                'week_start' => $weekStart,
                'motivation_kpi' => $kpiData['motivationScore'],
                'social_kpi' => $kpiData['socialScore'],
                'emotional_kpi' => $kpiData['emotionalScore']
            ]);
        }

        // ✅ STEP 6 — Load KPI history
        $kpiHistory = KpiSnapshot::where('user_id', $user->id)
            ->orderBy('week_start', 'asc')
            ->get();

        // ✅ STEP 7 — Generate AI recommendation
        $aiRecommender = new AiRecommender();

        // Always prioritize risk detection if emotional KPI is very low (<= 2.0 per spec)
        if ($kpiData['emotionalScore'] <= 2.0) {
            $recommendation = [
                'type' => 'risk_detection',
                'component' => 'risk_detection',
                'link' => '#' // temporary link
            ];
        } else {
            $recommendation = $aiRecommender->recommend(
                $kpiData['motivationScore'],
                $kpiData['socialScore'],
                $kpiData['emotionalScore']
            );
        }

        return view('dashboard', [
            'user' => $user,
            'motivationScore' => $kpiData['motivationScore'],
            'socialScore' => $kpiData['socialScore'],
            'emotionalScore' => $kpiData['emotionalScore'],
            'motivationInterpretation' => $kpiData['motivationInterpretation'],
            'socialInterpretation' => $kpiData['socialInterpretation'],
            'emotionalInterpretation' => $kpiData['emotionalInterpretation'],
            'kpiHistory' => $kpiHistory,
            'aiRecommendation' => $recommendation
        ]);
    }

    // =============================================================
    private function calculateKPIsFromCheckin($checkin)
    {
        $motivationScore = round((
            $checkin->studies_interesting +
            $checkin->academic_confidence +
            $checkin->workload_management +
            (6 - $checkin->no_energy) +
            (6 - $checkin->hard_to_stay_focused)
        ) / 5, 2);

        $socialScore = round((
            $checkin->peer_connection +
            $checkin->peer_interaction +
            $checkin->university_belonging +
            $checkin->meaningful_connections +
            (6 - $checkin->feel_left_out) +
            (6 - $checkin->no_one_to_talk)
        ) / 6, 2);

        // Emotional score per spec: 11 components
        $emotionalScore = round((
            $checkin->mood +
            (6 - $checkin->tense) +
            (6 - $checkin->overwhelmed) +
            (6 - $checkin->worry) +
            (6 - $checkin->sleep_trouble) +
            $checkin->openness_to_mentor +
            $checkin->knowledge_of_support +
            (6 - $checkin->low_pleasure) +
            (6 - $checkin->feeling_down) +
            (6 - $checkin->emotionally_drained) +
            (6 - $checkin->just_through_motions)
        ) / 11, 2);

        return [
            'motivationScore' => $motivationScore,
            'socialScore' => $socialScore,
            'emotionalScore' => $emotionalScore,
            'motivationInterpretation' => $this->interpretMotivation($motivationScore),
            'socialInterpretation' => $this->interpretSocial($socialScore),
            'emotionalInterpretation' => $this->interpretEmotional($emotionalScore)
        ];
    }

    // private function calculateKPIsFromOnboarding($user)
    // {
    //     $goalClarity = $user->goal_clarity ?? 3;
    //     $transitionConfidence = $user->transition_confidence ?? 3;
    //
    //     $motivatorScores = [
    //         'Academic growth' => 5,
    //         'Career opportunities' => 4,
    //         'Experiences and exposure' => 3,
    //         'Friends and connections' => 2,
    //     ];
    //    $primaryMotivatorScore = $motivatorScores[$user->primary_motivator] ?? 3;

    //    $alResults = is_array($user->al_results) ? $user->al_results : json_decode($user->al_results ?? '[]', true);
    //    $gradeScores = ['A'=>5,'B'=>4,'C'=>3,'S'=>2,'F'=>1];
    //        $grades = array_map(fn($row) => $gradeScores[$row['grade']] ?? 3, array_values($alResults ?? []));
    //        rsort($grades);
    //        $academicPerformance = count($grades) >= 3 ? array_sum(array_slice($grades, 0, 3)) / 3 : (count($grades) ? array_sum($grades)/count($grades) : 3);

    //    $employmentAdjustment = $user->is_employed ? -0.5 : 0;

    /*    $motivationScore = round(($goalClarity + $transitionConfidence + $academicPerformance + $primaryMotivatorScore + $employmentAdjustment) / 4, 2);

        $socialScores = ['Large Groups'=>5,'Small Groups'=>4,'1-on-1'=>3,'Online-only'=>1];
        $socialPreferenceScore = $socialScores[$user->social_preference] ?? 3;
        $introvertScore = (($user->introvert_extrovert_scale ?? 5)/10)*5;
        $groupComfortScore = $user->group_work_comfort ?? 3;
        $livingScores = ['Hostel'=>5,'Boarding'=>4,'Home'=>3,'Other'=>2];
        $livingArrangementScore = $livingScores[$user->living_arrangement] ?? 2;
        $communicationScore = max(1, min(count($user->preferred_support_types ?? []), 5));

        $socialScore = round(($socialPreferenceScore+$groupComfortScore+$communicationScore+$livingArrangementScore+$introvertScore)/5, 2);

        $stressScore = ['Low'=>5,'Moderate'=>3,'High'=>1][$user->stress_level] ?? 3;
        $overwhelmScore = 6 - ($user->overwhelm_level ?? 3);
        $peerStruggleScore = 6 - ($user->peer_struggle ?? 3);

        $emotionalScore = round(($stressScore + $overwhelmScore + $peerStruggleScore + $goalClarity) / 4, 2);

        return [
            'motivationScore' => $motivationScore,
            'socialScore' => $socialScore,
            'emotionalScore' => $emotionalScore,
            'motivationInterpretation' => $this->interpretMotivation($motivationScore),
            'socialInterpretation' => $this->interpretSocial($socialScore),
            'emotionalInterpretation' => $this->interpretEmotional($emotionalScore)
        ];
    } */

    private function interpretMotivation($score)
    {
        return $score >= 4 ? 'High' : ($score >= 2.5 ? 'Moderate' : 'Low');
    }

    private function interpretSocial($score)
    {
        return $score >= 4 ? 'Integrated' : ($score >= 2.5 ? 'Moderate' : 'Isolated');
    }

    private function interpretEmotional($score)
    {
        return $score >= 4 ? 'Stable' : ($score >= 2.5 ? 'Moderate' : 'At-risk');
    }
}
