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
    protected bool $useGitHubModels;

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
        $this->useGitHubModels = config('services.openai.use_github_models', true);
        $this->apiKey = $this->useGitHubModels 
            ? config('services.openai.github_token')
            : config('services.openai.api_key');
        $this->model = config('services.openai.model', 'o3');
        $this->apiUrl = config('services.openai.api_url');

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

        // 9. If crisis flags detected, include counselor recommendations
        $counselorRecommendations = [];
        $crisisResources = [];
        
        if (!empty($crisisFlags)) {
            $counselorRecommendations = $this->getCounselorRecommendations($user, $crisisFlags);
            $crisisResources = $this->getCrisisResources($crisisFlags);
            
            // Create crisis alert for red flags
            foreach ($crisisFlags as $flag) {
                if ($flag['severity'] === 'red') {
                    $crisisFlag = $conversation->crisisFlags()
                        ->where('message_id', $userMessageModel->id)
                        ->first();
                    
                    if ($crisisFlag) {
                        $this->crisisAlert->createCrisisAlert($crisisFlag);
                    }
                }
            }
        }

        return [
            'message' => $aiResponse,
            'message_id' => $assistantMessage->id,
            'crisis_flags' => $crisisFlags, // Only for internal use, not shown to student
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
        $recentMessages = $allMessages->slice(max(0, $allMessages->count() - 11), 10);

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
     */
    protected function generateResponse(array $messages): ?string
    {
        try {
            // Build request body
            $requestBody = [
                'model' => $this->model,
                'messages' => $messages,
            ];

            // GitHub Models o3 doesn't support temperature or max_tokens
            if ($this->useGitHubModels) {
                $requestBody['max_completion_tokens'] = 800;
                // o3 only supports temperature = 1.0 (default), so we don't include it
            } else {
                $requestBody['temperature'] = 0.7;
                $requestBody['max_tokens'] = 800;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])
            ->timeout(30)
            ->post($this->apiUrl, $requestBody);

            if ($response->successful()) {
                return $response->json('choices.0.message.content');
            }

            Log::error('OpenAI API error: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('AI response generation failed: ' . $e->getMessage());
            return null;
        }
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
