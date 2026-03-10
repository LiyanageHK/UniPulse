<?php
/**
 * Verify peer matching scores for the logged-in user.
 * Run: php artisan tinker scripts/verify_peer_scores.php
 * Set TARGET_USER_ID to the user you want to verify as.
 */

define('TARGET_USER_ID', null); // null = use first user found

use App\Models\StudentProfile;
use App\Models\WeeklyChecking;

$myProfile = TARGET_USER_ID
    ? StudentProfile::with('user')->where('user_id', TARGET_USER_ID)->first()
    : StudentProfile::with('user')->first();

if (!$myProfile) {
    echo "No profiles found.\n";
    return;
}

echo "=== LOGGED-IN USER ===\n";
echo "ID    : " . $myProfile->user_id . " (" . $myProfile->user->name . ")\n";
echo "Faculty: " . $myProfile->faculty . "\n";
echo "AL     : " . $myProfile->al_stream . "\n";
echo "LS     : " . $myProfile->learning_style . "\n";
echo "IE     : " . $myProfile->intro_extro . "\n";
echo "Stress : " . $myProfile->stress_level . "\n";
echo "Overwh : " . $myProfile->overwhelmed . "\n";
echo "Social : " . $myProfile->social_setting . "\n";
$mine_interests = is_array($myProfile->top_interests) ? $myProfile->top_interests : (json_decode($myProfile->top_interests, true) ?: []);
$mine_comms     = is_array($myProfile->communication_methods) ? $myProfile->communication_methods : (json_decode($myProfile->communication_methods, true) ?: []);
echo "Interests (" . count($mine_interests) . "): " . implode(', ', $mine_interests) . "\n";
echo "Comms    : " . implode(', ', $mine_comms) . "\n";

$myCheckin = WeeklyChecking::where('user_id', $myProfile->user_id)->latest()->first();
echo "WeeklyCheckin: " . ($myCheckin ? "mood={$myCheckin->overall_mood} feel_left_out={$myCheckin->feel_left_out}" : "NONE") . "\n\n";

// Load all other profiles
$others = StudentProfile::with('user')->where('user_id', '!=', $myProfile->user_id)->get();

// Pre-load latest check-ins
$allIds = $others->pluck('user_id')->push($myProfile->user_id)->unique()->values();
$latestCheckins = WeeklyChecking::whereIn('user_id', $allIds)
    ->select('user_id', 'overall_mood', 'feel_left_out', 'created_at')
    ->orderByDesc('created_at')
    ->get()
    ->unique('user_id')
    ->keyBy('user_id');

echo "=== SCORE BREAKDOWN PER PEER ===\n";
$results = [];

foreach ($others as $profile) {
    $score = 0.0;
    $log   = [];

    // 1. Interest & Hobby (25% max)
    $other_interests = is_array($profile->top_interests) ? $profile->top_interests : (json_decode($profile->top_interests, true) ?: []);
    $common = count(array_intersect($mine_interests, $other_interests));
    $max    = max(count($mine_interests), 1);
    $ipts   = ($common / $max) * 15;
    $score += $ipts;
    $log[]  = "  Interests: {$common}/{$max} * 15 = " . round($ipts, 2);

    $ls_match = ($profile->learning_style === $myProfile->learning_style);
    if ($ls_match) { $score += 10; $log[] = "  LearningStyle: match +10"; }
    else           { $log[] = "  LearningStyle: no match (mine={$myProfile->learning_style} other={$profile->learning_style})"; }

    // 2. Academic (20%)
    $fac_match = ($profile->faculty === $myProfile->faculty);
    if ($fac_match) { $score += 10; $log[] = "  Faculty: match +10"; }
    else            { $log[] = "  Faculty: no match (mine={$myProfile->faculty} other={$profile->faculty})"; }

    $al_match = ($profile->al_stream === $myProfile->al_stream);
    if ($al_match) { $score += 10; $log[] = "  AL_stream: match +10"; }
    else           { $log[] = "  AL_stream: no match (mine={$myProfile->al_stream} other={$profile->al_stream})"; }

    // 3. Personality (20%)
    $ie_match = ((int)$profile->intro_extro === (int)$myProfile->intro_extro);
    if ($ie_match) { $score += 10; $log[] = "  IntroExtro: match +10"; }
    else           { $log[] = "  IntroExtro: no match (mine={$myProfile->intro_extro} other={$profile->intro_extro})"; }

    $sl_match = ($profile->stress_level === $myProfile->stress_level);
    if ($sl_match) { $score += 10; $log[] = "  StressLevel: match +10"; }
    else           { $log[] = "  StressLevel: no match (mine={$myProfile->stress_level} other={$profile->stress_level})"; }

    // 4. Emotional & Wellbeing (15%)
    $ow_match = ((int)$profile->overwhelmed === (int)$myProfile->overwhelmed);
    if ($ow_match) { $score += 5; $log[] = "  Overwhelmed: match +5"; }
    else           { $log[] = "  Overwhelmed: no match (mine={$myProfile->overwhelmed} other={$profile->overwhelmed})"; }

    $otherCheckin = $latestCheckins->get($profile->user_id);
    if ($myCheckin && $otherCheckin) {
        $mood_match = ((int)$myCheckin->overall_mood === (int)$otherCheckin->overall_mood);
        if ($mood_match) { $score += 5; $log[] = "  WeeklyMood: match +5"; }
        else             { $log[] = "  WeeklyMood: no match (mine={$myCheckin->overall_mood} other={$otherCheckin->overall_mood})"; }

        $flo_match = ((int)$myCheckin->feel_left_out === (int)$otherCheckin->feel_left_out);
        if ($flo_match) { $score += 5; $log[] = "  FeelLeftOut: match +5"; }
        else            { $log[] = "  FeelLeftOut: no match (mine={$myCheckin->feel_left_out} other={$otherCheckin->feel_left_out})"; }
    } else {
        $myHas    = $myCheckin    ? 'yes' : 'NO';
        $otherHas = $otherCheckin ? 'yes' : 'NO';
        $log[] = "  WeeklyCheckin: skipped (myCheckin={$myHas} otherCheckin={$otherHas})";
    }

    // 5. Communication (10%)
    $other_comms  = is_array($profile->communication_methods) ? $profile->communication_methods : (json_decode($profile->communication_methods, true) ?: []);
    $comm_overlap = array_intersect($mine_comms, $other_comms);
    if (count($comm_overlap) > 0) { $score += 10; $log[] = "  Comms: overlap [" . implode(',', $comm_overlap) . "] +10"; }
    else                          { $log[] = "  Comms: no overlap"; }

    // 6. Social (10%)
    $soc_match = ($profile->social_setting === $myProfile->social_setting);
    if ($soc_match) { $score += 10; $log[] = "  SocialSetting: match +10"; }
    else            { $log[] = "  SocialSetting: no match (mine={$myProfile->social_setting} other={$profile->social_setting})"; }

    $pct = (int) round($score);
    $results[] = ['name' => $profile->user->name ?? '?', 'pct' => $pct, 'log' => $log];
}

// Sort same as controller
usort($results, fn($a, $b) => $b['pct'] <=> $a['pct']);

foreach ($results as $r) {
    echo "--- " . $r['name'] . " => " . $r['pct'] . "% ---\n";
    foreach ($r['log'] as $line) echo $line . "\n";
    echo "\n";
}
