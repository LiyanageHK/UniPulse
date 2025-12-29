<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\WeeklyCheckin;
use Carbon\Carbon;

$user = User::first();
if (!$user) {
    echo "No users found in DB.\n";
    exit(1);
}

$last = WeeklyCheckin::where('user_id', $user->id)->orderBy('week_start', 'desc')->first();

$requires = true;
if ($last) {
    $requires = Carbon::parse($last->week_start)->lt(Carbon::now()->subDays(7));
}

echo "User: {$user->id} ({$user->email})\n";
if ($last) {
    echo "Last check-in: {$last->id} {$last->week_start}\n";
    echo "Last check-in age (days): " . Carbon::now()->diffInDays(Carbon::parse($last->week_start)) . "\n";
} else {
    echo "No previous check-ins found.\n";
}

echo "Requires new weekly check-in (7-day rule): " . ($requires ? 'YES' : 'NO') . "\n";
