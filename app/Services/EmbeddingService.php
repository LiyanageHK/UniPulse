<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class EmbeddingService
{
    // API key used to authenticate embedding requests.
    protected ?string $apiKey = null;
    // Embedding model used by the selected provider.
    protected ?string $model = null;
    // Endpoint URL for embedding generation.
    protected ?string $apiUrl = null;
    // Indicates which embedding provider is currently active.
    protected string $provider;
    // Pinecone service used when embeddings are generated through Pinecone.
    protected PineconeService $pinecone;

    // Constructor injects the Pinecone service and resolves provider settings.
    public function __construct(PineconeService $pinecone)
    {
        // Store the injected Pinecone service.
        $this->pinecone = $pinecone;
        // Use separate embedding provider (defaults to main provider)
        // Read the embedding provider from configuration.
        $this->provider = config('services.openai.embedding_provider', config('services.openai.provider', 'azure'));
        
        // Set model, API key and URL based on embedding provider
        // Choose the correct embedding configuration for the active provider.
        switch ($this->provider) {
            case 'azure':
                // Use Azure embedding model.
                $this->model = config('services.openai.embedding_model', 'text-embedding-3-small');
                // Load Azure embedding API key.
                $this->apiKey = config('services.openai.azure_embedding_api_key');
                // Load Azure embedding URL.
                $this->apiUrl = config('services.openai.azure_embedding_url');
                break;
            case 'github':
                // Use GitHub embedding model.
                $this->model = config('services.openai.github_embedding_model', 'text-embedding-3-large');
                // Load GitHub embedding token.
                $this->apiKey = config('services.openai.github_embedding_token');
                // Load GitHub embedding URL.
                $this->apiUrl = config('services.openai.github_embedding_url');
                break;
            case 'pinecone':
                // Use Pinecone's embedding model.
                $this->model = config('services.pinecone.embedding_model', 'multilingual-e5-large');
                // Load Pinecone API key.
                $this->apiKey = config('services.pinecone.api_key');
                // Set the Pinecone embedding endpoint.
                $this->apiUrl = 'https://api.pinecone.io/embed';
                break;
            default: // openai
                // Use standard OpenAI embedding model.
                $this->model = config('services.openai.embedding_model', 'text-embedding-3-small');
                // Load OpenAI API key.
                $this->apiKey = config('services.openai.api_key');
                // Load OpenAI embedding URL.
                $this->apiUrl = config('services.openai.embedding_url');
        }

        // Log the initialized provider and model.
        Log::info('EmbeddingService initialized', ['provider' => $this->provider, 'model' => $this->model]);
    }

    /**
     * Generate embedding for a single text.
     */
    public function generateEmbedding(string $text): ?array
    {
        // Return null if the text is empty or only whitespace.
        if (empty(trim($text))) {
            return null;
        }

        // Check cache first (includes model to prevent dimension mismatch on switch)
        // Build a cache key that depends on both model and text content.
        $cacheKey = "embedding_{$this->model}_" . md5($text);
        // Return cached embedding if it already exists.
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Use Pinecone's embedding API when configured.
            if ($this->provider === 'pinecone') {
                // Request embedding from Pinecone.
                $embedding = $this->pinecone->embed($text, $this->model);
                // Cache the embedding when successfully generated.
                if ($embedding) {
                    Cache::put($cacheKey, $embedding, now()->addDays(7));
                }
                // Return the Pinecone embedding result.
                return $embedding;
            }

            // Azure uses api-key header, others use Bearer token
            // Prepare request headers for the selected provider.
            $headers = ['Content-Type' => 'application/json'];
            if ($this->provider === 'azure') {
                // Azure expects the API key header.
                $headers['api-key'] = $this->apiKey;
            } else {
                // Non-Azure providers use a Bearer token.
                $headers['Authorization'] = 'Bearer ' . $this->apiKey;
            }

            // Build request body - Azure uses model in URL
            // Start the request body with the text input.
            $requestBody = ['input' => $text];

            // Detailed logging for Unauthorized errors will be added after response
            // Add the model to the request body for non-Azure providers.
            if ($this->provider !== 'azure') {
                $requestBody['model'] = $this->model;
            }

            // Debug logging
            // Log the outgoing embedding request details.
            Log::debug('Embedding API call', [
                'provider' => $this->provider,
                'url' => $this->apiUrl,
                'has_api_key' => !empty($this->apiKey),
            ]);

            // Send the embedding generation request.
            $response = Http::withHeaders($headers)->timeout(10)->connectTimeout(5)->post($this->apiUrl, $requestBody);

            // Detailed logging for Unauthorized errors
            // Inspect the response for authorization issues.
            $status = $response->status();
            // Read the raw response body.
            $body = $response->body();
            // Track the error type for logging.
            $errorType = null;
            if (strpos($body, 'Unauthorized') !== false || $status === 401) {
                // Mark the response as unauthorized.
                $errorType = 'Unauthorized';
                // Log the unauthorized response details.
                Log::error('Embedding API Unauthorized error', [
                    'status' => $status,
                    'body' => $body,
                    'provider' => $this->provider,
                    'model' => $this->model,
                    'api_url' => $this->apiUrl,
                    'api_key_present' => !empty($this->apiKey),
                    'error_type' => $errorType,
                ]);
            }

            // If the request succeeds, extract the embedding vector.
            if ($response->successful()) {
                $embedding = $response->json('data.0.embedding');

                // Cache for 7 days
                // Store the embedding in cache for reuse.
                Cache::put($cacheKey, $embedding, now()->addDays(7));

                // Return the embedding vector.
                return $embedding;
            }

            // Log any non-successful embedding API response.
            Log::error('Embedding API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $this->apiUrl,
                'provider' => $this->provider,
            ]);
            // Return null when the API request fails.
            return null;
        } catch (\Exception $e) {
            // Log unexpected embedding generation failures.
            Log::error('Embedding generation failed: ' . $e->getMessage());
            // Return null on exception.
            return null;
        }
    }

    /**
     * Generate embeddings for multiple texts in batch.
     */
    public function generateBatchEmbeddings(array $texts): array
    {
        // Prepare the output array for embeddings.
        $embeddings = [];

        // Filter out empty texts
        // Remove blank items before making the API call.
        $texts = array_filter($texts, fn($text) => !empty(trim($text)));

        // Return an empty array if no valid texts remain.
        if (empty($texts)) {
            return [];
        }

        try {
            // Azure uses api-key header, others use Bearer token
            // Prepare headers for the batch request.
            $headers = ['Content-Type' => 'application/json'];
            if ($this->provider === 'azure') {
                // Azure header format.
                $headers['api-key'] = $this->apiKey;
            } else {
                // Bearer token header format.
                $headers['Authorization'] = 'Bearer ' . $this->apiKey;
            }

            // Build request body - Azure uses model in URL
            // Use the filtered texts as the input list.
            $requestBody = ['input' => array_values($texts)];
            if ($this->provider !== 'azure') {
                // Include the model for non-Azure providers.
                $requestBody['model'] = $this->model;
            }

            // Send the batch embedding request.
            $response = Http::withHeaders($headers)->timeout(60)->post($this->apiUrl, $requestBody);

            // Extract embeddings if the call succeeds.
            if ($response->successful()) {
                $data = $response->json('data');
                // Map returned embeddings by their original index.
                foreach ($data as $item) {
                    $embeddings[$item['index']] = $item['embedding'];
                }
                // Return the batch embeddings.
                return $embeddings;
            }

            // Log batch API failure details.
            Log::error('Batch embedding API error: ' . $response->body());
            // Return an empty result on failure.
            return [];
        } catch (\Exception $e) {
            // Log unexpected batch generation failures.
            Log::error('Batch embedding generation failed: ' . $e->getMessage());
            // Return an empty result when an exception occurs.
            return [];
        }
    }

    /**
     * Chunk text into smaller pieces for embedding.
     * Useful for long texts that exceed token limits.
     */
    public function chunkText(string $text, int $maxChunkSize = 500): array
    {
        // Split by sentences first
        // Break the text into sentences for cleaner chunking.
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

        // Initialize the chunk collection.
        $chunks = [];
        // Keep track of the chunk currently being built.
        $currentChunk = '';

        // Combine sentences until the chunk size limit is reached.
        foreach ($sentences as $sentence) {
            // Test what the chunk would look like if the sentence is added.
            $testChunk = empty($currentChunk) ? $sentence : $currentChunk . ' ' . $sentence;

            // If the chunk gets too large, start a new one.
            if (strlen($testChunk) > $maxChunkSize && !empty($currentChunk)) {
                // Save the current chunk.
                $chunks[] = trim($currentChunk);
                // Start the next chunk with the current sentence.
                $currentChunk = $sentence;
            } else {
                // Keep growing the current chunk.
                $currentChunk = $testChunk;
            }
        }

        // Add the last chunk if it contains content.
        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }

        // Return the chunked text array.
        return $chunks;
    }

    /**
     * Calculate cosine similarity between two embeddings.
     */
    public function cosineSimilarity(array $embedding1, array $embedding2): float
    {
        // Return zero if the vectors are not the same length.
        if (count($embedding1) !== count($embedding2)) {
            return 0.0;
        }

        // Initialize the dot product.
        $dotProduct = 0;
        // Initialize the first vector magnitude accumulator.
        $magnitude1 = 0;
        // Initialize the second vector magnitude accumulator.
        $magnitude2 = 0;

        // Compute dot product and magnitudes in one pass.
        for ($i = 0; $i < count($embedding1); $i++) {
            $dotProduct += $embedding1[$i] * $embedding2[$i];
            $magnitude1 += $embedding1[$i] * $embedding1[$i];
            $magnitude2 += $embedding2[$i] * $embedding2[$i];
        }

        // Convert squared magnitudes into actual magnitudes.
        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        // Return zero if one of the vectors has zero magnitude.
        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0.0;
        }

        // Return the cosine similarity score.
        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    /**
     * Get embedding dimensions for the current model.
     */
    public function getDimensions(): int
    {
        // Return the vector dimension for the configured embedding model.
        return match ($this->model) {
            'text-embedding-3-small' => 1536,
            'text-embedding-3-large' => 3072,
            'multilingual-e5-large'  => 1024,
            default => 1536,
        };
    }
}
