<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FeedbackValidationService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected string $provider;
    protected string $model;

    public function __construct()
    {
        $this->provider = config('services.openai.provider', 'azure');
        $this->model = config('services.openai.model', 'gpt-4.1');
        
        switch ($this->provider) {
            case 'azure':
                $this->apiKey = config('services.openai.azure_api_key');
                $this->apiUrl = config('services.openai.azure_chat_url');
                break;
            case 'github':
                $this->apiKey = config('services.openai.github_token');
                $this->apiUrl = config('services.openai.github_chat_url');
                break;
            default:
                $this->apiKey = config('services.openai.api_key');
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
            $prompt = $this->buildValidationPrompt($content, $rating);
            $response = $this->callLLM($prompt);
            
            if ($response) {
                return $this->parseValidationResponse($response);
            }
            
            // Default to moderate score if LLM fails
            return [
                'score' => 50,
                'notes' => [
                    'status' => 'fallback',
                    'message' => 'LLM validation unavailable, manual review recommended'
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Feedback validation failed: ' . $e->getMessage());
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
        $requestBody = ['messages' => $messages];
        
        if ($this->provider !== 'azure') {
            $requestBody['model'] = $this->model;
        }
        
        if ($this->provider === 'github') {
            $requestBody['max_completion_tokens'] = 300;
        } else {
            $requestBody['temperature'] = 0.3; // Lower for consistent evaluation
            $requestBody['max_tokens'] = 300;
        }

        $headers = ['Content-Type' => 'application/json'];
        if ($this->provider === 'azure') {
            $headers['api-key'] = $this->apiKey;
        } else {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }

        $response = Http::withHeaders($headers)
            ->timeout(15)
            ->post($this->apiUrl, $requestBody);

        if ($response->successful()) {
            return $response->json('choices.0.message.content');
        }

        Log::warning('LLM validation API error', ['status' => $response->status(), 'body' => $response->body()]);
        return null;
    }

    /**
     * Parse LLM validation response.
     */
    protected function parseValidationResponse(string $response): array
    {
        try {
            // Clean up response - remove markdown code blocks if present
            $cleaned = preg_replace('/```json\s*|\s*```/', '', trim($response));
            $data = json_decode($cleaned, true);
            
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
            Log::warning('Failed to parse LLM validation response', ['response' => $response]);
        }

        // Fallback parsing - assume moderate score
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
        return $rating >= 4 && $llmScore >= 70;
    }
}
