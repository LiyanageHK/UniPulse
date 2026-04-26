<?php

namespace App\Services;

use App\Models\User;
use App\Models\Message;
use App\Models\Memory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MemoryExtractionService
{

    // API key used to authenticate memory extraction requests.
    protected string $apiKey;
    // Model used for AI-based memory extraction.
    protected string $model;
    // Endpoint URL used for memory extraction requests.
    protected string $apiUrl;
    // Currently selected provider for AI calls.
    protected string $provider;

    // Phrases that indicate user wants to save something to memory
    // These phrases explicitly request that information be stored.
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
        'write that down',
        'keep that',
        'keep this',
        'please remember',
        'can you remember',
        'you should know',
        'i want you to know',
        'i want you to remember',
        'remember i',
    ];

    // Patterns that signal the student is sharing something personal worth remembering.
    // These trigger immediate extraction even outside the normal throttle cycle.
    // These patterns capture personal facts, preferences, goals, and identity details.
    protected array $personalDisclosurePatterns = [
        // Likes / loves / enjoys
        'i like', 'i love', 'i enjoy', 'i really like', 'i really love', 'i really enjoy',
        'my favourite', 'my favorite', 'i\'m a fan of', 'i\'m into', 'i\'m passionate about',
        // Dislikes
        'i hate', 'i dislike', 'i don\'t like', 'i can\'t stand',
        // Specific remembering requests
        'my name is', 'my age is', 'i\'m called', 'call me',
        'my birthday', 'my hobby is', 'my hobbies are',
        'i\'m interested in', 'i am interested in',
        'i\'m working on', 'i am working on',
        'i need to', 'i have to', 'i must',
        'i believe', 'i think that', 'i feel like',
        'my pet', 'my dog', 'my cat',
        'i\'m learning', 'i am learning', 'i started',
        'i moved to', 'i transferred',
        'i\'m taking', 'i am taking',
        'my project', 'my assignment', 'my thesis',
        'i work at', 'i volunteer', 'i intern',
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

    // Initialize provider configuration for memory extraction.
    public function __construct()
    {
        // Read the configured provider, defaulting to Azure.
        $this->provider = config('services.openai.provider', 'azure');
        
        // Set model, API key and URL based on provider
        // Select the appropriate AI endpoint credentials.
        switch ($this->provider) {
            case 'azure':
                // Use Azure OpenAI settings.
                $this->model = config('services.openai.model', 'gpt-4.1');
                // Load Azure API key.
                $this->apiKey = config('services.openai.azure_api_key');
                // Load Azure chat endpoint.
                $this->apiUrl = config('services.openai.azure_chat_url');
                break;
            case 'github':
                // Use GitHub Models settings.
                $this->model = config('services.openai.github_chat_model', 'openai/gpt-4.1');
                // Load GitHub token.
                $this->apiKey = config('services.openai.github_token');
                // Load GitHub chat endpoint.
                $this->apiUrl = config('services.openai.github_chat_url');
                break;
            default:
                // Use standard OpenAI settings.
                $this->model = config('services.openai.model', 'gpt-4.1');
                // Load OpenAI API key.
                $this->apiKey = config('services.openai.api_key');
                // Load OpenAI API endpoint.
                $this->apiUrl = config('services.openai.api_url');
        }
    }

    /**
     * Check if message contains explicit save intent.
     */
    public function hasExplicitSaveIntent(string $message): bool
    {
        // Normalize the message for case-insensitive matching.
        $lowerMessage = strtolower($message);
        
        // Check whether any explicit save phrase appears in the message.
        foreach ($this->saveIntentPhrases as $phrase) {
            if (str_contains($lowerMessage, $phrase)) {
                // Return true when the user clearly asks to save information.
                return true;
            }
        }
        
        // Return false if no save intent is detected.
        return false;
    }

    /**
     * Detect whether a message contains a personal disclosure worth saving immediately.
     * Examples: "I like hiking", "my goal is to become a doctor", "I'm studying CS".
     * These bypass the normal throttle so important facts are never missed.
     */
    public function hasPersonalDisclosure(string $message): bool
    {
        // Normalize the text before matching against disclosure patterns.
        $lower = strtolower(trim($message));

        // Only skip truly empty/trivial input (1-2 chars like "ok")
        // Ignore extremely short messages that are unlikely to contain disclosures.
        if (strlen($lower) < 5) {
            return false;
        }

        // Scan for any phrase that indicates a personal disclosure.
        foreach ($this->personalDisclosurePatterns as $pattern) {
            if (str_contains($lower, $pattern)) {
                // Return true when the user shares a personal fact.
                return true;
            }
        }

        // Return false when no disclosure pattern matches.
        return false;
    }

    /**
     * Extract memories from a conversation message.
     * Rule-based extraction runs FIRST (instant, no API call).
     * AI extraction is only used as fallback for complex messages.
     */
    public function extractMemoriesFromMessage(Message $message): array
    {
        // Get the message owner for logging and contextual checks.
        $user = $message->user;
        
        // Don't extract from assistant messages
        // Only extract memory from user-authored messages.
        if ($message->role !== 'user') {
            return [];
        }

        // Detect whether the message explicitly asks to save something.
        $hasExplicitIntent  = $this->hasExplicitSaveIntent($message->content);
        // Detect whether the message contains a personal disclosure.
        $hasDisclosure      = $this->hasPersonalDisclosure($message->content);

        // ── RULE-BASED FIRST (no API call needed) ──
        // For disclosures like "I like X", "my goal is Y" — extract locally.
        // Instant, free, and immune to rate limits.
        // Try the rule-based extractor before using the AI.
        $ruleMemories = $this->extractMemoryByRules($message);
        if (!empty($ruleMemories)) {
            // Log that rule-based extraction succeeded.
            Log::info('Memory extracted via rules (no AI call)', [
                'message_id' => $message->id,
                'user_id' => $user->id,
                'count' => count($ruleMemories),
            ]);

            // Increase importance when the user explicitly asked to save it.
            if ($hasExplicitIntent) {
                foreach ($ruleMemories as &$memory) {
                    $memory['importance'] = min(1.0, $memory['importance'] + 0.2);
                }
                unset($memory);
            }

            // Return the rule-based memories immediately.
            return $ruleMemories;
        }

        // ── AI EXTRACTION (only when rules didn't match) ──
        // Build a minimal context for AI when only a disclosure was found.
        if ($hasDisclosure && !$hasExplicitIntent) {
            $recentMessages = [['role' => 'user', 'content' => $message->content]];
        } else {
            // Otherwise, build a small recent conversation window.
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

        // Build the extraction prompt from the conversation context.
        $extractionPrompt = $this->buildExtractionPrompt($recentMessages, $hasExplicitIntent, $hasDisclosure);

        try {
            // Ask the AI to extract memories.
            $response = $this->callAI($extractionPrompt);

            // Return an empty array if AI produced no response.
            if (!$response) {
                Log::warning('AI memory extraction returned nothing', ['message_id' => $message->id]);
                return [];
            }

            // Convert the AI response into structured memory entries.
            $extractedMemories = $this->parseMemoriesFromResponse($response, $message);

            // Raise importance when the user explicitly requested saving.
            if ($hasExplicitIntent && !empty($extractedMemories)) {
                foreach ($extractedMemories as &$memory) {
                    $memory['importance'] = min(1.0, $memory['importance'] + 0.2);
                }
                unset($memory);
            }
            
            // Log successful AI-based extraction.
            Log::info('Extracted memories via AI', [
                'message_id' => $message->id,
                'user_id' => $user->id,
                'count' => count($extractedMemories),
                'explicit_intent' => $hasExplicitIntent
            ]);

            // Return the extracted memories.
            return $extractedMemories;

        } catch (\Exception $e) {
            // Log extraction failures for troubleshooting.
            Log::error('Memory extraction failed', [
                'message_id' => $message->id,
                'error' => $e->getMessage()
            ]);
            // Return an empty array on error.
            return [];
        }
    }

    /**
     * Build the AI prompt for memory extraction.
     */
    protected function buildExtractionPrompt(array $conversationContext, bool $hasExplicitSaveIntent = false, bool $hasDisclosure = false): string
    {
        // Start with an empty conversation transcript string.
        $conversationText = '';
        // Convert each message into a labeled line of transcript.
        foreach ($conversationContext as $msg) {
            // Label messages as Student or AI for the prompt.
            $role = $msg['role'] === 'user' ? 'Student' : 'AI';
            // Append the message to the conversation text.
            $conversationText .= "{$role}: {$msg['content']}\n\n";
        }

        // Start with no special instructions.
        $explicitInstructions = '';
        // Add explicit-save instructions when the user requested memory saving.
        if ($hasExplicitSaveIntent) {
            $explicitInstructions = <<<EXPLICIT

IMPORTANT: The student has EXPLICITLY requested to save something to memory (they used phrases like "save that", "remember this", etc.).
You MUST extract the specific information they want saved. Look for what follows their save request.
Give these memories HIGH importance (0.8-1.0) since the user specifically asked to remember them.
EXPLICIT;
    // Add disclosure-focused instructions when the user shared a personal fact.
        } elseif ($hasDisclosure) {
            $explicitInstructions = <<<DISCLOSURE

IMPORTANT: The student just shared a personal fact (a preference, hobby, goal, or identity detail).
Focus ONLY on extracting what they shared in this message. Ignore unrelated emotional context.
Examples of what to extract: hobbies, interests, career goals, study habits, dislikes, skills, lifestyle details.
Give these a moderate-to-high importance (0.6-0.9).
DISCLOSURE;
        }

    // Build the final prompt instructing the model to return JSON only.
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
        // Trim whitespace before parsing.
        $response = trim($response);
        // Remove opening JSON fences.
        $response = preg_replace('/^```json\s*/', '', $response);
        // Remove closing code fences.
        $response = preg_replace('/\s*```$/', '', $response);
        // Trim again after cleanup.
        $response = trim($response);

        try {
            // Decode the AI response into a PHP array.
            $memories = json_decode($response, true);
            
            // Return an empty list if the JSON is not an array.
            if (!is_array($memories)) {
                Log::warning('AI response is not an array', ['response' => $response]);
                return [];
            }

            // Validate and enrich each memory
            // Keep only valid memory entries and normalize them.
            $validMemories = [];
            foreach ($memories as $memory) {
                // Validate the memory structure before saving it.
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

            // Return all valid memories.
            return $validMemories;

        } catch (\Exception $e) {
            // Log JSON parsing failures.
            Log::error('Failed to parse memory JSON', [
                'response' => $response,
                'error' => $e->getMessage()
            ]);
            // Return no memories on parse failure.
            return [];
        }
    }

    /**
     * Validate memory structure.
     */
    protected function isValidMemory(mixed $memory): bool
    {
        // Reject any non-array memory object.
        if (!is_array($memory)) {
            return false;
        }

        // Define the required fields for each memory entry.
        $required = ['category', 'key', 'value', 'importance'];
        // Ensure every required field is present and non-empty.
        foreach ($required as $field) {
            if (!isset($memory[$field]) || empty($memory[$field])) {
                return false;
            }
        }

        // Validate category
        // Ensure the category matches one of the supported memory categories.
        if (!in_array($memory['category'], Memory::getCategories())) {
            Log::warning('Invalid memory category', ['category' => $memory['category']]);
            return false;
        }

        // Validate importance
        // Convert importance to a floating-point number.
        $importance = floatval($memory['importance']);
        // Reject out-of-range importance values.
        if ($importance < 0.0 || $importance > 1.0) {
            return false;
        }

        // Return true only when all checks pass.
        return true;
    }

    /**
     * Normalize memory key to snake_case.
     */
    protected function normalizeKey(string $key): string
    {
        // Lowercase and trim the key.
        $key = strtolower(trim($key));
        // Replace invalid characters with underscores.
        $key = preg_replace('/[^a-z0-9_]/', '_', $key);
        // Collapse repeated underscores.
        $key = preg_replace('/_+/', '_', $key);
        // Remove leading and trailing underscores.
        return trim($key, '_');
    }

    /**
     * Call AI for memory extraction.
     */
    protected function callAI(string $prompt): ?string
    {
        try {
            // Build request body - Azure doesn't need model in body
            // Prepare the chat payload for the AI provider.
            $requestBody = [
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a memory extraction system. Return only valid JSON.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
            ];

            // Add model to body only for non-Azure providers
            // Include the model when the provider expects it.
            if ($this->provider !== 'azure') {
                $requestBody['model'] = $this->model;
            }

            // Provider-specific settings
            // Configure completion limits by provider.
            if ($this->provider === 'github') {
                $requestBody['max_completion_tokens'] = 300;
            } else {
                $requestBody['temperature'] = 0.3;
                $requestBody['max_tokens'] = 300;
            }

            // Azure uses api-key header, others use Bearer token
            // Create the authorization headers.
            $headers = ['Content-Type' => 'application/json'];
            if ($this->provider === 'azure') {
                // Azure header format.
                $headers['api-key'] = $this->apiKey;
            } else {
                // Bearer token header format.
                $headers['Authorization'] = 'Bearer ' . $this->apiKey;
            }

            // Send the prompt to the AI provider.
            $response = Http::withHeaders($headers)
            ->timeout(30)
            ->post($this->apiUrl, $requestBody);

            // Return the content when the request succeeds.
            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            // Retry once after delay on rate-limit (429)
            // Retry on rate-limit responses.
            if ($response->status() === 429) {
                Log::warning('Memory extraction rate-limited (429), retrying after 5s');
                // Pause before retrying.
                sleep(5);
                // Retry the same request once.
                $retryResponse = Http::withHeaders($headers)
                    ->timeout(30)
                    ->post($this->apiUrl, $requestBody);
                // Return the retry result if successful.
                if ($retryResponse->successful()) {
                    return $retryResponse->json('choices.0.message.content');
                }
            }

            // Detailed logging for Unauthorized errors
            // Capture the response metadata for diagnostics.
            $status = $response->status();
            // Read the raw response body.
            $body = $response->body();
            // Track a simple error type.
            $errorType = null;
            if (strpos($body, 'Unauthorized') !== false || $status === 401) {
                // Mark unauthorized failures.
                $errorType = 'Unauthorized';
            }
            // Log the failed AI call.
            Log::error('Memory extraction API error', [
                'status' => $status,
                'body' => $body,
                'provider' => $this->provider,
                'model' => $this->model,
                'api_url' => $this->apiUrl,
                'api_key_present' => !empty($this->apiKey),
                'error_type' => $errorType,
            ]);
            // Return null on API failure.
            return null;

        } catch (\Exception $e) {
            // Log unexpected AI call failures.
            Log::error('Memory extraction API call failed', ['error' => $e->getMessage()]);
            // Return null on exception.
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
        // Define default importance scores by category.
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

        // Use the category score or a default if unknown.
        $baseScore = $categoryImportance[$category] ?? 0.5;

        // Adjust based on content length and specificity
        // Measure how detailed the memory value is.
        $length = strlen($value);
        // Increase importance slightly for longer, more detailed facts.
        if ($length > 100) {
            $baseScore += 0.1; // More detailed = likely more important
        }

        // Cap at 1.0
        // Clamp the score to the maximum allowed value.
        return min(1.0, $baseScore);
    }

    /**
     * Rule-based memory extraction fallback.
     * Parses the message locally using patterns — no AI call needed.
     * Guarantees a memory is saved even when the LLM is rate-limited or down.
     */
    protected function extractMemoryByRules(Message $message): array
    {
        // Read the message content and normalize it.
        $content = $message->content;
        // Lowercase and trim for regex matching.
        $lower = strtolower(trim($content));

        // Map patterns → (category, key_prefix)
        // Define local extraction patterns and their target categories.
        $rules = [
            // Preferences — likes
            '/\bi (?:really )?(?:like|love|enjoy)\b\s+(.+)/i' => ['preferences', 'likes'],
            '/\bi\'?m (?:a fan of|into|passionate about)\b\s+(.+)/i' => ['preferences', 'likes'],
            '/\bmy favou?rite\b\s+(.+)/i' => ['preferences', 'favourite'],
            // Preferences — dislikes
            '/\bi (?:hate|dislike|don\'?t like|can\'?t stand)\b\s+(.+)/i' => ['preferences', 'dislikes'],
            // Goals
            '/\bi (?:want to be|want to become|plan to|hope to)\b\s+(.+)/i' => ['goals', 'goal'],
            '/\bmy (?:goal|dream) is\b\s+(.+)/i' => ['goals', 'goal'],
            // Academic
            '/\bi\'?m (?:studying|taking|learning)\b\s+(.+)/i' => ['academic', 'studying'],
            '/\bmy (?:major|course|degree) is\b\s+(.+)/i' => ['academic', 'major'],
            '/\bmy (?:project|thesis|assignment) is\b\s+(.+)/i' => ['academic', 'project'],
            // Personal info
            '/\bmy name is\b\s+(.+)/i' => ['personal_info', 'name'],
            '/\bcall me\b\s+(.+)/i' => ['personal_info', 'name'],
            '/\bi\'?m from\b\s+(.+)/i' => ['personal_info', 'from'],
            '/\bi live (?:in|at)\b\s+(.+)/i' => ['personal_info', 'location'],
            '/\bmy birthday\b\s+(.+)/i' => ['personal_info', 'birthday'],
            // Hobbies / activities
            '/\bi (?:play|sing|draw|paint|code|program|read|write|run|exercise|work out|watch|listen to)\b\s*(.*)/i' => ['preferences', 'hobby'],
            // Relationships
            '/\bmy (?:family|parents|brother|sister|friend|boyfriend|girlfriend)\b\s+(.+)/i' => ['relationships', 'relationship'],
            // Experiences
            '/\bi (?:started|moved to|transferred)\b\s+(.+)/i' => ['experiences', 'experience'],
            '/\bi work at\b\s+(.+)/i' => ['experiences', 'work'],
            '/\bi (?:volunteer|intern)\b\s+(.+)/i' => ['experiences', 'experience'],
        ];

        // Test each extraction rule against the message.
        foreach ($rules as $pattern => [$category, $keyPrefix]) {
            if (preg_match($pattern, $content, $matches)) {
                // Clean up matched value — strip trailing punctuation
                // Remove ending punctuation from the full match.
                $value = trim(preg_replace('/[.!?]+$/', '', $matches[0]));
                // Clean the captured detail for key generation.
                $detail = trim(preg_replace('/[.!?]+$/', '', $matches[1] ?? ''));

                // Build a snake_case key from the captured detail
                // Normalize the detail into a key-friendly format.
                $keyWords = preg_replace('/[^a-z0-9\s]/', '', strtolower($detail));
                // Limit the key to a few meaningful words.
                $keyWords = preg_split('/\s+/', $keyWords, 4); // max 3 words for key
                // Build the final key string.
                $key = $keyPrefix . '_' . implode('_', array_filter($keyWords));
                // Keep the key reasonably short.
                $key = substr($key, 0, 60); // keep keys reasonable length

                // Assemble the memory payload.
                $memory = [
                    'category' => $category,
                    'key' => $key,
                    'value' => ucfirst($value),
                    'importance' => 0.75,
                    'source_message_id' => $message->id,
                    'source_conversation_id' => $message->conversation_id,
                ];

                // Log the rule-based extraction result.
                Log::info('Rule-based memory extracted (AI fallback)', [
                    'message_id' => $message->id,
                    'user_id' => $message->user_id,
                    'category' => $category,
                    'key' => $key,
                    'value' => $value,
                ]);

                // Return the single extracted memory.
                return [$memory];
            }
        }

        // No pattern matched — store the raw statement as a general preference
        // Only if the message is clearly a disclosure (already validated upstream)
        // Use a fallback generic disclosure memory when no rule matched.
        if ($this->hasPersonalDisclosure($content)) {
            // Remove punctuation from the content for storage.
            $cleanContent = trim(preg_replace('/[.!?]+$/', '', $content));
            // Convert the content into a key-like string.
            $keyWords = preg_replace('/[^a-z0-9\s]/', '', strtolower($cleanContent));
            // Limit key size.
            $keyWords = preg_split('/\s+/', $keyWords, 5);
            // Build a generic key prefix.
            $key = 'said_' . implode('_', array_filter($keyWords));
            // Keep the key within a reasonable length.
            $key = substr($key, 0, 60);

            // Log the fallback extraction.
            Log::info('Rule-based memory extracted (generic disclosure)', [
                'message_id' => $message->id,
                'user_id' => $message->user_id,
                'value' => $cleanContent,
            ]);

            // Return a generic preference memory.
            return [[
                'category' => 'preferences',
                'key' => $key,
                'value' => ucfirst($cleanContent),
                'importance' => 0.65,
                'source_message_id' => $message->id,
                'source_conversation_id' => $message->conversation_id,
            ]];
        }

        // Return no memories when nothing matches.
        return [];
    }
}
