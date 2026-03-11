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
        // A user that has completed onboarding more than 7 days ago but hasn't checked in this week
        $user = User::factory()->create([
            'onboarding_completed' => true,
            'created_at' => Carbon::now()->subDays(10) // Past the 7-day onboarding period
        ]);

        // Simulate login - this should redirect to weekly checkin
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('weekly.checkin'));
    }

    public function test_user_with_recent_checkin_can_access_dashboard_after_login()
    {
        $user = User::factory()->create([
            'onboarding_completed' => true,
            'created_at' => Carbon::now()->subDays(10) // Past the 7-day onboarding period
        ]);

        // Create a checkin for the current week
        $currentWeekStart = Carbon::now()->startOfWeek()->toDateString();

        WeeklyCheckin::create([
            'user_id' => $user->id,
            'week_start' => $currentWeekStart,
            'mood'=>3,'tense'=>3,'overwhelmed'=>3,'worry'=>3,'sleep_trouble'=>3,'openness_to_mentor'=>3,'knowledge_of_support'=>3,
            'peer_connection'=>3,'peer_interaction'=>3,'group_participation'=>'None','feel_left_out'=>3,'no_one_to_talk'=>3,'university_belonging'=>3,'meaningful_connections'=>3,
            'studies_interesting'=>3,'academic_confidence'=>3,'workload_management'=>3,'no_energy'=>3,'low_pleasure'=>3,'feeling_down'=>3,'emotionally_drained'=>3,'hard_to_stay_focused'=>3,'just_through_motions'=>3
        ]);

        // Simulate login - should go to dashboard since they have current week checkin
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_in_first_week_not_redirected_to_weekly_checkin()
    {
        // Create a user that was created today (within first week)
        $user = User::factory()->create([
            'onboarding_completed' => true,
            'created_at' => Carbon::now()
        ]);

        // Simulate login - should go to dashboard since they're in first week
        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
    }
}
