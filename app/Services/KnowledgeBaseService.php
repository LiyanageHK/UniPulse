<?php

namespace App\Services;

use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\ConversationEmbedding;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class KnowledgeBaseService
{
    protected EmbeddingService $embeddingService;
    protected PineconeService $pinecone;

    public function __construct(EmbeddingService $embeddingService, PineconeService $pinecone)
    {
        $this->embeddingService = $embeddingService;
        $this->pinecone = $pinecone;
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

        // Delete old profile embedding (MySQL + Pinecone)
        $oldProfileIds = ConversationEmbedding::where('user_id', $user->id)
            ->where('type', ConversationEmbedding::TYPE_PROFILE)
            ->pluck('id')
            ->map(fn($id) => 'emb_' . $id)
            ->toArray();

        ConversationEmbedding::where('user_id', $user->id)
            ->where('type', ConversationEmbedding::TYPE_PROFILE)
            ->delete();

        if (!empty($oldProfileIds)) {
            $this->pinecone->delete($oldProfileIds);
        }

        // Create new profile embedding
        $embeddingRecord = ConversationEmbedding::create([
            'user_id' => $user->id,
            'type' => ConversationEmbedding::TYPE_PROFILE,
            'content' => $profileText,
            'summary' => 'User profile information',
            'embedding' => $embedding,
            'topic' => 'profile',
            'keywords' => $this->extractKeywords($profileText),
            'importance_score' => 1.0,
            'model' => config('services.openai.embedding_model', 'text-embedding-3-small'),
            'dimensions' => $this->embeddingService->getDimensions(),
        ]);

        // Dual-write to Pinecone
        $this->pinecone->upsertAsync('emb_' . $embeddingRecord->id, $embedding, [
            'user_id'          => $user->id,
            'type'             => ConversationEmbedding::TYPE_PROFILE,
            'topic'            => 'profile',
            'importance_score' => 1.0,
            'content'          => substr($profileText, 0, 1000),
            'summary'          => 'User profile information',
        ]);

        // Invalidate profile context cache
        Cache::forget("user_profile_context_{$user->id}");
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

        // Learning preferences — translate to readable text
        if ($user->learning_style) {
            $raw = $user->learning_style;
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                $raw = is_array($decoded) ? $decoded : [$raw];
            }
            $style = implode(', ', (array) $raw);
            $parts[] = "Prefers {$style} learning";
        }

        // Confidence in university transition (1–5 scale)
        if ($user->transition_confidence) {
            $conf = (int) $user->transition_confidence;
            $confLabel = match(true) {
                $conf <= 2 => 'not very confident about adapting to university life',
                $conf == 3 => 'somewhat confident about university life',
                default    => 'confident about adapting to university life',
            };
            $parts[] = "Feels {$confLabel}";
        }

        // Social preference
        if ($user->social_preference) {
            $parts[] = "Comfortable in {$user->social_preference} social settings";
        }

        // Group work comfort (1–5 scale)
        if ($user->group_work_comfort) {
            $gwc = (int) $user->group_work_comfort;
            $gwcLabel = $gwc >= 4 ? 'enjoys group work' : ($gwc <= 2 ? 'prefers working alone' : 'neutral about group work');
            $parts[] = ucfirst($gwcLabel);
        }

        // Self-reported stress level (text from onboarding: Low/Moderate/High)
        if ($user->stress_level) {
            $parts[] = "Self-reported stress level at onboarding: {$user->stress_level}";
        }

        // Interests & goals
        if ($user->primary_motivator) {
            $parts[] = "Motivated by: {$user->primary_motivator}";
        }
        if ($user->goal_clarity) {
            $gc = (int) $user->goal_clarity;
            $gcLabel = $gc >= 4 ? 'has clear goals' : ($gc <= 2 ? 'feels unclear about goals' : 'has somewhat clear goals');
            $parts[] = ucfirst($gcLabel);
        }
        if ($user->interests) {
            $interests = is_array($user->interests) ? implode(', ', $user->interests) : $user->interests;
            $parts[] = "Interests: {$interests}";
        }
        if ($user->hobbies) {
            $hobbies = is_array($user->hobbies) ? implode(', ', $user->hobbies) : $user->hobbies;
            $parts[] = "Hobbies: {$hobbies}";
        }

        // Wellbeing signals — translate numbers to readable context
        if ($user->overwhelm_level) {
            $ov = (int) $user->overwhelm_level;
            $ovLabel = match(true) {
                $ov >= 4 => 'often feels overwhelmed',
                $ov == 3 => 'sometimes feels overwhelmed',
                default  => 'rarely feels overwhelmed',
            };
            $parts[] = ucfirst($ovLabel);
        }
        if ($user->peer_struggle) {
            $ps = (int) $user->peer_struggle;
            if ($ps >= 3) {
                $parts[] = 'Struggles to connect with peers';
            }
        }
        if ($user->preferred_support_types) {
            $support = is_array($user->preferred_support_types) ? implode(', ', $user->preferred_support_types) : $user->preferred_support_types;
            $parts[] = "Prefers support via: {$support}";
        }

        $profileText = implode('. ', $parts) . '.';

        // Append latest weekly check-in summary if available
        $checkinContext = $this->buildCheckinContext($user);
        if ($checkinContext) {
            $profileText .= "\n\n" . $checkinContext;
        }

        return $profileText;
    }

    /**
     * Build a readable wellbeing summary from the student's latest weekly check-in.
     * Translates scores into natural language the AI can use silently for context.
     * Returns empty string if no check-in data exists.
     */
    protected function buildCheckinContext(User $user): string
    {
        // Try WeeklyChecking first (newer model with scoring methods)
        $checkin = \App\Models\WeeklyChecking::where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$checkin) {
            // Fall back to WeeklyCheckin
            $checkin = \App\Models\WeeklyCheckin::where('user_id', $user->id)
                ->latest()
                ->first();

            if (!$checkin) {
                return '';
            }

            // Basic summary from the simpler model
            $mood = $checkin->mood ?? null;
            if (!$mood) return '';
            $moodLabel = $mood <= 2 ? 'low' : ($mood >= 4 ? 'good' : 'okay');
            return "Latest weekly check-in: mood is {$moodLabel}.";
        }

        // Use the scoring methods from WeeklyChecking
        $scores  = $checkin->calculateAreaScores();
        $signals = [];

        // Only surface signals that are meaningfully elevated or low — not all scores
        $stressRisk = \App\Models\WeeklyChecking::determineRisk('stress', $scores['stress']);
        if ($stressRisk !== 'Low') {
            $signals[] = $stressRisk === 'High'
                ? 'showing high stress and anxiety this week'
                : 'experiencing moderate stress this week';
        }

        $deprRisk = \App\Models\WeeklyChecking::determineRisk('depression', $scores['depression']);
        if ($deprRisk !== 'Low') {
            $signals[] = $deprRisk === 'High'
                ? 'mood has been quite low recently'
                : 'mood has been a bit low recently';
        }

        $disengRisk = \App\Models\WeeklyChecking::determineRisk('disengagement', $scores['disengagement']);
        if ($disengRisk !== 'Low') {
            $signals[] = $disengRisk === 'High'
                ? 'feeling emotionally drained and disengaged'
                : 'feeling somewhat burnt out';
        }

        $socialRisk = \App\Models\WeeklyChecking::determineRisk('social_isolation', $scores['social_isolation']);
        if ($socialRisk !== 'Low') {
            $signals[] = $socialRisk === 'High'
                ? 'feeling socially isolated'
                : 'feeling somewhat disconnected from peers';
        }

        // Overall mood from the check-in (1–5 scale)
        if ($checkin->overall_mood) {
            $mood = (int) $checkin->overall_mood;
            $moodLabel = match(true) {
                $mood <= 2 => 'reported feeling quite low overall',
                $mood == 3 => 'reported feeling okay overall',
                default    => 'reported feeling reasonably well overall',
            };
            $signals[] = $moodLabel;
        }

        if (empty($signals)) {
            return "Latest weekly check-in: no significant concerns flagged.";
        }

        $checkinDate = $checkin->created_at->diffForHumans();
        $last = array_pop($signals);
        $signalText = empty($signals)
            ? $last
            : implode(', ', $signals) . ', and ' . $last;
        return "Latest weekly check-in ({$checkinDate}): student is {$signalText}.";
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

        $embeddingRecord = ConversationEmbedding::create([
            'user_id' => $message->user_id,
            'conversation_id' => $message->conversation_id,
            'message_id' => $message->id,
            'type' => ConversationEmbedding::TYPE_MESSAGE,
            'content' => $message->content,
            'summary' => substr($message->content, 0, 100),
            'embedding' => $embedding,
            'topic' => $topic,
            'keywords' => $keywords,
            'importance_score' => $importanceScore,
            'model' => config('services.openai.embedding_model', 'text-embedding-3-small'),
            'dimensions' => $this->embeddingService->getDimensions(),
        ]);

        // Dual-write to Pinecone
        $this->pinecone->upsertAsync('emb_' . $embeddingRecord->id, $embedding, [
            'user_id'          => (int) $message->user_id,
            'type'             => ConversationEmbedding::TYPE_MESSAGE,
            'conversation_id'  => (int) $message->conversation_id,
            'message_id'       => (int) $message->id,
            'topic'            => $topic,
            'importance_score' => $importanceScore,
            'content'          => substr($message->content, 0, 1000),
            'summary'          => substr($message->content, 0, 100),
        ]);
        return $embeddingRecord;
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
        // Get IDs before deleting from MySQL so we can remove from Pinecone too
        $idsToDelete = ConversationEmbedding::where('user_id', $user->id)
            ->where('type', ConversationEmbedding::TYPE_MESSAGE)
            ->where('created_at', '<', now()->subDays($daysToKeep))
            ->where('importance_score', '<', 0.7)
            ->pluck('id')
            ->map(fn($id) => 'emb_' . $id)
            ->toArray();

        $deleted = ConversationEmbedding::where('user_id', $user->id)
            ->where('type', ConversationEmbedding::TYPE_MESSAGE)
            ->where('created_at', '<', now()->subDays($daysToKeep))
            ->where('importance_score', '<', 0.7)
            ->delete();

        // Clean up Pinecone
        if (!empty($idsToDelete)) {
            $this->pinecone->delete($idsToDelete);
        }

        return $deleted;
    }
}
