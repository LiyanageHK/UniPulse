<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeeklyCheckin;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklyCheckinObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_created_checkin_updates_user()
    {
        $user = User::factory()->create(['onboarding_completed' => true]);
        // preserve onboarding-owned transition confidence for this user
        $user->transition_confidence = 3;
        $user->save();

        $w = WeeklyCheckin::create([
            'user_id' => $user->id,
            'week_start' => Carbon::now()->toDateString(),
            'mood'=>3,'tense'=>5,'overwhelmed'=>2,'worry'=>4,'sleep_trouble'=>4,'openness_to_mentor'=>3,'knowledge_of_support'=>3,
            'peer_connection'=>3,'peer_interaction'=>2,'group_participation'=>'None','feel_left_out'=>1,'no_one_to_talk'=>3,'university_belonging'=>3,'meaningful_connections'=>3,
            'studies_interesting'=>3,'academic_confidence'=>4,'workload_management'=>3,'no_energy'=>3,'low_pleasure'=>3,'feeling_down'=>3,'emotionally_drained'=>3,'hard_to_stay_focused'=>3,'just_through_motions'=>3
        ]);

        $user = $user->fresh();

        $this->assertEquals('High', $user->stress_level); // round((5+4+4)/3)=4 -> 'High'
        $this->assertEquals(2, $user->overwhelm_level);
        $this->assertEquals(2, $user->peer_struggle); // round((1+3)/2)=2
        $this->assertEquals(3, $user->transition_confidence);
        $this->assertEquals(2, $user->group_work_comfort);
        $this->assertNotNull($user->last_checkin_at);
    }

    public function test_updated_latest_checkin_updates_user()
    {
        $user = User::factory()->create(['onboarding_completed' => true]);
        // preserve onboarding-owned transition confidence for this user
        $user->transition_confidence = 3;
        $user->save();

        $old = WeeklyCheckin::create([
            'user_id' => $user->id,
            'week_start' => Carbon::now()->subWeek()->toDateString(),
            'mood'=>3,'tense'=>2,'overwhelmed'=>2,'worry'=>2,'sleep_trouble'=>2,'openness_to_mentor'=>3,'knowledge_of_support'=>3,
            'peer_connection'=>3,'peer_interaction'=>3,'group_participation'=>'None','feel_left_out'=>2,'no_one_to_talk'=>2,'university_belonging'=>3,'meaningful_connections'=>3,
            'studies_interesting'=>3,'academic_confidence'=>3,'workload_management'=>3,'no_energy'=>3,'low_pleasure'=>3,'feeling_down'=>3,'emotionally_drained'=>3,'hard_to_stay_focused'=>3,'just_through_motions'=>3
        ]);

        $latest = WeeklyCheckin::create([
            'user_id' => $user->id,
            'week_start' => Carbon::now()->toDateString(),
            'mood'=>3,'tense'=>2,'overwhelmed'=>3,'worry'=>3,'sleep_trouble'=>2,'openness_to_mentor'=>3,'knowledge_of_support'=>3,
            'peer_connection'=>3,'peer_interaction'=>4,'group_participation'=>'None','feel_left_out'=>4,'no_one_to_talk'=>5,'university_belonging'=>3,'meaningful_connections'=>3,
            'studies_interesting'=>3,'academic_confidence'=>3,'workload_management'=>3,'no_energy'=>3,'low_pleasure'=>3,'feeling_down'=>3,'emotionally_drained'=>3,'hard_to_stay_focused'=>3,'just_through_motions'=>3
        ]);

        // Update latest to different values
        $latest->update(['tense'=>5,'worry'=>5,'sleep_trouble'=>5,'feel_left_out'=>1,'no_one_to_talk'=>1,'academic_confidence'=>5,'peer_interaction'=>1]);

        $user = $user->fresh();

        // stress = round((5+5+5)/3)=5 -> 'High'
        $this->assertEquals('High', $user->stress_level);
        $this->assertEquals(3, $user->overwhelm_level);
        $this->assertEquals(1, $user->peer_struggle);
        $this->assertEquals(3, $user->transition_confidence);
        $this->assertEquals(1, $user->group_work_comfort);
    }

    public function test_deleting_latest_rolls_back_to_previous()
    {
        $user = User::factory()->create(['onboarding_completed' => true]);
        // preserve onboarding-owned transition confidence for this user
        $user->transition_confidence = 3;
        $user->save();

        $old = WeeklyCheckin::create([
            'user_id' => $user->id,
            'week_start' => Carbon::now()->subWeek()->toDateString(),
            'mood'=>3,'tense'=>5,'overwhelmed'=>2,'worry'=>4,'sleep_trouble'=>4,'openness_to_mentor'=>3,'knowledge_of_support'=>3,
            'peer_connection'=>3,'peer_interaction'=>2,'group_participation'=>'None','feel_left_out'=>1,'no_one_to_talk'=>3,'university_belonging'=>3,'meaningful_connections'=>3,
            'studies_interesting'=>3,'academic_confidence'=>4,'workload_management'=>3,'no_energy'=>3,'low_pleasure'=>3,'feeling_down'=>3,'emotionally_drained'=>3,'hard_to_stay_focused'=>3,'just_through_motions'=>3
        ]);

        $latest = WeeklyCheckin::create([
            'user_id' => $user->id,
            'week_start' => Carbon::now()->toDateString(),
            'mood'=>3,'tense'=>2,'overwhelmed'=>3,'worry'=>3,'sleep_trouble'=>2,'openness_to_mentor'=>3,'knowledge_of_support'=>3,
            'peer_connection'=>3,'peer_interaction'=>4,'group_participation'=>'None','feel_left_out'=>4,'no_one_to_talk'=>5,'university_belonging'=>3,'meaningful_connections'=>3,
            'studies_interesting'=>3,'academic_confidence'=>3,'workload_management'=>3,'no_energy'=>3,'low_pleasure'=>3,'feeling_down'=>3,'emotionally_drained'=>3,'hard_to_stay_focused'=>3,'just_through_motions'=>3
        ]);

        // The user currently reflects latest
        $user = $user->fresh();
        $this->assertEquals(4, $user->group_work_comfort);

        // Delete latest — observer should resync to previous
        $latest->delete();

        $user = $user->fresh();
        $this->assertEquals(2, $user->group_work_comfort);
    }
}
