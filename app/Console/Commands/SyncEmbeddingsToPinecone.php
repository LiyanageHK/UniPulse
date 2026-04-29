<?php

namespace App\Console\Commands;

use App\Models\ConversationEmbedding;
use App\Models\Memory;
use App\Services\PineconeService;
use Illuminate\Console\Command;

class SyncEmbeddingsToPinecone extends Command
{
    // Artisan command signature and available options for syncing embeddings.
    protected $signature = 'pinecone:sync
                            {--embeddings : Sync conversation embeddings only}
                            {--memories : Sync memory embeddings only}
                            {--user= : Sync for a specific user ID only}
                            {--re-embed : Regenerate embeddings using the current provider instead of stored ones}';

    // Description shown in the Artisan command list.
    protected $description = 'Backfill existing MySQL embeddings into Pinecone';

    // Execute the sync process for Pinecone.
    public function handle(PineconeService $pinecone, \App\Services\EmbeddingService $embeddingService): int
    {
        // Stop if Pinecone is not available.
        if (!$pinecone->isAvailable()) {
            // Show an error message to the console.
            $this->error('Pinecone is not configured or not enabled. Check PINECONE_ENABLED, PINECONE_API_KEY, and PINECONE_INDEX_HOST in .env');
            // Return failure status.
            return self::FAILURE;
        }

        // Determine whether conversation embeddings should be synced.
        $syncEmbeddings = !$this->option('memories');  // default: sync embeddings unless --memories-only
        // Determine whether memory embeddings should be synced.
        $syncMemories   = !$this->option('embeddings'); // default: sync memories unless --embeddings-only
        // Read an optional user filter from the command line.
        $userId         = $this->option('user');

        // Sync conversation embeddings when enabled.
        if ($syncEmbeddings) {
            $this->syncConversationEmbeddings($pinecone, $embeddingService, $userId);
        }

        // Sync memory embeddings when enabled.
        if ($syncMemories) {
            $this->syncMemoryEmbeddings($pinecone, $embeddingService, $userId);
        }

        // Add a blank line for cleaner console output.
        $this->newLine();
        // Show completion message.
        $this->info('✅ Pinecone sync complete!');

        // Return success status.
        return self::SUCCESS;
    }

    /**
     * Sync conversation embeddings to Pinecone.
     */
    protected function syncConversationEmbeddings(PineconeService $pinecone, \App\Services\EmbeddingService $embService, ?string $userId): void
    {
        // Inform the user that conversation embeddings are being synced.
        $this->info('📦 Syncing conversation embeddings to Pinecone...');

        // Build the query for embeddings that already exist in MySQL.
        $query = ConversationEmbedding::whereNotNull('embedding');

        // Restrict to a specific user when requested.
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Count all conversation embeddings that match the filters.
        $total = $query->count();

        // Exit early if there is nothing to sync.
        if ($total === 0) {
            // Tell the user no records were found.
            $this->warn('  No conversation embeddings found to sync.');
            return;
        }

        // Create a progress bar for the sync process.
        $bar = $this->output->createProgressBar($total);
        // Start the progress bar.
        $bar->start();

        // Track successful sync operations.
        $synced = 0;
        // Track failed sync operations.
        $failed = 0;

        // Check whether embeddings should be regenerated before syncing.
        $reEmbed = $this->option('re-embed');
        // Get the expected embedding dimensions for validation.
        $dimensions = $embService->getDimensions();

        // Process the embeddings in chunks to reduce memory usage.
        $query->chunk(50, function ($embeddings) use ($pinecone, $embService, $reEmbed, $dimensions, &$synced, &$failed, $bar) {
            // Prepare a batch vector list for Pinecone.
            $vectors = [];

            // Loop through each embedding in the chunk.
            foreach ($embeddings as $emb) {
                // Read the stored embedding vector.
                $embedding = $emb->embedding;

                // Regenerate the embedding when requested.
                if ($reEmbed) {
                    // Choose the best text source for re-embedding.
                    $text = $emb->content ?: $emb->summary;
                    // Generate a fresh embedding.
                    $embedding = $embService->generateEmbedding($text);
                    // Save the regenerated embedding and dimensions locally.
                    if ($embedding) {
                        $emb->update(['embedding' => $embedding, 'dimensions' => $dimensions]);
                    }
                }

                // Skip invalid or mismatched embeddings.
                if (empty($embedding) || !is_array($embedding) || count($embedding) !== $dimensions) {
                    // Count this item as failed.
                    $failed++;
                    // Advance the progress bar even when skipping.
                    $bar->advance();
                    continue;
                }

                // Prepare Pinecone metadata for this embedding.
                $metadata = [
                    'user_id'         => (int) $emb->user_id,
                    'type'            => $emb->type,
                    'topic'           => $emb->topic ?? 'general',
                    'importance_score' => (float) $emb->importance_score,
                    'content'         => substr($emb->content ?? '', 0, 1000),
                    'summary'         => substr($emb->summary ?? '', 0, 200),
                ];

                // Include conversation ID when available.
                if ($emb->conversation_id) {
                    $metadata['conversation_id'] = (int) $emb->conversation_id;
                }
                // Include message ID when available.
                if ($emb->message_id) {
                    $metadata['message_id'] = (int) $emb->message_id;
                }

                // Build the vector payload for Pinecone.
                $vectors[] = [
                    'id'       => 'emb_' . $emb->id,
                    'values'   => $embedding,
                    'metadata' => $metadata,
                ];
            }

            // Upsert the batch to Pinecone when vectors exist.
            if (!empty($vectors)) {
                // Submit the batch upload request.
                $success = $pinecone->batchUpsert($vectors);
                if ($success) {
                    // Count all vectors in the batch as synced.
                    $synced += count($vectors);
                } else {
                    // Count all vectors in the batch as failed.
                    $failed += count($vectors);
                }
            }

            // Advance the progress bar by the processed count.
            $bar->advance(count($vectors) + ($failed > 0 ? 0 : 0));
        });

        // Finish the progress bar.
        $bar->finish();
        // Add spacing after the progress bar.
        $this->newLine();
        // Print the sync summary.
        $this->info("  ✓ Embeddings synced: {$synced} | Failed: {$failed}");
    }

