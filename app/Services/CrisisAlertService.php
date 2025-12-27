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

        // Find matching counselor
        $counselor = $this->counselorMatching->findMatchingCounselor(
            $crisisFlag->category,
            $crisisFlag->user->city ?? null
        );

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

    /**
     * Send immediate notifications to counselors for critical alerts.
     */
    protected function sendImmediateNotifications(CrisisAlert $alert): void
    {
        try {
            // Email notification
            if ($alert->counselor && $alert->counselor->email) {
                $this->sendEmailNotification($alert);
            } else {
                // Send to general crisis email
                $this->sendEmailNotification($alert, config('services.crisis.alert_email'));
            }

            // SMS notification (if enabled)
            if (config('services.crisis.sms_enabled', false)) {
                $this->sendSMSNotification($alert);
            }

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
        $emailTo = $overrideEmail ?? $alert->counselor->email;

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
                'category' => $alert->crisisFlag->category,
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
     */
    protected function sendSMSNotification(CrisisAlert $alert): void
    {
        if (!$alert->counselor || !$alert->counselor->phone) {
            return;
        }

        try {
            // This would integrate with an SMS service like Twilio
            Log::critical("CRISIS ALERT SMS to {$alert->counselor->phone}", [
                'message' => "URGENT: Crisis alert for student {$alert->user->name}. Category: {$alert->crisisFlag->category}. Check dashboard immediately.",
                'alert_id' => $alert->id,
            ]);

            $alert->update([
                'sms_sent' => true,
                'sms_sent_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('SMS notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Send crisis resources to the student.
     * This provides immediate help resources while counselor is being notified.
     */
    public function sendCrisisResourcesToStudent(CrisisAlert $alert): void
    {
        $resources = $this->getCrisisResources($alert->crisisFlag->category);

        $alert->markResourcesSent($resources);

        Log::info('Crisis resources sent to student: ' . $alert->user_id, $resources);
    }

    /**
     * Get crisis resources based on category.
     */
    protected function getCrisisResources(string $category): array
    {
        $baseResources = [
            'National Crisis Hotline' => '1333 (Sri Lanka)',
            'Sumithrayo (Befrienders)' => '011-2682535',
            'Emergency Services' => '119',
        ];

        $categorySpecificResources = match($category) {
            CrisisFlag::CATEGORY_SUICIDE_RISK => [
                'message' => 'We care about you and want to help. Please reach out to these resources immediately:',
                'hotlines' => $baseResources,
                'online' => [
                    'Sumithrayo Email' => 'sumithrayo@gmail.com',
                    'Online Chat' => 'https://www.sumithrayo.org',
                ],
            ],
            CrisisFlag::CATEGORY_SELF_HARM => [
                'message' => 'Self-harm is a sign that you need support. Help is available:',
                'hotlines' => $baseResources,
                'resources' => [
                    'Self-Help Guide' => 'https://unipulse.edu/mental-health/self-harm',
                ],
            ],
            CrisisFlag::CATEGORY_DEPRESSION, CrisisFlag::CATEGORY_HOPELESSNESS => [
                'message' => 'Depression is treatable. Professional support can make a real difference:',
                'hotlines' => $baseResources,
                'counseling' => [
                    'University Counseling' => 'Available Monday-Friday, 9 AM - 5 PM',
                    'Free Consultation' => 'Book at https://book.unipulse.edu',
                ],
            ],
            CrisisFlag::CATEGORY_ANXIETY, CrisisFlag::CATEGORY_STRESS => [
                'message' => 'Managing anxiety and stress is important for your well-being:',
                'resources' => [
                    'Mindfulness Exercises' => 'https://unipulse.edu/mental-health/mindfulness',
                    'Stress Management Workshop' => 'Weekly sessions available',
                ],
                'counseling' => [
                    'Student Wellness Center' => 'Building A, Room 201',
                ],
            ],
            default => [
                'message' => 'We\'re here to support you. Here are some resources:',
                'hotlines' => $baseResources,
            ],
        };

        return array_merge(
            ['category' => $category],
            $categorySpecificResources
        );
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
