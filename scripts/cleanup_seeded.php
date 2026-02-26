<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Delete all seeded users (by email pattern)
$deleted = App\Models\User::where('email', 'like', '%@unipulse.student.lk')->delete();
echo "Deleted users: {$deleted}\n";

// Delete orphan profiles
$dp = App\Models\StudentProfile::whereNotIn('user_id', App\Models\User::pluck('id'))->delete();
echo "Deleted orphan profiles: {$dp}\n";

echo "Users: " . App\Models\User::count() . ", Profiles: " . App\Models\StudentProfile::count() . "\n";
