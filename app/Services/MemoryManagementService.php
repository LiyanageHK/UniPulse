<?php

namespace App\Services;

use App\Models\User;
use App\Models\Memory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class MemoryManagementService
{
    // Service used to generate embeddings for memory records.
    protected EmbeddingService $embeddingService;
    // Pinecone service used for vector cleanup and sync.
    protected PineconeService $pinecone;

    // Inject dependencies needed for memory management.
    public function __construct(EmbeddingService $embeddingService, PineconeService $pinecone)
    {
        // Store the embedding service instance.
        $this->embeddingService = $embeddingService;
        // Store the Pinecone service instance.
        $this->pinecone = $pinecone;
    }

    /**
     * Save a memory with deduplication.
     * If similar memory exists, update it instead of creating duplicate.
     */
    public function saveMemory(User $user, array $memoryData): Memory
    {
        // Check for existing similar memories
        // Look for an existing memory that matches this key and category.
        $similarMemory = $this->findSimilarMemory(
            $user,
            $memoryData['key'],
            $memoryData['value'],
            $memoryData['category']
        );

        // If a matching memory exists, update it instead of creating a duplicate.
        if ($similarMemory) {
            // Update existing memory
            // Log that an existing memory is being updated.
            Log::info('Updating existing memory', [
                'memory_id' => $similarMemory->id,
                'old_value' => $similarMemory->memory_value,
                'new_value' => $memoryData['value']
            ]);

            // Return the updated memory record.
            return $this->updateMemory($similarMemory, $memoryData);
        }

        // Create new memory
        // Insert a new memory record into the database.
        $memory = Memory::create([
            'user_id' => $user->id,
            'category' => $memoryData['category'],
            'memory_key' => $memoryData['key'],
            'memory_value' => $memoryData['value'],
            'importance_score' => $memoryData['importance'] ?? 0.5,
            'source_conversation_id' => $memoryData['source_conversation_id'] ?? null,
            'source_message_id' => $memoryData['source_message_id'] ?? null,
        ]);

        // Generate embedding asynchronously (in production, use queue)
        // Try to generate and store the memory embedding.
        try {
            $this->generateMemoryEmbedding($memory);
        } catch (\Exception $e) {
            // Log embedding generation failures without stopping the save flow.
            Log::warning('Failed to generate memory embedding', [
                'memory_id' => $memory->id,
                'error' => $e->getMessage()
            ]);
        }

        // Log the new memory creation.
        Log::info('Created new memory', [
            'memory_id' => $memory->id,
            'user_id' => $user->id,
            'category' => $memory->category,
            'key' => $memory->memory_key
        ]);

        // Return the newly created memory.
        return $memory;
    }

    /**
     * Batch save memories from extraction.
     */
    public function batchSaveMemories(User $user, array $memoriesData): Collection
    {
        // Prepare an empty collection for saved memories.
        $savedMemories = collect();

        // Save each memory one by one.
        foreach ($memoriesData as $memoryData) {
            try {
                // Save the memory using the single-memory flow.
                $memory = $this->saveMemory($user, $memoryData);
                // Add the saved memory to the collection.
                $savedMemories->push($memory);
            } catch (\Exception $e) {
                // Log any failure for the current memory item.
                Log::error('Failed to save memory', [
                    'user_id' => $user->id,
                    'memory_data' => $memoryData,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Return all successfully saved memories.
        return $savedMemories;
    }

    /**
     * Find similar existing memory using key matching and embedding similarity.
     */
    protected function findSimilarMemory(User $user, string $key, string $value, string $category): ?Memory
    {
        // Only deduplicate on exact key + category match (same fact being restated).
        // Never merge different memories just because embeddings are similar.
        // Search for an exact duplicate by user, key, and category.
        $exactMatch = Memory::where('user_id', $user->id)
            ->where('memory_key', $key)
            ->where('category', $category)
            ->first();

        // Return the matching memory if one exists.
        return $exactMatch;
    }

    /**
     * Update an existing memory.
     */
    public function updateMemory(Memory $memory, array $newData): Memory
    {
        // Prepare the fields that may be updated.
        $updates = [
            'memory_value' => $newData['value'] ?? $memory->memory_value,
            'importance_score' => $newData['importance'] ?? $memory->importance_score,
        ];

        // Update source if provided
        // Replace the source message ID when provided.
        if (isset($newData['source_message_id'])) {
            $updates['source_message_id'] = $newData['source_message_id'];
        }
        // Replace the source conversation ID when provided.
        if (isset($newData['source_conversation_id'])) {
            $updates['source_conversation_id'] = $newData['source_conversation_id'];
        }

        // If value changed, regenerate embedding
        // Mark the embedding as stale if the memory value changed.
        if ($updates['memory_value'] !== $memory->memory_value) {
            $updates['embedding'] = null; // Will be regenerated
        }

        // Save the updated fields to the database.
        $memory->update($updates);

        // Regenerate embedding if value changed
        // Rebuild the embedding only when the value was changed.
        if (isset($updates['embedding'])) {
            try {
                // Generate a new embedding for the updated memory.
                $this->generateMemoryEmbedding($memory);
            } catch (\Exception $e) {
                // Log embedding regeneration issues without failing the update.
                Log::warning('Failed to regenerate memory embedding', [
                    'memory_id' => $memory->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Return the latest version of the memory.
        return $memory->fresh();
    }

    /**
     * Delete a memory (hard delete).
     */
    public function deleteMemory(Memory $memory): bool
    {
        // Log the memory deletion request.
        Log::info('Deleting memory', [
            'memory_id' => $memory->id,
            'user_id' => $memory->user_id
        ]);

        // Clean up Pinecone vector
        // Delete the vector record from Pinecone before removing the memory.
        $this->pinecone->delete(['mem_' . $memory->id]);

        // Permanently remove the memory from the database.
        return $memory->forceDelete();
    }

    /**
     * Get all memories for a user.
     */
    public function getUserMemories(User $user, ?string $category = null, ?float $minImportance = null): Collection
    {
        // Start a query for all memories belonging to the user.
        $query = Memory::where('user_id', $user->id)
            ->orderBy('importance_score', 'desc')
            ->orderBy('updated_at', 'desc');

        // Apply category filtering when requested.
        if ($category) {
            $query->where('category', $category);
        }

        // Apply minimum importance filtering when provided.
        if ($minImportance !== null) {
            $query->where('importance_score', '>=', $minImportance);
        }

        // Return the filtered memory collection.
        return $query->get();
    }

    /**
     * Get memories by category.
     */
    public function getMemoriesByCategory(User $user): array
    {
        // Load all memories for the user.
        $memories = Memory::where('user_id', $user->id)->get();

        // Prepare grouped results by category.
        $grouped = [];
        // Create a bucket for each known memory category.
        foreach (Memory::getCategories() as $category) {
            // Filter memories for the current category.
            $grouped[$category] = $memories->where('category', $category)->values();
        }

        // Return memories grouped by category.
        return $grouped;
    }

    /**
     * Get important memories (high importance score).
     */
    public function getImportantMemories(User $user, float $minImportance = 0.7): Collection
    {
        // Return memories at or above the importance threshold.
        return Memory::where('user_id', $user->id)
            ->where('importance_score', '>=', $minImportance)
            ->orderBy('importance_score', 'desc')
            ->get();
    }

    /**
     * Generate embedding for a memory.
     */
    protected function generateMemoryEmbedding(Memory $memory): void
    {
        // Generate an embedding from the memory value.
        $embedding = $this->embeddingService->generateEmbedding($memory->memory_value);

        // If an embedding was created, store it on the memory.
        if ($embedding) {
            $memory->update([
                'embedding' => $embedding,
                'embedding_model' => 'text-embedding-3-small',
                'embedding_dimensions' => count($embedding),
            ]);

            // Sync write to Pinecone (memories are small — no need to defer)
            // Upsert the memory vector into Pinecone for semantic search.
            $this->pinecone->upsert('mem_' . $memory->id, $embedding, [
                'user_id'          => (int) $memory->user_id,
                'type'             => 'memory',
                'category'         => $memory->category,
                'memory_key'       => $memory->memory_key,
                'importance_score' => $memory->importance_score,
                'content'          => substr($memory->memory_value, 0, 1000),
            ]);

            // Log successful embedding generation.
            Log::info('Generated embedding for memory', [
                'memory_id' => $memory->id,
                'dimensions' => count($embedding)
            ]);
        }
    }

    /**
     * Search memories by text query using semantic similarity.
     */
    public function searchMemories(User $user, string $query, int $topK = 5): Collection
    {
        // Generate an embedding for the search query.
        $queryEmbedding = $this->embeddingService->generateEmbedding($query);

        // Return empty results if query embedding generation fails.
        if (!$queryEmbedding) {
            return collect();
        }

        // Load candidate memories that already have embeddings.
        $memories = Memory::where('user_id', $user->id)
            ->whereNotNull('embedding')
            ->get();

        // Calculate similarity scores
        // Score each memory using cosine similarity.
        $scored = $memories->map(function ($memory) use ($queryEmbedding) {
            // Compute semantic similarity between query and memory.
            $similarity = $this->embeddingService->cosineSimilarity(
                $queryEmbedding,
                $memory->embedding
            );

            // Return the memory with similarity and weighted score.
            return [
                'memory' => $memory,
                'similarity' => $similarity,
                'score' => $similarity * $memory->importance_score, // Weighted by importance
            ];
        });

        // Sort by weighted score and take top K
        // Sort memories by relevance score and return the top results.
        return $scored
            ->sortByDesc('score')
            ->take($topK)
            ->pluck('memory');
    }

    /**
     * Get memory statistics for a user.
     */
    public function getMemoryStats(User $user): array
    {
        // Load all memories for the user.
        $memories = Memory::where('user_id', $user->id)->get();

        // Prepare the statistics structure.
        $stats = [
            'total_memories' => $memories->count(),
            'by_category' => [],
            'avg_importance' => round($memories->avg('importance_score'), 2),
            'most_recent' => $memories->sortByDesc('updated_at')->first(),
        ];

        // Count memories for each supported category.
        foreach (Memory::getCategories() as $category) {
            $stats['by_category'][$category] = $memories->where('category', $category)->count();
        }

        // Return the compiled statistics.
        return $stats;
    }
}
