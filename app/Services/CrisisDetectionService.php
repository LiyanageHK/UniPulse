<?php

namespace App\Services;

use App\Models\Message;
use App\Models\CrisisFlag;
use App\Models\Conversation;
use Illuminate\Support\Facades\Log;

class CrisisDetectionService
{
    // Crisis keywords based on the uploaded document
    
    // Red Flag Keywords - Critical (Immediate automated escalation)
    protected array $redFlagKeywords = [
        'suicide', 'suicidal', 'kill myself', 'end it all', 'end my life',
        'self-harm', 'self harm', 'cut myself', 'hurt myself', 'harm myself',
        'want to die', 'better off dead', 'no reason to live',
    ];

    // Yellow Flag Keywords - Concerning (Activate crisis support protocol)
    protected array $yellowFlagKeywords = [
        'hopeless', 'worthless', 'can\'t cope', 'cannot cope',
        'no way out', 'empty', 'nobody cares', 'no one cares',
        'give up', 'can\'t go on', 'cannot go on',
    ];

    // Blue Flag Keywords - Warning (Increase support level)
    protected array $blueFlagKeywords = [
        'overwhelmed', 'stressed', 'anxious', 'depressed',
        'cry', 'crying', 'sad', 'lonely', 'alone',
        'can\'t sleep', 'cannot sleep', 'exhausted',
    ];

    /** 
     * Analyze a message for crisis indicators.
     * Returns array of detected flags or empty array if none found.
     */
    public function analyzeMessage(Message $message): array
    {
        $content = strtolower($message->content);
        $detectedFlags = [];

        // Check for red flags first (highest priority)
        $redFlags = $this->detectFlags($content, $this->redFlagKeywords, CrisisFlag::SEVERITY_RED);
        if (!empty($redFlags)) {
            // For red flags, verify it's self-referential (about the person, not about a pet/object)
            if ($this->isSelfReferential($message->content, $redFlags)) {
                $detectedFlags = array_merge($detectedFlags, $redFlags);
            }
        }

        // Check for yellow flags
        $yellowFlags = $this->detectFlags($content, $this->yellowFlagKeywords, CrisisFlag::SEVERITY_YELLOW);
        $detectedFlags = array_merge($detectedFlags, $yellowFlags);

        // Check for blue flags
        $blueFlags = $this->detectFlags($content, $this->blueFlagKeywords, CrisisFlag::SEVERITY_BLUE);
        $detectedFlags = array_merge($detectedFlags, $blueFlags);

        // Store flags in database
        if (!empty($detectedFlags)) {
            $this->storeCrisisFlags($message, $detectedFlags);
            $message->update(['has_crisis_flags' => true]);
            
            // Update conversation stats
            $message->conversation->updateCrisisStats();
        }

        return $detectedFlags;
    }

    /**
     * Detect specific flags in text.
     */
    protected function detectFlags(string $content, array $keywords, string $severity): array
    {
        $detectedFlags = [];

        foreach ($keywords as $keyword) {
            if (str_contains($content, $keyword)) {
                $detectedFlags[] = [
                    'severity' => $severity,
                    'keyword' => $keyword,
                    'category' => $this->categorizeFlag($keyword, $severity),
                    'context_snippet' => $this->extractContext($content, $keyword),
                    'confidence_score' => $this->calculateConfidence($content, $keyword, $severity),
                ];
            }
        }

        return $detectedFlags;
    }

    /**
     * Check if detected crisis keyword is self-referential.
     * Important for red flags to distinguish "I want to kill myself" from "I want to kill this exam".
     */
    protected function isSelfReferential(string $content, array $flags): bool
    {
        $content = strtolower($content);

        // Self-referential indicators
        $selfIndicators = [
            'i want', 'i need', 'i feel', 'i am', 'i\'m',
            'i can\'t', 'i cannot', 'i have', 'i\'ve',
            'myself', 'my life', 'my self'
        ];

        // Non-self indicators (things, exams, pets, etc.)
        $nonSelfIndicators = [
            'this exam', 'this test', 'this assignment',
            'my dog', 'my cat', 'my pet',
            'this project', 'this course', 'this class',
        ];

        // Check for non-self first (higher priority - avoid false positives)
        foreach ($nonSelfIndicators as $indicator) {
            if (str_contains($content, $indicator)) {
                return false;
            }
        }

        // Check for self-referential
        foreach ($selfIndicators as $indicator) {
            if (str_contains($content, $indicator)) {
                return true;
            }
        }

        // Default to true for safety if unclear
        return true;
    }

