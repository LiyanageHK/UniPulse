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

$oldDate = Carbon::now()->subDays(10)->toDateString();
$checkin = WeeklyCheckin::create([
    'user_id' => $user->id,
    'week_start' => $oldDate,
    'mood'=>3,'tense'=>3,'overwhelmed'=>3,'worry'=>3,'sleep_trouble'=>3,'openness_to_mentor'=>3,'knowledge_of_support'=>3,
    'peer_connection'=>3,'peer_interaction'=>3,'group_participation'=>'None','feel_left_out'=>3,'no_one_to_talk'=>3,'university_belonging'=>3,'meaningful_connections'=>3,
    'studies_interesting'=>3,'academic_confidence'=>3,'workload_management'=>3,'no_energy'=>3,'low_pleasure'=>3,'feeling_down'=>3,'emotionally_drained'=>3,'hard_to_stay_focused'=>3,'just_through_motions'=>3
]);

echo "Inserted test checkin id={$checkin->id} week_start={$checkin->week_start}\n";

// Run debug script
passthru('php scripts/debug_weekcheck.php', $ret);

// Clean up
$checkin->delete();
echo "Deleted test checkin id={$checkin->id}\n";

exit($ret);
