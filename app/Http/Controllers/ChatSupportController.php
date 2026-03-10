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
    protected AiChatService $aiChat;
    protected KnowledgeBaseService $knowledgeBase;
    protected MemoryManagementService $memoryManagement;
    protected PineconeService $pinecone;

    public function __construct(
        AiChatService $aiChat,
        KnowledgeBaseService $knowledgeBase,
        MemoryManagementService $memoryManagement,
        PineconeService $pinecone
    ) {
        $this->aiChat = $aiChat;
        $this->knowledgeBase = $knowledgeBase;
        $this->memoryManagement = $memoryManagement;
        $this->pinecone = $pinecone;
    }

    /**
     * Show the chat dashboard page with stats and recent conversations.
     */
    public function dashboard()
    {
        $user = Auth::user();

        $activeChatsCount = Conversation::where('user_id', $user->id)->where('status', 'active')->count();
        $archivedChatsCount = Conversation::where('user_id', $user->id)->where('status', 'archived')->count();
        $totalCrisisFlags = \App\Models\CrisisFlag::where('user_id', $user->id)->count();
        $lastMessage = Message::where('user_id', $user->id)
            ->where('role', 'user')
            ->latest()
            ->first();
        $lastChatTime = $lastMessage ? $lastMessage->created_at->diffForHumans() : null;

        // Keep the rest of the dashboard data for other sections
        $totalConversations = Conversation::where('user_id', $user->id)->count();
        $activeChats = $activeChatsCount;
        $archivedChats = $archivedChatsCount;
        $totalMessagesSent = Message::where('user_id', $user->id)
            ->where('role', 'user')
            ->count();
        $memoryCount = Memory::where('user_id', $user->id)->count();
        $recentConversations = Conversation::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderByDesc('last_message_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(function ($conversation) {
                $lastMessage = $conversation->messages()
                    ->whereIn('role', ['user', 'assistant'])
                    ->orderByDesc('created_at')
                    ->first();

                return [
                    'id' => $conversation->id,
                    'title' => $conversation->title,
                    'message_count' => $conversation->message_count,
                    'time_ago' => $conversation->last_message_at
                        ? $conversation->last_message_at->diffForHumans()
                        : $conversation->created_at->diffForHumans(),
                    'last_message_preview' => $lastMessage
                        ? Str::limit($lastMessage->content, 80)
                        : null,
                    'last_message_role' => $lastMessage?->role,
                ];
            });

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

        // Generate title for the conversation
        $title = $this->aiChat->generateAiConversationTitle($request->initial_message);

        // IDEMPOTENCY CHECK: Prevent duplicate conversations created by retries/double-submits
        // Check if an identical conversation was created in the last 10 seconds
        $recentDuplicate = Conversation::where('user_id', $user->id)
            ->where('title', $title)
            ->whereRaw('created_at >= NOW() - INTERVAL 10 SECOND')
            ->first();

        if ($recentDuplicate && $recentDuplicate->messages()->count() > 0) {
            // This is likely a retry - return the existing conversation
            Log::info('Duplicate conversation creation prevented', [
                'user_id' => $user->id,
                'title' => $title,
                'existing_conversation_id' => $recentDuplicate->id,
            ]);

            $response = $this->aiChat->chat($user, $recentDuplicate, $request->initial_message);

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
            'title' => $title,
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
                'created_at' => $message->created_at->setTimezone(config('app.timezone'))->toIso8601String(),
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

            // 6. Clean up Pinecone vectors for this conversation
            $this->pinecone->deleteByFilter(['conversation_id' => (int) $conversation->id]);

            // 7. Finally delete the conversation itself
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

        // Clean up memory vectors from Pinecone
        $this->pinecone->deleteByFilter([
            'user_id' => (int) $user->id,
            'type'    => 'memory',
        ]);

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

    /**
     * Archive all active conversations for the authenticated user.
     */
    public function archiveAllConversations(Request $request)
    {
        $user = Auth::user();

        $count = Conversation::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'archived']);

        Log::info('All active conversations archived', [
            'user_id' => $user->id,
            'count' => $count,
        ]);

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
        $user = Auth::user();

        $count = Conversation::where('user_id', $user->id)
            ->where('status', 'archived')
            ->update(['status' => 'active']);

        Log::info('All archived conversations unarchived', [
            'user_id' => $user->id,
            'count' => $count,
        ]);

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
        $user = Auth::user();

        $conversations = Conversation::where('user_id', $user->id)
            ->where('status', 'active')
            ->get();

        $deletedCount = 0;

        foreach ($conversations as $conversation) {
            try {
                // Delete all related data
                $crisisFlagIds = $conversation->crisisFlags()->pluck('id')->toArray();
                if (!empty($crisisFlagIds)) {
                    \App\Models\CrisisAlert::whereIn('crisis_flag_id', $crisisFlagIds)->delete();
                }

                $conversation->crisisFlags()->delete();
                ConversationEmbedding::where('conversation_id', $conversation->id)->delete();
                Memory::where('source_conversation_id', $conversation->id)->delete();

                $messageIds = $conversation->messages()->pluck('id')->toArray();
                if (!empty($messageIds)) {
                    ConversationEmbedding::whereIn('message_id', $messageIds)->delete();
                    Memory::whereIn('source_message_id', $messageIds)->delete();
                    $conversation->messages()->delete();
                }

                $conversation->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                Log::error('Failed to delete conversation in bulk delete', [
                    'conversation_id' => $conversation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('All active conversations deleted', [
            'user_id' => $user->id,
            'count' => $deletedCount,
        ]);

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
        $user = Auth::user();

        $conversations = Conversation::where('user_id', $user->id)
            ->where('status', 'archived')
            ->get();

        $deletedCount = 0;

        foreach ($conversations as $conversation) {
            try {
                // Delete all related data
                $crisisFlagIds = $conversation->crisisFlags()->pluck('id')->toArray();
                if (!empty($crisisFlagIds)) {
                    \App\Models\CrisisAlert::whereIn('crisis_flag_id', $crisisFlagIds)->delete();
                }

                $conversation->crisisFlags()->delete();
                ConversationEmbedding::where('conversation_id', $conversation->id)->delete();
                Memory::where('source_conversation_id', $conversation->id)->delete();

                $messageIds = $conversation->messages()->pluck('id')->toArray();
                if (!empty($messageIds)) {
                    ConversationEmbedding::whereIn('message_id', $messageIds)->delete();
                    Memory::whereIn('source_message_id', $messageIds)->delete();
                    $conversation->messages()->delete();
                }

                $conversation->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                Log::error('Failed to delete conversation in bulk delete', [
                    'conversation_id' => $conversation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('All archived conversations deleted', [
            'user_id' => $user->id,
            'count' => $deletedCount,
        ]);

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
        $user = Auth::user();
        $q = trim($request->query('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['success' => true, 'conversations' => []]);
        }

        // Escape LIKE wildcards to prevent unintended matches
        $safeLike = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';

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

                return [
                    'id' => $c->id,
                    'title' => $c->title,
                    'status' => $c->status,
                    'snippet' => $snippet,
                    'message_count' => $c->message_count,
                    'last_message_at' => $c->last_message_at ? $c->last_message_at->diffForHumans() : null,
                ];
            });

        return response()->json(['success' => true, 'conversations' => $conversations]);
    }

    /**
     * Export a conversation as Markdown or plain text.
     */
    public function exportConversation(Request $request, $id)
    {
        $user = Auth::user();
        $conversation = Conversation::with([
            'messages' => function ($q) {
                $q->whereIn('role', ['user', 'assistant'])->orderBy('created_at', 'asc');
            }
        ])->findOrFail($id);

        if ($conversation->user_id !== $user->id) {
            abort(403);
        }

        $format = $request->query('format', 'txt');
        $lines = [];
        $lines[] = "# {$conversation->title}";
        $lines[] = "Downloaded: " . now()->format('Y-m-d H:i:s');
        $lines[] = str_repeat('-', 50);
        $lines[] = "";

        foreach ($conversation->messages as $message) {
            $role = $message->role === 'user' ? 'You' : 'UniPulse AI';
            $msgTime = $message->created_at->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
            $lines[] = "**{$role}** ({$msgTime})";
            $lines[] = $message->content;
            $lines[] = "";
        }

        $lines[] = str_repeat('-', 50);
        $lines[] = "**Important:** This conversation was generated by an AI support tool and is not a substitute for professional mental health advice.";
        $lines[] = "If you or someone you know needs immediate help, please call:";
        $lines[] = "- **1926** — National Mental Health Helpline (NIMH) — 24/7";
        $lines[] = "- **1333** — CCCline Crisis Support — 24/7";
        $lines[] = "- **119** — Emergency Services";

        $content = implode("\n", $lines);
        $filename = "unipulse-chat-" . $conversation->id . "-" . date('Y-m-d') . ".{$format}";

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
        $request->validate([
            'feedback' => 'required|in:1,-1',
            'reason' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $message = Message::with('conversation')->findOrFail($id);

        if ($message->conversation->user_id !== $user->id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        $metadata = $message->metadata ?? [];
        $metadata['feedback'] = (int) $request->feedback;
        $metadata['feedback_reason'] = $request->reason ?? null;
        $metadata['feedback_at'] = now()->toISOString();
        $message->update(['metadata' => $metadata]);

        return response()->json(['success' => true]);
    }

    /**
     * Regenerate the last assistant response in a conversation.
     */
    public function regenerateMessage(Request $request, $id)
    {
        $user = Auth::user();
        $assistantMessage = Message::with('conversation')->findOrFail($id);
        $conversation = $assistantMessage->conversation;

        if ($conversation->user_id !== $user->id) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 403);
        }

        if ($assistantMessage->role !== 'assistant') {
            return response()->json(['success' => false, 'error' => 'Only assistant messages can be regenerated'], 400);
        }

        // Find the preceding user message
        $userMessage = Message::where('conversation_id', $conversation->id)
            ->where('role', 'user')
            ->where('created_at', '<=', $assistantMessage->created_at)
            ->orderBy('created_at', 'desc')
            ->skip(1) // skip itself if roles interleave; pick the user message before
            ->first();

        // Simpler: get last user message before this assistant message
        $userMessage = Message::where('conversation_id', $conversation->id)
            ->where('role', 'user')
            ->where('created_at', '<', $assistantMessage->created_at)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$userMessage) {
            return response()->json(['success' => false, 'error' => 'No user message found to regenerate from'], 400);
        }

        // Delete the old assistant message and its embeddings
        ConversationEmbedding::where('message_id', $assistantMessage->id)->delete();
        $assistantMessage->delete();
        $conversation->decrement('message_count');

        // Re-run the chat with the same user message text
        try {
            $response = $this->aiChat->chat($user, $conversation, $userMessage->content);

            return response()->json([
                'success' => true,
                'response' => $response,
            ]);
        } catch (\Exception $e) {
            Log::error('Regenerate failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Failed to regenerate response. Please try again.'], 500);
        }
    }

    /**
     * Get all counselors grouped by category.
     */
    public function getCounselors(Request $request)
    {
        $counselors = \App\Models\Counselor::orderBy('category')
            ->orderBy('name')
            ->get();

        // Group by category
        $grouped = $counselors->groupBy('category')->map(function ($items, $category) {
            return [
                'category' => $category,
                'label' => $this->getCounselorCategoryLabel($category),
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
        return $category;
    }

    /**
     * Get counselors by a specific category.
     */
    public function getCounselorsByCategory(Request $request, string $category)
    {
        $counselors = \App\Models\Counselor::where('category', $category)
            ->orderBy('name')
            ->get();

        $formattedCounselors = $counselors->map(function ($counselor) {
            return [
                'id' => $counselor->id,
                'name' => $counselor->name,
                'title' => $counselor->title,
                'hospital' => $counselor->hospital,
            ];
        });

        return response()->json([
            'success' => true,
            'category' => $category,
            'label' => $this->getCounselorCategoryLabel($category),
            'counselors' => $formattedCounselors,
            'total' => $counselors->count(),
        ]);
    }
}
