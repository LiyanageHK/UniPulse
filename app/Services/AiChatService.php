<?php

namespace App\Services;

use App\Jobs\CreateMessageEmbedding;
use App\Jobs\ExtractMessageMemory;
use App\Models\User;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiChatService
{
    // API key used to authenticate requests to the selected AI provider.
    protected string $apiKey;
    // The model name or deployment identifier used for chat generation.
    protected string $model;
    // The endpoint URL used to send chat completion requests.
    protected string $apiUrl;
    // Indicates which provider is currently active.
    protected string $provider;

    // Service for retrieving relevant context from stored knowledge.
    protected RagRetrievalService $ragRetrieval;
    // Service for detecting crisis-related content in messages.
    protected CrisisDetectionService $crisisDetection;
    // Service for creating crisis alerts when red flags are detected.
    protected CrisisAlertService $crisisAlert;
    // Service for recommending appropriate counselors.
    protected CounselorMatchingService $counselorMatching;
    // Service for deciding whether message memory should be extracted.
    protected MemoryExtractionService $memoryExtraction;

    // Constructor injects all required supporting services.
    public function __construct(
        RagRetrievalService $ragRetrieval,
        CrisisDetectionService $crisisDetection,
        CrisisAlertService $crisisAlert,
        CounselorMatchingService $counselorMatching,
        MemoryExtractionService $memoryExtraction
    ) {
        // Read the configured provider, defaulting to Azure.
        $this->provider = config('services.openai.provider', 'azure');

        // Set model, API key and URL based on provider
        // Choose the correct model and endpoint based on the configured provider.
        switch ($this->provider) {
            case 'azure':
                // Use Azure OpenAI configuration.
                $this->model = config('services.openai.model', 'gpt-4.1');
                // Load the Azure API key.
                $this->apiKey = config('services.openai.azure_api_key');
                // Load the Azure chat endpoint.
                $this->apiUrl = config('services.openai.azure_chat_url');
                break;
            case 'github':
                // Use GitHub Models configuration.
                $this->model = config('services.openai.github_chat_model', 'openai/gpt-4.1');
                // Load the GitHub token.
                $this->apiKey = config('services.openai.github_token');
                // Load the GitHub chat endpoint.
                $this->apiUrl = config('services.openai.github_chat_url');
                break;
            default: // openai
                // Use standard OpenAI configuration.
                $this->model = config('services.openai.model', 'gpt-4.1');
                // Load the OpenAI API key.
                $this->apiKey = config('services.openai.api_key');
                // Load the OpenAI API endpoint.
                $this->apiUrl = config('services.openai.api_url');
        }

        // Log the provider and model that were initialized.
        Log::info('AiChatService initialized', ['provider' => $this->provider, 'model' => $this->model]);

        // Store the injected RAG retrieval service.
        $this->ragRetrieval = $ragRetrieval;
        // Store the injected crisis detection service.
        $this->crisisDetection = $crisisDetection;
        // Store the injected crisis alert service.
        $this->crisisAlert = $crisisAlert;
        // Store the injected counselor matching service.
        $this->counselorMatching = $counselorMatching;
        // Store the injected memory extraction service.
        $this->memoryExtraction = $memoryExtraction;
    }

    /**
     * Process user message and generate AI response.
     */
    public function chat(User $user, Conversation $conversation, string $userMessage): array
    {
        // Load chat-related configuration values.
        $chatConfig = config('services.openai.chat', []);

        // Strict chat isolation: Never pull past conversations into the current active conversation
        // Disable past conversation context by default.
        $includePastConversations = false;

        // Read the similarity threshold for the current conversation.
        $minSimilarity = (float) ($chatConfig['current_conversation_similarity'] ?? 0.4);

        // 1. Store user message
        // Save the incoming user message to the database first.
        $userMessageModel = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'role' => 'user',
            'content' => $userMessage,
        ]);

        // Update conversation
        // Increase the message count and update the latest activity time.
        $conversation->update([
            'message_count' => $conversation->message_count + 1,
            'last_message_at' => now(),
        ]);

        // 2. Detect crisis indicators in user message
        // Analyze the user message for crisis signals.
        $crisisFlags = $this->crisisDetection->analyzeMessage($userMessageModel);

        // Handle red flags: show default safe message FIRST, then AI conversational response
        // Filter out red-flag detections.
        $redFlagsEarly = array_filter($crisisFlags, fn($f) => $f['severity'] === 'red');
        // If red flags exist, trigger the crisis flow.
        if (!empty($redFlagsEarly)) {
            // Create crisis alerts for admin notifications
            // Create alerts for each detected red flag.
            foreach ($redFlagsEarly as $flag) {
                // Fetch the crisis flag record attached to the current message.
                $crisisFlag = $conversation->crisisFlags()
                    ->where('message_id', $userMessageModel->id)
                    ->first();
                // Create an alert only when a matching crisis flag exists.
                if ($crisisFlag) {
                    // Send the crisis flag to the alert service.
                    $this->crisisAlert->createCrisisAlert($crisisFlag);
                }
            }

            // Log that a red flag was detected.
            Log::warning('RED FLAG detected - sending default + AI response', [
                'user_id' => $user->id,
                'conversation_id' => $conversation->id,
                'message_id' => $userMessageModel->id,
            ]);

            // Store the default safe message as first assistant response
            // Build the immediate safety response shown to the user.
            $safeResponse = $this->getRedFlagSafeResponse();
            // Save the safety response as an assistant message.
            $safeMessage = Message::create([
                'conversation_id' => $conversation->id,
                'role' => 'assistant',
                'content' => $safeResponse,
                'metadata' => [
                    'model' => 'safe_response',
                    'crisis_flags_detected' => count($crisisFlags),
                    'red_flag_block' => true,
                ],
                'has_crisis_flags' => true,
            ]);
            // Increment the conversation message count for the safety reply.
            $conversation->increment('message_count');
            // Update the conversation's latest activity timestamp.
            $conversation->update(['last_message_at' => now()]);

            // Prepare crisis response metadata for the frontend.
            $crisisResponse = [
                'type' => 'crisis_red',
                'severity' => 'red',
                'categories' => $this->getCounselorCategories(),
                'hotlines' => $this->getCrisisHotlines(),
            ];

            // Attach the crisis response payload to the safe message.
            $metadata = $safeMessage->metadata ?? [];
            $metadata['crisis_response'] = $crisisResponse;
            $safeMessage->update(['metadata' => $metadata]);

            // Now generate AI conversational response (like a real friend)
            // Retrieve contextual information for the message.
            $contextData = $this->ragRetrieval->getSmartContext(
                $user,
                $userMessage,
                $conversation->id,
                5,
                $includePastConversations,
                $minSimilarity
            );

            // Build the prompt messages for the AI request.
            $messages = $this->buildPrompt(
                $user,
                $conversation,
                $userMessage,
                $contextData,
                $crisisFlags,
                $includePastConversations
            );

            // Ask the model to generate a follow-up response.
            $aiResponse = $this->generateResponse($messages);

            // If a follow-up response exists, store it.
            if ($aiResponse) {
                // Save the generated assistant follow-up message.
                $aiMessage = Message::create([
                    'conversation_id' => $conversation->id,
                    'role' => 'assistant',
                    'content' => $aiResponse,
                    'metadata' => [
                        'model' => $this->model,
                        'context_retrieved' => $contextData['retrieved_chunks'] > 0,
                        'crisis_flags_detected' => count($crisisFlags),
                        'follow_up_to_safe_response' => true,
                    ],
                ]);
                // Increase the conversation message count for the follow-up reply.
                $conversation->increment('message_count');
                // Refresh the latest activity timestamp again.
                $conversation->update(['last_message_at' => now()]);
            }

            // Return the crisis-safe response along with the follow-up AI reply.
            return [
                'message' => $safeResponse,
                'message_id' => $safeMessage->id,
                'ai_follow_up' => $aiResponse ?? null,
                'ai_follow_up_id' => isset($aiMessage) ? $aiMessage->id : null,
                'crisis_flags' => $crisisFlags,
                'crisis_response' => $crisisResponse,
                'counselor_recommendations' => [],
                'crisis_resources' => $this->getCrisisResources(array_values($redFlagsEarly)),
                'conversation_updated' => true,
                'suggested_prompts' => [],
            ];
        }

        // 3. Get RAG context
        // Retrieve relevant retrieval-augmented context for the current message.
        $contextData = $this->ragRetrieval->getSmartContext(
            $user,
            $userMessage,
            $conversation->id,
            5,
            $includePastConversations,
            $minSimilarity
        );

        // 4. Build prompt with system instructions
        // Build the full chat prompt using system instructions and context.
        $messages = $this->buildPrompt(
            $user,
            $conversation,
            $userMessage,
            $contextData,
            $crisisFlags,
            $includePastConversations
        );

        // 5. Generate AI response
        // Call the provider to generate a response.
        $aiResponse = $this->generateResponse($messages);

        // If generation fails, use a fallback support message.
        if (!$aiResponse) {
            $aiResponse = "I apologize, but I'm having trouble processing your message right now. Please try again in a moment.";
        }

        // 6. Store AI response
        // Save the assistant response in the database.
        $assistantMessage = Message::create([
            'conversation_id' => $conversation->id,
            'role' => 'assistant',
            'content' => $aiResponse,
            'metadata' => [
                'model' => $this->model,
                'context_retrieved' => $contextData['retrieved_chunks'] > 0,
                'crisis_flags_detected' => count($crisisFlags),
            ],
        ]);

        // Update conversation with message count and last message timestamp
        // Update the conversation statistics after storing the assistant message.
        $conversation->update([
            'message_count' => $conversation->message_count + 1,
            'last_message_at' => now(),
        ]);

        // 7. Background jobs — memory extraction + embedding.
        //    Dispatched to Redis queue when worker is running; falls back to sync on 'sync' driver.

        // Measure the user message length for memory extraction decisions.
        $contentLength = strlen(trim($userMessageModel->content));
        // Keep the raw user message text for intent checks.
        $msgContent = $userMessageModel->content;

        // Memory extraction triggers — ordered by priority:
        // 1. Explicit: student says "save that", "remember this", etc.  → ALWAYS extract
        // 2. Disclosure: student shares personal info ("I like X", "my goal is Y") → ALWAYS extract
        // 3. Periodic: every 3rd user message with enough content   → catches general context
        // Check if the user explicitly asked to save something.
        $isExplicit = $this->memoryExtraction->hasExplicitSaveIntent($msgContent);
        // Check if the user disclosed personal information.
        $isDisclosure = $this->memoryExtraction->hasPersonalDisclosure($msgContent);

        // Explicit intent and personal disclosures bypass ALL length/throttle checks
        // Determine whether memory extraction should run.
        if ($isExplicit || $isDisclosure) {
            // Always extract when the message clearly indicates a memory-worthy statement.
            $shouldExtract = true;
        } else {
            // Periodic extraction for longer messages (general context)
            // Count the user's messages inside this conversation.
            $userMsgCount = $conversation->messages()->where('role', 'user')->count();
            // Trigger periodic extraction based on length and cadence.
            $shouldExtract = $contentLength >= 40 && $userMsgCount % 3 === 0;
        }

        // Read the active queue driver.
        $queueDriver = config('queue.default', 'sync');

        // Dispatch memory extraction only when the rules allow it.
        if ($shouldExtract) {
            // If the queue is synchronous, process immediately.
            if ($queueDriver === 'sync') {
                // Sync driver: run inline so memories are saved immediately
                // Dispatch the memory job synchronously.
                ExtractMessageMemory::dispatchSync($userMessageModel->id, $user->id);
            } else {
                // Queue the memory extraction job asynchronously.
                ExtractMessageMemory::dispatch($userMessageModel->id, $user->id)
                    ->onQueue('memory')
                    ->delay(now()->addSeconds(3));
            }
        }

        // Always create message embedding (needed for future RAG retrieval)
        // Always generate a vector embedding for the user message.
        if ($queueDriver === 'sync') {
            // Run embedding generation immediately in sync mode.
            CreateMessageEmbedding::dispatchSync($userMessageModel->id);
        } else {
            // Queue embedding generation with a small delay.
            CreateMessageEmbedding::dispatch($userMessageModel->id)
                ->onQueue('embeddings')
                ->delay(now()->addSeconds($shouldExtract ? 8 : 3)); // offset from memory job
        }

        // 9. Build severity-based crisis response
        // Prepare default crisis-related response values.
        $counselorRecommendations = [];
        // Start with an empty crisis resource set.
        $crisisResources = [];
        // Start with no crisis response.
        $crisisResponse = null;

        // Log crisis flags detected
        // Record all detected crisis signals for debugging.
        Log::info('Crisis flags detected', [
            'message_id' => $userMessageModel->id,
            'total_flags' => count($crisisFlags),
            'flags' => $crisisFlags,
        ]);

        // Filter flags by severity
        // Split the detected flags into red, yellow, and blue groups.
        $redFlags = array_filter($crisisFlags, fn($flag) => $flag['severity'] === 'red');
        // Collect yellow flags.
        $yellowFlags = array_filter($crisisFlags, fn($flag) => $flag['severity'] === 'yellow');
        // Collect blue flags.
        $blueFlags = array_filter($crisisFlags, fn($flag) => $flag['severity'] === 'blue');

        // Log the severity breakdown.
        Log::info('Flags filtered by severity', [
            'red_count' => count($redFlags),
            'yellow_count' => count($yellowFlags),
            'blue_count' => count($blueFlags),
        ]);

        // Handle based on highest severity
        // Select the appropriate crisis response based on severity.
        if (!empty($redFlags)) {
            // RED: Show category buttons for user to select
            // Prepare the severe crisis response payload.
            $crisisResponse = [
                'type' => 'crisis_red',
                'severity' => 'red',
                'categories' => $this->getCounselorCategories(),
                'hotlines' => $this->getCrisisHotlines(),
            ];
            // Build crisis resources from the red flags.
            $crisisResources = $this->getCrisisResources(array_values($redFlags));

            // Create crisis alerts for red flags
            // Create alerts for each red-flagged message.
            foreach ($redFlags as $flag) {
                // Re-fetch the crisis flag linked to the message.
                $crisisFlag = $conversation->crisisFlags()
                    ->where('message_id', $userMessageModel->id)
                    ->first();

                // Send the alert to the service if the flag exists.
                if ($crisisFlag) {
                    $this->crisisAlert->createCrisisAlert($crisisFlag);
                }
            }
        } elseif (!empty($yellowFlags)) {
            // YELLOW: Ask caring escalation questions
            // Build a softer escalation response.
            $crisisResponse = [
                'type' => 'crisis_yellow',
                'severity' => 'yellow',
                'escalation_questions' => [
                    'I want to make sure I understand what you\'re going through. Have you been having any thoughts of harming yourself?',
                    'Are you in a safe place right now?',
                ],
                'offer_support' => true,
            ];
        } elseif (!empty($blueFlags)) {
            // BLUE: Kind continuation with optional support offer
            // Build a light-support response for blue flags.
            $crisisResponse = [
                'type' => 'crisis_blue',
                'severity' => 'blue',
                'offer_support_option' => true,
                'support_message' => 'If you\'d like to speak with a professional counselor, I can show you available support options.',
            ];
        }

        // Update message metadata with crisis response for persistence
        // Persist the crisis response onto the assistant message metadata.
        if ($crisisResponse) {
            $metadata = $assistantMessage->metadata ?? [];
            $metadata['crisis_response'] = $crisisResponse;
            $assistantMessage->update(['metadata' => $metadata]);
        }

        // Return the final chat result payload.
        return [
            'message' => $aiResponse,
            'message_id' => $assistantMessage->id,
            'crisis_flags' => $crisisFlags, // Only for internal use, not shown to student
            'crisis_response' => $crisisResponse,
            'counselor_recommendations' => $counselorRecommendations,
            'crisis_resources' => $crisisResources,
            'conversation_updated' => true,
        ];
    }

    /**
     * Build chat prompt with system instructions and context.
     */
    protected function buildPrompt(
        User $user,
        Conversation $conversation,
        string $userMessage,
        array $contextData,
        array $crisisFlags,
        bool $includePastConversations
    ): array {
        // System prompt
        // Build the base system prompt using crisis flags.
        $systemPrompt = $this->getSystemPrompt($crisisFlags);

        // Decide whether the message looks like a problem statement.
        $looksLikeProblem = $this->messageIndicatesProblem($userMessage);

        // Dynamic length rule based on user message length
        // This forces the AI to output short responses when the user is sending short messages.
        // Count the words in the user message.
        $wordCount = str_word_count($userMessage);

        // Add a crisis-specific emotion rule when distress is detected.
        if ($looksLikeProblem || !empty($crisisFlags)) {
            $systemPrompt .= "\n\nCRITICAL EMOTION RULE: The user is expressing serious distress. Respond like a close friend who genuinely cares. Your FIRST priority is to ask what's wrong — 'What happened?' or 'Why do you feel like that?' or 'Do you want to talk about it?'. Be warm, real, and conversational. Do NOT give advice or push resources. Just listen and ask. Response should be 20 to 50 words.";
        // Keep responses very short for very short messages.
        } elseif ($wordCount <= 7) {
            $systemPrompt .= "\n\nCRITICAL LENGTH RULE: Your FIRST PRIORITY is brevity. Respond in exactly 2 to 8 words. Do not exceed 8 words.";
        // Keep responses short for short-to-medium messages.
        } elseif ($wordCount <= 12) {
            $systemPrompt .= "\n\nCRITICAL LENGTH RULE: Your FIRST PRIORITY is brevity. Keep your response short, between 8 to 20 words.";
        // Use a concise response for longer messages.
        } else {
            $systemPrompt .= "\n\nCRITICAL LENGTH RULE: Provide a thoughtful but concise response. Do not exceed 40 words unless absolutely necessary for emotional support.";
        }

        // Read clarification settings from configuration.
        $requireClarification = (bool) config('services.openai.chat.require_clarification', true);
        // Determine how early clarification-only behavior should apply.
        $clarificationOnlyUntil = (int) config('services.openai.chat.clarification_only_until', 2);

        // Add context from RAG
        // Start with an empty context string.
        $contextMessage = '';
        // Append retrieved RAG context if available.
        if (!empty($contextData['rag_context'])) {
            $contextMessage .= $contextData['rag_context'] . "\n\n";
        }
        // Append recent conversation context if available.
        if (!empty($contextData['recent_context'])) {
            $contextMessage .= $contextData['recent_context'] . "\n\n";
        }

        // Start the message list with the system prompt.
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Add context as a system message if available
        // Inject background information when context exists.
        if (!empty($contextMessage)) {
            // Add contextual background as another system message.
            $messages[] = [
                'role' => 'system',
                'content' => "Here is relevant background information about the student:\n\n" . $contextMessage
            ];

            // Debug: Log what context is being used
            // Log the context details for debugging.
            Log::info('RAG Context for user ' . $user->id . ':', [
                'has_profile' => $contextData['has_profile_data'] ?? false,
                'chunks_retrieved' => $contextData['retrieved_chunks'] ?? 0,
                'context_preview' => substr($contextMessage, 0, 200) . '...'
            ]);
        }

        // Add a strict instruction to avoid unrelated past conversations when disabled.
        if (!$includePastConversations) {
            $messages[] = [
                'role' => 'system',
                'content' => 'You are starting fresh or continuing a specific topic stream. Do not mention, reference, or imply knowledge of anything from past/unrelated conversations. ONLY focus on the topic that started THIS current conversation and information the student tells you right now. Stay extremely focused on resolving the current thread.',
            ];
        }

        // Add conversation history — fetch only what we need from DB, skip the current user message
        // Increase limit to 20 for deep, coherent context within the current conversation
        // Read the maximum number of history messages.
        $historyLimit = max(0, (int) config('services.openai.chat.history_limit', 20));

        // Efficient: pull only last N+1 messages in DB, reverse to chronological order
        // The +1 accounts for the user message we just saved (which we exclude below)
        // Load recent messages in reverse chronological order.
        $allMessages = $conversation->messages()
            ->where('role', '!=', 'system')
            ->orderBy('created_at', 'desc')
            ->take($historyLimit + 1)
            ->get()
            ->reverse()
            ->values();

        // Exclude the last message (current user message already added at bottom)
        // Slice the list to exclude the current user message duplicate.
        $recentMessages = $historyLimit > 0
            ? $allMessages->slice(0, max(0, $allMessages->count() - 1))
            : collect();

        // Only inject a clarification nudge when the student has shared a problem/distress
        // but the conversation is still very early (first 2 AI replies).
        // Never force this for greetings, casual chat, or already-detailed messages.
        // Count assistant messages in the current history.
        $assistantCount = $allMessages->where('role', 'assistant')->count();

        // Insert a clarification prompt only when early problem detection conditions are met.
        if ($requireClarification && empty($crisisFlags) && $looksLikeProblem && $assistantCount < $clarificationOnlyUntil) {
            $messages[] = [
                'role' => 'system',
                'content' => 'The student seems to be sharing something difficult. Respond warmly and ask one gentle, open question to understand more before offering any advice. Do not suggest solutions yet.',
            ];
        }

        // Add prior conversation messages into the prompt.
        foreach ($recentMessages as $msg) {
            $messages[] = [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        }

        // Add current user message (from parameter, not from DB to avoid duplication)
        // Append the current user message last.
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        // Return the fully assembled chat prompt.
        return $messages;
    }

    /**
     * Get system prompt based on crisis context.
     */
    protected function getSystemPrompt(array $crisisFlags): string
    {
        // Start with the core system prompt instructions.
        $basePrompt = <<<PROMPT
You are a warm, friendly AI support companion for UniPulse, a Sri Lankan university student wellbeing platform.

Conversation style:
- Talk naturally like a caring friend, not a therapist running an interrogation.
- ALWAYS include appropriate emojis in your responses to make the conversation feel natural, expressive, and empathetic. Do not overdo it.
- FIRST PRIORITY: Keep your responses as short as possible. Match the user's conversation pace.
- Dynamically adjust your response length strictly based on the injected CRITICAL LENGTH RULE.
- If the student is sharing deep feelings or needs comforting, you may be slightly more thoughtful, but still concise.
- If the student is just chatting or greeting, respond warmly and naturally. Do NOT force a "why" question on every message.
- Only ask a follow-up question when it genuinely helps the conversation — not as a rule on every reply.
- Never give unsolicited advice or coping strategies. If you want to help, first make sure you understand what the student is actually going through.
- If the student shares a problem or distress, gently explore it before suggesting anything.
- Never provide medical advice or diagnoses.
- Be culturally sensitive to the Sri Lankan university context.

Using memory and background information:
- You may be given background facts about the student (their profile, goals, past conversations, weekly check-in results, etc.). Use these silently to give more relevant, personalised responses.
- Do NOT announce that you remember something ("I remember you told me...", "Based on what I know about you..."). Just respond naturally as if you know the person.
- Only bring up a past detail if it is directly relevant to what the student just said — not to show off that you remember it.
- If the student shares something that contradicts a stored fact, go with what they say now.
- EXCEPTION: If the student directly asks "what do you know about me?" or similar, give a warm, friendly summary of what you know — their name, university, interests, how they've been feeling recently — so they can see their information is being used to support them. Keep it conversational, not like reading a data report.
PROMPT;

        // Adjust prompt based on crisis flags
        // Modify the prompt based on crisis severity.
        if (!empty($crisisFlags)) {
            // Check whether any red flags are present.
            $hasRed = collect($crisisFlags)->contains('severity', 'red');
            // Check whether any yellow flags are present.
            $hasYellow = collect($crisisFlags)->contains('severity', 'yellow');

            if ($hasRed) {
                // Add red-flag crisis instructions.
                $basePrompt .= <<<CRISIS


CRITICAL: The student has expressed serious distress. Your response must:
1. Respond like a real, caring friend — warm, personal, and genuine
2. Ask them what's going on: "What's making you feel this way?" or "Do you want to talk about what happened?"
3. Validate their feelings — let them know it's okay to feel this way and they're not alone
4. Do NOT immediately push hotline numbers or resources — have a real conversation first
5. Keep your tone natural and human, not clinical or robotic
6. The system will automatically show crisis resources below your message — you don't need to mention them
7. Never minimise their feelings or offer quick-fix advice

Remember: Be a friend first. Listen, ask, and care. The crisis resources will be shown automatically.
CRISIS;
            } elseif ($hasYellow) {
                // Add yellow-flag distress instructions.
                $basePrompt .= <<<CONCERNING


Note: The student is showing signs of emotional distress. Be especially:
1. Empathetic and validating of their feelings
2. Gentle in your approach
3. Ask one caring, safety-focused question (e.g. "Are you in a safe place right now?")
4. Do not jump to coping strategies yet — check in first
5. Keep the response to 2 sentences maximum
CONCERNING;
            }
        }

        // Return the final system prompt.
        return $basePrompt;
    }

    /**
     * Generate AI response using OpenAI API.
     * Returns supportive default message if response is blocked or empty.
     */
    protected function generateResponse(array $messages): ?string
    {
        try {
            // Read chat generation settings.
            $chatConfig = config('services.openai.chat', []);
            // Read the maximum number of tokens for the response.
            $maxTokens = (int) ($chatConfig['max_tokens'] ?? 250);
            // Read the temperature setting for response creativity.
            $temperature = (float) ($chatConfig['temperature'] ?? 0.4);

            // Build request body - Azure doesn't need model in body since it's in the URL
            // Prepare the base request payload.
            $requestBody = [
                'messages' => $messages,
            ];

            // Add model to body only for non-Azure providers
            // Include the model only when the provider expects it in the body.
            if ($this->provider !== 'azure') {
                $requestBody['model'] = $this->model;
            }

            // Provider-specific settings
            // Choose token settings based on provider type.
            if ($this->provider === 'github') {
                $requestBody['max_completion_tokens'] = $maxTokens;
            } else {
                $requestBody['temperature'] = $temperature;
                $requestBody['max_tokens'] = $maxTokens;
            }

            // Azure uses api-key header, others use Bearer token
            // Build the HTTP headers for the selected provider.
            $headers = ['Content-Type' => 'application/json'];
            if ($this->provider === 'azure') {
                $headers['api-key'] = $this->apiKey;
            } else {
                $headers['Authorization'] = 'Bearer ' . $this->apiKey;
            }

            // Send the request to the AI provider.
            $response = Http::withHeaders($headers)
                ->timeout(20)
                ->connectTimeout(5)
                ->post($this->apiUrl, $requestBody);

            // If the request succeeds, read the response content.
            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');

                // Check if response is empty or null
                // If the model returned empty text, use a fallback message.
                if (empty(trim($content ?? ''))) {
                    Log::warning('Empty AI response received');
                    return $this->getDefaultSupportMessage();
                }

                // Return the generated response content.
                return $content;
            }

            // Check for content filter rejection
            // Inspect the error payload when the request fails.
            $errorBody = $response->json();
            // Read the top-level error code.
            $errorCode = $errorBody['error']['code'] ?? null;
            // Read the inner policy violation code.
            $innerCode = $errorBody['error']['innererror']['code'] ?? null;

            // Fall back when the provider blocked the response.
            if ($errorCode === 'content_filter' || $innerCode === 'ResponsibleAIPolicyViolation') {
                // Log that the response was filtered.
                Log::warning('AI response blocked by content filter', [
                    'filter_result' => $errorBody['error']['innererror']['content_filter_result'] ?? null
                ]);
                // Return the safe fallback response.
                return $this->getDefaultSupportMessage();
            }

            // Detailed logging for Unauthorized errors
            // Capture the HTTP status and body for diagnostics.
            $status = $response->status();
            // Store the raw response body.
            $body = $response->body();
            // Keep track of whether the error looks like an authorization issue.
            $errorType = null;
            if (strpos($body, 'Unauthorized') !== false || $status === 401) {
                $errorType = 'Unauthorized';
            }
            // Log the provider response failure in detail.
            Log::error('OpenAI API error', [
                'status' => $status,
                'body' => $body,
                'provider' => $this->provider,
                'model' => $this->model,
                'api_url' => $this->apiUrl,
                'api_key_present' => !empty($this->apiKey),
                'error_type' => $errorType,
            ]);
            // Return the safe fallback message.
            return $this->getDefaultSupportMessage();

        } catch (\Exception $e) {
            // Log unexpected exceptions during response generation.
            Log::error('AI response generation failed: ' . $e->getMessage());
            // Return the fallback support response.
            return $this->getDefaultSupportMessage();
        }
    }

    /**
     * Get default support message when AI response is unavailable.
     * Provides a warm, supportive fallback that encourages reaching out.
     */
    protected function getDefaultSupportMessage(): string
    {
        // Prepare a set of safe fallback support responses.
        $messages = [
            "I hear you, and I want you to know that your feelings are valid. Sometimes it helps to take a moment to breathe. If you're going through a difficult time, please know that support is available. Would you like to talk about what's on your mind, or would you prefer some information about counseling resources?",

            "Thank you for sharing with me. I'm here to listen and support you. Whatever you're experiencing right now, you don't have to face it alone. Would you like to tell me more about what's happening, or would it help to know about some support resources available to you?",

            "I appreciate you reaching out. It takes courage to share what's on your mind. I'm here to support you in any way I can. Would you like to continue talking, or would you find it helpful if I shared some resources for additional support?",
        ];

        // Return one fallback message at random.
        return $messages[array_rand($messages)];
    }

    /**
     * Get counselor recommendations based on crisis flags.
     */
    protected function getCounselorRecommendations(User $user, array $crisisFlags): array
    {
        // If there are no crisis flags, return no recommendations.
        if (empty($crisisFlags)) {
            return [];
        }

        // Ask the counselor matching service for top counselor suggestions.
        return $this->counselorMatching->getRecommendedCounselors($user->id, 3)->toArray();
    }

    /**
     * Get counselor recommendations matched to specific crisis categories.
     * Only called for RED flags.
     */
    protected function getCounselorRecommendationsByCategory(User $user, array $redFlags): array
    {
        // If no red flags exist, return nothing.
        if (empty($redFlags)) {
            return [];
        }

        // Retrieve counselor matches for the user.
        $topCounselors = $this->counselorMatching->getRecommendedCounselors($user->id);

        // Format counselor recommendations with human-readable reasons.
        return collect($topCounselors)->map(fn($item) => [
            'id' => $item['counselor']->id ?? null,
            'name' => $item['counselor']->name ?? '',
            'title' => $item['counselor']->title ?? '',
            'hospital' => $item['counselor']->hospital ?? '',
            'match_reason' => !empty($item['matched_categories'])
                ? 'Specializes in: ' . implode(', ', $item['matched_categories'])
                : 'General mental health support',
        ])->values()->toArray();
    }

    /**
     * Get display label for a crisis category.
     */
    protected function getCategoryLabel(string $category): string
    {
        // Convert internal category keys to display labels.
        return match ($category) {
            'suicide_risk' => 'Suicide Prevention',
            'self_harm' => 'Self-Harm Support',
            'depression' => 'Depression',
            'anxiety' => 'Anxiety',
            'stress' => 'Stress Management',
            'loneliness' => 'Loneliness & Social Support',
            'hopelessness' => 'Hope & Resilience',
            'general' => 'General Mental Health',
            default => ucfirst(str_replace('_', ' ', $category)),
        };
    }

    /**
     * Get crisis resources to show to student.
     */
    protected function getCrisisResources(array $crisisFlags): array
    {
        // If there are no crisis flags, return no resources.
        if (empty($crisisFlags)) {
            return [];
        }

        // Define emergency hotline and online support resources.
        $resources = [
            'hotlines' => [
                '1333' => 'National Mental Health Helpline (24/7)',
                '011-2682535' => 'Sumithrayo (Befrienders) ',
                '119' => 'Emergency Services',
            ],
            'online' => [
                'Sumithrayo Email' => 'sumithrayo@gmail.com',
                'Chat Support' => 'Available on this platform',
            ],
        ];

        // Return the configured crisis resource list.
        return $resources;
    }

    /**
     * Get all counselor categories for category buttons.
     */
    protected function getCounselorCategories(): array
    {
        // Return the set of categories shown as UI buttons.
        return [
            ['key' => 'Academic & Study Support', 'label' => 'Academic & Study Support', 'color' => '#3b82f6'],
            ['key' => 'Mental Health & Wellness', 'label' => 'Mental Health & Wellness', 'color' => '#8b5cf6'],
            ['key' => 'Social Integration & Peer Relationships', 'label' => 'Social & Peer Relationships', 'color' => '#06b6d4'],
            ['key' => 'Crisis & Emergency Intervention', 'label' => 'Crisis & Emergency', 'color' => '#ef4444'],
            ['key' => 'Career Guidance & Future Planning', 'label' => 'Career Guidance', 'color' => '#f59e0b'],
            ['key' => 'Relationship & Love Affairs', 'label' => 'Relationship Support', 'color' => '#ec4899'],
            ['key' => 'Family & Home-Related Issues', 'label' => 'Family & Home Issues', 'color' => '#10b981'],
            ['key' => 'Physical Health & Lifestyle', 'label' => 'Physical Health', 'color' => '#14b8a6'],
            ['key' => 'Financial Wellness', 'label' => 'Financial Wellness', 'color' => '#84cc16'],
            ['key' => 'Extracurricular & Personal Development', 'label' => 'Personal Development', 'color' => '#6366f1'],
        ];
    }

    /**
     * Get crisis hotlines for immediate help.
     */
    protected function getCrisisHotlines(): array
    {
        // Return a list of crisis hotline contacts.
        return [
            ['number' => '119', 'name' => 'Police Sri Lanka', 'available' => '24/7'],
            ['number' => '1926', 'name' => 'National Mental Health Helpline (NIMH)', 'available' => '24/7'],
            ['number' => '1333', 'name' => 'CCCline - Crisis Support', 'available' => '24/7'],
            ['number' => '1375', 'name' => 'Lanka Life Line (LLL)', 'available' => '24/7'],
            ['number' => '+94 767 520 620', 'name' => 'Sri Lanka Sumithrayo', 'available' => '24/7'],
            ['number' => '+94 775 676 555', 'name' => 'Women In Need (WIN)', 'available' => '24/7'],
        ];
    }

    /**
     * Detect whether a message likely contains a problem or distress signal.
     * Used to decide whether to nudge the AI toward clarifying questions.
     * Returns false for greetings, casual chat, and short/neutral messages.
     */
    protected function messageIndicatesProblem(string $message): bool
    {
        // Lowercase and trim the message for keyword checks.
        $lower = strtolower(trim($message));

        // Ignore completely empty or 1-2 character messages like "ok"
        // Treat extremely short inputs as non-problem messages.
        if (strlen($lower) < 3) {
            return false;
        }

        // Define a list of words and phrases associated with distress.
        $problemSignals = [
            'stress',
            'stressed',
            'anxious',
            'anxiety',
            'worried',
            'worry',
            'sad',
            'depressed',
            'depression',
            'lonely',
            'alone',
            'scared',
            'problem',
            'issue',
            'trouble',
            'difficult',
            'hard time',
            'struggling',
            'fail',
            'failed',
            'failing',
            'exam',
            'assignment',
            'deadline',
            'fight',
            'argument',
            'relationship',
            'breakup',
            'family',
            'can\'t',
            'cannot',
            'don\'t know',
            'lost',
            'confused',
            'overwhelmed',
            'help',
            'advice',
            'what should',
            'what do i',
            'how do i',
            'not okay',
            'not ok',
            'upset',
            'angry',
            'frustrated',
            'tired',
        ];

        // Check whether any distress signal appears in the message.
        foreach ($problemSignals as $signal) {
            if (str_contains($lower, $signal)) {
                // Return true when the user appears to be describing a problem.
                return true;
            }
        }

        // Return false when no signal is detected.
        return false;
    }

    /**
     * Determine whether the user is asking to reference past conversations.
     */
    protected function shouldIncludePastConversations(string $userMessage): bool
    {
        // Define patterns that indicate the user wants to reference previous chats.
        $patterns = [
            '/\bremember\b/i',
            '/\blast time\b/i',
            '/\bprevious(ly)?\b/i',
            '/\bearlier\b/i',
            '/\bwe talked\b/i',
            '/\bwe discussed\b/i',
            '/\bas I mentioned\b/i',
            '/\bas we said\b/i',
            '/\bfrom before\b/i',
        ];

        // Check the message against each pattern.
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $userMessage)) {
                // Return true when the user explicitly references the past.
                return true;
            }
        }

        // Otherwise, do not include past conversations.
        return false;
    }

    /**
     * Safe, human-reviewed response for red-flag (suicide/severe crisis) messages.
     * Shown as the first immediate response before the AI follow-up.
     */
    protected function getRedFlagSafeResponse(): string
    {
        // Return the immediate human-reviewed safety response.
        return "I hear you, and I want you to know you matter. What you're feeling right now is real and serious — and you deserve real support, not just a chat. Can you tell me what's making you feel this way? I'm here to listen. You don't have to face this alone.";
    }

    /**
     * Generate a conversation title from the first message — no API call.
     * Strips filler/stop words and picks the 5 most meaningful words.
     */
    public function generateAiConversationTitle(string $firstMessage): string
    {
        // Log the start of title generation.
        Log::debug('AI conversation title generation started', [
            'message_length' => strlen($firstMessage),
            'message_preview' => substr($firstMessage, 0, 150) . (strlen($firstMessage) > 150 ? '...' : ''),
        ]);

        // Generate the title using the stop-word based method.
        $title = $this->generateConversationTitle($firstMessage);

        // Log the finished title generation result.
        Log::debug('AI conversation title generation completed', [
            'generated_title' => $title,
            'title_word_count' => str_word_count($title),
        ]);

        // Return the generated title.
        return $title;
    }

    /**
     * Generate conversation title based on first message.
     * Uses stop-word filtering to pick meaningful words — no API call needed.
     */
    public function generateConversationTitle(string $firstMessage): string
    {
        // Log the initial state for title generation.
        Log::debug('Title generation process started', [
            'original_message' => $firstMessage,
            'message_word_count' => str_word_count($firstMessage),
        ]);

        // Define common stop words that should not appear in a title.
        $stopWords = [
            'i',
            'am',
            'is',
            'are',
            'was',
            'be',
            'been',
            'the',
            'a',
            'an',
            'and',
            'or',
            'but',
            'my',
            'me',
            'we',
            'to',
            'in',
            'on',
            'at',
            'so',
            'it',
            'of',
            'do',
            'did',
            'have',
            'has',
            'just',
            'not',
            'get',
            'got',
            'this',
            'that',
            'with',
            'for',
            'im',
            'its',
        ];

        // Strip punctuation, lowercase, split
        // Remove punctuation and normalize casing.
        $cleaned = preg_replace('/[^a-zA-Z0-9\s]/', '', strtolower($firstMessage));
        // Split the cleaned text into words and filter out noise.
        $words = array_filter(explode(' ', $cleaned), fn($w) => strlen($w) > 2 && !in_array($w, $stopWords));

        // Log the intermediate title generation state.
        Log::debug('Title generation processing', [
            'cleaned_text' => $cleaned,
            'extracted_words' => array_values($words),
            'words_after_filtering' => count($words),
        ]);

        // Keep only the first five meaningful words.
        $meaningful = array_slice(array_values($words), 0, 5);

        // Fall back when no meaningful words are available.
        if (empty($meaningful)) {
            // Fallback: just take first 6 raw words
            // Use the first few raw words as a simple fallback title.
            $raw = array_slice(explode(' ', $firstMessage), 0, 6);
            // Build the fallback title string.
            $fallbackTitle = ucfirst(implode(' ', $raw)) ?: 'New Conversation';

            // Log that fallback generation was required.
            Log::warning('Title generation used fallback method', [
                'reason' => 'no meaningful words found after filtering',
                'raw_words_used' => $raw,
                'fallback_title' => $fallbackTitle,
            ]);

            // Return the fallback title.
            return $fallbackTitle;
        }

        // Build the final title from the meaningful words.
        $finalTitle = ucfirst(implode(' ', $meaningful));

        // Log the successful title generation result.
        Log::debug('Title generation completed successfully', [
            'meaningful_words' => $meaningful,
            'final_title' => $finalTitle,
        ]);

        // Return the generated title.
        return $finalTitle;
    }
}
