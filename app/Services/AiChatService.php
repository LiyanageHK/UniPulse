<?php

namespace App\Services;

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
    protected KnowledgeBaseService $knowledgeBase;
    protected MemoryExtractionService $memoryExtraction;
    protected MemoryManagementService $memoryManagement;

    public function __construct(
        RagRetrievalService $ragRetrieval,
        CrisisDetectionService $crisisDetection,
        CrisisAlertService $crisisAlert,
        CounselorMatchingService $counselorMatching,
        KnowledgeBaseService $knowledgeBase,
        MemoryExtractionService $memoryExtraction,
        MemoryManagementService $memoryManagement
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
        $this->knowledgeBase = $knowledgeBase;
        $this->memoryExtraction = $memoryExtraction;
        $this->memoryManagement = $memoryManagement;
    }

    /**
     * Process user message and generate AI response.
     */
    public function chat(User $user, Conversation $conversation, string $userMessage): array
    {
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

        // 3. Get RAG context
        $contextData = $this->ragRetrieval->getSmartContext(
            $user,
            $userMessage,
            $conversation->id
        );

        // 4. Build prompt with system instructions
        $messages = $this->buildPrompt($user, $conversation, $userMessage, $contextData, $crisisFlags);

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

        $conversation->increment('message_count');

        // 7. Extract and save memories from user message (async in production)
        // This runs after the response to avoid blocking
        try {
            $extractedMemories = $this->memoryExtraction->extractMemoriesFromMessage($userMessageModel);
            if (!empty($extractedMemories)) {
                $this->memoryManagement->batchSaveMemories($user, $extractedMemories);
                Log::info('Saved memories from conversation', [
                    'user_id' => $user->id,
                    'message_id' => $userMessageModel->id,
                    'count' => count($extractedMemories)
                ]);
            }
        } catch (\Exception $e) {
            // Don't let memory extraction failures affect chat
            Log::error('Memory extraction failed', [
                'message_id' => $userMessageModel->id,
                'error' => $e->getMessage()
            ]);
        }

        // 8. Create embedding for user message (async in production)
        $this->knowledgeBase->createMessageEmbedding($userMessageModel);

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
    protected function buildPrompt(User $user, Conversation $conversation, string $userMessage, array $contextData, array $crisisFlags): array
    {
        // System prompt
        $systemPrompt = $this->getSystemPrompt($crisisFlags);

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

        // Add conversation history (last 10 messages, EXCLUDING the current message we just saved)
        // Get all messages ordered by creation time
        $allMessages = $conversation->messages()
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Skip the last message (current user message) and take the 10 before it
        $recentMessages = $allMessages->slice(max(0, $allMessages->count() - 11), 10);//

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
You are a compassionate and empathetic AI counselor for UniPulse, a university student support system. Your role is to:

1. Provide supportive, understanding, and non-judgmental responses to students
2. Help students with academic stress, personal problems, and general university concerns
3. Recognize signs of distress and provide appropriate support
4. Encourage students to seek professional help when needed
5. Maintain confidentiality and create a safe space for students

Guidelines:
- Be warm, empathetic, and supportive
- Ask clarifying questions to understand the student's situation better
- Provide practical advice and coping strategies
- Normalize their feelings and experiences
- Encourage self-care and healthy habits
- Know when to suggest professional counseling

Important:
- Never provide medical advice or diagnoses
- Always encourage students in crisis to seek immediate professional help
- Be culturally sensitive to the Sri Lankan context
- Use simple, clear language
PROMPT;

        // Adjust prompt based on crisis flags
        if (!empty($crisisFlags)) {
            $hasRed = collect($crisisFlags)->contains('severity', 'red');
            $hasYellow = collect($crisisFlags)->contains('severity', 'yellow');

            if ($hasRed) {
                $basePrompt .= <<<CRISIS


CRITICAL: The student has expressed serious distress. Your response should:
1. Acknowledge their pain with deep empathy
2. Reassure them that their feelings are valid and help is available
3. Strongly encourage them to reach out to crisis resources immediately
4. Be caring but firm about the importance of professional support
5. Avoid minimizing their feelings or offering quick fixes

Remember: This is a crisis situation. Professional intervention is essential.
CRISIS;
            } elseif ($hasYellow) {
                $basePrompt .= <<<CONCERNING


Note: The student is showing signs of emotional distress. Be especially:
1. Empathetic and validating of their feelings
2. Gentle in your approach
3. Clear about the availability of counseling support
4. Focused on immediate coping strategies
5. Encouraging about seeking additional support if needed
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
                $requestBody['max_completion_tokens'] = 800;
            } else {
                $requestBody['temperature'] = 0.7;
                $requestBody['max_tokens'] = 800;
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

            Log::error('OpenAI API error: ' . $response->body());
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

        // Get city from user profile
        $city = $user->university ?? null; // or parse from university name

        return $this->counselorMatching->getRecommendedCounselors(
            $user->id,
            $city,
            3
        )->toArray();
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

        return $topCounselors->map(fn($item) => [
            'id' => $item['counselor']->id,
            'name' => $item['counselor']->name,
            'title' => $item['counselor']->title,
            'hospital' => $item['counselor']->hospital,
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
        return match($category) {
            \App\Models\CrisisFlag::CATEGORY_SUICIDE_RISK => 'Suicide Prevention',
            \App\Models\CrisisFlag::CATEGORY_SELF_HARM => 'Self-Harm Support',
            \App\Models\CrisisFlag::CATEGORY_DEPRESSION => 'Depression',
            \App\Models\CrisisFlag::CATEGORY_ANXIETY => 'Anxiety',
            \App\Models\CrisisFlag::CATEGORY_STRESS => 'Stress Management',
            \App\Models\CrisisFlag::CATEGORY_LONELINESS => 'Loneliness & Social Support',
            \App\Models\CrisisFlag::CATEGORY_HOPELESSNESS => 'Hope & Resilience',
            \App\Models\CrisisFlag::CATEGORY_GENERAL => 'General Mental Health',
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
     * Generate conversation title based on first message.
     */
    public function generateConversationTitle(string $firstMessage): string
    {
        // Simple title generation - first few words
        $words = explode(' ', $firstMessage);
        $title = implode(' ', array_slice($words, 0, 6));

        if (count($words) > 6) {
            $title .= '...';
        }

        return $title ?: 'New Conversation';
    }
}
