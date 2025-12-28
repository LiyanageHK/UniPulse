<?php

namespace App\Services;

use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\ConversationEmbedding;
use Illuminate\Support\Facades\Log;

class KnowledgeBaseService
{
    protected EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Build or update knowledge base for a user.
     * This includes user profile data and past conversations.
     */
    public function buildUserKnowledgeBase(User $user): void
    {
        // 1. Create/Update profile embedding
        $this->createProfileEmbedding($user);

        // 2. Create embeddings for past conversations
        $this->createConversationEmbeddings($user);
    }

    /**
     * Create embedding for user profile data.
     */
    protected function createProfileEmbedding(User $user): void
    {
        // Build profile text from user data
        $profileText = $this->buildProfileText($user);

        if (empty($profileText)) {
            return;
        }

        // Generate embedding
        $embedding = $this->embeddingService->generateEmbedding($profileText);

        if (!$embedding) {
            Log::warning('Failed to generate profile embedding for user: ' . $user->id);
            return;
        }

        // Delete old profile embedding
        ConversationEmbedding::where('user_id', $user->id)
            ->where('type', ConversationEmbedding::TYPE_PROFILE)
            ->delete();

        // Create new profile embedding
        ConversationEmbedding::create([
            'user_id' => $user->id,
            'type' => ConversationEmbedding::TYPE_PROFILE,
            'content' => $profileText,
            'summary' => 'User profile information',
            'embedding' => $embedding,
            'topic' => 'profile',
            'keywords' => $this->extractKeywords($profileText),
            'importance_score' => 1.0, // Profile is always highly important
            'model' => config('services.openai.embedding_model', 'text-embedding-3-small'),
            'dimensions' => $this->embeddingService->getDimensions(),
        ]);
    }

    /**
     * Build readable profile text from user data.
     */
    protected function buildProfileText(User $user): string
    {
        $parts = [];

        // Basic info
        if ($user->name) {
            $parts[] = "Name: {$user->name}";
        }

        // Academic info
        if ($user->university) {
            $parts[] = "University: {$user->university}";
        }
        if ($user->faculty) {
            $parts[] = "Faculty: {$user->faculty}";
        }
        if ($user->al_stream) {
            $parts[] = "A/L Stream: {$user->al_stream}";
        }

        // Learning preferences
        if ($user->learning_style) {
            $style = is_array($user->learning_style) ? implode(', ', $user->learning_style) : $user->learning_style;
            $parts[] = "Learning Style: {$style}";
        }
        if ($user->transition_confidence) {
            $parts[] = "Transition Confidence: {$user->transition_confidence}";
        }

        // Social & personality
        if ($user->social_preference) {
            $parts[] = "Social Preference: {$user->social_preference}";
        }
        if ($user->group_work_comfort) {
            $parts[] = "Group Work Comfort: {$user->group_work_comfort}";
        }
        if ($user->stress_level) {
            $parts[] = "Current Stress Level: {$user->stress_level}";
        }

        // Interests & goals
        if ($user->primary_motivator) {
            $parts[] = "Primary Motivator: {$user->primary_motivator}";
        }
        if ($user->goal_clarity) {
            $parts[] = "Goal Clarity: {$user->goal_clarity}";
        }
        if ($user->interests) {
            $interests = is_array($user->interests) ? implode(', ', $user->interests) : $user->interests;
            $parts[] = "Interests: {$interests}";
        }
        if ($user->hobbies) {
            $hobbies = is_array($user->hobbies) ? implode(', ', $user->hobbies) : $user->hobbies;
            $parts[] = "Hobbies: {$hobbies}";
        }

        // Wellbeing
        if ($user->overwhelm_level) {
            $parts[] = "Overwhelm Level: {$user->overwhelm_level}";
        }
        if ($user->peer_struggle) {
            $parts[] = "Struggles with: {$user->peer_struggle}";
        }
        if ($user->preferred_support_types) {
            $support = is_array($user->preferred_support_types) ? implode(', ', $user->preferred_support_types) : $user->preferred_support_types;
            $parts[] = "Preferred Support: {$support}";
        }

        return implode(". ", $parts) . ".";
    }