    /**
     * Categorize flag based on keyword for counselor matching.
     */
    protected function categorizeFlag(string $keyword, string $severity): string
    {
        // Map keywords to categories
        if (str_contains($keyword, 'suic')) {
            return CrisisFlag::CATEGORY_SUICIDE_RISK;
        }
        if (str_contains($keyword, 'harm') || str_contains($keyword, 'cut') || str_contains($keyword, 'hurt')) {
            return CrisisFlag::CATEGORY_SELF_HARM;
        }
        if (str_contains($keyword, 'hopeless') || str_contains($keyword, 'worthless') || str_contains($keyword, 'no way out')) {
            return CrisisFlag::CATEGORY_HOPELESSNESS;
        }
        if (str_contains($keyword, 'depress')) {
            return CrisisFlag::CATEGORY_DEPRESSION;
        }
        if (str_contains($keyword, 'anxi')) {
            return CrisisFlag::CATEGORY_ANXIETY;
        }
        if (str_contains($keyword, 'stress') || str_contains($keyword, 'overwhelm')) {
            return CrisisFlag::CATEGORY_STRESS;
        }
        if (str_contains($keyword, 'lonely') || str_contains($keyword, 'alone')) {
            return CrisisFlag::CATEGORY_LONELINESS;
        }

        // Default based on severity
        return match($severity) {
            CrisisFlag::SEVERITY_RED => CrisisFlag::CATEGORY_SUICIDE_RISK,
            CrisisFlag::SEVERITY_YELLOW => CrisisFlag::CATEGORY_DEPRESSION,
            CrisisFlag::SEVERITY_BLUE => CrisisFlag::CATEGORY_STRESS,
            default => CrisisFlag::CATEGORY_STRESS,
        };
    }

    /**
     * Extract context around the keyword.
     */
    protected function extractContext(string $content, string $keyword, int $contextLength = 100): string
    {
        $pos = stripos($content, $keyword);
        
        if ($pos === false) {
            return substr($content, 0, $contextLength);
        }

        $start = max(0, $pos - $contextLength / 2);
        $excerpt = substr($content, $start, $contextLength);

        return '...' . trim($excerpt) . '...';
    }

    /**
     * Calculate confidence score for detection.
     */
    protected function calculateConfidence(string $content, string $keyword, string $severity): float
    {
        $confidence = 0.8; // Base confidence

        // Exact phrase match increases confidence
        if (str_contains($content, $keyword)) {
            $confidence += 0.1;
        }

        // First-person indicators increase confidence for self-referential context
        if (preg_match('/\b(i|me|my|myself)\b/i', $content)) {
            $confidence += 0.1;
        }

        return min($confidence, 1.0);
    }

    /**
     * Store detected crisis flags in database.
     */
    protected function storeCrisisFlags(Message $message, array $detectedFlags): void
    {
        foreach ($detectedFlags as $flag) {
            $crisisFlag = CrisisFlag::create([
                'message_id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'user_id' => $message->user_id,
                'severity' => $flag['severity'],
                'category' => $flag['category'],
                'detected_keywords' => [$flag['keyword']],
                'context_snippet' => $flag['context_snippet'],
                'confidence_score' => $flag['confidence_score'],
            ]);

            // Auto-escalate red flags
            if ($flag['severity'] === CrisisFlag::SEVERITY_RED) {
                $this->escalateRedFlag($crisisFlag);
            }
        }
    }

    /**
     * Escalate red flag to crisis alert.
     * This should be handled by CrisisAlertService, but we trigger it here.
     */
    protected function escalateRedFlag(CrisisFlag $crisisFlag): void
    {
        $crisisFlag->update([
            'escalated' => true,
            'escalated_at' => now(),
        ]);

        // The actual alert creation and notification will be handled by CrisisAlertService
        Log::critical('RED FLAG DETECTED - User: ' . $crisisFlag->user_id . ' - Category: ' . $crisisFlag->category);
    }

    /**
     * Get crisis flag summary for a conversation.
     */
    public function getConversationFlagSummary(Conversation $conversation): array
    {
        $flags = $conversation->crisisFlags;

        return [
            'total_flags' => $flags->count(),
            'red_flags' => $flags->where('severity', CrisisFlag::SEVERITY_RED)->count(),
            'yellow_flags' => $flags->where('severity', CrisisFlag::SEVERITY_YELLOW)->count(),
            'blue_flags' => $flags->where('severity', CrisisFlag::SEVERITY_BLUE)->count(),
            'escalated' => $flags->where('escalated', true)->count(),
            'categories' => $flags->pluck('category')->unique()->values()->toArray(),
        ];
    }
}
