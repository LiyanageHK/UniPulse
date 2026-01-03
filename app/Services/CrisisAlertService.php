<?php

namespace App\Services;

use App\Models\CrisisFlag;
use App\Models\CrisisAlert;
use App\Models\Counselor;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class CrisisAlertService
{
    protected CounselorMatchingService $counselorMatching;

    public function __construct(CounselorMatchingService $counselorMatching)
    {
        $this->counselorMatching = $counselorMatching;
    }

    /**
     * Create crisis alert for a red flag.
     * This triggers immediate notification to counselors.
     */
    public function createCrisisAlert(CrisisFlag $crisisFlag): CrisisAlert
    {
        // Determine priority
        $priority = match($crisisFlag->severity) {
            CrisisFlag::SEVERITY_RED => CrisisAlert::PRIORITY_CRITICAL,
            CrisisFlag::SEVERITY_YELLOW => CrisisAlert::PRIORITY_HIGH,
            default => CrisisAlert::PRIORITY_MEDIUM,
        };

        // Find a crisis counselor
        $counselor = Counselor::where('category', Counselor::CATEGORY_CRISIS)->first();

        // Create alert
        $alert = CrisisAlert::create([
            'crisis_flag_id' => $crisisFlag->id,
            'user_id' => $crisisFlag->user_id,
            'counselor_id' => $counselor?->id,
            'status' => CrisisAlert::STATUS_PENDING,
            'priority' => $priority,
        ]);

        // Send notifications
        if ($crisisFlag->isRed()) {
            $this->sendImmediateNotifications($alert);
            $this->sendCrisisResourcesToStudent($alert);
        }

        return $alert;
    }

    protected function sendImmediateNotifications(CrisisAlert $alert): void
    {
        try {
            // General crisis email notification (no specific counselor email anymore)
            $this->sendEmailNotification($alert, config('services.crisis.alert_email'));

            Log::info('Crisis alert notifications sent for alert: ' . $alert->id);
        } catch (\Exception $e) {
            Log::error('Failed to send crisis notifications: ' . $e->getMessage());
        }
    }

    /**
     * Send email notification to counselor.
     */
    protected function sendEmailNotification(CrisisAlert $alert, ?string $overrideEmail = null): void
    {
        $emailTo = $overrideEmail; // Standardized to general email as counselor email is removed

        if (!$emailTo) {
            return;
        }

        try {
            // In a production system, you'd have a proper Mail class
            // For now, we'll just log it
            Log::critical("CRISIS ALERT EMAIL to {$emailTo}", [
                'student_id' => $alert->user_id,
                'student_name' => $alert->user->name,
                'severity' => $alert->crisisFlag->severity,
                'keywords' => $alert->crisisFlag->detected_keywords,
                'context' => $alert->crisisFlag->context_snippet,
                'alert_id' => $alert->id,
            ]);

            $alert->update([
                'email_sent' => true,
                'email_sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Email notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Send SMS notification (placeholder for actual SMS implementation).
     * Counselor phone is removed, so this is disabled.
     */
    protected function sendSMSNotification(CrisisAlert $alert): void
    {
        return;
    }

    /**
     * Send crisis resources to the student.
     * This provides immediate help resources while counselor is being notified.
     */
    public function sendCrisisResourcesToStudent(CrisisAlert $alert): void
    {
        $resources = $this->getDefaultCrisisResources();

        $alert->markResourcesSent($resources);

        Log::info('Crisis resources sent to student: ' . $alert->user_id, $resources);
    }

    /**
     * Get default crisis resources.
     */
    protected function getDefaultCrisisResources(): array
    {
        return [
            'message' => 'We care about you and want to help. Please reach out to these resources immediately:',
            'hotlines' => [
                'National Crisis Hotline' => '1333 (Sri Lanka)',
                'Sumithrayo (Befrienders)' => '011-2682535',
                'Emergency Services' => '119',
            ],
            'online' => [
                'Sumithrayo Email' => 'sumithrayo@gmail.com',
                'Online Chat' => 'https://www.sumithrayo.org',
            ],
        ];
    }

    /**
     * Get pending alerts for dashboard.
     */
    public function getPendingAlerts(int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return CrisisAlert::with(['user', 'counselor', 'crisisFlag'])
            ->pending()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get critical alerts (red flags).
     */
    public function getCriticalAlerts(): \Illuminate\Database\Eloquent\Collection
    {
        return CrisisAlert::with(['user', 'counselor', 'crisisFlag'])
            ->critical()
            ->whereIn('status', [CrisisAlert::STATUS_PENDING, CrisisAlert::STATUS_ACKNOWLEDGED])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Mark alert as acknowledged by counselor.
     */
    public function acknowledgeAlert(CrisisAlert $alert, ?int $counselorId = null): void
    {
        $alert->acknowledge();

        if ($counselorId && !$alert->counselor_id) {
            $alert->update(['counselor_id' => $counselorId]);
        }

        Log::info('Crisis alert acknowledged: ' . $alert->id . ' by counselor: ' . ($counselorId ?? 'system'));
    }

    /**
     * Resolve an alert.
     */
    public function resolveAlert(CrisisAlert $alert, string $notes): void
    {
        $alert->resolve($notes);

        Log::info('Crisis alert resolved: ' . $alert->id);
    }
}
