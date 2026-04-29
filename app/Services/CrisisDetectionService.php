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
    // These keywords indicate the highest-risk crisis signals.
    protected array $redFlagKeywords = [
        // Suicide-related
        'suicide', 'suicidal', 'kill myself', 'end it all', 'end my life',
        'want to die', 'wanna die', 'better off dead', 'no reason to live',
        'don\'t want to live', 'dont want to live', 'do not want to live',
        'don\'t want to be here', 'dont want to be here',
        'don\'t want to exist', 'dont want to exist',
        'wish i was dead', 'wish i were dead', 'wish i wasn\'t alive',
        'take my own life', 'ending my life', 'ending it all',
        // Self-harm related
        'self-harm', 'self harm', 'cut myself', 'cutting myself',
        'hurt myself', 'hurting myself', 'harm myself', 'harming myself',
        'burning myself', 'hitting myself',
    ];

    // Yellow Flag Keywords - Concerning (Activate crisis support protocol)
    // These keywords indicate serious concern but not the highest severity.
    protected array $yellowFlagKeywords = [
        'hopeless', 'worthless', 'can\'t cope', 'cannot cope',
        'no way out', 'empty', 'nobody cares', 'no one cares',
        'give up', 'can\'t go on', 'cannot go on',
    ];

    // Blue Flag Keywords - Warning (Increase support level)
    // These keywords indicate emotional distress or lower-severity risk.
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
        // Convert the message content to lowercase for case-insensitive matching.
        $content = strtolower($message->content);
        // Prepare an empty list for any detected crisis flags.
        $detectedFlags = [];

        // Check for red flags first (highest priority)
        // Search for red-flag keywords in the message.
        $redFlags = $this->detectFlags($content, $this->redFlagKeywords, CrisisFlag::SEVERITY_RED);
        // If red flags were found, verify that they refer to the user.
        if (!empty($redFlags)) {
            // For red flags, verify it's self-referential (about the person, not about a pet/object)
            // Only keep red flags if the message is self-referential.
            if ($this->isSelfReferential($message->content, $redFlags)) {
                // Merge valid red flags into the final detected list.
                $detectedFlags = array_merge($detectedFlags, $redFlags);
            }
        }

        // Check for yellow flags
        // Detect yellow-flag keywords.
        $yellowFlags = $this->detectFlags($content, $this->yellowFlagKeywords, CrisisFlag::SEVERITY_YELLOW);
        // Add yellow flags to the total list.
        $detectedFlags = array_merge($detectedFlags, $yellowFlags);

        // Check for blue flags
        // Detect blue-flag keywords.
        $blueFlags = $this->detectFlags($content, $this->blueFlagKeywords, CrisisFlag::SEVERITY_BLUE);
        // Add blue flags to the total list.
        $detectedFlags = array_merge($detectedFlags, $blueFlags);

        // Store flags in database
        // If any flags were found, persist them and update related records.
        if (!empty($detectedFlags)) {
            // Save crisis flags for the message.
            $this->storeCrisisFlags($message, $detectedFlags);
            // Mark the message as having crisis flags.
            $message->update(['has_crisis_flags' => true]);
            
            // Update conversation stats
            // Refresh conversation-level crisis statistics.
            $message->conversation->updateCrisisStats();
        }

        // Return the full list of detected crisis flags.
        return $detectedFlags;
    }

    /**
     * Detect specific flags in text.
     */
    protected function detectFlags(string $content, array $keywords, string $severity): array
    {
        // Initialize the list of matches for this severity.
        $detectedFlags = [];

        // Check each keyword against the message content.
        foreach ($keywords as $keyword) {
            // If the keyword exists in the content, record a flag.
            if (str_contains($content, $keyword)) {
                // Add the detected flag and its metadata.
                $detectedFlags[] = [
                    'severity' => $severity,
                    'keyword' => $keyword,
                    'context_snippet' => $this->extractContext($content, $keyword),
                    'confidence_score' => $this->calculateConfidence($content, $keyword, $severity),
                ];
            }
        }

        // Return all detected matches for the provided severity.
        return $detectedFlags;
    }

    /**
     * Check if detected crisis keyword is self-referential.
     * Important for red flags to distinguish "I want to kill myself" from "I want to kill this exam".
     */
    protected function isSelfReferential(string $content, array $flags): bool
    {
        // Normalize the text to lowercase for matching.
        $content = strtolower($content);

        // Self-referential indicators
        // These phrases suggest the user is talking about themselves.
        $selfIndicators = [
            'i want', 'i need', 'i feel', 'i am', 'i\'m',
            'i can\'t', 'i cannot', 'i have', 'i\'ve',
            'myself', 'my life', 'my self'
        ];

        // Non-self indicators (things, exams, pets, etc.)
        // These phrases suggest the message may be about something else.
        $nonSelfIndicators = [
            'this exam', 'this test', 'this assignment',
            'my dog', 'my cat', 'my pet',
            'this project', 'this course', 'this class',
        ];

        // Check for non-self first (higher priority - avoid false positives)
        // If any non-self indicator exists, do not treat the message as self-directed.
        foreach ($nonSelfIndicators as $indicator) {
            if (str_contains($content, $indicator)) {
                // Return false because the message likely is not about the user.
                return false;
            }
        }

        // Check for self-referential
        // Look for first-person wording that indicates the user is describing themselves.
        foreach ($selfIndicators as $indicator) {
            if (str_contains($content, $indicator)) {
                // Return true because the content appears self-referential.
                return true;
            }
        }

        // Default to true for safety if unclear
        // If the context is ambiguous, treat it as self-referential for safety.
        return true;
    }

    /**
     * Extract context around the keyword.
     */
    protected function extractContext(string $content, string $keyword, int $contextLength = 100): string
    {
        // Find the keyword position in the text.
        $pos = stripos($content, $keyword);
        
        // If the keyword is not found, return the beginning of the content.
        if ($pos === false) {
            return substr($content, 0, $contextLength);
        }

        // Compute the start position for the surrounding excerpt.
        $start = max(0, $pos - $contextLength / 2);
        // Extract a short snippet around the detected keyword.
        $excerpt = substr($content, $start, $contextLength);

        // Return the snippet wrapped with ellipses for readability.
        return '...' . trim($excerpt) . '...';
    }

    /**
     * Calculate confidence score for detection.
     */
    protected function calculateConfidence(string $content, string $keyword, string $severity): float
    {
        // Start with a baseline confidence score.
        $confidence = 0.8; // Base confidence

        // Exact phrase match increases confidence
        // Strong keyword matches slightly raise confidence.
        if (str_contains($content, $keyword)) {
            $confidence += 0.1;
        }

        // First-person indicators increase confidence for self-referential context
        // Personal language raises confidence that the message is about the user.
        if (preg_match('/\b(i|me|my|myself)\b/i', $content)) {
            $confidence += 0.1;
        }

        // Clamp the score to a maximum of 1.0.
        return min($confidence, 1.0);
    }

    /**
     * Store detected crisis flags in database.
     */
    protected function storeCrisisFlags(Message $message, array $detectedFlags): void
    {
        // Save each detected flag separately.
        foreach ($detectedFlags as $flag) {
            // Create the crisis flag record.
            $crisisFlag = CrisisFlag::create([
                'message_id' => $message->id,
                'conversation_id' => $message->conversation_id,
                'user_id' => $message->user_id,
                'severity' => $flag['severity'],
                'detected_keywords' => [$flag['keyword']],
                'context_snippet' => $flag['context_snippet'],
                'confidence_score' => $flag['confidence_score'],
            ]);

            // Auto-escalate red flags
            // Immediately escalate the most severe red flags.
            if ($flag['severity'] === CrisisFlag::SEVERITY_RED) {
                // Mark the red flag as escalated.
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
        // Mark the crisis flag as escalated in the database.
        $crisisFlag->update([
            'escalated' => true,
            'escalated_at' => now(),
        ]);

        // The actual alert creation and notification will be handled by CrisisAlertService
        // Log the critical event for audit and monitoring purposes.
        Log::critical('RED FLAG DETECTED - User: ' . $crisisFlag->user_id);
    }

    /**
     * Get crisis flag summary for a conversation.
     */
    public function getConversationFlagSummary(Conversation $conversation): array
    {
        // Retrieve all crisis flags linked to the conversation.
        $flags = $conversation->crisisFlags;

        // Return a severity-based summary of the conversation's flags.
        return [
            'total_flags' => $flags->count(),
            'red_flags' => $flags->where('severity', CrisisFlag::SEVERITY_RED)->count(),
            'yellow_flags' => $flags->where('severity', CrisisFlag::SEVERITY_YELLOW)->count(),
            'blue_flags' => $flags->where('severity', CrisisFlag::SEVERITY_BLUE)->count(),
        ];
    }
}