    /**
     * Sync memory embeddings to Pinecone.
     */
    protected function syncMemoryEmbeddings(PineconeService $pinecone, \App\Services\EmbeddingService $embService, ?string $userId): void
    {
        // Inform the user that memory embeddings are being synced.
        $this->info('🧠 Syncing memory embeddings to Pinecone...');

        // Build the query for stored memory embeddings.
        $query = Memory::whereNotNull('embedding');

        // Restrict the sync to one user when requested.
        if ($userId) {
            $query->where('user_id', $userId);
        }

        // Count the total memory embeddings to sync.
        $total = $query->count();

        // Exit early if there are no memory embeddings.
        if ($total === 0) {
            // Tell the user no records were found.
            $this->warn('  No memory embeddings found to sync.');
            return;
        }

        // Create a progress bar for memory sync.
        $bar = $this->output->createProgressBar($total);
        // Start the progress bar.
        $bar->start();

        // Track successful memory syncs.
        $synced = 0;
        // Track failed memory syncs.
        $failed = 0;

        // Check whether re-embedding is requested.
        $reEmbed = $this->option('re-embed');
        // Get the expected dimensions for validation.
        $dimensions = $embService->getDimensions();

        // Process memory records in chunks.
        $query->chunk(50, function ($memories) use ($pinecone, $embService, $reEmbed, $dimensions, &$synced, &$failed, $bar) {
            // Prepare vectors for this chunk.
            $vectors = [];

            // Loop through each memory record.
            foreach ($memories as $memory) {
                // Read the stored memory embedding.
                $embedding = $memory->embedding;

                // Regenerate memory embeddings when requested.
                if ($reEmbed) {
                    // Generate a fresh embedding from the memory text.
                    $embedding = $embService->generateEmbedding($memory->memory_value);
                    // Save the regenerated vector locally.
                    if ($embedding) {
                        $memory->update(['embedding' => $embedding]);
                    }
                }

                // Skip invalid or mismatched vectors.
                if (empty($embedding) || !is_array($embedding) || count($embedding) !== $dimensions) {
                    // Count this record as failed.
                    $failed++;
                    // Advance the progress bar for skipped items.
                    $bar->advance();
                    continue;
                }

                // Build the Pinecone vector payload for the memory.
                $vectors[] = [
                    'id'       => 'mem_' . $memory->id,
                    'values'   => $embedding,
                    'metadata' => [
                        'user_id'          => (int) $memory->user_id,
                        'type'             => 'memory',
                        'category'         => $memory->category,
                        'memory_key'       => $memory->memory_key,
                        'importance_score' => (float) $memory->importance_score,
                        'content'          => substr($memory->memory_value ?? '', 0, 1000),
                    ],
                ];
            }

            // Upsert the memory batch if there are vectors to send.
            if (!empty($vectors)) {
                // Submit the Pinecone batch upload.
                $success = $pinecone->batchUpsert($vectors);
                if ($success) {
                    // Count all vectors in the batch as synced.
                    $synced += count($vectors);
                } else {
                    // Count all vectors in the batch as failed.
                    $failed += count($vectors);
                }
            }

            // Advance the bar by the number of vectors processed.
            $bar->advance(count($vectors));
        });

        // Finish the progress display.
        $bar->finish();
        // Add spacing after the progress bar.
        $this->newLine();
        // Print the memory sync summary.
        $this->info("  ✓ Memories synced: {$synced} | Failed: {$failed}");
    }
}
