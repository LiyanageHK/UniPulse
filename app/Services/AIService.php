<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service to communicate with the Python FastAPI AI service.
 * Handles API calls, error handling, retries, and logging.
 */
class AIService
{
    protected string $baseUrl;
    protected int $timeout;
    protected int $retries;

    public function __construct()
    {
        $this->baseUrl = config('services.ai.base_url', 'http://127.0.0.1:8000');
        // Keep timeout low: (timeout × (retries+1)) + retries × 1s sleep must stay < PHP max_execution_time (60s)
        // Default: 15s × 2 attempts + 1s sleep = ~31s total — safely under 60s
        $this->timeout = config('services.ai.timeout', 15);
        $this->retries = config('services.ai.retries', 1);
    }

    /**
     * Send text to the AI service for full multi-factor analysis.
     *
     * @param  string  $text  The combined journal text
     * @return array|null  Analysis results or null on failure
     */
    public function analyze(string $text): ?array
    {
        $attempt = 0;

        while ($attempt <= $this->retries) {
            try {
                $response = Http::timeout($this->timeout)
                    ->connectTimeout(5)   // fail fast if Python is not running
                    ->post("{$this->baseUrl}/predict", [
                        'text' => $text,
                    ]);

                if ($response->successful()) {
                    $data = $response->json();

                    Log::info('AI analysis completed', [
                        'lri_score' => $data['lri_score'] ?? null,
                        'risk_level' => $data['risk_level'] ?? null,
                    ]);

                    return $this->validateResponse($data);
                }

                Log::warning('AI service returned non-success status', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'attempt' => $attempt + 1,
                ]);
            } catch (\Exception $e) {
                Log::error('AI service request failed', [
                    'attempt' => $attempt + 1,
                    'error' => $e->getMessage(),
                ]);
            }

            $attempt++;

            if ($attempt <= $this->retries) {
                sleep(1); // Brief pause before retry
            }
        }

        Log::critical('AI service unavailable after all retry attempts');
        return null;
    }

    /**
     * Check if the AI service is healthy.
     */
    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(5)->connectTimeout(3)->get("{$this->baseUrl}/health");
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Validate and sanitize the AI response.
     */
    protected function validateResponse(array $data): array
    {
        $required = [
            'stress_probability',
            'sentiment_score',
            'pronoun_ratio',
            'absolutist_score',
            'withdrawal_score',
            'lri_score',
            'risk_level',
        ];

        foreach ($required as $key) {
            if (!array_key_exists($key, $data)) {
                Log::warning("AI response missing field: {$key}");
                $data[$key] = ($key === 'risk_level') ? 'Low' : 0.0;
            }
        }

        // Clamp numeric values to valid ranges
        foreach (['stress_probability', 'sentiment_score', 'pronoun_ratio', 'absolutist_score', 'withdrawal_score'] as $field) {
            $data[$field] = max(0.0, min(1.0, (float) $data[$field]));
        }

        $data['lri_score'] = max(0.0, min(1.0, (float) $data['lri_score']));

        $validLevels = ['Low', 'Moderate', 'High'];
        if (!in_array($data['risk_level'], $validLevels)) {
            $data['risk_level'] = 'Low';
        }

        return $data;
    }
}
