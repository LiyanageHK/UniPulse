<?php
/**
 * Verify score between user ID 2 (Sachith) and Mahen Samarasinghe only.
 */
use App\Models\StudentProfile;
use App\Models\WeeklyChecking;
use App\Models\User;

// Logged-in user
$myProfile = StudentProfile::with('user')->where('user_id', 2)->first();

// Find Mahen
$mahen = User::where('name', 'like', '%Mahen%')->first();
if (!$mahen) { echo "Mahen not found\n"; return; }
$otherProfile = StudentProfile::where('user_id', $mahen->id)->first();
if (!$otherProfile) { echo "Mahen has no profile\n"; return; }

$myCheckin    = WeeklyChecking::where('user_id', $myProfile->user_id)->latest('created_at')->first();
$otherCheckin = WeeklyChecking::where('user_id', $otherProfile->user_id)->latest('created_at')->first();

$mine_interests  = is_array($myProfile->top_interests)    ? $myProfile->top_interests    : (json_decode($myProfile->top_interests, true)    ?: []);
$other_interests = is_array($otherProfile->top_interests) ? $otherProfile->top_interests : (json_decode($otherProfile->top_interests, true) ?: []);
$mine_comms      = is_array($myProfile->communication_methods)    ? $myProfile->communication_methods    : (json_decode($myProfile->communication_methods, true)    ?: []);
$other_comms     = is_array($otherProfile->communication_methods) ? $otherProfile->communication_methods : (json_decode($otherProfile->communication_methods, true) ?: []);

$score = 0.0;
echo "=== Sachith (user 2) vs Mahen ({$mahen->id}) ===\n\n";

// 1. Interest & Hobby
$common = count(array_intersect($mine_interests, $other_interests));
$max    = max(count($mine_interests), 1);
$ipts   = ($common / $max) * 15;
$score += $ipts;
echo "[1] INTEREST & HOBBY (max 25%)\n";
echo "    Interests: {$common}/{$max} * 15 = " . round($ipts,2) . " pts  [mine:".implode(',',$mine_interests)." | other:".implode(',',$other_interests)."]\n";
$ls = ($otherProfile->learning_style === $myProfile->learning_style) ? 10 : 0;
$score += $ls;
echo "    LearningStyle: mine={$myProfile->learning_style} other={$otherProfile->learning_style} => +{$ls}\n";
echo "    Subtotal: " . round($ipts + $ls, 2) . "\n\n";

// 2. Academic
echo "[2] ACADEMIC COMPATIBILITY (max 20%)\n";
$f  = ($otherProfile->faculty   === $myProfile->faculty)   ? 10 : 0; $score += $f;
$al = ($otherProfile->al_stream === $myProfile->al_stream) ? 10 : 0; $score += $al;
echo "    Faculty:   mine={$myProfile->faculty} other={$otherProfile->faculty} => +{$f}\n";
echo "    AL_stream: mine={$myProfile->al_stream} other={$otherProfile->al_stream} => +{$al}\n";
echo "    Subtotal: " . ($f+$al) . "\n\n";

// 3. Personality
echo "[3] PERSONALITY COMPATIBILITY (max 20%)\n";
$ie = ((int)$otherProfile->intro_extro === (int)$myProfile->intro_extro) ? 10 : 0; $score += $ie;
$sl = ($otherProfile->stress_level     === $myProfile->stress_level)     ? 10 : 0; $score += $sl;
echo "    IntroExtro:  mine={$myProfile->intro_extro} other={$otherProfile->intro_extro} => +{$ie}\n";
echo "    StressLevel: mine={$myProfile->stress_level} other={$otherProfile->stress_level} => +{$sl}\n";
echo "    Subtotal: " . ($ie+$sl) . "\n\n";

// 4. Emotional & Wellbeing
echo "[4] EMOTIONAL & WELLBEING ALIGNMENT (max 15%)\n";
$ow = ((int)$otherProfile->overwhelmed === (int)$myProfile->overwhelmed) ? 5 : 0; $score += $ow;
echo "    Overwhelmed: mine={$myProfile->overwhelmed} other={$otherProfile->overwhelmed} => +{$ow}\n";
if ($myCheckin && $otherCheckin) {
    $mood = ((int)$myCheckin->overall_mood    === (int)$otherCheckin->overall_mood)    ? 5 : 0; $score += $mood;
    $flo  = ((int)$myCheckin->feel_left_out   === (int)$otherCheckin->feel_left_out)   ? 5 : 0; $score += $flo;
    echo "    WeeklyMood:  mine={$myCheckin->overall_mood} other={$otherCheckin->overall_mood} => +{$mood}\n";
    echo "    FeelLeftOut: mine={$myCheckin->feel_left_out} other={$otherCheckin->feel_left_out} => +{$flo}\n";
    echo "    Subtotal: " . ($ow+$mood+$flo) . "\n\n";
} else {
    echo "    WeeklyCheckin: mine=".($myCheckin ? 'YES' : 'NO')." other=".($otherCheckin ? 'YES' : 'NO')." => mood/flo skipped\n";
    echo "    Subtotal: {$ow}\n\n";
}

// 5. Communication
echo "[5] COMMUNICATION PREFERENCE (max 10%)\n";
$overlap = array_intersect($mine_comms, $other_comms);
$cm = count($overlap) > 0 ? 10 : 0; $score += $cm;
echo "    mine=[".implode(',',$mine_comms)."] other=[".implode(',',$other_comms)."] overlap=[".implode(',',$overlap)."] => +{$cm}\n\n";

// 6. Social
echo "[6] SOCIAL PREFERENCES (max 10%)\n";
$soc = ($otherProfile->social_setting === $myProfile->social_setting) ? 10 : 0; $score += $soc;
echo "    mine={$myProfile->social_setting} other={$otherProfile->social_setting} => +{$soc}\n\n";

echo "=== TOTAL SCORE: " . round($score) . "% (expected: 45%) ===\n";
echo ($score == 45 ? "✓ MATCHES SCREEN\n" : "✗ MISMATCH - screen shows 45%\n");
