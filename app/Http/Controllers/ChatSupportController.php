<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Memory;
use App\Models\ConversationEmbedding;
use App\Services\AiChatService;
use App\Services\KnowledgeBaseService;
use App\Services\MemoryManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatSupportController extends Controller
{
    protected AiChatService $aiChat;
    protected KnowledgeBaseService $knowledgeBase;
    protected MemoryManagementService $memoryManagement;

    public function __construct(
        AiChatService $aiChat, 
        KnowledgeBaseService $knowledgeBase,
        MemoryManagementService $memoryManagement
    ) {
        $this->aiChat = $aiChat;
        $this->knowledgeBase = $knowledgeBase;
        $this->memoryManagement = $memoryManagement;
    }

    /**
     * Show the chat support page.
     */
    public function index()
    {
        return view('chat-support');
    }

    /**
     * Start a new conversation.
     */
    public function startConversation(Request $request)
    {
        $request->validate([
            'initial_message' => 'required|string|max:5000',
            'topic' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        // Build user knowledge base on first conversation
        $existingConversations = Conversation::where('user_id', $user->id)->count();
        if ($existingConversations === 0) {
            try {
                $this->knowledgeBase->buildUserKnowledgeBase($user);
            } catch (\Exception $e) {
                Log::warning('Failed to build knowledge base: ' . $e->getMessage());
            }
        }

        // Create conversation
        $conversation = Conversation::create([
            'user_id' => $user->id,
            'title' => $this->aiChat->generateConversationTitle($request->initial_message),
            'initial_topic' => $request->topic,
            'status' => 'active',
        ]);

        // Process first message
        $response = $this->aiChat->chat($user, $conversation, $request->initial_message);

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'created_at' => $conversation->created_at,
            ],
            'response' => $response,
        ]);
    }

    /**
     * Send a message in an existing conversation.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'required|string|max:5000',
        ]);

        $user = Auth::user();
        $conversation = Conversation::findOrFail($request->conversation_id);

        // Verify user owns this conversation
        if ($conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to conversation',
            ], 403);
        }

        // Check if conversation is archived
        if ($conversation->status === 'archived') {
            return response()->json([
                'success' => false,
                'error' => 'Cannot send messages to archived conversation',
            ], 400);
        }

        try {
            $response = $this->aiChat->chat($user, $conversation, $request->message);

            return response()->json([
                'success' => true,
                'response' => $response,
                'conversation' => [
                    'message_count' => $conversation->fresh()->message_count,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Chat error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Failed to process message. Please try again.',
            ], 500);
        }
    }

    /**
     * Get conversation history with messages.
     */
    public function getConversation(Request $request, $id)
    {
        $user = Auth::user();
        $conversation = Conversation::with('messages')->findOrFail($id);

        // Verify user owns this conversation
        if ($conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to conversation',
            ], 403);
        }

        $messages = $conversation->messages->map(function ($message) {
            return [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'created_at' => $message->created_at,
                'formatted_time' => $message->getFormattedTime(),
            ];
        });

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'status' => $conversation->status,
                'message_count' => $conversation->message_count,
                'created_at' => $conversation->created_at,
                'last_message_at' => $conversation->last_message_at,
            ],
            'messages' => $messages,
        ]);
    }

    /**
     * List all conversations for the authenticated user.
     */
    public function listConversations(Request $request)
    {
        $user = Auth::user();
        
        $status = $request->query('status', 'active');
        
        $conversations = Conversation::where('user_id', $user->id)
            ->when($status !== 'all', function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->orderBy('last_message_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($conversation) {
                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'status' => $conversation->status,
                    'message_count' => $conversation->message_count,
                    'has_crisis_flags' => $conversation->hasCrisisFlags(),
                    'created_at' => $conversation->created_at,
                    'last_message_at' => $conversation->last_message_at,
                ];
            });

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
        ]);
    }

    /**
     * Archive a conversation.
     */
    public function archiveConversation(Request $request, $id)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);

        // Verify user owns this conversation
        if ($conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to conversation',
            ], 403);
        }

        $conversation->update(['status' => 'archived']);

        return response()->json([
            'success' => true,
            'message' => 'Conversation archived successfully',
        ]);
    }

    /**
     * Unarchive a conversation.
     */
    public function unarchiveConversation(Request $request, $id)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);

        // Verify user owns this conversation
        if ($conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to conversation',
            ], 403);
        }

        $conversation->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'Conversation restored successfully',
        ]);
    }

    /**
     * Rename a conversation.
     */
    public function renameConversation(Request $request, $id)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);

        // Verify user owns this conversation
        if ($conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to conversation',
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $conversation->update(['title' => $request->title]);

        Log::info('Conversation renamed', [
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'new_title' => $request->title,
        ]);

        return response()->json([
            'success' => true,
            'conversation' => [
                'id' => $conversation->id,
                'title' => $conversation->title,
                'updated_at' => $conversation->updated_at,
            ],
            'message' => 'Conversation renamed successfully',
        ]);
    }

    /**
     * Delete a conversation and ALL related data.
     * Note: Conversations with crisis flags cannot be deleted.
     */
    public function deleteConversation(Request $request, $id)
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);

        // Verify user owns this conversation
        if ($conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to conversation',
            ], 403);
        }

        // Delete all related data (cascade)
        $deletedData = [
            'messages' => 0,
            'embeddings' => 0,
            'memories' => 0,
            'crisis_flags' => 0,
            'crisis_alerts' => 0,
        ];

        try {
            // 1. Delete all crisis alerts linked to this conversation's flags
            $crisisFlagIds = $conversation->crisisFlags()->pluck('id')->toArray();
            if (!empty($crisisFlagIds)) {
                $deletedData['crisis_alerts'] = \App\Models\CrisisAlert::whereIn('crisis_flag_id', $crisisFlagIds)->delete();
            }
            
            // 2. Delete all crisis flags for this conversation
            $deletedData['crisis_flags'] = $conversation->crisisFlags()->delete();

            // 3. Delete all embeddings related to this conversation
            $deletedData['embeddings'] = ConversationEmbedding::where('conversation_id', $conversation->id)->delete();

            // 4. Delete all memories sourced from this conversation
            $deletedData['memories'] = Memory::where('source_conversation_id', $conversation->id)->delete();

            // 5. Delete all messages (will also remove message-specific embeddings and memories via cascade)
            $messageIds = $conversation->messages()->pluck('id')->toArray();
            if (!empty($messageIds)) {
                // Delete embeddings linked to messages
                ConversationEmbedding::whereIn('message_id', $messageIds)->delete();
                // Delete memories linked to messages
                Memory::whereIn('source_message_id', $messageIds)->delete();
                // Delete messages
                $deletedData['messages'] = $conversation->messages()->delete();
            }

            // 6. Finally delete the conversation itself
            $conversation->delete();

            Log::info('Conversation deleted with all references', [
                'conversation_id' => $id,
                'user_id' => $user->id,
                'deleted_data' => $deletedData,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Conversation and all related data deleted successfully',
                'deleted' => $deletedData,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete conversation', [
                'conversation_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to delete conversation. Please try again.',
            ], 500);
        }
    }

    /**
     * Get all memories for the authenticated user.
     */
    public function getMemories(Request $request)
    {
        $user = Auth::user();
        
        $category = $request->query('category');
        $minImportance = $request->query('min_importance');
        
        $memories = $this->memoryManagement->getUserMemories($user, $category, $minImportance);
        
        return response()->json([
            'success' => true,
            'memories' => $memories->map(function ($memory) {
                return [
                    'id' => $memory->id,
                    'category' => $memory->category,
                    'category_name' => $memory->category_name,
                    'memory_key' => $memory->memory_key,
                    'memory_value' => $memory->memory_value,
                    'importance_score' => $memory->importance_score,
                    'last_referenced_at' => $memory->last_referenced_at,
                    'created_at' => $memory->created_at,
                    'updated_at' => $memory->updated_at,
                ];
            }),
            'stats' => $this->memoryManagement->getMemoryStats($user),
        ]);
    }

    /**
     * Update a memory.
     */
    public function updateMemory(Request $request, $id)
    {
        $user = Auth::user();
        $memory = Memory::findOrFail($id);
        
        // Verify user owns this memory
        if ($memory->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to memory',
            ], 403);
        }
        
        $request->validate([
            'memory_value' => 'required|string|max:1000',
            'importance_score' => 'nullable|numeric|min:0|max:1',
        ]);
        
        $updated = $this->memoryManagement->updateMemory($memory, [
            'value' => $request->memory_value,
            'importance' => $request->importance_score ?? $memory->importance_score,
        ]);
        
        return response()->json([
            'success' => true,
            'memory' => [
                'id' => $updated->id,
                'category' => $updated->category,
                'memory_key' => $updated->memory_key,
                'memory_value' => $updated->memory_value,
                'importance_score' => $updated->importance_score,
                'updated_at' => $updated->updated_at,
            ],
            'message' => 'Memory updated successfully',
        ]);
    }

    /**
     * Delete a memory.
     */
    public function deleteMemory(Request $request, $id)
    {
        $user = Auth::user();
        $memory = Memory::findOrFail($id);
        
        // Verify user owns this memory
        if ($memory->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to memory',
            ], 403);
        }
        
        $this->memoryManagement->deleteMemory($memory);
        
        return response()->json([
            'success' => true,
            'message' => 'Memory deleted successfully',
        ]);
    }

    /**
     * Clear all memories for the authenticated user.
     */
    public function clearAllMemories(Request $request)
    {
        $user = Auth::user();
        
        $count = Memory::where('user_id', $user->id)->delete();
        
        Log::info('All memories cleared for user', [
            'user_id' => $user->id,
            'count' => $count,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'All memories cleared successfully',
            'deleted_count' => $count,
        ]);
    }
}