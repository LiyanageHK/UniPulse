<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Journal;
use App\Models\WeeklySummary;
use App\Models\User;
use App\Services\RollingWeekService;

$user = User::where('email', 'risktest@unipulse.dev')->first();
if (!$user) {
    echo "User risktest@unipulse.dev not found.\n";
    exit(1);
}
$uid = $user->id;

echo "=== JOURNALS (" . Journal::where('user_id', $uid)->count() . " records) ===\n";
Journal::where('user_id', $uid)->orderBy('entry_date')->each(function($j) {
    echo "  id={$j->id}  date={$j->entry_date}\n";
});

echo "\n=== WEEKLY SUMMARIES (" . WeeklySummary::where('user_id', $uid)->count() . " records) ===\n";
WeeklySummary::where('user_id', $uid)->orderBy('week_index')->each(function($w) {
    echo "  id={$w->id}  week_index={$w->week_index}  week_start={$w->week_start}  week_end={$w->week_end}  lri={$w->lri_score}  level={$w->risk_level}\n";
});

// Cross-check: for each weekly summary, how many journals exist in that window?
echo "\n=== CROSS-CHECK: journals per summary week ===\n";
WeeklySummary::where('user_id', $uid)->orderBy('week_index')->each(function($w) use ($uid) {
    $count = Journal::where('user_id', $uid)
        ->whereBetween('entry_date', [$w->week_start, $w->week_end])
        ->count();
    $flag = $count === 0 ? '  <-- ORPHANED (no journals!)' : '';
    echo "  week_index={$w->week_index}  ({$w->week_start} to {$w->week_end})  journals={$count}{$flag}\n";
});

// Rolling week service check
$rollingWeek = app(RollingWeekService::class);
$firstDate = $rollingWeek->getFirstJournalDate($uid);
echo "\n=== ROLLING WEEK SERVICE ===\n";
echo "  First journal date: " . ($firstDate ? $firstDate->toDateString() : 'NULL') . "\n";
if ($firstDate) {
    $currentWeek = $rollingWeek->getCurrentWeekInfo($uid);
    echo "  Current week_index: " . ($currentWeek ? $currentWeek['week_index'] : 'NULL') . "\n";
    echo "  Current week_start: " . ($currentWeek ? $currentWeek['week_start']->toDateString() : 'NULL') . "\n";
    echo "  Current week_end:   " . ($currentWeek ? $currentWeek['week_end']->toDateString() : 'NULL') . "\n";
}
