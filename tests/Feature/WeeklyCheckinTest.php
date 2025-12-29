<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeeklyCheckin;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklyCheckinTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_without_recent_checkin_redirected_to_weekly_checkin()
    {
        // A user that has completed onboarding but hasn't checked in recently
        $user = User::factory()->create(['onboarding_completed' => true]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect(route('weekly.checkin'));
    }

    public function test_user_with_recent_checkin_sees_dashboard()
    {
        $user = User::factory()->create(['onboarding_completed' => true]);

        WeeklyCheckin::create([
            'user_id' => $user->id,
            'week_start' => Carbon::now()->subDays(3)->toDateString(),
            'mood'=>3,'tense'=>3,'overwhelmed'=>3,'worry'=>3,'sleep_trouble'=>3,'openness_to_mentor'=>3,'knowledge_of_support'=>3,
            'peer_connection'=>3,'peer_interaction'=>3,'group_participation'=>'None','feel_left_out'=>3,'no_one_to_talk'=>3,'university_belonging'=>3,'meaningful_connections'=>3,
            'studies_interesting'=>3,'academic_confidence'=>3,'workload_management'=>3,'no_energy'=>3,'low_pleasure'=>3,'feeling_down'=>3,'emotionally_drained'=>3,'hard_to_stay_focused'=>3,'just_through_motions'=>3
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
    }
}
