<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PineconeService
{
    protected ?string $apiKey;
    protected ?string $indexHost;
    protected string $namespace;
    protected bool $enabled;

    public function __construct()
    {
        $this->apiKey    = config('services.pinecone.api_key');
        $this->indexHost = config('services.pinecone.index_host');
        $this->namespace = config('services.pinecone.namespace', 'unipulse');
        $this->enabled   = (bool) config('services.pinecone.enabled', false);
    }

    /**
     * Check if Pinecone is properly configured and enabled.
     */
    public function isAvailable(): bool
    {
        $ok = $this->enabled && !empty($this->apiKey) && !empty($this->indexHost);
        
        if (!$ok) {
            Log::warning('PineconeService: configuration missing or disabled', [
                'enabled' => $this->enabled,
                'has_api_key' => !empty($this->apiKey),
                'has_index_host' => !empty($this->indexHost),
            ]);
        }
        
        return $ok;
    }

    /**
     * Upsert a single vector into Pinecone.
     *
     * @param string $id       Unique vector ID (e.g. "emb_123" or "mem_456")
     * @param array  $vector   The embedding array (e.g. 1536 floats)
     * @param array  $metadata Key-value metadata for filtering (user_id, type, etc.)
     * @return bool
     */
    public function upsert(string $id, array $vector, array $metadata = []): bool
    {
        if (!$this->isAvailable()) {
            return false;
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->connectTimeout(5)
                ->post($this->url('/vectors/upsert'), [
                    'vectors' => [
                        [
                            'id'       => $id,
                            'values'   => $vector,
                            'metadata' => $metadata,
                        ],
                    ],
                    'namespace' => $this->namespace,
                ]);

            if ($response->successful()) {
                Log::debug('PineconeService: upsert successful', ['id' => $id]);
                return true;
            }

            Log::error('PineconeService: upsert failed', [
                'id'     => $id,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('PineconeService: upsert exception', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Queue a Pinecone upsert to run AFTER the HTTP response is sent.
     * This prevents blocking the chat response while writing to Pinecone.
     */
    public function upsertAsync(string $id, array $vector, array $metadata = []): void
    {
        if (!$this->isAvailable()) {
            return;
        }

        // Use Laravel's app terminating callback to run after response
        app()->terminating(function () use ($id, $vector, $metadata) {
            $this->upsert($id, $vector, $metadata);
        });
    }

    /**
     * Batch upsert vectors into Pinecone.
     *
     * @param array $vectors Array of ['id' => string, 'values' => array, 'metadata' => array]
     * @return bool
     */
    public function batchUpsert(array $vectors): bool
    {
        if (!$this->isAvailable() || empty($vectors)) {
            return false;
        }

        try {
            // Pinecone accepts max ~100 vectors per upsert call
            $chunks = array_chunk($vectors, 100);

            foreach ($chunks as $chunk) {
                $response = Http::withHeaders($this->headers())
                    ->timeout(30)
                    ->post($this->url('/vectors/upsert'), [
                        'vectors'   => $chunk,
                        'namespace' => $this->namespace,
                    ]);

                if (!$response->successful()) {
                    Log::error('PineconeService: batch upsert failed', [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                    return false;
                }
            }

            Log::info('PineconeService: batch upsert successful', ['count' => count($vectors)]);
            return true;

        } catch (\Exception $e) {
            Log::error('PineconeService: batch upsert exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Query Pinecone for similar vectors.
     *
     * @param array $vector    The query embedding
     * @param int   $topK      Number of results to return
     * @param array $filter    Metadata filter (e.g. ['user_id' => 5, 'type' => 'message'])
     * @param bool  $includeMetadata Whether to return metadata with results
     * @return array Array of matches: [{id, score, metadata}, ...]
     */
    public function query(array $vector, int $topK = 5, array $filter = [], bool $includeMetadata = true): array
    {
        if (!$this->isAvailable()) {
            return [];
        }

        try {
            $body = [
                'vector'          => $vector,
                'topK'            => $topK,
                'includeMetadata' => $includeMetadata,
                'namespace'       => $this->namespace,
            ];

            if (!empty($filter)) {
                $body['filter'] = $this->buildFilter($filter);
            }

            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->connectTimeout(5)
                ->post($this->url('/query'), $body);

            if ($response->successful()) {
                $matches = $response->json('matches', []);

                Log::debug('PineconeService: query returned results', [
                    'topK'    => $topK,
                    'results' => count($matches),
                ]);

                return $matches;
            }

            Log::error('PineconeService: query failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return [];

        } catch (\Exception $e) {
            Log::error('PineconeService: query exception', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Run multiple Pinecone queries in PARALLEL using Http::pool().
     * Each query gets a named key so results can be identified.
     *
     * @param array $queries Array of ['key' => string, 'vector' => array, 'topK' => int, 'filter' => array]
     * @return array Keyed results: ['key' => [matches], ...]
     */
    public function queryParallel(array $queries): array
    {
        if (!$this->isAvailable() || empty($queries)) {
            return [];
        }

        try {
            $url = $this->url('/query');
            $headers = $this->headers();
            $ns = $this->namespace;

            $responses = Http::pool(function ($pool) use ($queries, $url, $headers, $ns) {
                foreach ($queries as $query) {
                    $body = [
                        'vector'          => $query['vector'],
                        'topK'            => $query['topK'] ?? 5,
                        'includeMetadata' => true,
                        'namespace'       => $ns,
                    ];

                    if (!empty($query['filter'])) {
                        $body['filter'] = $this->buildFilter($query['filter']);
                    }

                    $pool->as($query['key'])
                        ->withHeaders($headers)
                        ->timeout(10)
                        ->connectTimeout(5)
                        ->post($url, $body);
                }
            });

            $results = [];
            foreach ($queries as $query) {
                $key = $query['key'];
                if (isset($responses[$key]) && $responses[$key]->successful()) {
                    $results[$key] = $responses[$key]->json('matches', []);
                } else {
                    $results[$key] = [];
                    if (isset($responses[$key])) {
                        Log::error('PineconeService: parallel query failed', [
                            'key'    => $key,
                            'status' => $responses[$key]->status(),
                        ]);
                    }
                }
            }

            Log::debug('PineconeService: parallel query completed', [
                'queries' => count($queries),
                'results' => array_map('count', $results),
            ]);

            return $results;

        } catch (\Exception $e) {
            Log::error('PineconeService: parallel query exception', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Delete vectors by their IDs.
     *
     * @param array $ids Vector IDs to delete
     * @return bool
     */
    public function delete(array $ids): bool
    {
        if (!$this->isAvailable() || empty($ids)) {
            return false;
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->post($this->url('/vectors/delete'), [
                    'ids'       => $ids,
                    'namespace' => $this->namespace,
                ]);

            if ($response->successful()) {
                Log::debug('PineconeService: deleted vectors', ['count' => count($ids)]);
                return true;
            }

            Log::error('PineconeService: delete failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('PineconeService: delete exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Delete all vectors matching a metadata filter.
     *
     * @param array $filter Metadata filter (e.g. ['conversation_id' => 42])
     * @return bool
     */
    public function deleteByFilter(array $filter): bool
    {
        if (!$this->isAvailable() || empty($filter)) {
            return false;
        }

        try {
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->post($this->url('/vectors/delete'), [
                    'filter'    => $this->buildFilter($filter),
                    'namespace' => $this->namespace,
                ]);

            if ($response->successful()) {
                Log::debug('PineconeService: deleted by filter', ['filter' => $filter]);
                return true;
            }

            Log::error('PineconeService: deleteByFilter failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('PineconeService: deleteByFilter exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Generate embeddings using Pinecone Inference API.
     *
     * @param string|array $input The text or array of texts to embed
     * @param string $model The model name (e.g. "multilingual-e5-large")
     * @return array|null The embedding(s)
     */
    public function embed(string|array $input, string $model = 'multilingual-e5-large'): ?array
    {
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            $isBatch = is_array($input);
            $inputs = $isBatch ? $input : [$input];

            // Inference API is global: api.pinecone.io
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->connectTimeout(5)
                ->post('https://api.pinecone.io/embed', [
                    'model' => $model,
                    'parameters' => [
                        'input_type' => 'query', // recommended for RAG retrieval
                        'truncate' => 'END'
                    ],
                    'inputs' => array_map(fn($t) => ['text' => $t], $inputs)
                ]);

            if ($response->successful()) {
                $data = $response->json('data');
                
                if ($isBatch) {
                    return array_map(fn($d) => $d['values'], $data);
                }
                
                return $data[0]['values'] ?? null;
            }

            Log::error('PineconeService: inference embed failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('PineconeService: inference embed exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Build Pinecone-compatible filter from simple key-value pairs.
     * Converts ['user_id' => 5, 'type' => 'message'] into Pinecone filter format.
     */
    protected function buildFilter(array $filter): array
    {
        $conditions = [];

        foreach ($filter as $key => $value) {
            if (is_array($value)) {
                // Array values use $in operator
                $conditions[$key] = ['$in' => $value];
            } else {
                // Scalar values use $eq operator
                $conditions[$key] = ['$eq' => $value];
            }
        }

        return $conditions;
    }

    /**
     * Build full URL for a Pinecone API endpoint.
     */
    protected function url(string $path): string
    {
        return rtrim($this->indexHost, '/') . $path;
    }

    /**
     * Get request headers for Pinecone API.
     */
    protected function headers(): array
    {
        return [
            'Api-Key'                => $this->apiKey,
            'Content-Type'           => 'application/json',
            'Accept'                 => 'application/json',
            'X-Pinecone-Api-Version' => '2024-10', // Required for inference API endpoints
        ];
    }
}
