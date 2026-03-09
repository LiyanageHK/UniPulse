<?php

namespace App\Services;

use App\Models\User;
use App\Models\Message;
use App\Models\Memory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MemoryExtractionService
{
    protected string $apiKey;
    protected string $model;
    protected string $apiUrl;
    protected string $provider;

    // Phrases that indicate user wants to save something to memory
    protected array $saveIntentPhrases = [
        'save that',
        'save this',
        'remember that',
        'remember this',
        'add to memory',
        'save to memory',
        'keep in mind',
        'don\'t forget',
        'note that',
        'note this down',
        'make a note',
        'store this',
        'memorize',
        'remember my',
        'save my',
    ];

    // Patterns that signal the student is sharing something personal worth remembering.
    // These trigger immediate extraction even outside the normal throttle cycle.
    protected array $personalDisclosurePatterns = [
        // Likes / loves / enjoys
        'i like', 'i love', 'i enjoy', 'i really like', 'i really love', 'i really enjoy',
        'my favourite', 'my favorite', 'i\'m a fan of', 'i\'m into', 'i\'m passionate about',
        // Dislikes
        'i hate', 'i dislike', 'i don\'t like', 'i can\'t stand',
        // Goals / aspirations
        'i want to be', 'i want to become', 'i want to do', 'i want to study',
        'my goal is', 'my dream is', 'i plan to', 'i\'m planning to',
        'i hope to', 'i wish i could', 'someday i want',
        // Identity / personal facts
        'i am studying', 'i\'m studying', 'i study', 'my major is', 'my course is',
        'i\'m in my', 'i am in my', 'i\'m a', 'i am a',
        'i live', 'i\'m from', 'i am from', 'i grew up',
        'my family', 'my parents', 'my brother', 'my sister',
        // Struggles / patterns
        'i always', 'i usually', 'i tend to', 'i often', 'i never',
        'i struggle with', 'i find it hard', 'i find it difficult',
        'i\'m bad at', 'i\'m good at', 'i\'m great at',
        // Preferences
        'i prefer', 'i\'d rather', 'i work best', 'i feel better when',
        // Hobbies / activities
        'i play', 'i sing', 'i draw', 'i paint', 'i code', 'i program',
        'i read', 'i write', 'i run', 'i exercise', 'i work out',
        'i watch', 'i listen to',
    ];

    public function __construct()
    {
        $this->provider = config('services.openai.provider', 'azure');
        
        // Set model, API key and URL based on provider
        switch ($this->provider) {
            case 'azure':
                $this->model = config('services.openai.model', 'gpt-4.1');
                $this->apiKey = config('services.openai.azure_api_key');
                $this->apiUrl = config('services.openai.azure_chat_url');
                break;
            case 'github':
                $this->model = config('services.openai.github_chat_model', 'openai/gpt-4.1');
                $this->apiKey = config('services.openai.github_token');
                $this->apiUrl = config('services.openai.github_chat_url');
                break;
            default:
                $this->model = config('services.openai.model', 'gpt-4.1');
                $this->apiKey = config('services.openai.api_key');
                $this->apiUrl = config('services.openai.api_url');
        }
    }

    /**
     * Check if message contains explicit save intent.
     */
    public function hasExplicitSaveIntent(string $message): bool
    {
        $lowerMessage = strtolower($message);
        
        foreach ($this->saveIntentPhrases as $phrase) {
            if (str_contains($lowerMessage, $phrase)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Detect whether a message contains a personal disclosure worth saving immediately.
     * Examples: "I like hiking", "my goal is to become a doctor", "I'm studying CS".
     * These bypass the normal throttle so important facts are never missed.
     */
    public function hasPersonalDisclosure(string $message): bool
    {
        $lower = strtolower(trim($message));

        // Too short to be meaningful
        if (strlen($lower) < 10) {
            return false;
        }

        foreach ($this->personalDisclosurePatterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract memories from a conversation message.
     * Uses AI to identify important information worth remembering.
     */
    public function extractMemoriesFromMessage(Message $message): array
    {
        $user = $message->user;
        
        // Don't extract from assistant messages
        if ($message->role !== 'user') {
            return [];
        }

        // Check for explicit save intent or personal disclosure
        $hasExplicitIntent  = $this->hasExplicitSaveIntent($message->content);
        $hasDisclosure      = $this->hasPersonalDisclosure($message->content);

        // When triggered by a direct disclosure ("I love X", "my goal is Y"), focus extraction
        // on just that message — the broader context window pulls in unrelated emotional history.
        if ($hasDisclosure && !$hasExplicitIntent) {
            $recentMessages = [['role' => 'user', 'content' => $message->content]];
        } else {
            // General extraction: use last 5 messages for context
            $conversation   = $message->conversation;
            $recentMessages = $conversation->messages()
                ->where('id', '<=', $message->id)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get()
                ->reverse()
                ->map(fn($msg) => ['role' => $msg->role, 'content' => $msg->content])
                ->toArray();
        }

        // Build extraction prompt
        $extractionPrompt = $this->buildExtractionPrompt($recentMessages, $hasExplicitIntent, $hasDisclosure);

        try {
            $response = $this->callAI($extractionPrompt);

            if (!$response) {
                Log::warning('No AI response for memory extraction', ['message_id' => $message->id]);
                return [];
            }

            // Parse the AI response to extract memories
            $extractedMemories = $this->parseMemoriesFromResponse($response, $message);

            // Boost importance for explicit save requests
            if ($hasExplicitIntent && !empty($extractedMemories)) {
                foreach ($extractedMemories as &$memory) {
                    $memory['importance'] = min(1.0, $memory['importance'] + 0.2);
                }
                unset($memory);
            }
            
            Log::info('Extracted memories from message', [
                'message_id' => $message->id,
                'user_id' => $user->id,
                'count' => count($extractedMemories),
                'explicit_intent' => $hasExplicitIntent
            ]);

            return $extractedMemories;

        } catch (\Exception $e) {
            Log::error('Memory extraction failed', [
                'message_id' => $message->id,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Build the AI prompt for memory extraction.
     */
    protected function buildExtractionPrompt(array $conversationContext, bool $hasExplicitSaveIntent = false, bool $hasDisclosure = false): string
    {
        $conversationText = '';
        foreach ($conversationContext as $msg) {
            $role = $msg['role'] === 'user' ? 'Student' : 'AI';
            $conversationText .= "{$role}: {$msg['content']}\n\n";
        }

        $explicitInstructions = '';
        if ($hasExplicitSaveIntent) {
            $explicitInstructions = <<<EXPLICIT

IMPORTANT: The student has EXPLICITLY requested to save something to memory (they used phrases like "save that", "remember this", etc.).
You MUST extract the specific information they want saved. Look for what follows their save request.
Give these memories HIGH importance (0.8-1.0) since the user specifically asked to remember them.
EXPLICIT;
        } elseif ($hasDisclosure) {
            $explicitInstructions = <<<DISCLOSURE

IMPORTANT: The student just shared a personal fact (a preference, hobby, goal, or identity detail).
Focus ONLY on extracting what they shared in this message. Ignore unrelated emotional context.
Examples of what to extract: hobbies, interests, career goals, study habits, dislikes, skills, lifestyle details.
Give these a moderate-to-high importance (0.6-0.9).
DISCLOSURE;
        }

        return <<<PROMPT
You are a memory extraction system. Analyze the conversation and extract important information about the student that should be remembered for future conversations.
{$explicitInstructions}

Conversation:
{$conversationText}

Extract memories following these rules:

1. Only extract information that is:
   - Explicitly stated or clearly implied
   - Factual and specific (not vague statements)
   - Useful for personalizing future conversations
   - Respectful of privacy

2. Extract in JSON array format with each memory as:
   {
     "category": "one of: personal_info, academic, goals, preferences, emotional, relationships, health, experiences",
     "key": "short_semantic_key",
     "value": "clear, concise memory statement in first person",
     "importance": 0.0-1.0 (how critical this information is)
   }

3. Categories:
   - personal_info: Name, age, location, living situation, family background
   - academic: Major, year, courses, academic struggles/strengths, study habits
   - goals: Career aspirations, academic goals, life objectives
   - preferences: Learning preferences, hobbies, interests, communication style
   - emotional: Stress triggers, coping mechanisms, emotional patterns, anxieties
   - relationships: Friends, family, romantic relationships, social connections
   - health: Mental health, physical health, sleep, exercise
   - experiences: Significant events, achievements, challenges, transitions

4. Importance scoring:
   - 0.9-1.0: Critical identity/goal information OR explicitly requested to save
   - 0.7-0.8: Important preferences or recurring patterns
   - 0.5-0.6: Useful context but not essential
   - 0.3-0.4: Minor details

5. Key naming: Use snake_case, be specific (e.g., "current_major", "career_goal", "exam_stress_trigger")

If no significant memories should be extracted, return an empty array: []

Return ONLY valid JSON - no explanations or markdown.
PROMPT;
    }

    /**
     * Parse memories from AI response.
     */
    protected function parseMemoriesFromResponse(string $response, Message $message): array
    {
        // Clean markdown code blocks if present
        $response = trim($response);
        $response = preg_replace('/^```json\s*/', '', $response);
        $response = preg_replace('/\s*```$/', '', $response);
        $response = trim($response);

        try {
            $memories = json_decode($response, true);
            
            if (!is_array($memories)) {
                Log::warning('AI response is not an array', ['response' => $response]);
                return [];
            }

            // Validate and enrich each memory
            $validMemories = [];
            foreach ($memories as $memory) {
                if ($this->isValidMemory($memory)) {
                    $validMemories[] = [
                        'category' => $memory['category'],
                        'key' => $this->normalizeKey($memory['key']),
                        'value' => trim($memory['value']),
                        'importance' => floatval($memory['importance']),
                        'source_message_id' => $message->id,
                        'source_conversation_id' => $message->conversation_id,
                    ];
                }
            }

            return $validMemories;

        } catch (\Exception $e) {
            Log::error('Failed to parse memory JSON', [
                'response' => $response,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Validate memory structure.
     */
    protected function isValidMemory(mixed $memory): bool
    {
        if (!is_array($memory)) {
            return false;
        }

        $required = ['category', 'key', 'value', 'importance'];
        foreach ($required as $field) {
            if (!isset($memory[$field]) || empty($memory[$field])) {
                return false;
            }
        }

        // Validate category
        if (!in_array($memory['category'], Memory::getCategories())) {
            Log::warning('Invalid memory category', ['category' => $memory['category']]);
            return false;
        }

        // Validate importance
        $importance = floatval($memory['importance']);
        if ($importance < 0.0 || $importance > 1.0) {
            return false;
        }

        return true;
    }

    /**
     * Normalize memory key to snake_case.
     */
    protected function normalizeKey(string $key): string
    {
        $key = strtolower(trim($key));
        $key = preg_replace('/[^a-z0-9_]/', '_', $key);
        $key = preg_replace('/_+/', '_', $key);
        return trim($key, '_');
    }

    /**
     * Call AI for memory extraction.
     */
    protected function callAI(string $prompt): ?string
    {
        try {
            // Build request body - Azure doesn't need model in body
            $requestBody = [
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a memory extraction system. Return only valid JSON.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
            ];

            // Add model to body only for non-Azure providers
            if ($this->provider !== 'azure') {
                $requestBody['model'] = $this->model;
            }

            // Provider-specific settings
            if ($this->provider === 'github') {
                $requestBody['max_completion_tokens'] = 300;
            } else {
                $requestBody['temperature'] = 0.3;
                $requestBody['max_tokens'] = 300;
            }

            // Azure uses api-key header, others use Bearer token
            $headers = ['Content-Type' => 'application/json'];
            if ($this->provider === 'azure') {
                $headers['api-key'] = $this->apiKey;
            } else {
                $headers['Authorization'] = 'Bearer ' . $this->apiKey;
            }

            $response = Http::withHeaders($headers)
            ->timeout(30)
            ->post($this->apiUrl, $requestBody);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            // Detailed logging for Unauthorized errors
            $status = $response->status();
            $body = $response->body();
            $errorType = null;
            if (strpos($body, 'Unauthorized') !== false || $status === 401) {
                $errorType = 'Unauthorized';
            }
            Log::error('Memory extraction API error', [
                'status' => $status,
                'body' => $body,
                'provider' => $this->provider,
                'model' => $this->model,
                'api_url' => $this->apiUrl,
                'api_key_present' => !empty($this->apiKey),
                'error_type' => $errorType,
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('Memory extraction API call failed', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Calculate importance score based on context.
     * Can be enhanced with more sophisticated logic.
     */
    public function calculateImportanceScore(string $category, string $value): float
    {
        // Base importance by category
        $categoryImportance = [
            Memory::CATEGORY_PERSONAL_INFO => 0.7,
            Memory::CATEGORY_ACADEMIC => 0.8,
            Memory::CATEGORY_GOALS => 0.9,
            Memory::CATEGORY_PREFERENCES => 0.6,
            Memory::CATEGORY_EMOTIONAL => 0.8,
            Memory::CATEGORY_RELATIONSHIPS => 0.5,
            Memory::CATEGORY_HEALTH => 0.8,
            Memory::CATEGORY_EXPERIENCES => 0.6,
        ];

        $baseScore = $categoryImportance[$category] ?? 0.5;

        // Adjust based on content length and specificity
        $length = strlen($value);
        if ($length > 100) {
            $baseScore += 0.1; // More detailed = likely more important
        }

        // Cap at 1.0
        return min(1.0, $baseScore);
    }
}
