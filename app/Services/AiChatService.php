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
    protected string $apiKey;
    protected string $model;
    protected string $apiUrl;
    protected string $provider;

    protected RagRetrievalService $ragRetrieval;
    protected CrisisDetectionService $crisisDetection;
    protected CrisisAlertService $crisisAlert;
    protected CounselorMatchingService $counselorMatching;
    protected MemoryExtractionService $memoryExtraction;

    public function __construct(
        RagRetrievalService $ragRetrieval,
        CrisisDetectionService $crisisDetection,
        CrisisAlertService $crisisAlert,
        CounselorMatchingService $counselorMatching,
        MemoryExtractionService $memoryExtraction
    ) {
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
            default: // openai
                $this->model = config('services.openai.model', 'gpt-4.1');
                $this->apiKey = config('services.openai.api_key');
                $this->apiUrl = config('services.openai.api_url');
        }

        Log::info('AiChatService initialized', ['provider' => $this->provider, 'model' => $this->model]);

        $this->ragRetrieval = $ragRetrieval;
        $this->crisisDetection = $crisisDetection;
        $this->crisisAlert = $crisisAlert;
        $this->counselorMatching = $counselorMatching;
        $this->memoryExtraction = $memoryExtraction;
    }

    /**
     * Process user message and generate AI response.
     */
    public function chat(User $user, Conversation $conversation, string $userMessage): array
    {
        $chatConfig = config('services.openai.chat', []);
        
        // Strict chat isolation: Never pull past conversations into the current active conversation
        $includePastConversations = false;
        
        $minSimilarity = (float) ($chatConfig['current_conversation_similarity'] ?? 0.4);

        // 1. Store user message
        $userMessageModel = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'role' => 'user',
            'content' => $userMessage,
        ]);

        // Update conversation
        $conversation->update([
            'message_count' => $conversation->message_count + 1,
            'last_message_at' => now(),
        ]);

        // 2. Detect crisis indicators in user message
        $crisisFlags = $this->crisisDetection->analyzeMessage($userMessageModel);

        // Handle red flags: show default safe message FIRST, then AI conversational response
        $redFlagsEarly = array_filter($crisisFlags, fn($f) => $f['severity'] === 'red');
        if (!empty($redFlagsEarly)) {
            // Create crisis alerts for admin notifications
            foreach ($redFlagsEarly as $flag) {
                $crisisFlag = $conversation->crisisFlags()
                    ->where('message_id', $userMessageModel->id)
                    ->first();
                if ($crisisFlag) {
                    $this->crisisAlert->createCrisisAlert($crisisFlag);
                }
            }

            Log::warning('RED FLAG detected - sending default + AI response', [
                'user_id' => $user->id,
                'conversation_id' => $conversation->id,
                'message_id' => $userMessageModel->id,
            ]);

            // Store the default safe message as first assistant response
            $safeResponse = $this->getRedFlagSafeResponse();
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
            $conversation->increment('message_count');
            $conversation->update(['last_message_at' => now()]);

            $crisisResponse = [
                'type' => 'crisis_red',
                'severity' => 'red',
                'categories' => $this->getCounselorCategories(),
                'hotlines' => $this->getCrisisHotlines(),
            ];

            $metadata = $safeMessage->metadata ?? [];
            $metadata['crisis_response'] = $crisisResponse;
            $safeMessage->update(['metadata' => $metadata]);

            // Now generate AI conversational response (like a real friend)
            $contextData = $this->ragRetrieval->getSmartContext(
                $user, $userMessage, $conversation->id, 5,
                $includePastConversations, $minSimilarity
            );

            $messages = $this->buildPrompt(
                $user, $conversation, $userMessage, $contextData,
                $crisisFlags, $includePastConversations
            );

            $aiResponse = $this->generateResponse($messages);

            if ($aiResponse) {
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
                $conversation->increment('message_count');
                $conversation->update(['last_message_at' => now()]);
            }

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
        $contextData = $this->ragRetrieval->getSmartContext(
            $user,
            $userMessage,
            $conversation->id,
            5,
            $includePastConversations,
            $minSimilarity
        );

        // 4. Build prompt with system instructions
        $messages = $this->buildPrompt(
            $user,
            $conversation,
            $userMessage,
            $contextData,
            $crisisFlags,
            $includePastConversations
        );

        // 5. Generate AI response
        $aiResponse = $this->generateResponse($messages);

        if (!$aiResponse) {
            $aiResponse = "I apologize, but I'm having trouble processing your message right now. Please try again in a moment.";
        }

        // 6. Store AI response
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
        $conversation->update([
            'message_count' => $conversation->message_count + 1,
            'last_message_at' => now(),
        ]);

        // 7. Background jobs — memory extraction + embedding.
        //    Dispatched to Redis queue when worker is running; falls back to sync on 'sync' driver.

        // Skip extraction for very short messages (greetings, "ok", etc. — nothing to extract)
        $contentLength = strlen(trim($userMessageModel->content));
        $msgContent = $userMessageModel->content;

        // Three triggers for memory extraction:
        // 1. Throttle: every 2nd message in the conversation (catches general context)
        // 2. Explicit: student says "save that", "remember this", etc.
        // 3. Disclosure: student shares personal info — "I like X", "my goal is Y", "I'm studying Z"
        $userMsgCount = $conversation->messages()->where('role', 'user')->count();
        $isExplicit   = $this->memoryExtraction->hasExplicitSaveIntent($msgContent);
        $isDisclosure = $this->memoryExtraction->hasPersonalDisclosure($msgContent);

        $shouldExtract = $contentLength >= 15   // lower bar — short disclosures like "I love music" count
            && ($isExplicit
                || $isDisclosure
                || ($contentLength >= 40 && $userMsgCount % 2 === 0));

        $queueDriver = config('queue.default', 'sync');

        if ($shouldExtract) {
            if ($queueDriver === 'sync') {
                // Sync driver: run inline so memories are saved immediately
                ExtractMessageMemory::dispatchSync($userMessageModel->id, $user->id);
            } else {
                ExtractMessageMemory::dispatch($userMessageModel->id, $user->id)
                    ->onQueue('memory')
                    ->delay(now()->addSeconds(3));
            }
        }

        // Always create message embedding (needed for future RAG retrieval)
        if ($queueDriver === 'sync') {
            CreateMessageEmbedding::dispatchSync($userMessageModel->id);
        } else {
            CreateMessageEmbedding::dispatch($userMessageModel->id)
                ->onQueue('embeddings')
                ->delay(now()->addSeconds($shouldExtract ? 8 : 3)); // offset from memory job
        }

        // 9. Build severity-based crisis response
        $counselorRecommendations = [];
        $crisisResources = [];
        $crisisResponse = null;

        // Log crisis flags detected
        Log::info('Crisis flags detected', [
            'message_id' => $userMessageModel->id,
            'total_flags' => count($crisisFlags),
            'flags' => $crisisFlags,
        ]);

        // Filter flags by severity
        $redFlags = array_filter($crisisFlags, fn($flag) => $flag['severity'] === 'red');
        $yellowFlags = array_filter($crisisFlags, fn($flag) => $flag['severity'] === 'yellow');
        $blueFlags = array_filter($crisisFlags, fn($flag) => $flag['severity'] === 'blue');

        Log::info('Flags filtered by severity', [
            'red_count' => count($redFlags),
            'yellow_count' => count($yellowFlags),
            'blue_count' => count($blueFlags),
        ]);

        // Handle based on highest severity
        if (!empty($redFlags)) {
            // RED: Show category buttons for user to select
            $crisisResponse = [
                'type' => 'crisis_red',
                'severity' => 'red',
                'categories' => $this->getCounselorCategories(),
                'hotlines' => $this->getCrisisHotlines(),
            ];
            $crisisResources = $this->getCrisisResources(array_values($redFlags));

            // Create crisis alerts for red flags
            foreach ($redFlags as $flag) {
                $crisisFlag = $conversation->crisisFlags()
                    ->where('message_id', $userMessageModel->id)
                    ->first();

                if ($crisisFlag) {
                    $this->crisisAlert->createCrisisAlert($crisisFlag);
                }
            }
        } elseif (!empty($yellowFlags)) {
            // YELLOW: Ask caring escalation questions
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
            $crisisResponse = [
                'type' => 'crisis_blue',
                'severity' => 'blue',
                'offer_support_option' => true,
                'support_message' => 'If you\'d like to speak with a professional counselor, I can show you available support options.',
            ];
        }

        // Update message metadata with crisis response for persistence
        if ($crisisResponse) {
            $metadata = $assistantMessage->metadata ?? [];
            $metadata['crisis_response'] = $crisisResponse;
            $assistantMessage->update(['metadata' => $metadata]);
        }

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
        $systemPrompt = $this->getSystemPrompt($crisisFlags);

        $looksLikeProblem = $this->messageIndicatesProblem($userMessage);

        // Dynamic length rule based on user message length
        // This forces the AI to output short responses when the user is sending short messages.
        $wordCount = str_word_count($userMessage);
        
        if ($looksLikeProblem || !empty($crisisFlags)) {
            $systemPrompt .= "\n\nCRITICAL EMOTION RULE: The user is expressing serious distress. Respond like a close friend who genuinely cares. Your FIRST priority is to ask what's wrong — 'What happened?' or 'Why do you feel like that?' or 'Do you want to talk about it?'. Be warm, real, and conversational. Do NOT give advice or push resources. Just listen and ask. Response should be 20 to 50 words.";
        } elseif ($wordCount <= 7) {
            $systemPrompt .= "\n\nCRITICAL LENGTH RULE: Your FIRST PRIORITY is brevity. Respond in exactly 2 to 8 words. Do not exceed 8 words.";
        } elseif ($wordCount <= 12) {
            $systemPrompt .= "\n\nCRITICAL LENGTH RULE: Your FIRST PRIORITY is brevity. Keep your response short, between 8 to 20 words.";
        } else {
            $systemPrompt .= "\n\nCRITICAL LENGTH RULE: Provide a thoughtful but concise response. Do not exceed 40 words unless absolutely necessary for emotional support.";
        }

        $requireClarification = (bool) config('services.openai.chat.require_clarification', true);
        $clarificationOnlyUntil = (int) config('services.openai.chat.clarification_only_until', 2);

        // Add context from RAG
        $contextMessage = '';
        if (!empty($contextData['rag_context'])) {
            $contextMessage .= $contextData['rag_context'] . "\n\n";
        }
        if (!empty($contextData['recent_context'])) {
            $contextMessage .= $contextData['recent_context'] . "\n\n";
        }

        $messages = [
            ['role' => 'system', 'content' => $systemPrompt],
        ];

        // Add context as a system message if available
        if (!empty($contextMessage)) {
            $messages[] = [
                'role' => 'system',
                'content' => "Here is relevant background information about the student:\n\n" . $contextMessage
            ];

            // Debug: Log what context is being used
            Log::info('RAG Context for user ' . $user->id . ':', [
                'has_profile' => $contextData['has_profile_data'] ?? false,
                'chunks_retrieved' => $contextData['retrieved_chunks'] ?? 0,
                'context_preview' => substr($contextMessage, 0, 200) . '...'
            ]);
        }

        if (!$includePastConversations) {
            $messages[] = [
                'role' => 'system',
                'content' => 'You are starting fresh or continuing a specific topic stream. Do not mention, reference, or imply knowledge of anything from past/unrelated conversations. ONLY focus on the topic that started THIS current conversation and information the student tells you right now. Stay extremely focused on resolving the current thread.',
            ];
        }

        // Add conversation history — fetch only what we need from DB, skip the current user message
        // Increase limit to 20 for deep, coherent context within the current conversation
        $historyLimit = max(0, (int) config('services.openai.chat.history_limit', 20));

        // Efficient: pull only last N+1 messages in DB, reverse to chronological order
        // The +1 accounts for the user message we just saved (which we exclude below)
        $allMessages = $conversation->messages()
            ->where('role', '!=', 'system')
            ->orderBy('created_at', 'desc')
            ->take($historyLimit + 1)
            ->get()
            ->reverse()
            ->values();

        // Exclude the last message (current user message already added at bottom)
        $recentMessages = $historyLimit > 0
            ? $allMessages->slice(0, max(0, $allMessages->count() - 1))
            : collect();

        // Only inject a clarification nudge when the student has shared a problem/distress
        // but the conversation is still very early (first 2 AI replies).
        // Never force this for greetings, casual chat, or already-detailed messages.
        $assistantCount = $allMessages->where('role', 'assistant')->count();

        if ($requireClarification && empty($crisisFlags) && $looksLikeProblem && $assistantCount < $clarificationOnlyUntil) {
            $messages[] = [
                'role' => 'system',
                'content' => 'The student seems to be sharing something difficult. Respond warmly and ask one gentle, open question to understand more before offering any advice. Do not suggest solutions yet.',
            ];
        }

        foreach ($recentMessages as $msg) {
            $messages[] = [
                'role' => $msg->role,
                'content' => $msg->content,
            ];
        }

        // Add current user message (from parameter, not from DB to avoid duplication)
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        return $messages;
    }

    /**
     * Get system prompt based on crisis context.
     */
    protected function getSystemPrompt(array $crisisFlags): string
    {
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
        if (!empty($crisisFlags)) {
            $hasRed = collect($crisisFlags)->contains('severity', 'red');
            $hasYellow = collect($crisisFlags)->contains('severity', 'yellow');

            if ($hasRed) {
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

        return $basePrompt;
    }

    /**
     * Generate AI response using OpenAI API.
     * Returns supportive default message if response is blocked or empty.
     */
    protected function generateResponse(array $messages): ?string
    {
        try {
            $chatConfig = config('services.openai.chat', []);
            $maxTokens = (int) ($chatConfig['max_tokens'] ?? 250);
            $temperature = (float) ($chatConfig['temperature'] ?? 0.4);

            // Build request body - Azure doesn't need model in body since it's in the URL
            $requestBody = [
                'messages' => $messages,
            ];

            // Add model to body only for non-Azure providers
            if ($this->provider !== 'azure') {
                $requestBody['model'] = $this->model;
            }

            // Provider-specific settings
            if ($this->provider === 'github') {
                $requestBody['max_completion_tokens'] = $maxTokens;
            } else {
                $requestBody['temperature'] = $temperature;
                $requestBody['max_tokens'] = $maxTokens;
            }

            // Azure uses api-key header, others use Bearer token
            $headers = ['Content-Type' => 'application/json'];
            if ($this->provider === 'azure') {
                $headers['api-key'] = $this->apiKey;
            } else {
                $headers['Authorization'] = 'Bearer ' . $this->apiKey;
            }

            $response = Http::withHeaders($headers)
                ->timeout(20)
                ->connectTimeout(5)
                ->post($this->apiUrl, $requestBody);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');

                // Check if response is empty or null
                if (empty(trim($content ?? ''))) {
                    Log::warning('Empty AI response received');
                    return $this->getDefaultSupportMessage();
                }

                return $content;
            }

            // Check for content filter rejection
            $errorBody = $response->json();
            $errorCode = $errorBody['error']['code'] ?? null;
            $innerCode = $errorBody['error']['innererror']['code'] ?? null;

            if ($errorCode === 'content_filter' || $innerCode === 'ResponsibleAIPolicyViolation') {
                Log::warning('AI response blocked by content filter', [
                    'filter_result' => $errorBody['error']['innererror']['content_filter_result'] ?? null
                ]);
                return $this->getDefaultSupportMessage();
            }

            // Detailed logging for Unauthorized errors
            $status = $response->status();
            $body = $response->body();
            $errorType = null;
            if (strpos($body, 'Unauthorized') !== false || $status === 401) {
                $errorType = 'Unauthorized';
            }
            Log::error('OpenAI API error', [
                'status' => $status,
                'body' => $body,
                'provider' => $this->provider,
                'model' => $this->model,
                'api_url' => $this->apiUrl,
                'api_key_present' => !empty($this->apiKey),
                'error_type' => $errorType,
            ]);
            return $this->getDefaultSupportMessage();

        } catch (\Exception $e) {
            Log::error('AI response generation failed: ' . $e->getMessage());
            return $this->getDefaultSupportMessage();
        }
    }

    /**
     * Get default support message when AI response is unavailable.
     * Provides a warm, supportive fallback that encourages reaching out.
     */
    protected function getDefaultSupportMessage(): string
    {
        $messages = [
            "I hear you, and I want you to know that your feelings are valid. Sometimes it helps to take a moment to breathe. If you're going through a difficult time, please know that support is available. Would you like to talk about what's on your mind, or would you prefer some information about counseling resources?",

            "Thank you for sharing with me. I'm here to listen and support you. Whatever you're experiencing right now, you don't have to face it alone. Would you like to tell me more about what's happening, or would it help to know about some support resources available to you?",

            "I appreciate you reaching out. It takes courage to share what's on your mind. I'm here to support you in any way I can. Would you like to continue talking, or would you find it helpful if I shared some resources for additional support?",
        ];

        return $messages[array_rand($messages)];
    }

    /**
     * Get counselor recommendations based on crisis flags.
     */
    protected function getCounselorRecommendations(User $user, array $crisisFlags): array
    {
        if (empty($crisisFlags)) {
            return [];
        }

        return $this->counselorMatching->getRecommendedCounselors($user->id, 3)->toArray();
    }

    /**
     * Get counselor recommendations matched to specific crisis categories.
     * Only called for RED flags.
     */
    protected function getCounselorRecommendationsByCategory(User $user, array $redFlags): array
    {
        if (empty($redFlags)) {
            return [];
        }

        $topCounselors = $this->counselorMatching->getRecommendedCounselors($user->id);

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
        if (empty($crisisFlags)) {
            return [];
        }

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

        return $resources;
    }

    /**
     * Get all counselor categories for category buttons.
     */
    protected function getCounselorCategories(): array
    {
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
        $lower = strtolower(trim($message));

        // Ignore completely empty or 1-2 character messages like "ok"
        if (strlen($lower) < 3) {
            return false;
        }

        $problemSignals = [
            'stress', 'stressed', 'anxious', 'anxiety', 'worried', 'worry',
            'sad', 'depressed', 'depression', 'lonely', 'alone', 'scared',
            'problem', 'issue', 'trouble', 'difficult', 'hard time', 'struggling',
            'fail', 'failed', 'failing', 'exam', 'assignment', 'deadline',
            'fight', 'argument', 'relationship', 'breakup', 'family',
            'can\'t', 'cannot', 'don\'t know', 'lost', 'confused', 'overwhelmed',
            'help', 'advice', 'what should', 'what do i', 'how do i',
            'not okay', 'not ok', 'upset', 'angry', 'frustrated', 'tired',
        ];

        foreach ($problemSignals as $signal) {
            if (str_contains($lower, $signal)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user is asking to reference past conversations.
     */
    protected function shouldIncludePastConversations(string $userMessage): bool
    {
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

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $userMessage)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Safe, human-reviewed response for red-flag (suicide/severe crisis) messages.
     * Shown as the first immediate response before the AI follow-up.
     */
    protected function getRedFlagSafeResponse(): string
    {
        return "I hear you, and I want you to know you matter. What you're feeling right now is real and serious — and you deserve real support, not just a chat. Can you tell me what's making you feel this way? I'm here to listen. You don't have to face this alone.";
    }

    /**
     * Generate a conversation title from the first message — no API call.
     * Strips filler/stop words and picks the 5 most meaningful words.
     */
    public function generateAiConversationTitle(string $firstMessage): string
    {
        return $this->generateConversationTitle($firstMessage);
    }

    /**
     * Generate conversation title based on first message.
     * Uses stop-word filtering to pick meaningful words — no API call needed.
     */
    public function generateConversationTitle(string $firstMessage): string
    {
        $stopWords = [
            'i', 'am', 'is', 'are', 'was', 'be', 'been', 'the', 'a', 'an',
            'and', 'or', 'but', 'my', 'me', 'we', 'to', 'in', 'on', 'at',
            'so', 'it', 'of', 'do', 'did', 'have', 'has', 'just', 'not',
            'get', 'got', 'this', 'that', 'with', 'for', 'im', 'its',
        ];

        // Strip punctuation, lowercase, split
        $cleaned = preg_replace('/[^a-zA-Z0-9\s]/', '', strtolower($firstMessage));
        $words   = array_filter(explode(' ', $cleaned), fn($w) => strlen($w) > 2 && !in_array($w, $stopWords));

        $meaningful = array_slice(array_values($words), 0, 5);

        if (empty($meaningful)) {
            // Fallback: just take first 6 raw words
            $raw = array_slice(explode(' ', $firstMessage), 0, 6);
            return ucfirst(implode(' ', $raw)) ?: 'New Conversation';
        }

        return ucfirst(implode(' ', $meaningful));
    }
}
