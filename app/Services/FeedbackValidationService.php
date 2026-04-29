<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FeedbackValidationService
{
    // API key used to authenticate requests to the AI provider.
    protected string $apiKey;
    // Endpoint URL used for validation requests.
    protected string $apiUrl;
    // Currently selected AI provider.
    protected string $provider;
    // Model used for validation scoring.
    protected string $model;

    // Resolve provider-specific configuration on construction.
    public function __construct()
    {
        // Read the configured provider, defaulting to Azure.
        $this->provider = config('services.openai.provider', 'azure');
        
        // Select the proper API credentials based on provider.
        switch ($this->provider) {
            case 'azure':
                // Azure configuration.
                $this->model = config('services.openai.model', 'gpt-4.1');
                // Load Azure API key.
                $this->apiKey = config('services.openai.azure_api_key');
                // Load Azure chat endpoint.
                $this->apiUrl = config('services.openai.azure_chat_url');
                break;
            case 'github':
                // GitHub Models configuration.
                $this->model = config('services.openai.github_chat_model', 'openai/gpt-4.1');
                // Load GitHub token.
                $this->apiKey = config('services.openai.github_token');
                // Load GitHub chat endpoint.
                $this->apiUrl = config('services.openai.github_chat_url');
                break;
            default:
                // Default OpenAI configuration.
                $this->model = config('services.openai.model', 'gpt-4.1');
                // Load OpenAI API key.
                $this->apiKey = config('services.openai.api_key');
                // Load OpenAI API endpoint.
                $this->apiUrl = config('services.openai.api_url');
        }
    }

    /**
     * Validate feedback content using LLM.
     * Returns validation score (0-100) and notes.
     */
    public function validateFeedback(string $content, int $rating): array
    {
        try {
            // Build the prompt used to judge the feedback.
            $prompt = $this->buildValidationPrompt($content, $rating);
            // Call the LLM with the constructed prompt.
            $response = $this->callLLM($prompt);
            
            // Parse the model response when available.
            if ($response) {
                return $this->parseValidationResponse($response);
            }
            
            // Default to moderate score if LLM fails
            // Return a safe fallback score when the model is unavailable.
            return [
                'score' => 50,
                'notes' => [
                    'status' => 'fallback',
                    'message' => 'LLM validation unavailable, manual review recommended'
                ]
            ];
            
        } catch (\Exception $e) {
            // Log unexpected validation failures.
            Log::error('Feedback validation failed: ' . $e->getMessage());
            // Return a fallback score on exception.
            return [
                'score' => 50,
                'notes' => [
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]
            ];
        }
    }

    /**
     * Build the validation prompt for LLM.
     */
    protected function buildValidationPrompt(string $content, int $rating): array
    {
        // Create the system prompt instructions for the validator.
        $systemPrompt = <<<PROMPT
You are a content moderation assistant for UniPulse, a university student mental health support platform. 
Your job is to evaluate student feedback submissions.

IMPORTANT: Short positive reviews like "wow", "amazing", "very good", "love it", "great app" are ACCEPTABLE and should be scored fairly.
These are valid expressions of satisfaction. Do NOT penalize feedback just for being short.

Evaluate the feedback based on:
1. **Appropriateness** (0-30 points): Is the content appropriate for public display? No profanity, hate speech, or harmful content. Short positive words score HIGH here.
2. **Relevance** (0-30 points): Is it expressing an opinion about the service? Even "great!" is relevant as it's user feedback.
3. **Authenticity** (0-20 points): Does it seem like genuine feedback? Short enthusiastic reviews are often the most authentic.
4. **Quality** (0-20 points): Is it helpful for prospective users? Brief positive reviews still provide value.

Scoring guidelines:
- "wow", "amazing", "love it" with 5 stars = score 70-80 (acceptable for approval)
- "very good app", "helpful" with 4-5 stars = score 75-85
- Detailed helpful review = score 80-95
- Inappropriate/spam content = score 0-30

Return your evaluation in this exact JSON format:
{
    "score": <total score 0-100>,
    "appropriateness": <score 0-30>,
    "relevance": <score 0-30>,
    "authenticity": <score 0-20>,
    "quality": <score 0-20>,
    "concerns": ["list of any concerns if score < 70"],
    "recommendation": "approve" | "review" | "reject"
}

Only return the JSON, no other text.
PROMPT;

    // Return the system and user messages for the LLM.
        return [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => "Please evaluate this student feedback (Rating: {$rating}/5 stars):\n\n\"{$content}\""]
        ];
    }

    /**
     * Call LLM API.
     */
    protected function callLLM(array $messages): ?string
    {
        // Build the initial request payload.
        $requestBody = ['messages' => $messages];
        
        // Include the model for providers that require it in the body.
        if ($this->provider !== 'azure') {
            $requestBody['model'] = $this->model;
        }
        
        // Add provider-specific token settings.
        if ($this->provider === 'github') {
            $requestBody['max_completion_tokens'] = 300;
        } else {
            $requestBody['temperature'] = 0.3; // Lower for consistent evaluation
            $requestBody['max_tokens'] = 300;
        }

        // Prepare the HTTP headers.
        $headers = ['Content-Type' => 'application/json'];
        if ($this->provider === 'azure') {
            // Azure uses the api-key header.
            $headers['api-key'] = $this->apiKey;
        } else {
            // Other providers use Bearer auth.
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }

        // Send the request to the LLM endpoint.
        $response = Http::withHeaders($headers)
            ->timeout(15)
            ->post($this->apiUrl, $requestBody);

        // Return the generated content when successful.
        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        // Detailed logging for Unauthorized errors
        // Inspect the response when the API call fails.
        $status = $response->status();
        // Read the raw response body for diagnostics.
        $body = $response->body();
        // Track whether the error appears to be authorization-related.
        $errorType = null;
        if (strpos($body, 'Unauthorized') !== false || $status === 401) {
            // Mark authorization problems for logging.
            $errorType = 'Unauthorized';
        }
        // Log the failed API request details.
        Log::warning('LLM validation API error', [
            'status' => $status,
            'body' => $body,
            'provider' => $this->provider,
            'model' => $this->model,
            'api_url' => $this->apiUrl,
            'api_key_present' => !empty($this->apiKey),
            'error_type' => $errorType,
        ]);
        // Return null when the provider call fails.
        return null;
    }

    /**
     * Parse LLM validation response.
     */
    protected function parseValidationResponse(string $response): array
    {
        try {
            // Clean up response - remove markdown code blocks if present
            // Strip JSON code fences and trim whitespace.
            $cleaned = preg_replace('/```json\s*|\s*```/', '', trim($response));
            // Decode the JSON into an associative array.
            $data = json_decode($cleaned, true);
            
            // Return the parsed payload when the JSON is valid.
            if (json_last_error() === JSON_ERROR_NONE && isset($data['score'])) {
                return [
                    'score' => min(100, max(0, (int)$data['score'])),
                    'notes' => [
                        'appropriateness' => $data['appropriateness'] ?? null,
                        'relevance' => $data['relevance'] ?? null,
                        'authenticity' => $data['authenticity'] ?? null,
                        'quality' => $data['quality'] ?? null,
                        'concerns' => $data['concerns'] ?? [],
                        'recommendation' => $data['recommendation'] ?? 'review'
                    ]
                ];
            }
        } catch (\Exception $e) {
            // Log parsing failures for troubleshooting.
            Log::warning('Failed to parse LLM validation response', ['response' => $response]);
        }

        // Fallback parsing - assume moderate score
        // Return a conservative fallback when parsing fails.
        return [
            'score' => 60,
            'notes' => [
                'raw_response' => substr($response, 0, 500),
                'parse_failed' => true
            ]
        ];
    }

    /**
     * Quick check if feedback should be auto-approved.
     */
    public function shouldAutoApprove(int $rating, int $llmScore): bool
    {
        // Auto-approve only strong feedback with a sufficient LLM score.
        return $rating >= 4 && $llmScore >= 70;
    }
}
