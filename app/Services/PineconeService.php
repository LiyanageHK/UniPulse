<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PineconeService
{
    // Pinecone API key used for authentication.
    protected ?string $apiKey;
    // Host URL for the Pinecone index.
    protected ?string $indexHost;
    // Namespace used to separate UniPulse vectors.
    protected string $namespace;
    // Indicates whether Pinecone is enabled in configuration.
    protected bool $enabled;

    // Load Pinecone configuration on service construction.
    public function __construct()
    {
        // Read the Pinecone API key from configuration.
        $this->apiKey    = config('services.pinecone.api_key');
        // Read the Pinecone index host from configuration.
        $this->indexHost = config('services.pinecone.index_host');
        // Read the Pinecone namespace, defaulting to unipulse.
        $this->namespace = config('services.pinecone.namespace', 'unipulse');
        // Read whether Pinecone integration is enabled.
        $this->enabled   = (bool) config('services.pinecone.enabled', false);
    }

    /**
     * Check if Pinecone is properly configured and enabled.
     */
    public function isAvailable(): bool
    {
        // Confirm that Pinecone has all required configuration values.
        $ok = $this->enabled && !empty($this->apiKey) && !empty($this->indexHost);
        
        // Log a warning if Pinecone cannot be used.
        if (!$ok) {
            Log::warning('PineconeService: configuration missing or disabled', [
                'enabled' => $this->enabled,
                'has_api_key' => !empty($this->apiKey),
                'has_index_host' => !empty($this->indexHost),
            ]);
        }
        
        // Return whether Pinecone is usable.
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
        // Do not continue if Pinecone is unavailable.
        if (!$this->isAvailable()) {
            return false;
        }

        try {
            // Send a vector upsert request to Pinecone.
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
                    // Log a failed upsert response.
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
            // Log unexpected upsert exceptions.
            Log::error('PineconeService: upsert exception', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            // Return failure when an exception occurs.
            return false;
        }
    }

    /**
     * Queue a Pinecone upsert to run AFTER the HTTP response is sent.
     * This prevents blocking the chat response while writing to Pinecone.
     */
    public function upsertAsync(string $id, array $vector, array $metadata = []): void
    {
        // Stop immediately if Pinecone is not available.
        if (!$this->isAvailable()) {
            return;
        }

        // Use Laravel's app terminating callback to run after response
        // Schedule the upsert to run after the HTTP response is sent.
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
        // Stop if Pinecone is unavailable or there are no vectors.
        if (!$this->isAvailable() || empty($vectors)) {
            return false;
        }

        try {
            // Pinecone accepts max ~100 vectors per upsert call
            // Split vectors into chunks of 100 for batch upload.
            $chunks = array_chunk($vectors, 100);

            // Send each chunk to Pinecone.
            foreach ($chunks as $chunk) {
                $response = Http::withHeaders($this->headers())
                    ->timeout(30)
                    ->post($this->url('/vectors/upsert'), [
                        'vectors'   => $chunk,
                        'namespace' => $this->namespace,
                    ]);

                if (!$response->successful()) {
                    // Log failure for the current batch chunk.
                    Log::error('PineconeService: batch upsert failed', [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                    return false;
                }
            }

            // Log that the batch upsert succeeded.
            Log::info('PineconeService: batch upsert successful', ['count' => count($vectors)]);
            // Return success after all chunks are written.
            return true;

        } catch (\Exception $e) {
            // Log any exception raised during batch upsert.
            Log::error('PineconeService: batch upsert exception', ['error' => $e->getMessage()]);
            // Return failure on exception.
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
        // Return an empty array if Pinecone cannot be used.
        if (!$this->isAvailable()) {
            return [];
        }

        try {
            // Prepare the Pinecone query body.
            $body = [
                'vector'          => $vector,
                'topK'            => $topK,
                'includeMetadata' => $includeMetadata,
                'namespace'       => $this->namespace,
            ];

            // Add a metadata filter when provided.
            if (!empty($filter)) {
                $body['filter'] = $this->buildFilter($filter);
            }

            // Send the query request to Pinecone.
            $response = Http::withHeaders($this->headers())
                ->timeout(10)
                ->connectTimeout(5)
                ->post($this->url('/query'), $body);

            // Return matches when the request succeeds.
            if ($response->successful()) {
                $matches = $response->json('matches', []);

                // Log the query summary.
                Log::debug('PineconeService: query returned results', [
                    'topK'    => $topK,
                    'results' => count($matches),
                ]);

                // Return the match list.
                return $matches;
            }

            // Log failed query responses.
            Log::error('PineconeService: query failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            // Return an empty array on failure.
            return [];

        } catch (\Exception $e) {
            // Log query exceptions.
            Log::error('PineconeService: query exception', ['error' => $e->getMessage()]);
            // Return an empty array on exception.
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
        // Return an empty array if Pinecone is unavailable or no queries were provided.
        if (!$this->isAvailable() || empty($queries)) {
            return [];
        }

        try {
            // Build reusable query settings.
            $url = $this->url('/query');
            // Cache request headers.
            $headers = $this->headers();
            // Store the namespace locally.
            $ns = $this->namespace;

            // Execute all queries concurrently.
            $responses = Http::pool(function ($pool) use ($queries, $url, $headers, $ns) {
                // Build and dispatch each parallel query.
                foreach ($queries as $query) {
                    // Prepare the query request body.
                    $body = [
                        'vector'          => $query['vector'],
                        'topK'            => $query['topK'] ?? 5,
                        'includeMetadata' => true,
                        'namespace'       => $ns,
                    ];

                    // Include a filter when specified.
                    if (!empty($query['filter'])) {
                        $body['filter'] = $this->buildFilter($query['filter']);
                    }

                    // Register the request in the pool using its query key.
                    $pool->as($query['key'])
                        ->withHeaders($headers)
                        ->timeout(10)
                        ->connectTimeout(5)
                        ->post($url, $body);
                }
            });

            // Prepare the final keyed result set.
            $results = [];
            // Process each query response individually.
            foreach ($queries as $query) {
                // Read the query key.
                $key = $query['key'];
                // Extract matches from successful responses.
                if (isset($responses[$key]) && $responses[$key]->successful()) {
                    $results[$key] = $responses[$key]->json('matches', []);
                } else {
                    // Use an empty array when the query failed.
                    $results[$key] = [];
                    if (isset($responses[$key])) {
                        // Log the failed parallel query.
                        Log::error('PineconeService: parallel query failed', [
                            'key'    => $key,
                            'status' => $responses[$key]->status(),
                        ]);
                    }
                }
            }

            // Log the parallel query summary.
            Log::debug('PineconeService: parallel query completed', [
                'queries' => count($queries),
                'results' => array_map('count', $results),
            ]);

            // Return results keyed by query name.
            return $results;

        } catch (\Exception $e) {
            // Log exceptions raised during parallel querying.
            Log::error('PineconeService: parallel query exception', ['error' => $e->getMessage()]);
            // Return an empty array on exception.
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
        // Stop if Pinecone is unavailable or there are no IDs.
        if (!$this->isAvailable() || empty($ids)) {
            return false;
        }

        try {
            // Send a vector deletion request.
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->post($this->url('/vectors/delete'), [
                    'ids'       => $ids,
                    'namespace' => $this->namespace,
                ]);

            // Return true when deletion succeeds.
            if ($response->successful()) {
                Log::debug('PineconeService: deleted vectors', ['count' => count($ids)]);
                return true;
            }

            // Log failed deletion responses.
            Log::error('PineconeService: delete failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            // Return false on failure.
            return false;

        } catch (\Exception $e) {
            // Log exceptions during vector deletion.
            Log::error('PineconeService: delete exception', ['error' => $e->getMessage()]);
            // Return false on exception.
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
        // Stop if Pinecone is unavailable or the filter is empty.
        if (!$this->isAvailable() || empty($filter)) {
            return false;
        }

        try {
            // Send a deletion request using a metadata filter.
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->post($this->url('/vectors/delete'), [
                    'filter'    => $this->buildFilter($filter),
                    'namespace' => $this->namespace,
                ]);

            // Return true when filtered deletion succeeds.
            if ($response->successful()) {
                Log::debug('PineconeService: deleted by filter', ['filter' => $filter]);
                return true;
            }

            // Log filtered deletion failure details.
            Log::error('PineconeService: deleteByFilter failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            // Return false on failure.
            return false;

        } catch (\Exception $e) {
            // Log exceptions during filtered deletion.
            Log::error('PineconeService: deleteByFilter exception', ['error' => $e->getMessage()]);
            // Return false on exception.
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
        // Return null if no API key is configured.
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            // Detect whether the input is a batch or a single text.
            $isBatch = is_array($input);
            // Normalize input to an array for the API payload.
            $inputs = $isBatch ? $input : [$input];

            // Inference API is global: api.pinecone.io
            // Send the embedding inference request to Pinecone.
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

            // Parse embeddings when the request succeeds.
            if ($response->successful()) {
                $data = $response->json('data');
                
                // Return a batch of embedding vectors when multiple inputs were provided.
                if ($isBatch) {
                    return array_map(fn($d) => $d['values'], $data);
                }
                
                // Return the single embedding vector.
                return $data[0]['values'] ?? null;
            }

            // Log failed inference requests.
            Log::error('PineconeService: inference embed failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            // Return null on failure.
            return null;

        } catch (\Exception $e) {
            // Log exceptions during Pinecone inference embedding.
            Log::error('PineconeService: inference embed exception', ['error' => $e->getMessage()]);
            // Return null on exception.
            return null;
        }
    }

    /**
     * Build Pinecone-compatible filter from simple key-value pairs.
     * Converts ['user_id' => 5, 'type' => 'message'] into Pinecone filter format.
     */
    protected function buildFilter(array $filter): array
    {
        // Initialize the Pinecone filter conditions.
        $conditions = [];

        // Convert simple key-value filters into Pinecone operators.
        foreach ($filter as $key => $value) {
            // Use $in for array values.
            if (is_array($value)) {
                // Array values use $in operator
                $conditions[$key] = ['$in' => $value];
            } else {
                // Scalar values use $eq operator
                // Use $eq for scalar values.
                $conditions[$key] = ['$eq' => $value];
            }
        }

        // Return the constructed filter tree.
        return $conditions;
    }

    /**
     * Build full URL for a Pinecone API endpoint.
     */
    protected function url(string $path): string
    {
        // Join the index host and endpoint path safely.
        return rtrim($this->indexHost, '/') . $path;
    }

    /**
     * Get request headers for Pinecone API.
     */
    protected function headers(): array
    {
        // Return the standard Pinecone request headers.
        return [
            'Api-Key'                => $this->apiKey,
            'Content-Type'           => 'application/json',
            'Accept'                 => 'application/json',
            'X-Pinecone-Api-Version' => '2024-10', // Required for inference API endpoints
        ];
    }
}