    /**
     * Create embeddings for user's past conversations.
     */
    protected function createConversationEmbeddings(User $user): void
    {
        // Get all user messages from past conversations
        $messages = Message::where('user_id', $user->id)
            ->where('role', 'user') // Only user messages, not AI responses
            ->whereDoesntHave('embeddings') // Not already embedded
            ->with('conversation')
            ->get();

        foreach ($messages as $message) {
            $this->createMessageEmbedding($message);
        }
    }

    /**
     * Create embedding for a single message.
     */
    public function createMessageEmbedding(Message $message): ?ConversationEmbedding
    {
        if (empty(trim($message->content))) {
            return null;
        }

        // Generate embedding
        $embedding = $this->embeddingService->generateEmbedding($message->content);

        if (!$embedding) {
            Log::warning('Failed to generate message embedding for message: ' . $message->id);
            return null;
        }

        // Extract topic and keywords
        $topic = $message->conversation->title ?? 'general';
        $keywords = $this->extractKeywords($message->content);

        // Calculate importance score based on length, recency, and crisis flags
        $importanceScore = $this->calculateImportanceScore($message);

        return ConversationEmbedding::create([
            'user_id' => $message->user_id,
            'conversation_id' => $message->conversation_id,
            'message_id' => $message->id,
            'type' => ConversationEmbedding::TYPE_MESSAGE,
            'content' => $message->content,
            'summary' => substr($message->content, 0, 100), // First 100 chars as summary
            'embedding' => $embedding,
            'topic' => $topic,
            'keywords' => $keywords,
            'importance_score' => $importanceScore,
            'model' => config('services.openai.embedding_model', 'text-embedding-3-small'),
            'dimensions' => $this->embeddingService->getDimensions(),
        ]);
    }

    /**
     * Extract keywords from text (simple implementation).
     */
    protected function extractKeywords(string $text): array
    {
        // Remove common words and extract meaningful terms
        $commonWords = ['the', 'is', 'at', 'which', 'on', 'a', 'an', 'and', 'or', 'but', 'in', 'with', 'to', 'for', 'of', 'as', 'by', 'that', 'this', 'it', 'from', 'i', 'me', 'my', 'you', 'your'];
        
        $words = str_word_count(strtolower($text), 1);
        $words = array_filter($words, fn($word) => !in_array($word, $commonWords) && strlen($word) > 3);
        
        $wordFreq = array_count_values($words);
        arsort($wordFreq);
        
        return array_slice(array_keys($wordFreq), 0, 10); // Top 10 keywords
    }

    /**
     * Calculate importance score for a message.
     */
    protected function calculateImportanceScore(Message $message): float
    {
        $score = 0.5; // Base score

        // Recent messages are more important
        $daysSinceCreation = now()->diffInDays($message->created_at);
        if ($daysSinceCreation < 7) {
            $score += 0.3;
        } elseif ($daysSinceCreation < 30) {
            $score += 0.2;
        }

        // Messages with crisis flags are very important
        if ($message->has_crisis_flags) {
            $score += 0.4;
        }

        // Longer messages might be more detailed/important
        $wordCount = str_word_count($message->content);
        if ($wordCount > 50) {
            $score += 0.1;
        }

        return min($score, 1.0); // Cap at 1.0
    }

    /**
     * Clean up old embeddings to save space.
     */
    public function cleanupOldEmbeddings(User $user, int $daysToKeep = 90): int
    {
        return ConversationEmbedding::where('user_id', $user->id)
            ->where('type', ConversationEmbedding::TYPE_MESSAGE)
            ->where('created_at', '<', now()->subDays($daysToKeep))
            ->where('importance_score', '<', 0.7) // Keep important ones
            ->delete();
    }
}
