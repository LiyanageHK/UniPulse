<?php

namespace App\Services;

use App\Models\User;
use App\Models\Memory;
use App\Models\ConversationEmbedding;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RagRetrievalService
{
    protected EmbeddingService $embeddingService;
    protected PineconeService $pinecone;

    public function __construct(EmbeddingService $embeddingService, PineconeService $pinecone)
    {
        $this->embeddingService = $embeddingService;
        $this->pinecone = $pinecone;
    }

    /**
     * Retrieve relevant context for a user query using RAG.
     *
     * @param User $user The user making the query
     * @param string $query The current user message
     * @param int $topK Number of most relevant chunks to retrieve
     * @param float $minSimilarity Minimum similarity threshold (0.0 - 1.0)
     * @param int|null $currentConversationId Current conversation ID for scoping
     * @param bool $includePastConversations Whether to include other conversations
     * @return Collection Retrieved context chunks with metadata
     */
    /**
     * Retrieve context using a pre-generated embedding (avoids duplicate API calls).
     */
    public function retrieveContextWithEmbedding(
        User $user,
        array $queryEmbedding,
        int $topK = 5,
        float $minSimilarity = 0.5,
        ?int $currentConversationId = null,
        bool $includePastConversations = true
    ): Collection {
        // Try Pinecone first for fast ANN search
        if ($this->pinecone->isAvailable()) {
            return $this->retrieveContextFromPinecone(
                $user, $queryEmbedding, $topK, $minSimilarity,
                $currentConversationId, $includePastConversations
            );
        }

        // Fallback: MySQL-based brute-force cosine similarity
        return $this->retrieveContextFromMySQL(
            $user, $queryEmbedding, $topK, $minSimilarity,
            $currentConversationId, $includePastConversations
        );
    }

    /**
     * Retrieve context from Pinecone using ANN search.
     */
    protected function retrieveContextFromPinecone(
        User $user,
        array $queryEmbedding,
        int $topK,
        float $minSimilarity,
        ?int $currentConversationId,
        bool $includePastConversations
    ): Collection {
        // Build filter for Pinecone query
        $filter = ['user_id' => (int) $user->id];

        // If not including past conversations, restrict to profile + current conversation
        if (!$includePastConversations && $currentConversationId) {
            // Pinecone doesn't directly support OR filters on different fields easily,
            // so we'll query profiles and messages separately
            $profileResults = $this->pinecone->query($queryEmbedding, $topK, [
                'user_id' => (int) $user->id,
                'type'    => ConversationEmbedding::TYPE_PROFILE,
            ]);

            $messageResults = $this->pinecone->query($queryEmbedding, $topK, [
                'user_id'         => (int) $user->id,
                'type'            => ConversationEmbedding::TYPE_MESSAGE,
                'conversation_id' => (int) $currentConversationId,
            ]);

            $matches = array_merge($profileResults, $messageResults);
        } else {
            // Search across all user vectors
            $matches = $this->pinecone->query($queryEmbedding, $topK + 5, $filter);
        }

        if (empty($matches)) {
            Log::info('Pinecone returned no results for user', ['user_id' => $user->id]);
            return collect();
        }

        // Separate profile and message results
        $profileContext = collect($matches)->filter(
            fn($m) => ($m['metadata']['type'] ?? '') === ConversationEmbedding::TYPE_PROFILE
        );

        $messageContext = collect($matches)->filter(
            fn($m) => ($m['metadata']['type'] ?? '') === ConversationEmbedding::TYPE_MESSAGE
                   && ($m['score'] ?? 0) >= $minSimilarity
        )->take($topK - $profileContext->count());

        $allResults = $profileContext->concat($messageContext);

        Log::info('Pinecone RAG retrieval results', [
            'user_id'           => $user->id,
            'profile_included'  => $profileContext->count(),
            'messages_included' => $messageContext->count(),
            'threshold'         => $minSimilarity,
        ]);

        return $allResults->map(fn($match) => [
            'content'    => $match['metadata']['content'] ?? '',
            'summary'    => $match['metadata']['summary'] ?? '',
            'type'       => $match['metadata']['type'] ?? 'message',
            'topic'      => $match['metadata']['topic'] ?? 'general',
            'similarity' => round($match['score'] ?? 0, 3),
            'importance' => $match['metadata']['importance_score'] ?? 0.5,
            'created_at' => now(), // Pinecone doesn't store created_at, use now as fallback
        ])->values();
    }

    /**
     * Fallback: MySQL-based brute-force cosine similarity retrieval.
     */
    protected function retrieveContextFromMySQL(
        User $user,
        array $queryEmbedding,
        int $topK,
        float $minSimilarity,
        ?int $currentConversationId,
        bool $includePastConversations
    ): Collection {
        // Pre-filter in DB: skip low-importance embeddings and scope by conversation if needed
        $embeddings = ConversationEmbedding::where('user_id', $user->id)
            ->where('importance_score', '>', 0.2)
            ->when(!$includePastConversations && $currentConversationId, function ($q) use ($currentConversationId) {
                $q->where(function ($q2) use ($currentConversationId) {
                    $q2->where('type', ConversationEmbedding::TYPE_PROFILE)
                       ->orWhere('conversation_id', $currentConversationId);
                });
            })
            ->orderBy('importance_score', 'desc')
            ->limit(150)
            ->get();

        if ($embeddings->isEmpty()) {
            Log::info('No embeddings found for user after pre-filter', ['user_id' => $user->id]);
            return collect();
        }

        Log::info('Found embeddings for user', [
            'user_id' => $user->id,
            'total' => $embeddings->count(),
            'profile' => $embeddings->where('type', 'profile')->count(),
            'messages' => $embeddings->where('type', 'message')->count(),
        ]);

        // Calculate similarity scores
        $scoredEmbeddings = $embeddings->map(function ($embedding) use ($queryEmbedding) {
            $similarity = $this->embeddingService->cosineSimilarity($queryEmbedding, $embedding->embedding);
            return [
                'embedding' => $embedding,
                'similarity' => $similarity,
                'weighted_score' => $similarity * $embedding->importance_score,
            ];
        });

        $sorted = $scoredEmbeddings->sortByDesc('weighted_score');

        $profileContext = $sorted->filter(fn($item) => $item['embedding']->type === ConversationEmbedding::TYPE_PROFILE);
        $messageContext = $sorted
            ->filter(fn($item) => $item['embedding']->type === ConversationEmbedding::TYPE_MESSAGE)
            ->filter(fn($item) => $item['similarity'] >= $minSimilarity)
            ->take($topK - $profileContext->count());

        $relevantContext = $profileContext->concat($messageContext);

        Log::info('RAG retrieval results', [
            'profile_included' => $profileContext->count(),
            'messages_included' => $messageContext->count(),
            'threshold' => $minSimilarity,
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

    public function retrieveContext(
        User $user,
        string $query,
        int $topK = 5,
        float $minSimilarity = 0.5,
        ?int $currentConversationId = null,
        bool $includePastConversations = true
    ): Collection {
        // 1. Generate embedding for the query
        $queryEmbedding = $this->embeddingService->generateEmbedding($query);

        if (!$queryEmbedding) {
            Log::warning('Failed to generate query embedding for user: ' . $user->id);
            return collect();
        }

        Log::info('Query embedding generated', ['dimensions' => count($queryEmbedding)]);

        // 2. Get all user's embeddings (legacy — use retrieveContextWithEmbedding for performance)
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

        // 2a. Optionally limit message embeddings to the current conversation
        if (!$includePastConversations && $currentConversationId) {
            $embeddings = $embeddings->filter(function ($embedding) use ($currentConversationId) {
                if ($embedding->type === ConversationEmbedding::TYPE_PROFILE) {
                    return true;
                }

                return $embedding->type === ConversationEmbedding::TYPE_MESSAGE
                    && $embedding->conversation_id === $currentConversationId;
            });
        }

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

        // Get top messages above threshold
        $messageContext = $sorted
            ->filter(fn($item) => $item['embedding']->type === ConversationEmbedding::TYPE_MESSAGE)
            ->filter(fn($item) => $item['similarity'] >= $minSimilarity)
            ->take($topK - $profileContext->count()); // Reserve space for profile

        // Combine profile (always) + relevant messages
        $relevantContext = $profileContext->concat($messageContext);

        Log::info('RAG retrieval results', [
            'total_scored' => $scoredEmbeddings->count(),
            'profile_included' => $profileContext->count(),
            'messages_included' => $messageContext->count(),
            'threshold' => $minSimilarity,
            'include_past_conversations' => $includePastConversations,
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
    public function buildContextWindow(Collection $retrievedContext, ?Collection $memories = null, bool $includePastConversations = true): string
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
            $contextParts[] = $includePastConversations
                ? "=== Relevant Past Conversations ==="
                : "=== Current Conversation Highlights ===";
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
     * Retrieve memories using a pre-generated embedding (avoids a second API call).
     */
    public function retrieveMemoriesWithEmbedding(User $user, array $queryEmbedding, int $topK = 10): Collection
    {
        // Try Pinecone first for memory retrieval
        if ($this->pinecone->isAvailable()) {
            $matches = $this->pinecone->query($queryEmbedding, $topK, [
                'user_id' => (int) $user->id,
                'type'    => 'memory',
            ]);

            if (!empty($matches)) {
                // Get memory IDs from Pinecone results and load full Memory models
                $memoryIds = collect($matches)->map(function ($match) {
                    // Vector IDs are 'mem_{id}'
                    return (int) str_replace('mem_', '', $match['id'] ?? '');
                })->filter()->toArray();

                $memories = Memory::whereIn('id', $memoryIds)
                    ->where('user_id', $user->id)
                    ->get();

                foreach ($memories as $memory) {
                    $memory->markAsReferenced();
                }

                Log::info('Pinecone memory retrieval', [
                    'user_id' => $user->id,
                    'matches' => count($matches),
                    'loaded'  => $memories->count(),
                ]);

                // Also include high-importance memories without embeddings
                $noEmbedding = Memory::where('user_id', $user->id)
                    ->whereNull('embedding')
                    ->where('importance_score', '>=', 0.7)
                    ->get();

                return $memories->concat($noEmbedding)->unique('id')->values();
            }
        }

        // Fallback: MySQL-based memory retrieval
        $memories = Memory::where('user_id', $user->id)
            ->where('importance_score', '>', 0.2)
            ->orderBy('importance_score', 'desc')
            ->limit(100)
            ->get();

        if ($memories->isEmpty()) {
            return collect();
        }

        // Memories without embeddings: always include if importance >= 0.7
        $noEmbedding = $memories->filter(fn($m) => empty($m->embedding))->where('importance_score', '>=', 0.7);
        $withEmbedding = $memories->filter(fn($m) => !empty($m->embedding));

        if ($withEmbedding->isEmpty()) {
            return $noEmbedding->values();
        }

        $scored = $this->scoreAndSelectMemories($withEmbedding, $queryEmbedding, $topK, $user->id);
        return $scored->concat($noEmbedding)->unique('id')->values();
    }

    /**
     * Retrieve relevant memories for a query.
     * Uses semantic similarity to find related memories.
     */
    public function retrieveMemories(User $user, string $query, int $topK = 10): Collection
    {
        $queryEmbedding = $this->embeddingService->generateEmbedding($query);

        if (!$queryEmbedding) {
            return collect();
        }

        $memories = Memory::where('user_id', $user->id)
            ->whereNotNull('embedding')
            ->where('importance_score', '>', 0.2)
            ->orderBy('importance_score', 'desc')
            ->limit(100)
            ->get();

        if ($memories->isEmpty()) {
            return collect();
        }

        return $this->scoreAndSelectMemories($memories, $queryEmbedding, $topK, $user->id);
    }

    /**
     * Shared memory scoring and selection logic.
     */
    protected function scoreAndSelectMemories(Collection $memories, array $queryEmbedding, int $topK, int $userId): Collection
    {
        $scoredMemories = $memories->map(function ($memory) use ($queryEmbedding) {
            $similarity = $this->embeddingService->cosineSimilarity($queryEmbedding, $memory->embedding);
            return [
                'memory' => $memory,
                'similarity' => $similarity,
                'weighted_score' => $similarity * $memory->importance_score,
            ];
        });

        // High-importance memories get a lower similarity floor (0.2) rather than no floor.
        // They must still be semantically relevant — not injected unconditionally.
        $importantMemories = $scoredMemories
            ->filter(fn($item) => $item['memory']->importance_score >= 0.8)
            ->filter(fn($item) => $item['similarity'] >= 0.2)
            ->sortByDesc('weighted_score');

        $relevantMemories = $scoredMemories
            ->filter(fn($item) => $item['memory']->importance_score < 0.8)
            ->filter(fn($item) => $item['similarity'] >= 0.4)
            ->sortByDesc('weighted_score')
            ->take(max(0, $topK - $importantMemories->count()));

        $selectedMemories = $importantMemories->concat($relevantMemories);

        foreach ($selectedMemories as $item) {
            $item['memory']->markAsReferenced();
        }

        Log::info('Memory retrieval results', [
            'user_id' => $userId,
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
    public function getSmartContext(
        User $user,
        string $query,
        ?int $currentConversationId = null,
        int $topK = 5,
        bool $includePastConversations = false,
        float $minSimilarity = 0.5
    ): array {
        // Generate embedding ONCE and reuse for both RAG and memory retrieval
        $queryEmbedding = $this->embeddingService->generateEmbedding($query);

        if (!$queryEmbedding) {
            Log::warning('Failed to generate query embedding', ['user_id' => $user->id]);
            return $this->emptyContext();
        }

        $isTrivial = $this->isTrivialMessage($query);

        // ─── PINECONE PARALLEL PATH ───
        // Run profile, message, and memory queries in ONE parallel round-trip
        if ($this->pinecone->isAvailable()) {
            return $this->getSmartContextFromPinecone(
                $user, $queryEmbedding, $query,
                $currentConversationId, $topK, $minSimilarity,
                $includePastConversations, $isTrivial
            );
        }

        // ─── MYSQL FALLBACK PATH ───
        $ragContext = $this->retrieveContextFromMySQL(
            $user, $queryEmbedding, $topK, $minSimilarity,
            $currentConversationId, $includePastConversations
        );

        $memories = collect();
        if (!$isTrivial) {
            $memories = $this->retrieveMemoriesWithEmbedding($user, $queryEmbedding, $includePastConversations ? 10 : 5);
        }

        $contextWindow = $this->buildContextWindow($ragContext, $memories, $includePastConversations);

        return [
            'rag_context' => $contextWindow,
            'recent_context' => '',
            'retrieved_chunks' => $ragContext->count(),
            'memories_count' => $memories->count(),
            'includes_past_conversations' => $includePastConversations,
            'has_profile_data' => $ragContext->contains('type', ConversationEmbedding::TYPE_PROFILE),
        ];
    }

    /**
     * Get smart context using PARALLEL Pinecone queries.
     * Fires profile, message, and memory queries concurrently in one round-trip.
     */
    protected function getSmartContextFromPinecone(
        User $user,
        array $queryEmbedding,
        string $query,
        ?int $currentConversationId,
        int $topK,
        float $minSimilarity,
        bool $includePastConversations,
        bool $isTrivial
    ): array {
        $userId = (int) $user->id;
        $profileCacheKey = "user_profile_context_{$userId}";

        // 1. Try to get profile context from Laravel Cache first
        $profileContext = Cache::remember($profileCacheKey, 600, function () use ($userId, $queryEmbedding) {
            $results = $this->pinecone->query($queryEmbedding, 2, [
                'user_id' => $userId,
                'type'    => ConversationEmbedding::TYPE_PROFILE,
            ]);

            return collect($results)->map(fn($m) => [
                'content'    => $m['metadata']['content'] ?? '',
                'summary'    => $m['metadata']['summary'] ?? '',
                'type'       => ConversationEmbedding::TYPE_PROFILE,
                'topic'      => 'profile',
                'similarity' => round($m['score'] ?? 0, 3),
                'importance' => 1.0,
                'created_at' => now(),
            ])->toArray();
        });

        $profileContext = collect($profileContext);

        // Build remaining queries to run in parallel
        $queries = [
            [
                'key'    => 'messages',
                'vector' => $queryEmbedding,
                'topK'   => $topK + 3,
                'filter' => array_merge(
                    ['user_id' => $userId, 'type' => ConversationEmbedding::TYPE_MESSAGE],
                    (!$includePastConversations && $currentConversationId)
                        ? ['conversation_id' => (int) $currentConversationId]
                        : []
                ),
            ],
        ];

        // Only add memory query for non-trivial messages
        if (!$isTrivial) {
            $queries[] = [
                'key'    => 'memories',
                'vector' => $queryEmbedding,
                'topK'   => $includePastConversations ? 10 : 5,
                'filter' => [
                    'user_id' => $userId,
                    'type'    => 'memory',
                ],
            ];
        }

        // 🚀 Remaining queries run in parallel — single round-trip
        $results = $this->pinecone->queryParallel($queries);

        // Process message results (filter by minSimilarity)
        $messageContext = collect($results['messages'] ?? [])
            ->filter(fn($m) => ($m['score'] ?? 0) >= $minSimilarity)
            ->take($topK)
            ->map(fn($m) => [
                'content'    => $m['metadata']['content'] ?? '',
                'summary'    => $m['metadata']['summary'] ?? '',
                'type'       => ConversationEmbedding::TYPE_MESSAGE,
                'topic'      => $m['metadata']['topic'] ?? 'general',
                'similarity' => round($m['score'] ?? 0, 3),
                'importance' => $m['metadata']['importance_score'] ?? 0.5,
                'created_at' => now(),
            ]);

        $ragContext = $profileContext->concat($messageContext)->values();

        // Process memory results — load full Memory models for context building
        $memories = collect();
        if (!$isTrivial && !empty($results['memories'])) {
            $memoryIds = collect($results['memories'])->map(function ($match) {
                return (int) str_replace('mem_', '', $match['id'] ?? '');
            })->filter()->toArray();

            $memories = Memory::whereIn('id', $memoryIds)
                ->where('user_id', $user->id)
                ->get();

            foreach ($memories as $memory) {
                $memory->markAsReferenced();
            }

            // Also include high-importance memories without embeddings
            $noEmbedding = Memory::where('user_id', $user->id)
                ->whereNull('embedding')
                ->where('importance_score', '>=', 0.7)
                ->get();

            $memories = $memories->concat($noEmbedding)->unique('id')->values();
        }

        Log::info('Pinecone parallel RAG retrieval', [
            'user_id'  => $userId,
            'profile'  => $profileContext->count(),
            'messages' => $messageContext->count(),
            'memories' => $memories->count(),
            'parallel' => true,
        ]);

        $contextWindow = $this->buildContextWindow($ragContext, $memories, $includePastConversations);

        return [
            'rag_context' => $contextWindow,
            'recent_context' => '',
            'retrieved_chunks' => $ragContext->count(),
            'memories_count' => $memories->count(),
            'includes_past_conversations' => $includePastConversations,
            'has_profile_data' => $profileContext->isNotEmpty(),
        ];
    }

    /**
     * Check if a message is trivial (greetings, acknowledgements, filler)
     * and unlikely to benefit from memory retrieval.
     */
    private function isTrivialMessage(string $message): bool
    {
        $lower = strtolower(trim($message));

        // Very short messages — check against known trivial words
        if (strlen($lower) <= 10) {
            $trivialShort = [
                'hi', 'hey', 'hello', 'hiya', 'heya', 'yo',
                'ok', 'okay', 'k', 'yes', 'no', 'yep', 'nope', 'yeah', 'nah',
                'thanks', 'thx', 'ty', 'sure', 'alright',
                'lol', 'haha', 'nice', 'cool', 'great', 'awesome',
                'bye', 'later', 'cya',
            ];

            if (in_array($lower, $trivialShort) || strlen($lower) <= 2) {
                return true;
            }
        }

        // Longer but still trivial phrases (exact match, with optional trailing punctuation)
        $trivialPhrases = [
            'thank you', 'thank u', 'sounds good', 'got it', 'noted',
            'goodbye', 'see you', 'see ya', 'okay thanks', 'ok thanks',
            'ok thank you', 'hehe', 'lmao', 'good morning', 'good night',
        ];

        foreach ($trivialPhrases as $phrase) {
            if ($lower === $phrase || $lower === $phrase . '!' || $lower === $phrase . '.') {
                return true;
            }
        }

        return false;
    }

    /**
     * Empty context result used as a fallback.
     */
    protected function emptyContext(): array
    {
        return [
            'rag_context' => '',
            'recent_context' => '',
            'retrieved_chunks' => 0,
            'memories_count' => 0,
            'includes_past_conversations' => false,
            'has_profile_data' => false,
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
