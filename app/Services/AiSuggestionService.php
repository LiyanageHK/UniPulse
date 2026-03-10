<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service to generate supportive AI-powered wellbeing suggestions
 * using the Google Gemini API.
 *
 * For Moderate and High risk users, this service returns 3 short,
 * non-clinical, positive suggestions such as watching a helpful video,
 * trying an online activity, or practicing a simple relaxation exercise.
 *
 * Safety rule: The AI must NOT provide medical or diagnostic advice.
 */
class AiSuggestionService
{
    protected string $apiKey;
    protected string $apiUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY', ''));
        $this->apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';
        $this->timeout = 30;
    }

    /**
     * Generate 3 supportive AI suggestions based on journal text and risk level.
     *
     * @param  string  $journalText  The student's combined journal text
     * @param  string  $riskLevel    'Moderate' or 'High'
     * @return array   Array of suggestion strings (3 items)
     */
    public function generateAISuggestions(string $journalText, string $riskLevel): array
    {
        // Only generate for Moderate or High risk
        if (!in_array($riskLevel, ['Moderate', 'High'])) {
            return [];
        }

        try {
            $suggestions = $this->callGeminiApi($journalText, $riskLevel);

            if (!empty($suggestions)) {
                Log::info('AI suggestions generated successfully', [
                    'risk_level' => $riskLevel,
                    'suggestion_count' => count($suggestions),
                ]);
                return $suggestions;
            }
        } catch (\Exception $e) {
            Log::error('Gemini API call failed, using fallback suggestions', [
                'error' => $e->getMessage(),
                'risk_level' => $riskLevel,
            ]);
        }

        // Return fallback suggestions if API fails
        return $this->getFallbackSuggestions($riskLevel);
    }

    /**
     * Call the Google Gemini API and parse the response.
     *
     * @param  string  $journalText
     * @param  string  $riskLevel
     * @return array   Parsed suggestions (3 items)
     *
     * @throws \Exception
     */
    protected function callGeminiApi(string $journalText, string $riskLevel): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception('GEMINI_API_KEY is not configured');
        }

        $prompt = $this->buildPrompt($journalText, $riskLevel);

        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post("{$this->apiUrl}?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 500,
                    'topP' => 0.9,
                ],
                'safetySettings' => [
                    [
                        'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                        'threshold' => 'BLOCK_ONLY_HIGH',
                    ],
                ],
            ]);

        if (!$response->successful()) {
            Log::warning('Gemini API returned non-success status', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception("Gemini API error: HTTP {$response->status()}");
        }

        $data = $response->json();

        return $this->parseGeminiResponse($data);
    }

    /**
     * Build the prompt to send to Gemini.
     */
    protected function buildPrompt(string $journalText, string $riskLevel): string
    {
        // Truncate journal text to avoid exceeding token limits
        $truncatedText = mb_substr($journalText, 0, 2000);

        return <<<PROMPT
You are a supportive wellbeing assistant for university students.

A student wrote the following weekly journal entry:
{$truncatedText}

Risk level: {$riskLevel}

Provide exactly 3 short supportive suggestions such as:
- watching a helpful video
- trying a short online activity
- practicing a simple relaxation exercise

Rules:
- Avoid any medical or diagnostic advice
- Keep suggestions positive, warm, and short (1-2 sentences each)
- Make each suggestion actionable and specific
- Do NOT recommend seeing a doctor, therapist, or any medical professional
- Format: Return exactly 3 suggestions, one per line, numbered 1-3
PROMPT;
    }

    /**
     * Parse the Gemini API response into an array of suggestion strings.
     */
    protected function parseGeminiResponse(array $data): array
    {
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($text)) {
            Log::warning('Gemini response contained no text content');
            return [];
        }

        // Split by newlines and clean up
        $lines = array_filter(
            array_map(function ($line) {
                // Remove numbering like "1.", "2.", "3.", "1)", "2)", "- ", "* "
                $line = trim($line);
                $line = preg_replace('/^\d+[\.\)]\s*/', '', $line);
                $line = preg_replace('/^[-\*]\s*/', '', $line);
                return trim($line);
            }, explode("\n", $text)),
            fn($line) => !empty($line) && strlen($line) > 5
        );

        // Take exactly 3 suggestions
        $suggestions = array_values(array_slice($lines, 0, 3));

        // If we got fewer than 3, pad with fallback
        if (count($suggestions) < 3) {
            $fallback = $this->getFallbackSuggestions('Moderate');
            while (count($suggestions) < 3) {
                $suggestions[] = array_shift($fallback);
            }
        }

        return $suggestions;
    }

    /**
     * Provide fallback suggestions when the Gemini API is unavailable.
     * These are safe, non-clinical, supportive suggestions.
     */
    public function getFallbackSuggestions(string $riskLevel): array
    {
        if ($riskLevel === 'High') {
            return [
                'Try a 5-minute guided breathing exercise on YouTube to help calm your mind — search for "5-minute breathing exercise for students".',
                'Write down 3 small things that went well today, no matter how simple. This can help shift your focus to positive moments.',
                'Take a short walk outside or stretch for a few minutes — even a brief change of scenery can help reset your mood.',
            ];
        }

        // Moderate risk fallback
        return [
            'Watch a short motivational video or a calming nature video to take a mental break — even 5 minutes can refresh your mind.',
            'Try a quick online mindfulness activity like a body scan or gratitude journaling to center yourself.',
            'Do a simple stretching or desk yoga routine — search for "5-minute desk stretches" to release tension.',
        ];
    }

    /**
     * Check if the Gemini API is configured and reachable.
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }
}
