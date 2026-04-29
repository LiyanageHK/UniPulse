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
    // Service used to generate embeddings for profiles and conversations.
    protected EmbeddingService $embeddingService;
    // Pinecone service used for vector storage and deletion.
    protected PineconeService $pinecone;

    // Inject embedding and Pinecone services.
    public function __construct(EmbeddingService $embeddingService, PineconeService $pinecone)
    {
        // Store the embedding service instance.
        $this->embeddingService = $embeddingService;
        // Store the Pinecone service instance.
        $this->pinecone = $pinecone;
    }

    /**
     * Build or update knowledge base for a user.
     * This includes user profile data and past conversations.
     */
    public function buildUserKnowledgeBase(User $user): void
    {
        // 1. Create/Update profile embedding
        // Build or refresh the user's profile embedding.
        $this->createProfileEmbedding($user);

        // 2. Create embeddings for past conversations
        // Build embeddings for the user's previous conversations.
        $this->createConversationEmbeddings($user);
    }

    /**
     * Create embedding for user profile data.
     */
    protected function createProfileEmbedding(User $user): void
    {
        // Build profile text from user data
        // Turn user fields into readable profile text.
        $profileText = $this->buildProfileText($user);

        // Stop if there is nothing to embed.
        if (empty($profileText)) {
            return;
        }

        // Generate embedding
        // Create a semantic vector for the profile text.
        $embedding = $this->embeddingService->generateEmbedding($profileText);

        // Stop if embedding generation fails.
        if (!$embedding) {
            Log::warning('Failed to generate profile embedding for user: ' . $user->id);
            return;
        }

        // Delete old profile embedding (MySQL + Pinecone)
        // Gather Pinecone vector IDs for the old profile embedding.
        $oldProfileIds = ConversationEmbedding::where('user_id', $user->id)
            ->where('type', ConversationEmbedding::TYPE_PROFILE)
            ->pluck('id')
            ->map(fn($id) => 'emb_' . $id)
            ->toArray();

        // Remove old profile embeddings from MySQL.
        ConversationEmbedding::where('user_id', $user->id)
            ->where('type', ConversationEmbedding::TYPE_PROFILE)
            ->delete();

        // Remove old profile vectors from Pinecone when present.
        if (!empty($oldProfileIds)) {
            $this->pinecone->delete($oldProfileIds);
        }

        // Create new profile embedding
        // Save the refreshed profile embedding record.
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
        // Upsert the profile vector into Pinecone asynchronously.
        $this->pinecone->upsertAsync('emb_' . $embeddingRecord->id, $embedding, [
            'user_id'          => $user->id,
            'type'             => ConversationEmbedding::TYPE_PROFILE,
            'topic'            => 'profile',
            'importance_score' => 1.0,
            'content'          => substr($profileText, 0, 1000),
            'summary'          => 'User profile information',
        ]);

        // Invalidate profile context cache
        // Clear cached profile context so new data is used.
        Cache::forget("user_profile_context_{$user->id}");
    }

    /**
     * Build readable profile text from user data.
     */
    protected function buildProfileText(User $user): string
    {
        // Prepare pieces of profile information.
        $parts = [];

        // Basic info
        // Add the user's name when available.
        if ($user->name) {
            $parts[] = "Name: {$user->name}";
        }

        // Academic info
        // Add university information when present.
        if ($user->university) {
            $parts[] = "University: {$user->university}";
        }
        // Add faculty information when present.
        if ($user->faculty) {
            $parts[] = "Faculty: {$user->faculty}";
        }
        // Add A/L stream information when present.
        if ($user->al_stream) {
            $parts[] = "A/L Stream: {$user->al_stream}";
        }

        // Learning preferences — translate to readable text
        // Convert stored learning preferences into a readable sentence.
        if ($user->learning_style) {
            $raw = $user->learning_style;
            if (is_string($raw)) {
                // Try decoding JSON-formatted learning style data.
                $decoded = json_decode($raw, true);
                // Fall back to the raw string if decoding fails.
                $raw = is_array($decoded) ? $decoded : [$raw];
            }
            // Join multiple learning style values into text.
            $style = implode(', ', (array) $raw);
            $parts[] = "Prefers {$style} learning";
        }

        // Confidence in university transition (1–5 scale)
        // Translate transition confidence into a natural phrase.
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
        // Add the user's preferred social setting.
        if ($user->social_preference) {
            $parts[] = "Comfortable in {$user->social_preference} social settings";
        }

        // Group work comfort (1–5 scale)
        // Summarize comfort with group work.
        if ($user->group_work_comfort) {
            $gwc = (int) $user->group_work_comfort;
            $gwcLabel = $gwc >= 4 ? 'enjoys group work' : ($gwc <= 2 ? 'prefers working alone' : 'neutral about group work');
            $parts[] = ucfirst($gwcLabel);
        }

        // Self-reported stress level (text from onboarding: Low/Moderate/High)
        // Include the onboarding stress level if available.
        if ($user->stress_level) {
            $parts[] = "Self-reported stress level at onboarding: {$user->stress_level}";
        }

        // Interests & goals
        // Add the primary motivator when present.
        if ($user->primary_motivator) {
            $parts[] = "Motivated by: {$user->primary_motivator}";
        }
        // Translate goal clarity into a readable description.
        if ($user->goal_clarity) {
            $gc = (int) $user->goal_clarity;
            $gcLabel = $gc >= 4 ? 'has clear goals' : ($gc <= 2 ? 'feels unclear about goals' : 'has somewhat clear goals');
            $parts[] = ucfirst($gcLabel);
        }
        // Append interests when stored.
        if ($user->interests) {
            $interests = is_array($user->interests) ? implode(', ', $user->interests) : $user->interests;
            $parts[] = "Interests: {$interests}";
        }
        // Append hobbies when stored.
        if ($user->hobbies) {
            $hobbies = is_array($user->hobbies) ? implode(', ', $user->hobbies) : $user->hobbies;
            $parts[] = "Hobbies: {$hobbies}";
        }

        // Wellbeing signals — translate numbers to readable context
        // Convert overwhelm score into natural language.
        if ($user->overwhelm_level) {
            $ov = (int) $user->overwhelm_level;
            $ovLabel = match(true) {
                $ov >= 4 => 'often feels overwhelmed',
                $ov == 3 => 'sometimes feels overwhelmed',
                default  => 'rarely feels overwhelmed',
            };
            $parts[] = ucfirst($ovLabel);
        }
        // Add peer-connection struggles when relevant.
        if ($user->peer_struggle) {
            $ps = (int) $user->peer_struggle;
            if ($ps >= 3) {
                $parts[] = 'Struggles to connect with peers';
            }
        }
        // Add preferred support channels when present.
        if ($user->preferred_support_types) {
            $support = is_array($user->preferred_support_types) ? implode(', ', $user->preferred_support_types) : $user->preferred_support_types;
            $parts[] = "Prefers support via: {$support}";
        }

        // Join all profile parts into one readable paragraph.
        $profileText = implode('. ', $parts) . '.';

        // Append latest weekly check-in summary if available
        // Add the check-in context when present.
        $checkinContext = $this->buildCheckinContext($user);
        if ($checkinContext) {
            $profileText .= "\n\n" . $checkinContext;
        }

        // Return the full profile text.
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
        // Look up the most recent WeeklyChecking record.
        $checkin = \App\Models\WeeklyChecking::where('user_id', $user->id)
            ->latest()
            ->first();

        // Fallback to the older WeeklyCheckin model when needed.
        if (!$checkin) {
            // Fall back to WeeklyCheckin
            // Look up the simpler fallback check-in model.
            $checkin = \App\Models\WeeklyCheckin::where('user_id', $user->id)
                ->latest()
                ->first();

            // Return no context if neither model has data.
            if (!$checkin) {
                return '';
            }

            // Basic summary from the simpler model
            // Read the mood score from the fallback model.
            $mood = $checkin->mood ?? null;
            // Return empty if the fallback mood is missing.
            if (!$mood) return '';
            // Convert the numeric mood into a label.
            $moodLabel = $mood <= 2 ? 'low' : ($mood >= 4 ? 'good' : 'okay');
            // Return a one-line summary of the latest check-in.
            return "Latest weekly check-in: mood is {$moodLabel}.";
        }

        // Use the scoring methods from WeeklyChecking
        // Compute detailed area scores.
        $scores  = $checkin->calculateAreaScores();
        // Collect notable wellbeing signals.
        $signals = [];

        // Only surface signals that are meaningfully elevated or low — not all scores
        // Determine stress-related risk level.
        $stressRisk = \App\Models\WeeklyChecking::determineRisk('stress', $scores['stress']);
        if ($stressRisk !== 'Low') {
            $signals[] = $stressRisk === 'High'
                ? 'showing high stress and anxiety this week'
                : 'experiencing moderate stress this week';
        }

            // Determine depression-related risk level.
        $deprRisk = \App\Models\WeeklyChecking::determineRisk('depression', $scores['depression']);
        if ($deprRisk !== 'Low') {
            $signals[] = $deprRisk === 'High'
                ? 'mood has been quite low recently'
                : 'mood has been a bit low recently';
        }

            // Determine disengagement risk level.
        $disengRisk = \App\Models\WeeklyChecking::determineRisk('disengagement', $scores['disengagement']);
        if ($disengRisk !== 'Low') {
            $signals[] = $disengRisk === 'High'
                ? 'feeling emotionally drained and disengaged'
                : 'feeling somewhat burnt out';
        }

            // Determine social isolation risk level.
        $socialRisk = \App\Models\WeeklyChecking::determineRisk('social_isolation', $scores['social_isolation']);
        if ($socialRisk !== 'Low') {
            $signals[] = $socialRisk === 'High'
                ? 'feeling socially isolated'
                : 'feeling somewhat disconnected from peers';
        }

        // Overall mood from the check-in (1–5 scale)
            // Convert the overall mood score into a phrase.
        if ($checkin->overall_mood) {
            $mood = (int) $checkin->overall_mood;
            $moodLabel = match(true) {
                $mood <= 2 => 'reported feeling quite low overall',
                $mood == 3 => 'reported feeling okay overall',
                default    => 'reported feeling reasonably well overall',
            };
            $signals[] = $moodLabel;
        }

        // Return a calm message if no meaningful signals were found.
        if (empty($signals)) {
            return "Latest weekly check-in: no significant concerns flagged.";
        }

        // Build a natural language summary from the signals.
        $checkinDate = $checkin->created_at->diffForHumans();
        // Pop the final signal for sentence construction.
        $last = array_pop($signals);
        // Join the remaining signals into a readable phrase.
        $signalText = empty($signals)
            ? $last
            : implode(', ', $signals) . ', and ' . $last;
        // Return a human-friendly check-in summary.
        return "Latest weekly check-in ({$checkinDate}): student is {$signalText}.";
    }

    /**
     * Create embeddings for user's past conversations.
     */
    protected function createConversationEmbeddings(User $user): void
    {
        // Get all user messages from past conversations
        // Load all user-authored messages that are not yet embedded.
        $messages = Message::where('user_id', $user->id)
            ->where('role', 'user') // Only user messages, not AI responses
            ->whereDoesntHave('embeddings') // Not already embedded
            ->with('conversation')
            ->get();

        // Create embeddings for each message.
        foreach ($messages as $message) {
            $this->createMessageEmbedding($message);
        }
    }

    /**
     * Create embedding for a single message.
     */
    public function createMessageEmbedding(Message $message): ?ConversationEmbedding
    {
        // Skip empty message content.
        if (empty(trim($message->content))) {
            return null;
        }

        // Generate embedding
        // Produce a vector embedding for the message text.
        $embedding = $this->embeddingService->generateEmbedding($message->content);

        // Abort if embedding generation fails.
        if (!$embedding) {
            Log::warning('Failed to generate message embedding for message: ' . $message->id);
            return null;
        }

        // Extract topic and keywords
        // Use the conversation title as the topic label.
        $topic = $message->conversation->title ?? 'general';
        // Extract keywords from the message content.
        $keywords = $this->extractKeywords($message->content);

        // Calculate importance score based on length, recency, and crisis flags
        // Compute the importance of this message embedding.
        $importanceScore = $this->calculateImportanceScore($message);

        // Save the message embedding to the database.
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
        // Push the message vector to Pinecone as well.
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
        // Return the saved embedding record.
        return $embeddingRecord;
    }

    /**
     * Extract keywords from text (simple implementation).
     */
    protected function extractKeywords(string $text): array
    {
        // Remove common words and extract meaningful terms
        // Define stop words to filter out.
        $commonWords = ['the', 'is', 'at', 'which', 'on', 'a', 'an', 'and', 'or', 'but', 'in', 'with', 'to', 'for', 'of', 'as', 'by', 'that', 'this', 'it', 'from', 'i', 'me', 'my', 'you', 'your'];
        
        // Extract word tokens from the text.
        $words = str_word_count(strtolower($text), 1);
        // Remove stop words and very short tokens.
        $words = array_filter($words, fn($word) => !in_array($word, $commonWords) && strlen($word) > 3);
        
        // Count keyword frequencies.
        $wordFreq = array_count_values($words);
        // Sort keywords by frequency.
        arsort($wordFreq);
        
        // Return the top 10 keywords.
        return array_slice(array_keys($wordFreq), 0, 10); // Top 10 keywords
    }

    /**
     * Calculate importance score for a message.
     */
    protected function calculateImportanceScore(Message $message): float
    {
        // Start with a baseline score.
        $score = 0.5; // Base score

        // Recent messages are more important
        // Measure how old the message is.
        $daysSinceCreation = now()->diffInDays($message->created_at);
        // Increase importance for recent messages.
        if ($daysSinceCreation < 7) {
            $score += 0.3;
        } elseif ($daysSinceCreation < 30) {
            $score += 0.2;
        }

        // Messages with crisis flags are very important
        // Boost importance when crisis flags exist.
        if ($message->has_crisis_flags) {
            $score += 0.4;
        }

        // Longer messages might be more detailed/important
        // Count words to estimate message detail.
        $wordCount = str_word_count($message->content);
        // Give a small boost to long, detailed messages.
        if ($wordCount > 50) {
            $score += 0.1;
        }

        // Cap the score at 1.0.
        return min($score, 1.0); // Cap at 1.0
    }

    /**
     * Clean up old embeddings to save space.
     */
    public function cleanupOldEmbeddings(User $user, int $daysToKeep = 90): int
    {
        // Get IDs before deleting from MySQL so we can remove from Pinecone too
        // Collect Pinecone IDs for old, low-importance embeddings.
        $idsToDelete = ConversationEmbedding::where('user_id', $user->id)
            ->where('type', ConversationEmbedding::TYPE_MESSAGE)
            ->where('created_at', '<', now()->subDays($daysToKeep))
            ->where('importance_score', '<', 0.7)
            ->pluck('id')
            ->map(fn($id) => 'emb_' . $id)
            ->toArray();

        // Delete the matching embeddings from MySQL.
        $deleted = ConversationEmbedding::where('user_id', $user->id)
            ->where('type', ConversationEmbedding::TYPE_MESSAGE)
            ->where('created_at', '<', now()->subDays($daysToKeep))
            ->where('importance_score', '<', 0.7)
            ->delete();

        // Clean up Pinecone
        // Remove the old vectors from Pinecone when available.
        if (!empty($idsToDelete)) {
            $this->pinecone->delete($idsToDelete);
        }

        // Return how many rows were deleted from MySQL.
        return $deleted;
    }
}
