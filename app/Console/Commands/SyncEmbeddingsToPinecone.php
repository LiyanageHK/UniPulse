<?php

namespace App\Console\Commands;

use App\Models\ConversationEmbedding;
use App\Models\Memory;
use App\Services\PineconeService;
use Illuminate\Console\Command;

class SyncEmbeddingsToPinecone extends Command
{
    protected $signature = 'pinecone:sync
                            {--embeddings : Sync conversation embeddings only}
                            {--memories : Sync memory embeddings only}
                            {--user= : Sync for a specific user ID only}
                            {--re-embed : Regenerate embeddings using the current provider instead of stored ones}';

    protected $description = 'Backfill existing MySQL embeddings into Pinecone';

    public function handle(PineconeService $pinecone, \App\Services\EmbeddingService $embeddingService): int
    {
        if (!$pinecone->isAvailable()) {
            $this->error('Pinecone is not configured or not enabled. Check PINECONE_ENABLED, PINECONE_API_KEY, and PINECONE_INDEX_HOST in .env');
            return self::FAILURE;
        }

        $syncEmbeddings = !$this->option('memories');  // default: sync embeddings unless --memories-only
        $syncMemories   = !$this->option('embeddings'); // default: sync memories unless --embeddings-only
        $userId         = $this->option('user');

        if ($syncEmbeddings) {
            $this->syncConversationEmbeddings($pinecone, $embeddingService, $userId);
        }

        if ($syncMemories) {
            $this->syncMemoryEmbeddings($pinecone, $embeddingService, $userId);
        }

        $this->newLine();
        $this->info('✅ Pinecone sync complete!');

        return self::SUCCESS;
    }

    /**
     * Sync conversation embeddings to Pinecone.
     */
    protected function syncConversationEmbeddings(PineconeService $pinecone, \App\Services\EmbeddingService $embService, ?string $userId): void
    {
        $this->info('📦 Syncing conversation embeddings to Pinecone...');

        $query = ConversationEmbedding::whereNotNull('embedding');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $total = $query->count();

        if ($total === 0) {
            $this->warn('  No conversation embeddings found to sync.');
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $synced = 0;
        $failed = 0;

        $reEmbed = $this->option('re-embed');
        $dimensions = $embService->getDimensions();

        $query->chunk(50, function ($embeddings) use ($pinecone, $embService, $reEmbed, $dimensions, &$synced, &$failed, $bar) {
            $vectors = [];

            foreach ($embeddings as $emb) {
                $embedding = $emb->embedding;

                if ($reEmbed) {
                    $text = $emb->content ?: $emb->summary;
                    $embedding = $embService->generateEmbedding($text);
                    if ($embedding) {
                        $emb->update(['embedding' => $embedding, 'dimensions' => $dimensions]);
                    }
                }

                if (empty($embedding) || !is_array($embedding) || count($embedding) !== $dimensions) {
                    $failed++;
                    $bar->advance();
                    continue;
                }

                $metadata = [
                    'user_id'         => (int) $emb->user_id,
                    'type'            => $emb->type,
                    'topic'           => $emb->topic ?? 'general',
                    'importance_score' => (float) $emb->importance_score,
                    'content'         => substr($emb->content ?? '', 0, 1000),
                    'summary'         => substr($emb->summary ?? '', 0, 200),
                ];

                if ($emb->conversation_id) {
                    $metadata['conversation_id'] = (int) $emb->conversation_id;
                }
                if ($emb->message_id) {
                    $metadata['message_id'] = (int) $emb->message_id;
                }

                $vectors[] = [
                    'id'       => 'emb_' . $emb->id,
                    'values'   => $embedding,
                    'metadata' => $metadata,
                ];
            }

            if (!empty($vectors)) {
                $success = $pinecone->batchUpsert($vectors);
                if ($success) {
                    $synced += count($vectors);
                } else {
                    $failed += count($vectors);
                }
            }

            $bar->advance(count($vectors) + ($failed > 0 ? 0 : 0));
        });

        $bar->finish();
        $this->newLine();
        $this->info("  ✓ Embeddings synced: {$synced} | Failed: {$failed}");
    }

    /**
     * Sync memory embeddings to Pinecone.
     */
    protected function syncMemoryEmbeddings(PineconeService $pinecone, \App\Services\EmbeddingService $embService, ?string $userId): void
    {
        $this->info('🧠 Syncing memory embeddings to Pinecone...');

        $query = Memory::whereNotNull('embedding');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $total = $query->count();

        if ($total === 0) {
            $this->warn('  No memory embeddings found to sync.');
            return;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $synced = 0;
        $failed = 0;

        $reEmbed = $this->option('re-embed');
        $dimensions = $embService->getDimensions();

        $query->chunk(50, function ($memories) use ($pinecone, $embService, $reEmbed, $dimensions, &$synced, &$failed, $bar) {
            $vectors = [];

            foreach ($memories as $memory) {
                $embedding = $memory->embedding;

                if ($reEmbed) {
                    $embedding = $embService->generateEmbedding($memory->memory_value);
                    if ($embedding) {
                        $memory->update(['embedding' => $embedding]);
                    }
                }

                if (empty($embedding) || !is_array($embedding) || count($embedding) !== $dimensions) {
                    $failed++;
                    $bar->advance();
                    continue;
                }

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

            if (!empty($vectors)) {
                $success = $pinecone->batchUpsert($vectors);
                if ($success) {
                    $synced += count($vectors);
                } else {
                    $failed += count($vectors);
                }
            }

            $bar->advance(count($vectors));
        });

        $bar->finish();
        $this->newLine();
        $this->info("  ✓ Memories synced: {$synced} | Failed: {$failed}");
    }
}
