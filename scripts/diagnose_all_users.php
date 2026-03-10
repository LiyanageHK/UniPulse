<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Journal;
use App\Models\WeeklySummary;
use App\Models\User;
use App\Services\RollingWeekService;

// Check ALL users
$users = User::all();
foreach ($users as $user) {
    $uid = $user->id;
    $jCount = Journal::where('user_id', $uid)->count();
    $wCount = WeeklySummary::where('user_id', $uid)->count();
    if ($jCount === 0 && $wCount === 0) continue;

    echo "\n══════════════════════════════════════════════\n";
    echo "User #{$uid}: {$user->name} ({$user->email})\n";
    echo "══════════════════════════════════════════════\n";

    echo "JOURNALS ({$jCount}):\n";
    Journal::where('user_id', $uid)->orderBy('entry_date')->each(function($j) {
        echo "  id={$j->id}  date={$j->entry_date}\n";
    });

    echo "WEEKLY SUMMARIES ({$wCount}):\n";
    WeeklySummary::where('user_id', $uid)->orderBy('week_index')->each(function($w) {
        echo "  id={$w->id}  week_index={$w->week_index}  {$w->week_start} → {$w->week_end}  lri={$w->lri_score}  level={$w->risk_level}\n";
    });

    echo "CROSS-CHECK (orphaned summaries):\n";
    WeeklySummary::where('user_id', $uid)->orderBy('week_index')->each(function($w) use ($uid) {
        $count = Journal::where('user_id', $uid)
            ->whereBetween('entry_date', [$w->week_start, $w->week_end])
            ->count();
        $flag = $count === 0 ? ' <-- ORPHANED' : '';
        echo "  week#{$w->week_index} ({$w->week_start} to {$w->week_end})  journals={$count}{$flag}\n";
    });

    $rollingWeek = app(RollingWeekService::class);
    $first = $rollingWeek->getFirstJournalDate($uid);
    $cur   = $first ? $rollingWeek->getCurrentWeekInfo($uid) : null;
    echo "ROLLING WEEK: first=" . ($first ? $first->toDateString() : 'NULL')
        . "  current_index=" . ($cur ? $cur['week_index'] : 'NULL') . "\n";
}
