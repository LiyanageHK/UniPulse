<?php

namespace App\Services;

use App\Models\User;
use App\Models\Memory;
use App\Models\ConversationEmbedding;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RagRetrievalService
{
    protected EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Retrieve relevant context for a user query using RAG.
     * 
     * @param User $user The user making the query
     * @param string $query The current user message
     * @param int $topK Number of most relevant chunks to retrieve
     * @param float $minSimilarity Minimum similarity threshold (0.0 - 1.0)
     * @return Collection Retrieved context chunks with metadata
     */
    public function retrieveContext(User $user, string $query, int $topK = 5, float $minSimilarity = 0.5): Collection
    {
        // 1. Generate embedding for the query
        $queryEmbedding = $this->embeddingService->generateEmbedding($query);

        if (!$queryEmbedding) {
            Log::warning('Failed to generate query embedding for user: ' . $user->id);
            return collect();
        }

        Log::info('Query embedding generated', ['dimensions' => count($queryEmbedding)]);

        // 2. Get all user's embeddings
        $embeddings = ConversationEmbedding::where('user_id', $user->id)->get();

        if ($embeddings->isEmpty()) {
            Log::warning('No embeddings found for user: ' . $user->id);
            return collect();
        }

        Log::info('Found embeddings for user', [
            'user_id' => $user->id,
            'total' => $embeddings->count(),
            'profile' => $embeddings->where('type', 'profile')->count(),
            'messages' => $embeddings->where('type', 'message')->count()
        ]);

        // 3. Calculate similarity scores
        $scoredEmbeddings = $embeddings->map(function ($embedding) use ($queryEmbedding) {
            $similarity = $this->embeddingService->cosineSimilarity(
                $queryEmbedding,
                $embedding->embedding
            );

            return [
                'embedding' => $embedding,
                'similarity' => $similarity,
                'weighted_score' => $similarity * $embedding->importance_score,
            ];
        });

        // Sort by weighted score to get actual top results
        $sorted = $scoredEmbeddings->sortByDesc('weighted_score');

        // 4. ALWAYS include profile data (critical for personalization)
        $profileContext = $sorted->filter(fn($item) => $item['embedding']->type === ConversationEmbedding::TYPE_PROFILE);
        
        // Get top messages above threshold (lowered to 0.3 for better recall)
        $messageContext = $sorted
            ->filter(fn($item) => $item['embedding']->type === ConversationEmbedding::TYPE_MESSAGE)
            ->filter(fn($item) => $item['similarity'] >= 0.3) // Lowered threshold
            ->take($topK - $profileContext->count()); // Reserve space for profile

        // Combine profile (always) + relevant messages
        $relevantContext = $profileContext->concat($messageContext);

        Log::info('RAG retrieval results', [
            'total_scored' => $scoredEmbeddings->count(),
            'profile_included' => $profileContext->count(),
            'messages_included' => $messageContext->count(),
            'threshold' => 0.3,
            'top_3_scores' => $sorted->take(3)->pluck('similarity')->toArray(),
            'profile_score' => $profileContext->first()['similarity'] ?? 'N/A'
        ]);

        return $relevantContext->map(fn($item) => [
            'content' => $item['embedding']->content,
            'summary' => $item['embedding']->summary,
            'type' => $item['embedding']->type,
            'topic' => $item['embedding']->topic,
            'similarity' => round($item['similarity'], 3),
            'importance' => $item['embedding']->importance_score,
            'created_at' => $item['embedding']->created_at,
        ]);
    }

    /**
     * Build context window for LLM prompt.
     * Assembles retrieved chunks and memories into a formatted string.
     */
    public function buildContextWindow(Collection $retrievedContext, ?Collection $memories = null): string
    {
        $contextParts = [];

        // Group by type for better organization
        $profileContext = $retrievedContext->where('type', ConversationEmbedding::TYPE_PROFILE);
        $messageContext = $retrievedContext->where('type', ConversationEmbedding::TYPE_MESSAGE);

        // 1. Add profile information first
        if ($profileContext->isNotEmpty()) {
            $contextParts[] = "=== User Profile ===";
            foreach ($profileContext as $context) {
                $contextParts[] = $context['content'];
            }
            $contextParts[] = "";
        }

        // 2. Add user memories (important facts to remember)
        if ($memories && $memories->isNotEmpty()) {
            $contextParts[] = "=== Important Facts About Student ===";
            
            // Group memories by category
            $memoriesByCategory = $memories->groupBy('category');
            
            foreach ($memoriesByCategory as $category => $categoryMemories) {
                $categoryName = str_replace('_', ' ', ucwords($category));
                $contextParts[] = "\n{$categoryName}:";
                
                foreach ($categoryMemories as $memory) {
                    $contextParts[] = "- {$memory->memory_value}";
                }
            }
            $contextParts[] = "";
        }

        // 3. Add relevant past conversations
        if ($messageContext->isNotEmpty()) {
            $contextParts[] = "=== Relevant Past Conversations ===";
            foreach ($messageContext as $context) {
                $date = $context['created_at']->format('M d, Y');
                $topic = $context['topic'] ?? 'general';
                $contextParts[] = "[{$date} - Topic: {$topic}]";
                $contextParts[] = $context['content'];
                $contextParts[] = "";
            }
        }

        return implode("\n", $contextParts);
    }

    /**
     * Get conversation summary for a specific conversation ID.
     * Useful for including recent conversation context.
     */
    public function getConversationSummary(int $conversationId, int $lastNMessages = 10): string
    {
        $embeddings = ConversationEmbedding::where('conversation_id', $conversationId)
            ->where('type', ConversationEmbedding::TYPE_MESSAGE)
            ->orderBy('created_at', 'desc')
            ->take($lastNMessages)
            ->get()
            ->reverse(); // Chronological order

        if ($embeddings->isEmpty()) {
            return '';
        }

        $summary = ["=== Recent Conversation Summary ==="];
        foreach ($embeddings as $embedding) {
            $summary[] = $embedding->summary ?? substr($embedding->content, 0, 100);
        }

        return implode("\n", $summary);
    }

    /**
     * Retrieve relevant memories for a query.
     * Uses semantic similarity to find related memories.
     */
    public function retrieveMemories(User $user, string $query, int $topK = 10): Collection
    {
        // Generate embedding for the query
        $queryEmbedding = $this->embeddingService->generateEmbedding($query);

        if (!$queryEmbedding) {
            return collect();
        }

        // Get all user memories with embeddings
        $memories = Memory::where('user_id', $user->id)
            ->whereNotNull('embedding')
            ->get();

        if ($memories->isEmpty()) {
            return collect();
        }

        // Calculate similarity scores
        $scoredMemories = $memories->map(function ($memory) use ($queryEmbedding) {
            $similarity = $this->embeddingService->cosineSimilarity(
                $queryEmbedding,
                $memory->embedding
            );

            return [
                'memory' => $memory,
                'similarity' => $similarity,
                'weighted_score' => $similarity * $memory->importance_score,
            ];
        });

        // Always include high-importance memories (>=0.8)
        $importantMemories = $scoredMemories->filter(fn($item) => $item['memory']->importance_score >= 0.8);
        
        // Get other relevant memories above threshold
        $relevantMemories = $scoredMemories
            ->filter(fn($item) => $item['memory']->importance_score < 0.8)
            ->filter(fn($item) => $item['similarity'] >= 0.4)
            ->sortByDesc('weighted_score')
            ->take($topK - $importantMemories->count());

        // Combine and update reference timestamps
        $selectedMemories = $importantMemories->concat($relevantMemories);
        
        foreach ($selectedMemories as $item) {
            $item['memory']->markAsReferenced();
        }

        Log::info('Memory retrieval results', [
            'user_id' => $user->id,
            'total_memories' => $memories->count(),
            'important_included' => $importantMemories->count(),
            'relevant_included' => $relevantMemories->count(),
        ]);

        return $selectedMemories->pluck('memory');
    }

    /**
     * Smart context retrieval with conversation memory.
     * Combines recent conversation context with RAG retrieval and user memories.
     */
    public function getSmartContext(User $user, string $query, ?int $currentConversationId = null, int $topK = 5): array
    {
        // Get RAG context (profile + past conversations)
        $ragContext = $this->retrieveContext($user, $query, $topK);
        
        // Get user memories
        $memories = $this->retrieveMemories($user, $query, 10);
        
        // Build context window with memories
        $contextWindow = $this->buildContextWindow($ragContext, $memories);

        // Get recent conversation context if available
        $recentContext = '';
        if ($currentConversationId) {
            $recentContext = $this->getConversationSummary($currentConversationId);
        }

        return [
            'rag_context' => $contextWindow,
            'recent_context' => $recentContext,
            'retrieved_chunks' => $ragContext->count(),
            'memories_count' => $memories->count(),
            'has_profile_data' => $ragContext->contains('type', ConversationEmbedding::TYPE_PROFILE),
        ];
    }

    /**
     * Estimate token count for context (rough estimation).
     * Helps manage LLM context window limits.
     */
    public function estimateTokenCount(string $text): int
    {
        // Rough estimation: ~4 characters per token for English
        return intval(strlen($text) / 4);
    }

    /**
     * Truncate context to fit within token limit.
     */
    public function truncateContext(string $context, int $maxTokens = 2000): string
    {
        $estimatedTokens = $this->estimateTokenCount($context);
        
        if ($estimatedTokens <= $maxTokens) {
            return $context;
        }

        // Truncate to approximate character count
        $maxChars = $maxTokens * 4;
        return substr($context, 0, $maxChars) . "\n\n[Context truncated...]";
    }
}
