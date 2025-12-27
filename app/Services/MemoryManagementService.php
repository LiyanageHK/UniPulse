<?php

namespace App\Services;

use App\Models\User;
use App\Models\Memory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class MemoryManagementService
{
    protected EmbeddingService $embeddingService;

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Save a memory with deduplication.
     * If similar memory exists, update it instead of creating duplicate.
     */
    public function saveMemory(User $user, array $memoryData): Memory
    {
        // Check for existing similar memories
        $similarMemory = $this->findSimilarMemory(
            $user,
            $memoryData['key'],
            $memoryData['value'],
            $memoryData['category']
        );

        if ($similarMemory) {
            // Update existing memory
            Log::info('Updating existing memory', [
                'memory_id' => $similarMemory->id,
                'old_value' => $similarMemory->memory_value,
                'new_value' => $memoryData['value']
            ]);

            return $this->updateMemory($similarMemory, $memoryData);
        }

        // Create new memory
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
        try {
            $this->generateMemoryEmbedding($memory);
        } catch (\Exception $e) {
            Log::warning('Failed to generate memory embedding', [
                'memory_id' => $memory->id,
                'error' => $e->getMessage()
            ]);
        }

        Log::info('Created new memory', [
            'memory_id' => $memory->id,
            'user_id' => $user->id,
            'category' => $memory->category,
            'key' => $memory->memory_key
        ]);

        return $memory;
    }

    /**
     * Batch save memories from extraction.
     */
    public function batchSaveMemories(User $user, array $memoriesData): Collection
    {
        $savedMemories = collect();

        foreach ($memoriesData as $memoryData) {
            try {
                $memory = $this->saveMemory($user, $memoryData);
                $savedMemories->push($memory);
            } catch (\Exception $e) {
                Log::error('Failed to save memory', [
                    'user_id' => $user->id,
                    'memory_data' => $memoryData,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $savedMemories;
    }

    /**
     * Find similar existing memory using key matching and embedding similarity.
     */
    protected function findSimilarMemory(User $user, string $key, string $value, string $category): ?Memory
    {
        // First, try exact key match in same category
        $exactMatch = Memory::where('user_id', $user->id)
            ->where('memory_key', $key)
            ->where('category', $category)
            ->first();

        if ($exactMatch) {
            return $exactMatch;
        }

        // Second, try semantic similarity using embeddings
        $newEmbedding = $this->embeddingService->generateEmbedding($value);
        
        if (!$newEmbedding) {
            return null;
        }

        $memories = Memory::where('user_id', $user->id)
            ->where('category', $category)
            ->whereNotNull('embedding')
            ->get();

        foreach ($memories as $memory) {
            $similarity = $this->embeddingService->cosineSimilarity(
                $newEmbedding,
                $memory->embedding
            );

            // If very similar (>0.85), consider it the same memory
            if ($similarity > 0.85) {
                Log::info('Found similar memory via embedding', [
                    'existing_memory_id' => $memory->id,
                    'similarity' => $similarity
                ]);
                return $memory;
            }
        }

        return null;
    }

    /**
     * Update an existing memory.
     */
    public function updateMemory(Memory $memory, array $newData): Memory
    {
        $updates = [
            'memory_value' => $newData['value'] ?? $memory->memory_value,
            'importance_score' => $newData['importance'] ?? $memory->importance_score,
        ];

        // Update source if provided
        if (isset($newData['source_message_id'])) {
            $updates['source_message_id'] = $newData['source_message_id'];
        }
        if (isset($newData['source_conversation_id'])) {
            $updates['source_conversation_id'] = $newData['source_conversation_id'];
        }

        // If value changed, regenerate embedding
        if ($updates['memory_value'] !== $memory->memory_value) {
            $updates['embedding'] = null; // Will be regenerated
        }

        $memory->update($updates);

        // Regenerate embedding if value changed
        if (isset($updates['embedding'])) {
            try {
                $this->generateMemoryEmbedding($memory);
            } catch (\Exception $e) {
                Log::warning('Failed to regenerate memory embedding', [
                    'memory_id' => $memory->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $memory->fresh();
    }

    /**
     * Delete a memory (soft delete).
     */
    public function deleteMemory(Memory $memory): bool
    {
        Log::info('Deleting memory', [
            'memory_id' => $memory->id,
            'user_id' => $memory->user_id
        ]);

        return $memory->delete();
    }

    /**
     * Get all memories for a user.
     */
    public function getUserMemories(User $user, ?string $category = null, ?float $minImportance = null): Collection
    {
        $query = Memory::where('user_id', $user->id)
            ->orderBy('importance_score', 'desc')
            ->orderBy('updated_at', 'desc');

        if ($category) {
            $query->where('category', $category);
        }

        if ($minImportance !== null) {
            $query->where('importance_score', '>=', $minImportance);
        }

        return $query->get();
    }

    /**
     * Get memories by category.
     */
    public function getMemoriesByCategory(User $user): array
    {
        $memories = Memory::where('user_id', $user->id)->get();

        $grouped = [];
        foreach (Memory::getCategories() as $category) {
            $grouped[$category] = $memories->where('category', $category)->values();
        }

        return $grouped;
    }

    /**
     * Get important memories (high importance score).
     */
    public function getImportantMemories(User $user, float $minImportance = 0.7): Collection
    {
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
        $embedding = $this->embeddingService->generateEmbedding($memory->memory_value);

        if ($embedding) {
            $memory->update([
                'embedding' => $embedding,
                'embedding_model' => 'text-embedding-3-small',
                'embedding_dimensions' => count($embedding),
            ]);

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
        $queryEmbedding = $this->embeddingService->generateEmbedding($query);

        if (!$queryEmbedding) {
            return collect();
        }

        $memories = Memory::where('user_id', $user->id)
            ->whereNotNull('embedding')
            ->get();

        // Calculate similarity scores
        $scored = $memories->map(function ($memory) use ($queryEmbedding) {
            $similarity = $this->embeddingService->cosineSimilarity(
                $queryEmbedding,
                $memory->embedding
            );

            return [
                'memory' => $memory,
                'similarity' => $similarity,
                'score' => $similarity * $memory->importance_score, // Weighted by importance
            ];
        });

        // Sort by weighted score and take top K
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
        $memories = Memory::where('user_id', $user->id)->get();

        $stats = [
            'total_memories' => $memories->count(),
            'by_category' => [],
            'avg_importance' => round($memories->avg('importance_score'), 2),
            'most_recent' => $memories->sortByDesc('updated_at')->first(),
        ];

        foreach (Memory::getCategories() as $category) {
            $stats['by_category'][$category] = $memories->where('category', $category)->count();
        }

        return $stats;
    }
}
