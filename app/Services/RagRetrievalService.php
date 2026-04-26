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
    // Service responsible for generating embeddings for similarity search.
    protected EmbeddingService $embeddingService;
    // Pinecone service used for vector database retrieval when available.
    protected PineconeService $pinecone;

    // Inject the embedding and Pinecone services.
    public function __construct(EmbeddingService $embeddingService, PineconeService $pinecone)
    {
        // Store the embedding service for later use.
        $this->embeddingService = $embeddingService;
        // Store the Pinecone service for later use.
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
        // Prefer Pinecone when it is available.
        if ($this->pinecone->isAvailable()) {
            // Retrieve context through Pinecone.
            return $this->retrieveContextFromPinecone(
                $user, $queryEmbedding, $topK, $minSimilarity,
                $currentConversationId, $includePastConversations
            );
        }

        // Fallback: MySQL-based brute-force cosine similarity
        // Use MySQL similarity search as a fallback.
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
        // Start the Pinecone filter with the current user ID.
        $filter = ['user_id' => (int) $user->id];

        // If not including past conversations, restrict to profile + current conversation
        // Apply a narrower search scope when past conversations are excluded.
        if (!$includePastConversations && $currentConversationId) {
            // Pinecone doesn't directly support OR filters on different fields easily,
            // so we'll query profiles and messages separately
            // Query profile vectors independently.
            $profileResults = $this->pinecone->query($queryEmbedding, $topK, [
                'user_id' => (int) $user->id,
                'type'    => ConversationEmbedding::TYPE_PROFILE,
            ]);

            // Query current conversation message vectors independently.
            $messageResults = $this->pinecone->query($queryEmbedding, $topK, [
                'user_id'         => (int) $user->id,
                'type'            => ConversationEmbedding::TYPE_MESSAGE,
                'conversation_id' => (int) $currentConversationId,
            ]);

            // Merge the two query result sets.
            $matches = array_merge($profileResults, $messageResults);
        } else {
            // Search across all user vectors
            // Query all vectors for the user when broader context is allowed.
            $matches = $this->pinecone->query($queryEmbedding, $topK + 5, $filter);
        }

        // If Pinecone found nothing, return an empty collection.
        if (empty($matches)) {
            Log::info('Pinecone returned no results for user', ['user_id' => $user->id]);
            return collect();
        }

        // Separate profile and message results
        // Extract profile vectors from the full match list.
        $profileContext = collect($matches)->filter(
            fn($m) => ($m['metadata']['type'] ?? '') === ConversationEmbedding::TYPE_PROFILE
        );

        // Extract message vectors that meet the similarity threshold.
        $messageContext = collect($matches)->filter(
            fn($m) => ($m['metadata']['type'] ?? '') === ConversationEmbedding::TYPE_MESSAGE
                   && ($m['score'] ?? 0) >= $minSimilarity
        )->take($topK - $profileContext->count());

        // Combine profile and message context.
        $allResults = $profileContext->concat($messageContext);

        // Log the Pinecone retrieval summary.
        Log::info('Pinecone RAG retrieval results', [
            'user_id'           => $user->id,
            'profile_included'  => $profileContext->count(),
            'messages_included' => $messageContext->count(),
            'threshold'         => $minSimilarity,
        ]);

        // Format the final results for prompt construction.
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
        // Start a database query for relevant embeddings.
        $embeddings = ConversationEmbedding::where('user_id', $user->id)
            ->where('importance_score', '>', 0.2)
            ->when(!$includePastConversations && $currentConversationId, function ($q) use ($currentConversationId) {
                // Limit to profile plus the current conversation only.
                $q->where(function ($q2) use ($currentConversationId) {
                    $q2->where('type', ConversationEmbedding::TYPE_PROFILE)
                       ->orWhere('conversation_id', $currentConversationId);
                });
            })
            // Prefer more important embeddings first.
            ->orderBy('importance_score', 'desc')
            // Limit the number of embeddings loaded into memory.
            ->limit(150)
            ->get();

        // Return empty collection when no embeddings match the filter.
        if ($embeddings->isEmpty()) {
            Log::info('No embeddings found for user after pre-filter', ['user_id' => $user->id]);
            return collect();
        }

        // Log the number of candidate embeddings loaded.
        Log::info('Found embeddings for user', [
            'user_id' => $user->id,
            'total' => $embeddings->count(),
            'profile' => $embeddings->where('type', 'profile')->count(),
            'messages' => $embeddings->where('type', 'message')->count(),
        ]);

        // Calculate similarity scores
        // Score each embedding against the query embedding.
        $scoredEmbeddings = $embeddings->map(function ($embedding) use ($queryEmbedding) {
            // Compute cosine similarity.
            $similarity = $this->embeddingService->cosineSimilarity($queryEmbedding, $embedding->embedding);
            // Return the embedding plus its scoring metadata.
            return [
                'embedding' => $embedding,
                'similarity' => $similarity,
                'weighted_score' => $similarity * $embedding->importance_score,
            ];
        });

        // Sort by weighted score to prioritize relevance and importance together.
        $sorted = $scoredEmbeddings->sortByDesc('weighted_score');

        // Always include profile embeddings in the retrieved context.
        $profileContext = $sorted->filter(fn($item) => $item['embedding']->type === ConversationEmbedding::TYPE_PROFILE);
        // Filter message embeddings above the similarity threshold.
        $messageContext = $sorted
            ->filter(fn($item) => $item['embedding']->type === ConversationEmbedding::TYPE_MESSAGE)
            ->filter(fn($item) => $item['similarity'] >= $minSimilarity)
            ->take($topK - $profileContext->count());

        // Merge profile and message context.
        $relevantContext = $profileContext->concat($messageContext);

        // Log the MySQL retrieval summary.
        Log::info('RAG retrieval results', [
            'profile_included' => $profileContext->count(),
            'messages_included' => $messageContext->count(),
            'threshold' => $minSimilarity,
        ]);

        // Format the final context records.
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
        // Convert the query into an embedding vector.
        $queryEmbedding = $this->embeddingService->generateEmbedding($query);

        // Stop early if embedding generation fails.
        if (!$queryEmbedding) {
            Log::warning('Failed to generate query embedding for user: ' . $user->id);
            return collect();
        }

        // Log the embedding size.
        Log::info('Query embedding generated', ['dimensions' => count($queryEmbedding)]);

        // 2. Get all user's embeddings (legacy — use retrieveContextWithEmbedding for performance)
        // Load all embeddings for the user in the legacy flow.
        $embeddings = ConversationEmbedding::where('user_id', $user->id)->get();

        // Return empty context if none exist.
        if ($embeddings->isEmpty()) {
            Log::warning('No embeddings found for user: ' . $user->id);
            return collect();
        }

        // Log a summary of loaded embeddings.
        Log::info('Found embeddings for user', [
            'user_id' => $user->id,
            'total' => $embeddings->count(),
            'profile' => $embeddings->where('type', 'profile')->count(),
            'messages' => $embeddings->where('type', 'message')->count()
        ]);

        // 2a. Optionally limit message embeddings to the current conversation
        // Restrict message embeddings to the current conversation when needed.
        if (!$includePastConversations && $currentConversationId) {
            $embeddings = $embeddings->filter(function ($embedding) use ($currentConversationId) {
                // Always keep profile embeddings.
                if ($embedding->type === ConversationEmbedding::TYPE_PROFILE) {
                    return true;
                }

                // Keep only message embeddings from the current conversation.
                return $embedding->type === ConversationEmbedding::TYPE_MESSAGE
                    && $embedding->conversation_id === $currentConversationId;
            });
        }

        // 3. Calculate similarity scores
        // Score each embedding against the query.
        $scoredEmbeddings = $embeddings->map(function ($embedding) use ($queryEmbedding) {
            // Compute the semantic similarity value.
            $similarity = $this->embeddingService->cosineSimilarity(
                $queryEmbedding,
                $embedding->embedding
            );

            // Return the scored embedding data.
            return [
                'embedding' => $embedding,
                'similarity' => $similarity,
                'weighted_score' => $similarity * $embedding->importance_score,
            ];
        });

        // Sort by weighted score to get actual top results
        // Sort all embeddings by combined relevance score.
        $sorted = $scoredEmbeddings->sortByDesc('weighted_score');

        // 4. ALWAYS include profile data (critical for personalization)
        // Always include profile embeddings for personalization.
        $profileContext = $sorted->filter(fn($item) => $item['embedding']->type === ConversationEmbedding::TYPE_PROFILE);

        // Get top messages above threshold
        // Select message embeddings above the similarity threshold.
        $messageContext = $sorted
            ->filter(fn($item) => $item['embedding']->type === ConversationEmbedding::TYPE_MESSAGE)
            ->filter(fn($item) => $item['similarity'] >= $minSimilarity)
            ->take($topK - $profileContext->count()); // Reserve space for profile

        // Combine profile (always) + relevant messages
        // Merge profile and message results into one context list.
        $relevantContext = $profileContext->concat($messageContext);

        // Log the retrieval summary and top scores.
        Log::info('RAG retrieval results', [
            'total_scored' => $scoredEmbeddings->count(),
            'profile_included' => $profileContext->count(),
            'messages_included' => $messageContext->count(),
            'threshold' => $minSimilarity,
            'include_past_conversations' => $includePastConversations,
            'top_3_scores' => $sorted->take(3)->pluck('similarity')->toArray(),
            'profile_score' => $profileContext->first()['similarity'] ?? 'N/A'
        ]);

        // Format the context objects for prompt construction.
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
        // Prepare the parts of the final context window.
        $contextParts = [];

        // Group by type for better organization
        // Separate profile and message content.
        $profileContext = $retrievedContext->where('type', ConversationEmbedding::TYPE_PROFILE);
        // Filter only message-based context.
        $messageContext = $retrievedContext->where('type', ConversationEmbedding::TYPE_MESSAGE);

        // 1. Add profile information first
        // Insert profile context at the top when available.
        if ($profileContext->isNotEmpty()) {
            $contextParts[] = "=== User Profile ===";
            foreach ($profileContext as $context) {
                // Add each profile snippet.
                $contextParts[] = $context['content'];
            }
            $contextParts[] = "";
        }

        // 2. Add user memories (important facts to remember)
        // Append stored memories if provided.
        if ($memories && $memories->isNotEmpty()) {
            $contextParts[] = "=== Important Facts About Student ===";

            // Group memories by category
            // Organize memories by their category.
            $memoriesByCategory = $memories->groupBy('category');

            // Build category sections for memories.
            foreach ($memoriesByCategory as $category => $categoryMemories) {
                $categoryName = str_replace('_', ' ', ucwords($category));
                $contextParts[] = "\n{$categoryName}:";

                // Add each memory line under its category.
                foreach ($categoryMemories as $memory) {
                    $contextParts[] = "- {$memory->memory_value}";
                }
            }
            $contextParts[] = "";
        }

        // 3. Add relevant past conversations
        // Append message context when available.
        if ($messageContext->isNotEmpty()) {
            // Choose the correct section title based on scope.
            $contextParts[] = $includePastConversations
                ? "=== Relevant Past Conversations ==="
                : "=== Current Conversation Highlights ===";
            // Add each relevant conversation snippet.
            foreach ($messageContext as $context) {
                $date = $context['created_at']->format('M d, Y');
                $topic = $context['topic'] ?? 'general';
                $contextParts[] = "[{$date} - Topic: {$topic}]";
                $contextParts[] = $context['content'];
                $contextParts[] = "";
            }
        }

        // Combine all parts into one prompt context string.
        return implode("\n", $contextParts);
    }

    /**
     * Get conversation summary for a specific conversation ID.
     * Useful for including recent conversation context.
     */
    public function getConversationSummary(int $conversationId, int $lastNMessages = 10): string
    {
        // Load recent conversation embeddings for the specified conversation.
        $embeddings = ConversationEmbedding::where('conversation_id', $conversationId)
            ->where('type', ConversationEmbedding::TYPE_MESSAGE)
            ->orderBy('created_at', 'desc')
            ->take($lastNMessages)
            ->get()
            ->reverse(); // Chronological order

        // Return an empty string if no messages exist.
        if ($embeddings->isEmpty()) {
            return '';
        }

        // Initialize the summary output.
        $summary = ["=== Recent Conversation Summary ==="];
        // Append each embedding summary or a short content fallback.
        foreach ($embeddings as $embedding) {
            $summary[] = $embedding->summary ?? substr($embedding->content, 0, 100);
        }

        // Return the formatted summary.
        return implode("\n", $summary);
    }

    /**
     * Retrieve memories using a pre-generated embedding (avoids a second API call).
     */
    public function retrieveMemoriesWithEmbedding(User $user, array $queryEmbedding, int $topK = 10): Collection
    {
        // Try Pinecone first for memory retrieval
        // Use Pinecone if vector search is available.
        if ($this->pinecone->isAvailable()) {
            // Query Pinecone for memory vectors.
            $matches = $this->pinecone->query($queryEmbedding, $topK, [
                'user_id' => (int) $user->id,
                'type'    => 'memory',
            ]);

            // If Pinecone returns matches, load the corresponding Memory records.
            if (!empty($matches)) {
                // Get memory IDs from Pinecone results and load full Memory models
                // Convert vector IDs back to database IDs.
                $memoryIds = collect($matches)->map(function ($match) {
                    // Vector IDs are 'mem_{id}'
                    // Strip the prefix to obtain the numeric memory ID.
                    return (int) str_replace('mem_', '', $match['id'] ?? '');
                })->filter()->toArray();

                // Load matching memory rows owned by the user.
                $memories = Memory::whereIn('id', $memoryIds)
                    ->where('user_id', $user->id)
                    ->get();

                // Mark each returned memory as referenced.
                foreach ($memories as $memory) {
                    $memory->markAsReferenced();
                }

                // Log Pinecone memory retrieval results.
                Log::info('Pinecone memory retrieval', [
                    'user_id' => $user->id,
                    'matches' => count($matches),
                    'loaded'  => $memories->count(),
                ]);

                // Also include high-importance memories without embeddings
                // Include highly important memories even if they lack embeddings.
                $noEmbedding = Memory::where('user_id', $user->id)
                    ->whereNull('embedding')
                    ->where('importance_score', '>=', 0.7)
                    ->get();

                // Combine and deduplicate the memory collections.
                return $memories->concat($noEmbedding)->unique('id')->values();
            }
        }

        // Fallback: MySQL-based memory retrieval
        // Load important memories directly from the database.
        $memories = Memory::where('user_id', $user->id)
            ->where('importance_score', '>', 0.2)
            ->orderBy('importance_score', 'desc')
            ->limit(100)
            ->get();

        // Return empty collection if no memories are found.
        if ($memories->isEmpty()) {
            return collect();
        }

        // Memories without embeddings: always include if importance >= 0.7
        // Keep highly important memories even without embedding vectors.
        $noEmbedding = $memories->filter(fn($m) => empty($m->embedding))->where('importance_score', '>=', 0.7);
        // Keep the memories that have embeddings for semantic scoring.
        $withEmbedding = $memories->filter(fn($m) => !empty($m->embedding));

        // If none have embeddings, return the non-embedded important memories.
        if ($withEmbedding->isEmpty()) {
            return $noEmbedding->values();
        }

        // Score and select the most relevant memories.
        $scored = $this->scoreAndSelectMemories($withEmbedding, $queryEmbedding, $topK, $user->id);
        // Combine scored memories with the high-importance non-embedded ones.
        return $scored->concat($noEmbedding)->unique('id')->values();
    }

    /**
     * Retrieve relevant memories for a query.
     * Uses semantic similarity to find related memories.
     */
    public function retrieveMemories(User $user, string $query, int $topK = 10): Collection
    {
        // Generate an embedding for the user's query.
        $queryEmbedding = $this->embeddingService->generateEmbedding($query);

        // Return empty results if embedding generation fails.
        if (!$queryEmbedding) {
            return collect();
        }

        // Load candidate memories with embeddings.
        $memories = Memory::where('user_id', $user->id)
            ->whereNotNull('embedding')
            ->where('importance_score', '>', 0.2)
            ->orderBy('importance_score', 'desc')
            ->limit(100)
            ->get();

        // Return empty results if the user has no memories.
        if ($memories->isEmpty()) {
            return collect();
        }

        // Score and return the most relevant memories.
        return $this->scoreAndSelectMemories($memories, $queryEmbedding, $topK, $user->id);
    }

    /**
     * Shared memory scoring and selection logic.
     */
    protected function scoreAndSelectMemories(Collection $memories, array $queryEmbedding, int $topK, int $userId): Collection
    {
        // Calculate similarity for each memory.
        $scoredMemories = $memories->map(function ($memory) use ($queryEmbedding) {
            // Compare the query embedding with the memory embedding.
            $similarity = $this->embeddingService->cosineSimilarity($queryEmbedding, $memory->embedding);
            // Return the scored memory record.
            return [
                'memory' => $memory,
                'similarity' => $similarity,
                'weighted_score' => $similarity * $memory->importance_score,
            ];
        });

        // High-importance memories get a lower similarity floor (0.2) rather than no floor.
        // They must still be semantically relevant — not injected unconditionally.
        // Prioritize highly important memories that still meet a minimal relevance floor.
        $importantMemories = $scoredMemories
            ->filter(fn($item) => $item['memory']->importance_score >= 0.8)
            ->filter(fn($item) => $item['similarity'] >= 0.2)
            ->sortByDesc('weighted_score');

        // Select the remaining relevant memories below the high-importance threshold.
        $relevantMemories = $scoredMemories
            ->filter(fn($item) => $item['memory']->importance_score < 0.8)
            ->filter(fn($item) => $item['similarity'] >= 0.4)
            ->sortByDesc('weighted_score')
            ->take(max(0, $topK - $importantMemories->count()));

        // Combine important and relevant memories.
        $selectedMemories = $importantMemories->concat($relevantMemories);

        // Mark each selected memory as referenced.
        foreach ($selectedMemories as $item) {
            $item['memory']->markAsReferenced();
        }

        // Log the retrieval summary for auditing.
        Log::info('Memory retrieval results', [
            'user_id' => $userId,
            'total_memories' => $memories->count(),
            'important_included' => $importantMemories->count(),
            'relevant_included' => $relevantMemories->count(),
        ]);

        // Return only the memory models.
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
        // Create a single embedding for both retrieval paths.
        $queryEmbedding = $this->embeddingService->generateEmbedding($query);

        // Return an empty context if embedding generation fails.
        if (!$queryEmbedding) {
            Log::warning('Failed to generate query embedding', ['user_id' => $user->id]);
            return $this->emptyContext();
        }

        // Detect whether the message is too trivial for memory lookup.
        $isTrivial = $this->isTrivialMessage($query);

        // ─── PINECONE PARALLEL PATH ───
        // Run profile, message, and memory queries in ONE parallel round-trip
        // Use the Pinecone-optimized flow when available.
        if ($this->pinecone->isAvailable()) {
            return $this->getSmartContextFromPinecone(
                $user, $queryEmbedding, $query,
                $currentConversationId, $topK, $minSimilarity,
                $includePastConversations, $isTrivial
            );
        }

        // ─── MYSQL FALLBACK PATH ───
        // Use MySQL retrieval when Pinecone is unavailable.
        $ragContext = $this->retrieveContextFromMySQL(
            $user, $queryEmbedding, $topK, $minSimilarity,
            $currentConversationId, $includePastConversations
        );

        // Start with an empty memory collection.
        $memories = collect();
        // Skip memory retrieval for trivial messages.
        if (!$isTrivial) {
            // Retrieve memories using the shared query embedding.
            $memories = $this->retrieveMemoriesWithEmbedding($user, $queryEmbedding, $includePastConversations ? 10 : 5);
        }

        // Build the final prompt-ready context window.
        $contextWindow = $this->buildContextWindow($ragContext, $memories, $includePastConversations);

        // Return the assembled smart context data.
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
        // Convert the user ID to an integer for filtering.
        $userId = (int) $user->id;
        // Build a cache key for profile context retrieval.
        $profileCacheKey = "user_profile_context_{$userId}";

        // 1. Try to get profile context from Laravel Cache first
        // Fetch cached profile context or query Pinecone if missing.
        $profileContext = Cache::remember($profileCacheKey, 600, function () use ($userId, $queryEmbedding) {
            // Query Pinecone for profile vectors only.
            $results = $this->pinecone->query($queryEmbedding, 2, [
                'user_id' => $userId,
                'type'    => ConversationEmbedding::TYPE_PROFILE,
            ]);

            // Convert Pinecone results into a prompt-friendly array.
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

        // Wrap cached profile results in a collection.
        $profileContext = collect($profileContext);

        // Build remaining queries to run in parallel
        // Prepare Pinecone query definitions for the remaining data.
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
        // Add memory retrieval only when the message is meaningful.
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
        // Execute the remaining Pinecone queries in parallel.
        $results = $this->pinecone->queryParallel($queries);

        // Process message results (filter by minSimilarity)
        // Filter and format message matches from Pinecone.
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

        // Combine profile and message context.
        $ragContext = $profileContext->concat($messageContext)->values();

        // Process memory results — load full Memory models for context building
        // Load memory records from the Pinecone result IDs.
        $memories = collect();
        if (!$isTrivial && !empty($results['memories'])) {
            // Convert vector IDs back into memory IDs.
            $memoryIds = collect($results['memories'])->map(function ($match) {
                return (int) str_replace('mem_', '', $match['id'] ?? '');
            })->filter()->toArray();

            // Load the matching memory models from the database.
            $memories = Memory::whereIn('id', $memoryIds)
                ->where('user_id', $user->id)
                ->get();

            // Mark loaded memories as referenced.
            foreach ($memories as $memory) {
                $memory->markAsReferenced();
            }

            // Also include high-importance memories without embeddings
            // Add important memories that do not have embeddings.
            $noEmbedding = Memory::where('user_id', $user->id)
                ->whereNull('embedding')
                ->where('importance_score', '>=', 0.7)
                ->get();

            // Merge and deduplicate the memory list.
            $memories = $memories->concat($noEmbedding)->unique('id')->values();
        }

        // Log the parallel Pinecone retrieval summary.
        Log::info('Pinecone parallel RAG retrieval', [
            'user_id'  => $userId,
            'profile'  => $profileContext->count(),
            'messages' => $messageContext->count(),
            'memories' => $memories->count(),
            'parallel' => true,
        ]);

        // Build the final context window from Pinecone results.
        $contextWindow = $this->buildContextWindow($ragContext, $memories, $includePastConversations);

        // Return the assembled Pinecone smart context payload.
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
        // Normalize the message for matching.
        $lower = strtolower(trim($message));

        // Very short messages — check against known trivial words
        // Consider very short greetings and acknowledgements as trivial.
        if (strlen($lower) <= 10) {
            // List of short trivial messages.
            $trivialShort = [
                'hi', 'hey', 'hello', 'hiya', 'heya', 'yo',
                'ok', 'okay', 'k', 'yes', 'no', 'yep', 'nope', 'yeah', 'nah',
                'thanks', 'thx', 'ty', 'sure', 'alright',
                'lol', 'haha', 'nice', 'cool', 'great', 'awesome',
                'bye', 'later', 'cya',
            ];

            // Return true for exact short trivial matches.
            if (in_array($lower, $trivialShort) || strlen($lower) <= 2) {
                return true;
            }
        }

        // Longer but still trivial phrases (exact match, with optional trailing punctuation)
        // Define a list of phrases that are still too trivial to require retrieval.
        $trivialPhrases = [
            'thank you', 'thank u', 'sounds good', 'got it', 'noted',
            'goodbye', 'see you', 'see ya', 'okay thanks', 'ok thanks',
            'ok thank you', 'hehe', 'lmao', 'good morning', 'good night',
        ];

        // Check for exact phrase matches.
        foreach ($trivialPhrases as $phrase) {
            if ($lower === $phrase || $lower === $phrase . '!' || $lower === $phrase . '.') {
                return true;
            }
        }

        // Treat everything else as non-trivial.
        return false;
    }

    /**
     * Empty context result used as a fallback.
     */
    protected function emptyContext(): array
    {
        // Return a fully empty context structure.
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
        // Estimate token count from character length.
        return intval(strlen($text) / 4);
    }

    /**
     * Truncate context to fit within token limit.
     */
    public function truncateContext(string $context, int $maxTokens = 2000): string
    {
        // Estimate how many tokens the context uses.
        $estimatedTokens = $this->estimateTokenCount($context);

        // Return the context unchanged when it already fits.
        if ($estimatedTokens <= $maxTokens) {
            return $context;
        }

        // Truncate to approximate character count
        // Convert the token budget into a rough character budget.
        $maxChars = $maxTokens * 4;
        // Return the shortened context with a truncation notice.
        return substr($context, 0, $maxChars) . "\n\n[Context truncated...]";
    }
}
