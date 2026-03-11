<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Journal;
use App\Models\WeeklyChecking;
use App\Models\WeeklySummary;
use Carbon\Carbon;

/**
 * Seeds a demo user with:
 *  - 5 weekly check-in survey records  (survey-based risk panel)
 *  - 9 journal entries over 3 rolling weeks (journal-based LRI panel)
 *  - 3 WeeklySummary NLP records with escalating LRI scores
 *
 * Login:  risktest@unipulse.dev / password123
 */
class RiskDetectionTestSeeder extends Seeder
{
    public function run(): void
    {
        // ────────────────────────────────────────────────
        // 1. Create / find demo user
        // ────────────────────────────────────────────────
        $user = User::firstOrCreate(
            ['email' => 'risktest@unipulse.dev'],
            [
                'name'               => 'Demo Student',
                'password'           => Hash::make('password123'),
                'on_boarding_required' => false,
                'onboarding_completed' => true,
                'email_verified_at'  => now(),
            ]
        );

        $uid = $user->id;

        // Clear previous test data so seeder is idempotent
        WeeklyChecking::where('user_id', $uid)->delete();
        Journal::where('user_id', $uid)->delete();
        WeeklySummary::where('user_id', $uid)->delete();

        // ────────────────────────────────────────────────
        // 2. Weekly Check-In Survey Records  (need ≥ 5)
        //
        //  Scale: 1–5   (1 = never / not at all, 5 = always / very much)
        //  Higher scores for stress/depression/disengagement fields = MORE symptoms
        //  Higher scores for social/openness fields = BETTER (they get reversed inside the model)
        // ────────────────────────────────────────────────
        $checkInWeeks = [
            // Week 1 – 5 weeks ago – calm baseline
            [
                'created_at'         => now()->subWeeks(4),
                'overall_mood'       => 2,   // 2 = somewhat low mood
                'felt_supported'     => 2,
                'emotion_description'=> 'Feeling okay, a bit tired from the semester starting.',
                'trouble_sleeping'   => 2,
                'hard_to_focus'      => 2,
                'open_to_counselor'  => 4,   // willing to talk (high = good, will be reversed)
                'know_access_support'=> 4,
                'feeling_tense'      => 2,
                'worrying'           => 2,
                'interact_peers'     => 4,   // interacting well (high = good, reversed)
                'keep_up_workload'   => 2,
                'group_activities'   => json_encode([3]),
                'academic_challenges'=> json_encode([2]),
                'feel_left_out'      => 2,   // low = not left out
                'no_one_to_talk'     => 2,
                'no_energy'          => 2,
                'little_pleasure'    => 2,
                'feeling_down'       => 2,
                'emotionally_drained'=> 2,
                'going_through_motions' => 2,
            ],
            // Week 2 – 4 weeks ago – slight increase in stress
            [
                'created_at'         => now()->subWeeks(3),
                'overall_mood'       => 3,
                'felt_supported'     => 3,
                'emotion_description'=> 'Assignments piling up. Feeling some pressure.',
                'trouble_sleeping'   => 3,
                'hard_to_focus'      => 3,
                'open_to_counselor'  => 3,
                'know_access_support'=> 3,
                'feeling_tense'      => 3,
                'worrying'           => 3,
                'interact_peers'     => 3,
                'keep_up_workload'   => 3,
                'group_activities'   => json_encode([2]),
                'academic_challenges'=> json_encode([3]),
                'feel_left_out'      => 3,
                'no_one_to_talk'     => 3,
                'no_energy'          => 3,
                'little_pleasure'    => 3,
                'feeling_down'       => 3,
                'emotionally_drained'=> 3,
                'going_through_motions' => 3,
            ],
            // Week 3 – 3 weeks ago – moderate stress and isolation
            [
                'created_at'         => now()->subWeeks(2),
                'overall_mood'       => 4,
                'felt_supported'     => 3,
                'emotion_description'=> 'Struggling with workload and feeling disconnected from peers.',
                'trouble_sleeping'   => 4,
                'hard_to_focus'      => 4,
                'open_to_counselor'  => 2,   // less willing to seek help
                'know_access_support'=> 2,
                'feeling_tense'      => 4,
                'worrying'           => 4,
                'interact_peers'     => 2,   // less social interaction
                'keep_up_workload'   => 4,
                'group_activities'   => json_encode([1]),
                'academic_challenges'=> json_encode([4]),
                'feel_left_out'      => 4,
                'no_one_to_talk'     => 4,
                'no_energy'          => 4,
                'little_pleasure'    => 4,
                'feeling_down'       => 4,
                'emotionally_drained'=> 4,
                'going_through_motions' => 4,
            ],
            // Week 4 – 2 weeks ago – high risk across multiple areas
            [
                'created_at'         => now()->subWeeks(1)->subDays(2),
                'overall_mood'       => 5,
                'felt_supported'     => 4,
                'emotion_description'=> 'Everything feels overwhelming. Hard to get through the day.',
                'trouble_sleeping'   => 5,
                'hard_to_focus'      => 5,
                'open_to_counselor'  => 2,
                'know_access_support'=> 1,
                'feeling_tense'      => 5,
                'worrying'           => 5,
                'interact_peers'     => 1,   // almost no peer interaction
                'keep_up_workload'   => 5,
                'group_activities'   => json_encode([1]),
                'academic_challenges'=> json_encode([5]),
                'feel_left_out'      => 5,
                'no_one_to_talk'     => 5,
                'no_energy'          => 5,
                'little_pleasure'    => 5,
                'feeling_down'       => 5,
                'emotionally_drained'=> 5,
                'going_through_motions' => 5,
            ],
            // Week 5 – most recent – slight improvement
            [
                'created_at'         => now()->subDays(3),
                'overall_mood'       => 4,
                'felt_supported'     => 3,
                'emotion_description'=> 'Still stressed but slightly better after talking to a friend.',
                'trouble_sleeping'   => 4,
                'hard_to_focus'      => 4,
                'open_to_counselor'  => 2,
                'know_access_support'=> 2,
                'feeling_tense'      => 4,
                'worrying'           => 4,
                'interact_peers'     => 2,
                'keep_up_workload'   => 4,
                'group_activities'   => json_encode([2]),
                'academic_challenges'=> json_encode([4]),
                'feel_left_out'      => 4,
                'no_one_to_talk'     => 4,
                'no_energy'          => 4,
                'little_pleasure'    => 4,
                'feeling_down'       => 4,
                'emotionally_drained'=> 4,
                'going_through_motions' => 4,
            ],
        ];

        foreach ($checkInWeeks as $data) {
            $createdAt = $data['created_at'];
            unset($data['created_at']);
            $data['user_id'] = $uid;

            $record = WeeklyChecking::create($data);
            // Manually set timestamps for realistic spread
            $record->created_at = $createdAt;
            $record->updated_at = $createdAt;
            $record->saveQuietly();
        }

        // ────────────────────────────────────────────────
        // 3. Journal Entries  (3 rolling weeks × 3 entries)
        // ────────────────────────────────────────────────
        $journalEntries = [
            // Week 1 – 3 weeks ago – mild stress
            [
                'entry_date' => now()->subWeeks(3)->startOfWeek()->format('Y-m-d'),
                'content'    => "Started the week feeling okay. Went to a couple of lectures and managed to keep up. Had dinner with my study group which was nice. A bit anxious about the upcoming assignment deadline but nothing I can\'t handle.",
            ],
            [
                'entry_date' => now()->subWeeks(3)->startOfWeek()->addDays(2)->format('Y-m-d'),
                'content'    => "Spent most of the day at the library. Feeling tired but productive. I talked to my friend about a group project we\'re working on. Still sleeping okay though I wake up sometimes thinking about coursework.",
            ],
            [
                'entry_date' => now()->subWeeks(3)->startOfWeek()->addDays(4)->format('Y-m-d'),
                'content'    => "Week is wrapping up. I feel like I\'m keeping up with most things. The workload is manageable today. I went for a short walk which helped clear my head. Looking forward to the weekend.",
            ],

            // Week 2 – 2 weeks ago – moderate stress / some withdrawal
            [
                'entry_date' => now()->subWeeks(2)->startOfWeek()->format('Y-m-d'),
                'content'    => "I always seem to fall behind no matter how hard I try. I couldn\'t focus at all today. Everything feels too heavy. I didn\'t go to my afternoon lecture because I just couldn\'t face sitting there. Nobody would notice anyway.",
            ],
            [
                'entry_date' => now()->subWeeks(2)->startOfWeek()->addDays(2)->format('Y-m-d'),
                'content'    => "Stayed in my room for most of the day. I never feel like I belong here. My flatmates went out but I just couldn\'t bring myself to join. Nothing ever seems to work out for me. Couldn\'t sleep last night — just kept worrying and worrying.",
            ],
            [
                'entry_date' => now()->subWeeks(2)->startOfWeek()->addDays(4)->format('Y-m-d'),
                'content'    => "I never reach out to anyone because it always ends badly. I am completely alone and there is no point pretending otherwise. I feel exhausted all the time, no energy left for anything. I should study but I cannot move.",
            ],

            // Week 3 – last week – high stress / critical signals
            [
                'entry_date' => now()->subWeeks(1)->startOfWeek()->format('Y-m-d'),
                'content'    => "I cannot do this anymore. I am always failing, always behind, always struggling. I never sleep properly, I never eat properly, and I never feel okay. Everyone else seems fine. I should just withdraw from everything and disappear for a while.",
            ],
            [
                'entry_date' => now()->subWeeks(1)->startOfWeek()->addDays(2)->format('Y-m-d'),
                'content'    => "Missed all my classes this week. I never want to go back. I always feel this crushing weight on my chest. There is nobody I can talk to — I would never burden anyone with my problems. I spent the whole day in bed unable to do anything at all.",
            ],
            [
                'entry_date' => now()->subWeeks(1)->startOfWeek()->addDays(4)->format('Y-m-d'),
                'content'    => "I feel completely hopeless. I always mess everything up. I cannot imagine things ever getting better. I never reach out because nobody truly cares. I am so isolated and empty inside. I don\'t know how much more I can take of this.",
            ],
        ];

        foreach ($journalEntries as $entry) {
            Journal::create([
                'user_id'    => $uid,
                'content'    => $entry['content'],
                'entry_date' => $entry['entry_date'],
            ]);
        }

        // ────────────────────────────────────────────────
        // 4. WeeklySummary NLP Records  (escalating LRI)
        //    These mimic what the AI service would produce.
        // ────────────────────────────────────────────────
        $weeklySummaries = [
            [
                'week_index'       => 1,
                'week_start'       => now()->subWeeks(3)->startOfWeek()->format('Y-m-d'),
                'week_end'         => now()->subWeeks(3)->startOfWeek()->addDays(6)->format('Y-m-d'),
                'summary_text'     => 'Journal entries reflect mild stress related to academic workload. Mood appears generally stable with some anxiety about deadlines. Social engagement is present.',
                'stress_score'     => 0.2812,
                'sentiment_score'  => 0.4100,
                'pronoun_ratio'    => 0.0950,
                'absolutist_score' => 0.0300,
                'withdrawal_score' => 0.1200,
                'lri_score'        => 22.45,
                'risk_level'       => 'Low',
                'escalation_flag'  => false,
            ],
            [
                'week_index'       => 2,
                'week_start'       => now()->subWeeks(2)->startOfWeek()->format('Y-m-d'),
                'week_end'         => now()->subWeeks(2)->startOfWeek()->addDays(6)->format('Y-m-d'),
                'summary_text'     => 'Notable increase in stress and withdrawal language. High usage of absolutist terms ("never", "always", "no point"). Sentiment has declined significantly. Social isolation signals detected.',
                'stress_score'     => 0.5600,
                'sentiment_score'  => 0.6200,
                'pronoun_ratio'    => 0.1850,
                'absolutist_score' => 0.2400,
                'withdrawal_score' => 0.3500,
                'lri_score'        => 48.72,
                'risk_level'       => 'Medium',
                'escalation_flag'  => false,
            ],
            [
                'week_index'       => 3,
                'week_start'       => now()->subWeeks(1)->startOfWeek()->format('Y-m-d'),
                'week_end'         => now()->subWeeks(1)->startOfWeek()->addDays(6)->format('Y-m-d'),
                'summary_text'     => 'Critical stress indicators detected. Frequent absolutist language, strong withdrawal signals, highly negative sentiment. Student has missed classes and reports complete isolation. Immediate professional support recommended.',
                'stress_score'     => 0.8900,
                'sentiment_score'  => 0.8750,
                'pronoun_ratio'    => 0.2600,
                'absolutist_score' => 0.5100,
                'withdrawal_score' => 0.6800,
                'lri_score'        => 73.61,
                'risk_level'       => 'High',
                'escalation_flag'  => true,
            ],
            [
                'week_index'       => 4,
                'week_start'       => now()->startOfWeek()->format('Y-m-d'),
                'week_end'         => now()->startOfWeek()->addDays(6)->format('Y-m-d'),
                'summary_text'     => 'Slight reduction in stress indicators compared to last week. Student appears to be engaging more with support resources. Withdrawal language has decreased slightly though overall risk remains elevated.',
                'stress_score'     => 0.7200,
                'sentiment_score'  => 0.7100,
                'pronoun_ratio'    => 0.2100,
                'absolutist_score' => 0.3800,
                'withdrawal_score' => 0.5200,
                'lri_score'        => 58.93,
                'risk_level'       => 'Medium',
                'escalation_flag'  => false,
            ],
        ];

        foreach ($weeklySummaries as $summaryData) {
            WeeklySummary::create(array_merge($summaryData, ['user_id' => $uid]));
        }

        $this->command->info('');
        $this->command->info('✅  Risk Detection Test Data Seeded');
        $this->command->info('   Login email : risktest@unipulse.dev');
        $this->command->info('   Password    : password123');
        $this->command->info('   User ID     : ' . $uid);
        $this->command->info('   Weekly check-ins : 5 records');
        $this->command->info('   Journal entries  : 9 records (3 weeks)');
        $this->command->info('   Weekly summaries : 4 records (LRI: 22 → 48 → 73 → 58, High then improving)');
        $this->command->info('');
    }
}
