<?php

namespace App\Observers;

use App\Models\WeeklyCheckin;
use Illuminate\Support\Facades\Log;

class WeeklyCheckinObserver
{
    protected function syncUserFromLatest(WeeklyCheckin $checkin)
    {
        $user = $checkin->user;
        if (!$user) {
            return;
        }

        $latest = $user->weeklyCheckins()->orderBy('week_start','desc')->first();

        if (!$latest) {
            // No checkins left — clear auto-updated fields (but do NOT touch onboarding-owned fields like transition_confidence)
            $user->update([
                'stress_level' => null,
                'overwhelm_level' => null,
                'peer_struggle' => null,
                'group_work_comfort' => null,
                'last_checkin_at' => null,
            ]);
            return;
        }

        $numericStress = round((($latest->tense ?? 0) + ($latest->worry ?? 0) + ($latest->sleep_trouble ?? 0)) / 3);
        $peerStruggle = round((($latest->feel_left_out ?? 0) + ($latest->no_one_to_talk ?? 0)) / 2);

        // Map numeric stress to DB enum labels (higher numeric => higher stress)
        if ($numericStress >= 4) {
            $stressLabel = 'High';
        } elseif ($numericStress === 3) {
            $stressLabel = 'Moderate';
        } else {
            $stressLabel = 'Low';
        }

        $user->update([
            'stress_level' => $stressLabel,
            'overwhelm_level' => $latest->overwhelmed,
            'peer_struggle' => $peerStruggle,
            'group_work_comfort' => $latest->peer_interaction,
            'last_checkin_at' => now(),
        ]);
    }

    public function created(WeeklyCheckin $checkin)
    {
        $this->syncUserFromLatest($checkin);
    }

    public function updated(WeeklyCheckin $checkin)
    {
        $this->syncUserFromLatest($checkin);
    }

    public function deleted(WeeklyCheckin $checkin)
    {
        // When a checkin is deleted, ensure user is synced to the latest remaining checkin
        $this->syncUserFromLatest($checkin);
    }
}
