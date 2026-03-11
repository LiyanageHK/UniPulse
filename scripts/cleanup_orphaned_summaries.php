<?php

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Delete the 2 stale WeeklySummary records for user #3 (kavishka@gmail.com)
// id=16: week_index=1 with stale window (2026-03-02..2026-03-08) — overlaps with week 0
// id=18: week_index=2 with stale window (2026-02-23..2026-03-01) — orphaned (0 journals)
$deleted = DB::table('weekly_summaries')->whereIn('id', [16, 18])->delete();
echo "Deleted: $deleted stale/orphaned summaries\n";

// Verify state for user #3
$summaries = DB::table('weekly_summaries')->where('user_id', 3)->get();
echo "Remaining summaries for user #3: " . count($summaries) . "\n";
foreach ($summaries as $s) {
    echo "  id=$s->id  week_index=$s->week_index  $s->week_start -> $s->week_end  lri=$s->lri_score  level=$s->risk_level\n";
}

echo "\nDone.\n";
