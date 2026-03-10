<?php
// Run with: php artisan tinker < scripts/verify_risk_seed.php
// Or: php scripts/verify_risk_seed.php (from project root after bootstrapping)

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\WeeklyChecking;
use App\Models\WeeklySummary;
use App\Models\Journal;

$u = User::where('email', 'risktest@unipulse.dev')->first();

if (!$u) {
    echo "❌  Demo user not found. Run: php artisan db:seed --class=RiskDetectionTestSeeder\n";
    exit(1);
}

echo "===== SEEDED DATA VERIFICATION =====\n";
echo "User     : {$u->name} (ID {$u->id})\n";
echo "Email    : {$u->email}\n";
echo "Boarding : on_boarding_required=" . ($u->on_boarding_required ? 'true' : 'false') . "\n\n";

$wcCount = WeeklyChecking::where('user_id', $u->id)->count();
echo "Weekly check-ins : $wcCount records (need ≥ 5 to display risk panel)\n";

$jCount = Journal::where('user_id', $u->id)->count();
echo "Journal entries  : $jCount records\n";

$summaries = WeeklySummary::where('user_id', $u->id)->orderBy('week_index')->get();
echo "Weekly summaries : {$summaries->count()} records\n\n";

echo "--- Journal-Based LRI (Weekly Summaries) ---\n";
foreach ($summaries as $s) {
    $flag = $s->escalation_flag ? ' ⚠ ESCALATING' : '';
    echo "  Week #{$s->week_index}  | LRI: {$s->lri_score}  | Risk: {$s->risk_level}{$flag}\n";
    echo "          week_start={$s->week_start->format('Y-m-d')}  week_end={$s->week_end->format('Y-m-d')}\n";
}

echo "\n--- Survey-Based Risk Panel (computed) ---\n";
$weeklyScores  = WeeklyChecking::getLast4WeeksScores($u->id);
$weightedScores = WeeklyChecking::calculateWeightedScores($weeklyScores);
$trends        = WeeklyChecking::detectTrend($weeklyScores);

foreach ($weightedScores as $area => $score) {
    $risk  = WeeklyChecking::determineRisk($area, $score);
    $trend = $trends[$area];
    echo sprintf("  %-20s | Score: %4.2f | Risk: %-8s | Trend: %s\n", $area, $score, $risk, $trend);
}

echo "\n✅  Verification complete.\n";
echo "Login: risktest@unipulse.dev / password123\n";
echo "Navigate to: /dashboard-poornima\n";
