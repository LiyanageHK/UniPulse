<?php

// Test the weekly summary processing for user 2 (Silas Kirkland)
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Journal;
use App\Models\WeeklySummary;
use App\Services\WeeklySummaryService;
use Carbon\Carbon;

$userId = 2; // Silas Kirkland
$weekStart = Carbon::now()->startOfWeek();
$weekEnd = Carbon::now()->endOfWeek();

echo "User: {$userId}" . PHP_EOL;
echo "Week: {$weekStart->toDateString()} to {$weekEnd->toDateString()}" . PHP_EOL;

$journals = Journal::where('user_id', $userId)
    ->whereBetween('entry_date', [$weekStart, $weekEnd])
    ->get();
echo "Journals found: " . $journals->count() . PHP_EOL;

if ($journals->isEmpty()) {
    echo "NO JOURNALS — creating a test entry..." . PHP_EOL;
    Journal::create([
        'user_id' => $userId,
        'content' => 'I feel very stressed and alone this week. Nobody understands me. I am always worried about my future. I have no friends and feel completely isolated from everyone.',
        'entry_date' => Carbon::today()->toDateString(),
    ]);
    echo "Test journal created." . PHP_EOL;
}

echo PHP_EOL . "Processing weekly summary..." . PHP_EOL;

try {
    $service = app(WeeklySummaryService::class);
    $result = $service->processUserWeek($userId, $weekStart, $weekEnd);

    if ($result) {
        echo "SUCCESS!" . PHP_EOL;
        echo "  LRI Score: {$result->lri_score}" . PHP_EOL;
        echo "  Risk Level: {$result->risk_level}" . PHP_EOL;
        echo "  Stress: {$result->stress_score}" . PHP_EOL;
        echo "  Sentiment: {$result->sentiment_score}" . PHP_EOL;
        echo "  Pronoun: {$result->pronoun_ratio}" . PHP_EOL;
        echo "  Absolutist: {$result->absolutist_score}" . PHP_EOL;
        echo "  Withdrawal: {$result->withdrawal_score}" . PHP_EOL;
        echo "  Escalation: " . ($result->escalation_flag ? 'YES' : 'NO') . PHP_EOL;
    } else {
        echo "RETURNED NULL — check logs" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
