<?php
/**
 * Seeds risk detection sample data for a specific user by ID.
 * Usage: php scripts/seed_current_user.php <user_id>
 * Example: php scripts/seed_current_user.php 3
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\WeeklyChecking;
use App\Models\WeeklySummary;
use App\Models\Journal;

$userId = (int)($argv[1] ?? 3);

$user = User::find($userId);
if (!$user) {
    echo "❌  User ID $userId not found.\n";
    exit(1);
}

echo "Seeding data for: {$user->name} (ID {$userId})\n";

// Fix onboarding so risk detection page is accessible
$user->on_boarding_required = false;
$user->onboarding_completed = true;
$user->save();
echo "✅  Fixed on_boarding_required = false\n";

// Clear old test data
WeeklyChecking::where('user_id', $userId)->delete();
Journal::where('user_id', $userId)->delete();
WeeklySummary::where('user_id', $userId)->delete();

// ── Weekly Check-In Records (5 weeks) ──────────────────────────────────────
$checkIns = [
    ['weeks_ago' => 4, 'mood' => 2, 'tense' => 2, 'worry' => 2, 'sleep' => 2, 'focus' => 2, 'energy' => 2, 'pleasure' => 2, 'down' => 2, 'drained' => 2, 'motions' => 2, 'left_out' => 2, 'no_talk' => 2, 'interact' => 4, 'workload' => 2, 'counselor' => 4, 'support' => 4, 'felt_supported' => 2, 'desc' => 'Feeling reasonably okay. A bit tired from lectures but managing fine.'],
    ['weeks_ago' => 3, 'mood' => 3, 'tense' => 3, 'worry' => 3, 'sleep' => 3, 'focus' => 3, 'energy' => 3, 'pleasure' => 3, 'down' => 3, 'drained' => 3, 'motions' => 3, 'left_out' => 3, 'no_talk' => 3, 'interact' => 3, 'workload' => 3, 'counselor' => 3, 'support' => 3, 'felt_supported' => 3, 'desc' => 'Assignments piling up. Feeling some academic pressure.'],
    ['weeks_ago' => 2, 'mood' => 4, 'tense' => 4, 'worry' => 4, 'sleep' => 4, 'focus' => 4, 'energy' => 4, 'pleasure' => 4, 'down' => 4, 'drained' => 4, 'motions' => 4, 'left_out' => 4, 'no_talk' => 4, 'interact' => 2, 'workload' => 4, 'counselor' => 2, 'support' => 2, 'felt_supported' => 3, 'desc' => 'Struggling with workload and feeling disconnected from peers.'],
    ['weeks_ago' => 1, 'mood' => 5, 'tense' => 5, 'worry' => 5, 'sleep' => 5, 'focus' => 5, 'energy' => 5, 'pleasure' => 5, 'down' => 5, 'drained' => 5, 'motions' => 5, 'left_out' => 5, 'no_talk' => 5, 'interact' => 1, 'workload' => 5, 'counselor' => 1, 'support' => 1, 'felt_supported' => 4, 'desc' => 'Everything feels overwhelming. Hard to get through the day.'],
    ['weeks_ago' => 0, 'mood' => 4, 'tense' => 4, 'worry' => 4, 'sleep' => 4, 'focus' => 4, 'energy' => 4, 'pleasure' => 4, 'down' => 4, 'drained' => 4, 'motions' => 4, 'left_out' => 4, 'no_talk' => 4, 'interact' => 2, 'workload' => 4, 'counselor' => 2, 'support' => 2, 'felt_supported' => 3, 'desc' => 'Still stressed but slightly better after talking to a classmate.'],
];

foreach ($checkIns as $c) {
    $ts = now()->subWeeks($c['weeks_ago'])->subDays(rand(0, 2));
    $record = WeeklyChecking::create([
        'user_id'              => $userId,
        'overall_mood'         => $c['mood'],
        'felt_supported'       => $c['felt_supported'],
        'emotion_description'  => $c['desc'],
        'trouble_sleeping'     => $c['sleep'],
        'hard_to_focus'        => $c['focus'],
        'open_to_counselor'    => $c['counselor'],
        'know_access_support'  => $c['support'],
        'feeling_tense'        => $c['tense'],
        'worrying'             => $c['worry'],
        'interact_peers'       => $c['interact'],
        'keep_up_workload'     => $c['workload'],
        'group_activities'     => json_encode([rand(1,3)]),
        'academic_challenges'  => json_encode([rand(2,5)]),
        'feel_left_out'        => $c['left_out'],
        'no_one_to_talk'       => $c['no_talk'],
        'no_energy'            => $c['energy'],
        'little_pleasure'      => $c['pleasure'],
        'feeling_down'         => $c['down'],
        'emotionally_drained'  => $c['drained'],
        'going_through_motions'=> $c['motions'],
    ]);
    $record->created_at = $ts;
    $record->updated_at = $ts;
    $record->saveQuietly();
}
echo "✅  5 weekly check-in records inserted\n";

// ── Journal Entries (3 weeks × 3 entries) ──────────────────────────────────
$journals = [
    // Week 1 – mild
    [now()->subWeeks(3)->startOfWeek()->format('Y-m-d'), "Started the week feeling okay. Went to a couple of lectures and managed to keep up. Had dinner with my study group which was nice. A bit anxious about the upcoming assignment deadline but nothing I can't handle."],
    [now()->subWeeks(3)->startOfWeek()->addDays(2)->format('Y-m-d'), "Spent most of the day at the library. Feeling tired but productive. I talked to my friend about a group project. Still sleeping okay though I wake up sometimes thinking about coursework."],
    [now()->subWeeks(3)->startOfWeek()->addDays(4)->format('Y-m-d'), "Week is wrapping up. I feel like I'm keeping up with most things. The workload is manageable today. I went for a short walk which helped clear my head."],
    // Week 2 – moderate
    [now()->subWeeks(2)->startOfWeek()->format('Y-m-d'), "I always seem to fall behind no matter how hard I try. I couldn't focus at all today. Everything feels too heavy. I didn't go to my afternoon lecture because I just couldn't face it. Nobody would notice anyway."],
    [now()->subWeeks(2)->startOfWeek()->addDays(2)->format('Y-m-d'), "Stayed in my room for most of the day. I never feel like I belong here. My flatmates went out but I just couldn't bring myself to join. Nothing ever seems to work out for me. Couldn't sleep last night."],
    [now()->subWeeks(2)->startOfWeek()->addDays(4)->format('Y-m-d'), "I never reach out to anyone because it always ends badly. I am completely alone. I feel exhausted all the time, no energy left for anything. I should study but I cannot move."],
    // Week 3 – high risk
    [now()->subWeeks(1)->startOfWeek()->format('Y-m-d'), "I cannot do this anymore. I am always failing, always behind, always struggling. I never sleep properly, I never eat properly, and I never feel okay. Everyone else seems fine. I should just disappear for a while."],
    [now()->subWeeks(1)->startOfWeek()->addDays(2)->format('Y-m-d'), "Missed all my classes this week. I never want to go back. I always feel this crushing weight on my chest. There is nobody I can talk to. I spent the whole day in bed unable to do anything at all."],
    [now()->subWeeks(1)->startOfWeek()->addDays(4)->format('Y-m-d'), "I feel completely hopeless. I always mess everything up. I cannot imagine things ever getting better. I never reach out because nobody truly cares. I am so isolated and empty inside."],
];

foreach ($journals as [$date, $content]) {
    Journal::create(['user_id' => $userId, 'entry_date' => $date, 'content' => $content]);
}
echo "✅  9 journal entries inserted\n";

// ── Weekly Summaries (NLP-simulated, escalating LRI) ───────────────────────
$summaries = [
    [1, now()->subWeeks(3)->startOfWeek(), now()->subWeeks(3)->endOfWeek(), 'Mild stress, manageable workload. Social engagement present. Mood generally stable.', 0.2812, 0.4100, 0.0950, 0.0300, 0.1200, 22.45, 'Low', false],
    [2, now()->subWeeks(2)->startOfWeek(), now()->subWeeks(2)->endOfWeek(), 'Increased withdrawal language. Absolutist terms detected ("never", "always"). Sentiment declining. Social isolation signals present.', 0.5600, 0.6200, 0.1850, 0.2400, 0.3500, 48.72, 'Medium', false],
    [3, now()->subWeeks(1)->startOfWeek(), now()->subWeeks(1)->endOfWeek(), 'Critical stress indicators. Strong withdrawal signals, highly negative sentiment. Classes missed, complete isolation reported. Immediate support recommended.', 0.8900, 0.8750, 0.2600, 0.5100, 0.6800, 73.61, 'High', true],
];

foreach ($summaries as [$idx, $start, $end, $text, $stress, $sent, $pronoun, $abs, $with, $lri, $risk, $escalate]) {
    WeeklySummary::create([
        'user_id'          => $userId,
        'week_index'       => $idx,
        'week_start'       => $start->format('Y-m-d'),
        'week_end'         => $end->format('Y-m-d'),
        'summary_text'     => $text,
        'stress_score'     => $stress,
        'sentiment_score'  => $sent,
        'pronoun_ratio'    => $pronoun,
        'absolutist_score' => $abs,
        'withdrawal_score' => $with,
        'lri_score'        => $lri,
        'risk_level'       => $risk,
        'escalation_flag'  => $escalate,
    ]);
}
echo "✅  3 weekly summaries inserted (LRI: 22 → 48 → 73, escalating)\n";

// Verify
$wc = WeeklyChecking::getLast4WeeksScores($userId);
$ws = WeeklyChecking::calculateWeightedScores($wc);
$tr = WeeklyChecking::detectTrend($wc);
echo "\n--- Survey Risk Panel (computed) ---\n";
foreach ($ws as $area => $score) {
    $risk = WeeklyChecking::determineRisk($area, $score);
    printf("  %-20s | Score: %4.2f | Risk: %-8s | Trend: %s\n", $area, $score, $risk, $tr[$area]);
}
echo "\nDone! Visit /dashboard-poornima and refresh the page.\n";
