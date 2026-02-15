<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class EmbeddingService
{
    protected string $apiKey;
    protected string $model;
    protected string $apiUrl;
    protected string $provider;

    public function __construct()
    {
        // Use separate embedding provider (defaults to main provider)
        $this->provider = config('services.openai.embedding_provider', config('services.openai.provider', 'azure'));
        
        // Set model, API key and URL based on embedding provider
        switch ($this->provider) {
            case 'azure':
                $this->model = config('services.openai.embedding_model', 'text-embedding-3-small');
                $this->apiKey = config('services.openai.azure_embedding_api_key');
                $this->apiUrl = config('services.openai.azure_embedding_url');
                break;
            case 'github':
                $this->model = config('services.openai.github_embedding_model', 'text-embedding-3-large');
                $this->apiKey = config('services.openai.github_embedding_token');
                $this->apiUrl = config('services.openai.github_embedding_url');
                break;
            default: // openai
                $this->model = config('services.openai.embedding_model', 'text-embedding-3-small');
                $this->apiKey = config('services.openai.api_key');
                $this->apiUrl = config('services.openai.embedding_url');
        }

        Log::info('EmbeddingService initialized', ['provider' => $this->provider, 'model' => $this->model]);
    }

    /**
     * Generate embedding for a single text.
     */
    public function generateEmbedding(string $text): ?array
    {
        if (empty(trim($text))) {
            return null;
        }

        // Check cache first
        $cacheKey = 'embedding_' . md5($text);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Azure uses api-key header, others use Bearer token
            $headers = ['Content-Type' => 'application/json'];
            if ($this->provider === 'azure') {
                $headers['api-key'] = $this->apiKey;
            } else {
                $headers['Authorization'] = 'Bearer ' . $this->apiKey;
            }

            // Build request body - Azure uses model in URL
            $requestBody = ['input' => $text];
            if ($this->provider !== 'azure') {
                $requestBody['model'] = $this->model;
            }

            // Debug logging
            Log::debug('Embedding API call', [
                'provider' => $this->provider,
                'url' => $this->apiUrl,
                'has_api_key' => !empty($this->apiKey),
            ]);

            $response = Http::withHeaders($headers)->timeout(30)->post($this->apiUrl, $requestBody);

            if ($response->successful()) {
                $embedding = $response->json('data.0.embedding');
                
                // Cache for 7 days
                Cache::put($cacheKey, $embedding, now()->addDays(7));
                
                return $embedding;
            }

            Log::error('Embedding API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'url' => $this->apiUrl,
                'provider' => $this->provider,
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Embedding generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate embeddings for multiple texts in batch.
     */
    public function generateBatchEmbeddings(array $texts): array
    {
        $embeddings = [];
        
        // Filter out empty texts
        $texts = array_filter($texts, fn($text) => !empty(trim($text)));

        if (empty($texts)) {
            return [];
        }

        try {
            // Azure uses api-key header, others use Bearer token
            $headers = ['Content-Type' => 'application/json'];
            if ($this->provider === 'azure') {
                $headers['api-key'] = $this->apiKey;
            } else {
                $headers['Authorization'] = 'Bearer ' . $this->apiKey;
            }

            // Build request body - Azure uses model in URL
            $requestBody = ['input' => array_values($texts)];
            if ($this->provider !== 'azure') {
                $requestBody['model'] = $this->model;
            }

            $response = Http::withHeaders($headers)->timeout(60)->post($this->apiUrl, $requestBody);

            if ($response->successful()) {
                $data = $response->json('data');
                foreach ($data as $item) {
                    $embeddings[$item['index']] = $item['embedding'];
                }
                return $embeddings;
            }

            Log::error('Batch embedding API error: ' . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error('Batch embedding generation failed: ' . $e->getMessage());
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
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        $chunks = [];
        $currentChunk = '';

        foreach ($sentences as $sentence) {
            $testChunk = empty($currentChunk) ? $sentence : $currentChunk . ' ' . $sentence;
            
            if (strlen($testChunk) > $maxChunkSize && !empty($currentChunk)) {
                $chunks[] = trim($currentChunk);
                $currentChunk = $sentence;
            } else {
                $currentChunk = $testChunk;
            }
        }

        if (!empty($currentChunk)) {
            $chunks[] = trim($currentChunk);
        }

        return $chunks;
    }

    /**
     * Calculate cosine similarity between two embeddings.
     */
    public function cosineSimilarity(array $embedding1, array $embedding2): float
    {
        if (count($embedding1) !== count($embedding2)) {
            return 0.0;
        }

        $dotProduct = 0;
        $magnitude1 = 0;
        $magnitude2 = 0;

        for ($i = 0; $i < count($embedding1); $i++) {
            $dotProduct += $embedding1[$i] * $embedding2[$i];
            $magnitude1 += $embedding1[$i] * $embedding1[$i];
            $magnitude2 += $embedding2[$i] * $embedding2[$i];
        }

        $magnitude1 = sqrt($magnitude1);
        $magnitude2 = sqrt($magnitude2);

        if ($magnitude1 == 0 || $magnitude2 == 0) {
            return 0.0;
        }

        return $dotProduct / ($magnitude1 * $magnitude2);
    }

    /**
     * Get embedding dimensions for the current model.
     */
    public function getDimensions(): int
    {
        return match($this->model) {
            'text-embedding-3-small' => 1536,
            'text-embedding-3-large' => 3072,
            //'text-embedding-ada-002' => 1536,
            default => 1536,
        };
    }
}
