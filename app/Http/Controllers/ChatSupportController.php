<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Memory;
use App\Models\ConversationEmbedding;
use App\Services\AiChatService;
use App\Services\KnowledgeBaseService;
use App\Services\MemoryManagementService;
use App\Services\PineconeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ChatSupportController extends Controller
{
    // Handles all chat support, conversation, memory, and counselor-related endpoints.
    protected AiChatService $aiChat;
    // AI service used to generate replies and conversation titles.
    protected KnowledgeBaseService $knowledgeBase;
    // Service responsible for building and reading the user's knowledge base.
    protected MemoryManagementService $memoryManagement;
    // Service used to manage persistent user memories.
    protected PineconeService $pinecone;
    // Vector database service used for deleting and syncing embeddings.

    public function __construct(
        AiChatService $aiChat,
        KnowledgeBaseService $knowledgeBase,
        MemoryManagementService $memoryManagement,
        PineconeService $pinecone
    ) {
        // Store the AI chat service for later use in controller actions.
        $this->aiChat = $aiChat;
        // Store the knowledge base service for first-time user setup.
        $this->knowledgeBase = $knowledgeBase;
        // Store the memory management service for memory operations.
        $this->memoryManagement = $memoryManagement;
        // Store the Pinecone service for vector cleanup operations.
        $this->pinecone = $pinecone;
    }

    /**
     * Show the chat dashboard page with stats and recent conversations.
     */
    public function dashboard()
    {
        // Get the currently authenticated user.
        $user = Auth::user();

        // Count the user's active conversations.
        $activeChatsCount = Conversation::where('user_id', $user->id)->where('status', 'active')->count();
        // Count the user's archived conversations.
        $archivedChatsCount = Conversation::where('user_id', $user->id)->where('status', 'archived')->count();
        // Count crisis flags linked to the user.
        $totalCrisisFlags = \App\Models\CrisisFlag::where('user_id', $user->id)->count();
        // Find the most recent user message.
        $lastMessage = Message::where('user_id', $user->id)
            ->where('role', 'user')
            ->latest()
            ->first();
        // Convert the last message time into a human-readable relative time.
        $lastChatTime = $lastMessage ? $lastMessage->created_at->diffForHumans() : null;

        // Keep the rest of the dashboard data for other sections
        // Count all conversations belonging to the user.
        $totalConversations = Conversation::where('user_id', $user->id)->count();
        // Reuse the active conversation count for dashboard compatibility.
        $activeChats = $activeChatsCount;
        // Reuse the archived conversation count for dashboard compatibility.
        $archivedChats = $archivedChatsCount;
        // Count the number of user-authored messages.
        $totalMessagesSent = Message::where('user_id', $user->id)
            ->where('role', 'user')
            ->count();
        // Count how many memories exist for the user.
        $memoryCount = Memory::where('user_id', $user->id)->count();
        // Load the five most recent active conversations.
        $recentConversations = Conversation::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($conversation) {
                // Find the latest visible message inside this conversation.
                $lastMessage = $conversation->messages()
                    ->whereIn('role', ['user', 'assistant'])
                    ->orderByDesc('created_at')
                    ->first();

                // Build a compact summary for the dashboard UI.
                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'message_count' => $conversation->message_count,
                    // Show relative time based on the last activity or creation time.
                    'time_ago' => $conversation->last_message_at
                        ? $conversation->last_message_at->diffForHumans()
                        : $conversation->created_at->diffForHumans(),
                    // Show a short preview of the last message if available.
                    'last_message_preview' => $lastMessage
                        ? Str::limit($lastMessage->content, 80)
                        : null,
                    // Include whether the last message came from the user or assistant.
                    'last_message_role' => $lastMessage?->role,
                ];
            });

        // Return the dashboard view with all computed statistics.
        return view('chat-dashboard', [
            'activeChatsCount' => $activeChatsCount,
            'archivedChatsCount' => $archivedChatsCount,
            'totalCrisisFlags' => $totalCrisisFlags,
            'lastChatTime' => $lastChatTime,
            // legacy keys for other dashboard sections
            'totalConversations' => $totalConversations,
            'activeChats' => $activeChats,
            'archivedChats' => $archivedChats,
            'totalMessagesSent' => $totalMessagesSent,
            'memoryCount' => $memoryCount,
            'recentConversations' => $recentConversations,
        ]);
    }

    /**
     * Show the chat support page.
     */
    public function index()
    {
        // Render the main conversational support page.
        return view('chat-support');
    }

    /**
     * Start a new conversation.
     */
    public function startConversation(Request $request)
    {
        // Validate the initial user message and optional topic.
        $request->validate([
            'initial_message' => 'required|string|max:5000',
            'topic' => 'nullable|string|max:255',
        ]);

        // Get the authenticated user.
        $user = Auth::user();

        // Generate title for the conversation
        // Log the title generation request for debugging and traceability.
        Log::info('Starting conversation title generation', [
            'user_id' => $user->id,
            'initial_message_length' => strlen($request->initial_message),
            'initial_message_preview' => substr($request->initial_message, 0, 100) . (strlen($request->initial_message) > 100 ? '...' : ''),
        ]);

        // Ask the AI service to create a short title from the initial message.
        $title = $this->aiChat->generateAiConversationTitle($request->initial_message);

        // Log the generated title for debugging.
        Log::info('Conversation title generated', [
            'user_id' => $user->id,
            'generated_title' => $title,
            'title_length' => strlen($title),
        ]);

        // IDEMPOTENCY CHECK: Prevent duplicate conversations created by retries/double-submits
        // Check if an identical conversation was created in the last 10 seconds
        // Search for a recent conversation with the same title to avoid duplicate creation.
        $recentDuplicate = Conversation::where('user_id', $user->id)
            ->where('title', $title)
            ->whereRaw('created_at >= NOW() - INTERVAL 10 SECOND')
            ->first();

        // If a duplicate exists and already contains messages, reuse it instead of creating a new one.
        if ($recentDuplicate && $recentDuplicate->messages()->count() > 0) {
            // This is likely a retry - return the existing conversation
            // Log that duplicate creation was prevented.
            Log::info('Duplicate conversation creation prevented', [
                'user_id' => $user->id,
                'title' => $title,
                'existing_conversation_id' => $recentDuplicate->id,
            ]);

            // Reuse the existing conversation for the first user message.
            $response = $this->aiChat->chat($user, $recentDuplicate, $request->initial_message);

            // Return the reused conversation and AI response.
            return response()->json([
                'success' => true,
                'conversation' => [
                    'id' => $recentDuplicate->id,
                    'title' => $recentDuplicate->title,
                    'created_at' => $recentDuplicate->created_at,
                    'deduplicated' => true,
                ],
                'response' => $response,
            ]);
        }

        // Build user knowledge base on first conversation
        // Count how many conversations already exist for the user.
        $existingConversations = Conversation::where('user_id', $user->id)->count();
        // If this is the first conversation, try to build a knowledge base.
        if ($existingConversations === 0) {
            try {
                // Populate the user's knowledge base from available data.
                $this->knowledgeBase->buildUserKnowledgeBase($user);
            } catch (\Exception $e) {
                // Log the failure but continue creating the conversation.
                Log::warning('Failed to build knowledge base: ' . $e->getMessage());
            }
        }

        // Create conversation
        // Save the new conversation in the database.
        $conversation = Conversation::create([
            'user_id' => $user->id,
            'title' => $title,
            'initial_topic' => $request->topic,
            'status' => 'active',
        ]);

        // Process first message
        // Send the first message to the AI chat service.
        $response = $this->aiChat->chat($user, $conversation, $request->initial_message);

        // Return the created conversation and its first AI response.
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
        // Validate the conversation ID and message body.
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'message' => 'required|string|max:5000',
        ]);

        // Get the current user and the requested conversation.
        $user = Auth::user();
        $conversation = Conversation::findOrFail($request->conversation_id);

        // Verify user owns this conversation
        // Reject access if the conversation belongs to another user.
        if ($conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to conversation',
            ], 403);
        }

        // Check if conversation is archived
        // Prevent new messages from being added to archived conversations.
        if ($conversation->status === 'archived') {
            return response()->json([
                'success' => false,
                'error' => 'Cannot send messages to archived conversation',
            ], 400);
        }

        try {
            // Send the message to the AI service and get the reply.
            $response = $this->aiChat->chat($user, $conversation, $request->message);

            // Return the AI response and refreshed message count.
            return response()->json([
                'success' => true,
                'response' => $response,
                'conversation' => [
                    'message_count' => $conversation->fresh()->message_count,
                ],
            ]);
        } catch (\Exception $e) {
            // Log any unexpected chat failure.
            Log::error('Chat error: ' . $e->getMessage());

            // Return a generic error message to the client.
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
        // Get the current user and the full conversation history.
        $user = Auth::user();
        $conversation = Conversation::with('messages')->findOrFail($id);

        // Verify user owns this conversation
        // Reject access when the conversation is not owned by the authenticated user.
        if ($conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to conversation',
            ], 403);
        }

        // Format each message for front-end display.
        $messages = $conversation->messages->map(function ($message) {
            return [
                'id' => $message->id,
                'role' => $message->role,
                'content' => $message->content,
                'created_at' => $message->created_at->setTimezone(config('app.timezone'))->toIso8601String(),
                'formatted_time' => $message->getFormattedTime(),
            ];
        });

        // Return the conversation metadata and message history.
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
        // Get the current user and requested status filter.
        $user = Auth::user();

        // Default to active conversations unless the client requests otherwise.
        $status = $request->query('status', 'active');

        // Fetch conversations for the user and transform them for the UI.
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

        // Return the filtered list of conversations.
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
        // Get the current user and the conversation to archive.
        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);

        // Verify user owns this conversation
        // Prevent archiving conversations owned by other users.
        if ($conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to conversation',
            ], 403);
        }

        // Mark the conversation as archived.
        $conversation->update(['status' => 'archived']);

        // Confirm success to the client.
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
        // Get the current user and the conversation to restore.
        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);

        // Verify user owns this conversation
        // Prevent restoring conversations owned by other users.
        if ($conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to conversation',
            ], 403);
        }

        // Mark the conversation as active again.
        $conversation->update(['status' => 'active']);

        // Confirm restoration to the client.
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
        // Get the current user and the conversation to rename.
        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);

        // Verify user owns this conversation
        // Prevent renaming a conversation that belongs to another user.
        if ($conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to conversation',
            ], 403);
        }

        // Validate the new title.
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        // Save the new title.
        $conversation->update(['title' => $request->title]);

        // Log the rename operation for audit purposes.
        Log::info('Conversation renamed', [
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'new_title' => $request->title,
        ]);

        // Return the updated conversation details.
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
     */
    public function deleteConversation(Request $request, $id)
    {
        // Get the current user and the conversation to delete.
        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);

        // Verify user owns this conversation
        // Stop deletion if the conversation belongs to another user.
        if ($conversation->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to conversation',
            ], 403);
        }

        // Delete all related data (cascade)
        // Track how much related data gets removed.
        $deletedData = [
            'messages' => 0,
            'embeddings' => 0,
            'memories' => 0,
            'crisis_flags' => 0,
            'crisis_alerts' => 0,
        ];

        try {
            // 1. Delete all crisis alerts linked to this conversation's flags
            // Collect crisis flag IDs for the current conversation.
            $crisisFlagIds = $conversation->crisisFlags()->pluck('id')->toArray();
            if (!empty($crisisFlagIds)) {
                // Delete alerts that belong to those crisis flags.
                $deletedData['crisis_alerts'] = \App\Models\CrisisAlert::whereIn('crisis_flag_id', $crisisFlagIds)->delete();
            }

            // 2. Delete all crisis flags for this conversation
            // Remove the crisis flags themselves.
            $deletedData['crisis_flags'] = $conversation->crisisFlags()->delete();

            // 3. Delete all embeddings related to this conversation
            // Remove conversation-level vector embeddings.
            $deletedData['embeddings'] = ConversationEmbedding::where('conversation_id', $conversation->id)->delete();

            // 4. Delete all memories sourced from this conversation
            // Remove memories generated from this conversation.
            $deletedData['memories'] = Memory::where('source_conversation_id', $conversation->id)->delete();

            // 5. Delete all messages (will also remove message-specific embeddings and memories via cascade)
            // Collect all message IDs before deleting the messages.
            $messageIds = $conversation->messages()->pluck('id')->toArray();
            if (!empty($messageIds)) {
                // Delete embeddings linked to messages
                // Remove embeddings for individual messages.
                ConversationEmbedding::whereIn('message_id', $messageIds)->delete();
                // Delete memories linked to messages
                // Remove memories sourced from individual messages.
                Memory::whereIn('source_message_id', $messageIds)->delete();
                // Delete messages
                // Remove the conversation messages themselves.
                $deletedData['messages'] = $conversation->messages()->delete();
            }

            // 6. Clean up Pinecone vectors for this conversation
            // Remove vectors in Pinecone for this conversation.
            $this->pinecone->deleteByFilter(['conversation_id' => (int) $conversation->id]);

            // 7. Finally delete the conversation itself
            // Delete the conversation record after related data is removed.
            $conversation->delete();

            // Log the full deletion summary.
            Log::info('Conversation deleted with all references', [
                'conversation_id' => $id,
                'user_id' => $user->id,
                'deleted_data' => $deletedData,
            ]);

            // Return the deletion summary to the client.
            return response()->json([
                'success' => true,
                'message' => 'Conversation and all related data deleted successfully',
                'deleted' => $deletedData,
            ]);

        } catch (\Exception $e) {
            // Log the failure and the conversation ID for troubleshooting.
            Log::error('Failed to delete conversation', [
                'conversation_id' => $id,
                'error' => $e->getMessage(),
            ]);

            // Return a generic deletion failure message.
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
        // Get the current user and optional filters.
        $user = Auth::user();

        // Read memory filter inputs from the request.
        $category = $request->query('category');
        $minImportance = $request->query('min_importance');

        // Fetch the filtered set of user memories.
        $memories = $this->memoryManagement->getUserMemories($user, $category, $minImportance);

        // Return the memories and summary statistics.
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
        // Get the current user and the memory item to update.
        $user = Auth::user();
        $memory = Memory::findOrFail($id);

        // Verify user owns this memory
        // Prevent editing memories that belong to another user.
        if ($memory->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to memory',
            ], 403);
        }

        // Validate the updated memory fields.
        $request->validate([
            'memory_value' => 'required|string|max:1000',
            'importance_score' => 'nullable|numeric|min:0|max:1',
        ]);

        // Update the memory through the dedicated service.
        $updated = $this->memoryManagement->updateMemory($memory, [
            'value' => $request->memory_value,
            'importance' => $request->importance_score ?? $memory->importance_score,
        ]);

        // Return the refreshed memory details.
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
        // Get the current user and the memory item to delete.
        $user = Auth::user();
        $memory = Memory::findOrFail($id);

        // Verify user owns this memory
        // Prevent deletion of another user's memory.
        if ($memory->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access to memory',
            ], 403);
        }

        // Delete the memory through the service so related cleanup also happens.
        $this->memoryManagement->deleteMemory($memory);

        // Confirm successful deletion.
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
        // Get the current user whose memories should be cleared.
        $user = Auth::user();

        // Delete all database memories for this user.
        $count = Memory::where('user_id', $user->id)->delete();

        // Clean up memory vectors from Pinecone
        // Remove the matching memory vectors from Pinecone.
        $this->pinecone->deleteByFilter([
            'user_id' => (int) $user->id,
            'type' => 'memory',
        ]);

        // Log the cleanup for auditing.
        Log::info('All memories cleared for user', [
            'user_id' => $user->id,
            'count' => $count,
        ]);

        // Return the number of deleted memories.
        return response()->json([
            'success' => true,
            'message' => 'All memories cleared successfully',
            'deleted_count' => $count,
        ]);
    }

    /**
     * Archive all active conversations for the authenticated user.
     */
    public function archiveAllConversations(Request $request)
    {
        // Get the current user.
        $user = Auth::user();

        // Archive every active conversation owned by the user.
        $count = Conversation::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'archived']);

        // Log the bulk archive action.
        Log::info('All active conversations archived', [
            'user_id' => $user->id,
            'count' => $count,
        ]);

        // Confirm the bulk archive action.
        return response()->json([
            'success' => true,
            'message' => 'All active conversations archived successfully',
            'archived_count' => $count,
        ]);
    }

    /**
     * Unarchive all archived conversations for the authenticated user.
     */
    public function unarchiveAllConversations(Request $request)
    {
        // Get the current user.
        $user = Auth::user();

        // Restore every archived conversation owned by the user.
        $count = Conversation::where('user_id', $user->id)
            ->where('status', 'archived')
            ->update(['status' => 'active']);

        // Log the bulk restore action.
        Log::info('All archived conversations unarchived', [
            'user_id' => $user->id,
            'count' => $count,
        ]);

        // Confirm the bulk restore action.
        return response()->json([
            'success' => true,
            'message' => 'All archived conversations restored successfully',
            'unarchived_count' => $count,
        ]);
    }

    /**
     * Delete all active conversations for the authenticated user.
     */
    public function deleteAllActiveConversations(Request $request)
    {
        // Get the current user.
        $user = Auth::user();

        // Load all active conversations owned by the user.
        $conversations = Conversation::where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        // Track how many conversations are successfully deleted.
        $deletedCount = 0;

        // Delete each active conversation together with its related data.
        foreach ($conversations as $conversation) {
            try {
                // Delete all related data
                // Delete crisis alerts linked to this conversation.
                $crisisFlagIds = $conversation->crisisFlags()->pluck('id')->toArray();
                if (!empty($crisisFlagIds)) {
                    \App\Models\CrisisAlert::whereIn('crisis_flag_id', $crisisFlagIds)->delete();
                }

                // Remove crisis flags, embeddings, memories, and messages.
                $conversation->crisisFlags()->delete();
                ConversationEmbedding::where('conversation_id', $conversation->id)->delete();
                Memory::where('source_conversation_id', $conversation->id)->delete();

                // Remove message-linked embeddings and memories before deleting the messages.
                $messageIds = $conversation->messages()->pluck('id')->toArray();
                if (!empty($messageIds)) {
                    ConversationEmbedding::whereIn('message_id', $messageIds)->delete();
                    Memory::whereIn('source_message_id', $messageIds)->delete();
                    $conversation->messages()->delete();
                }

                // Delete the conversation record itself.
                $conversation->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                // Log any single conversation deletion failure and continue.
                Log::error('Failed to delete conversation in bulk delete', [
                    'conversation_id' => $conversation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Log the bulk deletion summary.
        Log::info('All active conversations deleted', [
            'user_id' => $user->id,
            'count' => $deletedCount,
        ]);

        // Return the number of deleted conversations.
        return response()->json([
            'success' => true,
            'message' => 'All active conversations deleted successfully',
            'deleted_count' => $deletedCount,
        ]);
    }

    /**
     * Delete all archived conversations for the authenticated user.
     */
    public function deleteAllArchivedConversations(Request $request)
    {
        // Get the current user.
        $user = Auth::user();

        // Load all archived conversations owned by the user.
        $conversations = Conversation::where('user_id', $user->id)
            ->where('status', 'archived')
            ->get();

        // Track how many conversations are successfully deleted.
        $deletedCount = 0;

        // Delete each archived conversation together with related data.
        foreach ($conversations as $conversation) {
            try {
                // Delete all related data
                // Delete crisis alerts linked to this conversation.
                $crisisFlagIds = $conversation->crisisFlags()->pluck('id')->toArray();
                if (!empty($crisisFlagIds)) {
                    \App\Models\CrisisAlert::whereIn('crisis_flag_id', $crisisFlagIds)->delete();
                }

                // Remove crisis flags, embeddings, memories, and messages.
                $conversation->crisisFlags()->delete();
                ConversationEmbedding::where('conversation_id', $conversation->id)->delete();
                Memory::where('source_conversation_id', $conversation->id)->delete();

                // Remove message-linked embeddings and memories before deleting the messages.
                $messageIds = $conversation->messages()->pluck('id')->toArray();
                if (!empty($messageIds)) {
                    ConversationEmbedding::whereIn('message_id', $messageIds)->delete();
                    Memory::whereIn('source_message_id', $messageIds)->delete();
                    $conversation->messages()->delete();
                }

                // Delete the conversation record itself.
                $conversation->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                // Log any single conversation deletion failure and continue.
                Log::error('Failed to delete conversation in bulk delete', [
                    'conversation_id' => $conversation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Log the bulk deletion summary.
        Log::info('All archived conversations deleted', [
            'user_id' => $user->id,
            'count' => $deletedCount,
        ]);

        // Return the number of deleted conversations.
        return response()->json([
            'success' => true,
            'message' => 'All archived conversations deleted successfully',
            'deleted_count' => $deletedCount,
        ]);
    }

    /**
     * Search conversations by title or message content.
     */
    public function searchConversations(Request $request)
    {
        // Get the current user and the search query.
        $user = Auth::user();
        $q = trim($request->query('q', ''));

        // Ignore very short queries.
        if (strlen($q) < 2) {
            return response()->json(['success' => true, 'conversations' => []]);
        }

        // Escape LIKE wildcards to prevent unintended matches
        // Build a safe SQL LIKE pattern.
        $safeLike = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';

        // Search conversation titles and message content for matches.
        $conversations = Conversation::where('user_id', $user->id)
            ->where(function ($query) use ($safeLike) {
                $query->where('title', 'LIKE', $safeLike)
                    ->orWhereHas('messages', function ($mq) use ($safeLike) {
                        $mq->whereIn('role', ['user', 'assistant'])
                            ->where('content', 'LIKE', $safeLike);
                    });
            })
            ->orderBy('last_message_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($c) use ($q, $safeLike) {
                // Find a matching message snippet to show as preview
                $snippet = null;
                $matchedMessage = $c->messages()
                    ->where('content', 'LIKE', $safeLike)
                    ->whereIn('role', ['user', 'assistant'])
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($matchedMessage) {
                    $content = $matchedMessage->content;
                    $pos = mb_stripos($content, $q);
                    if ($pos !== false) {
                        $start = max(0, $pos - 30);
                        $snippet = ($start > 0 ? '...' : '') . mb_substr($content, $start, 80) . (mb_strlen($content) > $start + 80 ? '...' : '');
                    }
                }

                // Return the search result item with an optional snippet.
                return [
                    'id' => $c->id,
                    'title' => $c->title,
                    'status' => $c->status,
                    'snippet' => $snippet,
                    'message_count' => $c->message_count,
                    'last_message_at' => $c->last_message_at ? $c->last_message_at->diffForHumans() : null,
                ];
            });

        // Return the matching conversations.
        return response()->json(['success' => true, 'conversations' => $conversations]);
    }

    /**
     * Export a conversation as Markdown or plain text.
     */
    public function exportConversation(Request $request, $id)
    {
        // Get the current user and the requested conversation.
        $user = Auth::user();
        $conversation = Conversation::with([
            'messages' => function ($q) {
                $q->whereIn('role', ['user', 'assistant'])->orderBy('created_at', 'asc');
            }
        ])->findOrFail($id);

        // Stop the export if the conversation is not owned by the user.
        if ($conversation->user_id !== $user->id) {
            abort(403);
        }

        // Read the export format from the request.
        $format = $request->query('format', 'txt');
        // Prepare the text content line by line.
        $lines = [];
        // Add the title header.
        $lines[] = "# {$conversation->title}";
        // Add the download timestamp.
        $lines[] = "Downloaded: " . now()->format('Y-m-d H:i:s');
        // Add a separator.
        $lines[] = str_repeat('-', 50);
        // Add a blank line after the header.
        $lines[] = "";

        // Render each message in chronological order.
        foreach ($conversation->messages as $message) {
            // Show the role in user-friendly text.
            $role = $message->role === 'user' ? 'You' : 'UniPulse AI';
            // Format the message timestamp.
            $msgTime = $message->created_at->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
            // Add the speaker line.
            $lines[] = "**{$role}** ({$msgTime})";
            // Add the raw message content.
            $lines[] = $message->content;
            // Add spacing between messages.
            $lines[] = "";
        }

        // Add the export footer separator.
        $lines[] = str_repeat('-', 50);
        // Add the safety disclaimer.
        $lines[] = "**Important:** This conversation was generated by an AI support tool and is not a substitute for professional mental health advice.";
        // Add emergency helpline guidance.
        $lines[] = "If you or someone you know needs immediate help, please call:";
        // Add the first emergency contact.
        $lines[] = "- **1926** — National Mental Health Helpline (NIMH) — 24/7";
        // Add the second emergency contact.
        $lines[] = "- **1333** — CCCline Crisis Support — 24/7";
        // Add the third emergency contact.
        $lines[] = "- **119** — Emergency Services";

        // Combine all lines into one downloadable text blob.
        $content = implode("\n", $lines);
        // Build the export filename.
        $filename = "unipulse-chat-" . $conversation->id . "-" . date('Y-m-d') . ".{$format}";

        // Return the content as a downloadable text file.
        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Submit feedback (thumbs up/down) on an assistant message.
     */
    public function submitFeedback(Request $request, $id)
    {
        // Validate the feedback value and optional reason.
        $request->validate([
            'feedback' => 'required|in:1,-1',
            'reason' => 'nullable|string|max:500',
        ]);

        // Get the current user and the target message.
        $user = Auth::user();
        $message = Message::with('conversation')->findOrFail($id);

        // Stop if the message does not belong to the user's conversation.
        if ($message->conversation->user_id !== $user->id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        // Load message metadata, then store the feedback values.
        $metadata = $message->metadata ?? [];
        $metadata['feedback'] = (int) $request->feedback;
        $metadata['feedback_reason'] = $request->reason ?? null;
        $metadata['feedback_at'] = now()->toISOString();
        $message->update(['metadata' => $metadata]);

        // Confirm feedback submission.
        return response()->json(['success' => true]);
    }

    /**
     * Regenerate the last assistant response in a conversation.
     */
    public function regenerateMessage(Request $request, $id)
    {
        // Get the current user, assistant message, and conversation.
        $user = Auth::user();
        $assistantMessage = Message::with('conversation')->findOrFail($id);
        $conversation = $assistantMessage->conversation;

        // Prevent regeneration if the message belongs to another user.
        if ($conversation->user_id !== $user->id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        // Only assistant messages may be regenerated.
        if ($assistantMessage->role !== 'assistant') {
            return response()->json(['success' => false, 'error' => 'Only assistant messages can be regenerated'], 400);
        }

        // Find the preceding user message
        // Attempt to locate the user message before the assistant reply.
        $userMessage = Message::where('conversation_id', $conversation->id)
            ->where('role', 'user')
            ->where('created_at', '<=', $assistantMessage->created_at)
            ->orderBy('created_at', 'desc')
            ->skip(1) // skip itself if roles interleave; pick the user message before
            ->first();

        // Simpler: get last user message before this assistant message
        // Replace the earlier lookup with the direct previous user message search.
        $userMessage = Message::where('conversation_id', $conversation->id)
            ->where('role', 'user')
            ->where('created_at', '<', $assistantMessage->created_at)
            ->orderBy('created_at', 'desc')
            ->first();

        // Abort if there is no prior user message.
        if (!$userMessage) {
            return response()->json(['success' => false, 'error' => 'No user message found to regenerate from'], 400);
        }

        // Delete the old assistant message and its embeddings
        // Remove embeddings connected to the assistant message.
        ConversationEmbedding::where('message_id', $assistantMessage->id)->delete();
        // Delete the assistant message itself.
        $assistantMessage->delete();
        // Reduce the conversation message count to reflect the deletion.
        $conversation->decrement('message_count');

        // Re-run the chat with the same user message text
        try {
            // Generate a fresh assistant response from the same user prompt.
            $response = $this->aiChat->chat($user, $conversation, $userMessage->content);

            // Return the regenerated response.
            return response()->json([
                'success' => true,
                'response' => $response,
            ]);
        } catch (\Exception $e) {
            // Log regeneration errors for debugging.
            Log::error('Regenerate failed: ' . $e->getMessage());
            // Return a regeneration failure message.
            return response()->json(['success' => false, 'error' => 'Failed to regenerate response. Please try again.'], 500);
        }
    }

    /**
     * Get all counselors grouped by category.
     */
    public function getCounselors(Request $request)
    {
        // Load all counselors sorted by category and name.
        $counselors = \App\Models\Counselor::orderBy('category')
            ->orderBy('name')
            ->get();

        // Group by category
        // Convert the flat list into grouped counselor categories.
        $grouped = $counselors->groupBy('category')->map(function ($items, $category) {
            return [
                'category' => $category,
                // Convert the category value into a display label.
                'label' => $this->getCounselorCategoryLabel($category),
                // Map each counselor into a small response payload.
                'counselors' => $items->map(function ($counselor) {
                    return [
                        'id' => $counselor->id,
                        'name' => $counselor->name,
                        'title' => $counselor->title,
                        'hospital' => $counselor->hospital,
                    ];
                })->values(),
            ];
        })->values();

        // Return counselor categories and total count.
        return response()->json([
            'success' => true,
            'categories' => $grouped,
            'total' => $counselors->count(),
        ]);
    }

    /**
     * Get display label for counselor category.
     * Now returns the category directly since full names are stored.
     */
    private function getCounselorCategoryLabel(string $category): string
    {
        // Categories are now stored as full names, return as-is
        // Return the category exactly as stored in the database.
        return $category;
    }

    /**
     * Get counselors by a specific category.
     */
    public function getCounselorsByCategory(Request $request, string $category)
    {
        // Load all counselors from the requested category.
        $counselors = \App\Models\Counselor::where('category', $category)
            ->orderBy('name')
            ->get();

        // Format each counselor for the response.
        $formattedCounselors = $counselors->map(function ($counselor) {
            return [
                'id' => $counselor->id,
                'name' => $counselor->name,
                'title' => $counselor->title,
                'hospital' => $counselor->hospital,
            ];
        });

        // Return the selected category and its counselors.
        return response()->json([
            'success' => true,
            'category' => $category,
            'label' => $this->getCounselorCategoryLabel($category),
            'counselors' => $formattedCounselors,
            'total' => $counselors->count(),
        ]);
    }
}