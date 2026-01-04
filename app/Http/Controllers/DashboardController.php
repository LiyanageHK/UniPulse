<?php

namespace App\Http\Controllers;

use App\Models\WeeklyCheckin;
use App\Models\KpiSnapshot;
use App\Services\AiRecommender;
use Carbon\Carbon;
use App\Models\Conversation;
use App\Models\Feedback;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $weekStart = $now->startOfWeek()->toDateString();

        // ✅ STEP 1 — Redirect if onboarding not completed
        if (!$user->onboarding_completed) {
            return redirect()->route('onboarding.step1');
        }

        // ✅ STEP 2 — Determine onboarding week
        $onboardingDate = Carbon::parse($user->created_at);

        // Always get last weekly check-in (may be null)
        $lastCheckin = WeeklyCheckin::where('user_id', $user->id)
            ->orderBy('week_start', 'desc')
            ->first();

        // ✅ STEP 3 — Enforce weekly check-in ONLY after onboarding week
        if ($onboardingDate->diffInDays($now) >= 7) {
            if (
                !$lastCheckin ||
                Carbon::parse($lastCheckin->week_start)->lt($now->copy()->subDays(7))
            ) {
                return redirect()->route('weekly.checkin')
                    ->with('info', 'Please complete a weekly check-in.');
            }
        }

        // ✅ STEP 4 — Conversational Support Stats
        $activeChatsCount = Conversation::where('user_id', $user->id)->active()->count();
        $archivedChatsCount = Conversation::where('user_id', $user->id)->archived()->count();
        $totalCrisisFlags = Conversation::where('user_id', $user->id)->sum('crisis_flags_count');
        
        $lastMessage = Message::where('user_id', $user->id)->where('role', 'user')->latest()->first();
        $lastChatTime = $lastMessage ? $lastMessage->created_at->diffForHumans() : 'No activity';

        /**
         * =====================================================
         * FIRST WEEK AFTER ONBOARDING — NO KPI CALCULATION
         * =====================================================
         */
        if (!$lastCheckin) {
            // First week after onboarding — provide UI hints and availability date
            $onboardAt = $user->onboarding_completed_at ?? $user->created_at;
            $availableDate = Carbon::parse($onboardAt)->addWeek()->format('j M Y');

            return view('dashboard', [
                'user' => $user,
                'motivationScore' => null,
                'socialScore' => null,
                'emotionalScore' => null,
                'motivationInterpretation' => null,
                'socialInterpretation' => null,
                'emotionalInterpretation' => null,
                'kpiHistory' => collect(),
                'aiRecommendation' => null,
                'isFirstWeek' => true,
                'kpiAvailableDate' => $availableDate,
                // Chat Stats
                'activeChatsCount' => $activeChatsCount,
                'archivedChatsCount' => $archivedChatsCount,
                'lastChatTime' => $lastChatTime,
                'totalCrisisFlags' => $totalCrisisFlags,
            ]);
        }

        /**
         * =====================================================
         * WEEKLY CHECK-IN EXISTS — NORMAL KPI FLOW
         * =====================================================
         */

        // ✅ STEP 5 — Calculate KPIs from weekly check-in
        $kpiData = $this->calculateKPIsFromCheckin($lastCheckin);

        // ✅ STEP 6 — Save / update current week snapshot
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

        // ✅ STEP 7 — Load KPI history
        $kpiHistory = KpiSnapshot::where('user_id', $user->id)
            ->orderBy('week_start', 'asc')
            ->get();

        // ✅ STEP 8 — AI Recommendation
        $aiRecommender = new AiRecommender();

        if ($kpiData['emotionalScore'] <= 2.0) {
            $recommendation = [
                'type' => 'risk_detection',
                'component' => 'risk_detection',
                'link' => '#',
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
            'aiRecommendation' => $recommendation,
            // Chat Stats
            'activeChatsCount' => $activeChatsCount,
            'archivedChatsCount' => $archivedChatsCount,
            'lastChatTime' => $lastChatTime,
            'totalCrisisFlags' => $totalCrisisFlags,
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
            'emotionalInterpretation' => $this->interpretEmotional($emotionalScore),
        ];
    }

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
