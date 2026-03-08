<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardRecommendationTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_displays_risk_detection_button()
    {
        $user = User::factory()->make();

        $view = $this->view('dashboard', [
            'user' => $user,
            'motivationScore' => 3,
            'socialScore' => 3,
            'emotionalScore' => 2,
            'motivationInterpretation' => 'Moderate',
            'socialInterpretation' => 'Moderate',
            'emotionalInterpretation' => 'At-risk',
            'kpiHistory' => collect(), // no history, so charts won't render
            'aiRecommendation' => ['type' => 'risk_detection', 'link' => '/components/risk_detection'],
            'isFirstWeek' => false,
            'activeChatsCount' => 0,
            'archivedChatsCount' => 0,
            'lastChatTime' => 'never',
            'totalCrisisFlags' => 0,
        ]);

        $view->assertSee('Open Risk Detection');
    }

    public function test_view_displays_peer_matching_button_and_chart()
    {
        $user = User::factory()->make();

        // provide minimal history so chart block is rendered
        $history = collect([ (object)['week_start' => now()->subWeek()->toDateString(), 'motivation_kpi'=>3, 'social_kpi'=>3, 'emotional_kpi'=>3] ]);

        $view = $this->view('dashboard', [
            'user' => $user,
            'motivationScore' => 3,
            'socialScore' => 3,
            'emotionalScore' => 3,
            'motivationInterpretation' => 'Moderate',
            'socialInterpretation' => 'Moderate',
            'emotionalInterpretation' => 'Moderate',
            'kpiHistory' => $history,
            'aiRecommendation' => ['type' => 'peer_matching', 'link' => '/components/peer_matching'],
            'isFirstWeek' => false,
            'activeChatsCount' => 0,
            'archivedChatsCount' => 0,
            'lastChatTime' => 'never',
            'totalCrisisFlags' => 0,
            'riskHistory' => collect([0]),
            'peerHistory' => collect([1]),
        ]);

        $view->assertSee('Open Peer Matching');
        $view->assertSee('riskPeerChart');
    }
}
