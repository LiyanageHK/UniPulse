<x-app-layout>
    {{-- <x-peer-macthing-nav /> --}}
    <div class="flex bg-gray-100" style="height: calc(100vh - 64px)">

        <!-- Left Sidebar: Users/Chats List -->
        <div class="w-1/3 bg-white border-r overflow-y-auto">
            <div class="p-4 font-bold text-lg border-b">Chats</div>
            <ul id="chatList">
                @foreach ($chats as $chat)
                    @php
                        $userId = auth()->id();
                        $otherUser = $chat->getOtherUser($userId);
                    @endphp
                    <li class="px-4 py-3 hover:bg-gray-100 cursor-pointer chat-user" data-chat-id="{{ $chat->id }}"
                        data-other-user-id="{{ $otherUser->id }}" data-other-user-name="{{ $otherUser->name }}">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                {{ strtoupper(substr($otherUser->name, 0, 1)) }}
                            </div>
                            <div class="flex-1">
                                <div class="font-semibold">{{ $otherUser->name }}</div>
                                <div class="text-sm text-gray-500 truncate last-message-{{ $chat->id }}">
                                    {{ $chat->last_message ?? 'No messages yet' }}
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Right Sidebar: Messages Window -->
        <div class="flex-1 flex flex-col">
            <div class="p-4 bg-white border-b flex items-center">
                <div class="w-7 h-7 bg-gray-300 rounded-full flex items-center justify-center" id="chatUserAvatar"></div>
                <div class="ml-3">
                    <div id="chatUserName" class="font-semibold"></div>
                </div>
            </div>

            <!-- Messages Container - Now with proper scroll -->
            <div id="messagesContainer" class="flex-1 p-4 overflow-y-auto bg-gray-50"
                style="max-height: calc(100vh - 200px); overflow-y: scroll;">
                <div class="text-gray-500 text-center mt-10">Select a chat to start messaging</div>
            </div>

            <div class="p-4 bg-white border-t flex">
                <input id="messageInput" type="text" placeholder="Type a message"
                    class="flex-1 border rounded-full px-4 py-2 focus:outline-none focus:ring" disabled>
                <button id="sendButton"
                    class="ml-3 bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600 disabled:bg-gray-400 disabled:cursor-not-allowed"
                    disabled>Send</button>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    <script>
        // Initialize Supabase
        const SUPABASE_URL = '{{ $supabaseUrl }}';
        const SUPABASE_KEY = '{{ $supabaseKey }}';
        const supabase = window.supabase.createClient(SUPABASE_URL, SUPABASE_KEY);

        // Current user and chat state
        const currentUserId = {{ auth()->id() }};
        let currentChatId = null;
        let currentOtherUserId = null;
        let messageSubscription = null;

        // DOM elements
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        const messagesContainer = document.getElementById('messagesContainer');

        // Load messages for a chat
        async function loadMessages(chatId) {
            try {
                const {
                    data,
                    error
                } = await supabase
                    .from('messages')
                    .select('*')
                    .eq('chat_id', chatId)
                    .order('created_at', {
                        ascending: true
                    });

                if (error) throw error;

                displayMessages(data);

                // Scroll to bottom after messages are rendered
                setTimeout(() => {
                    scrollToBottom();
                }, 100);

            } catch (error) {
                console.error('Error loading messages:', error);
                messagesContainer.innerHTML =
                    '<div class="text-red-500 text-center mt-10">Error loading messages</div>';
            }
        }

        // Display messages in the container
        function displayMessages(messages) {
            if (messages.length === 0) {
                messagesContainer.innerHTML =
                    '<div class="text-gray-500 text-center mt-10">No messages yet. Start the conversation!</div>';
                return;
            }

            messagesContainer.innerHTML = messages.map(msg => {
                const isMine = msg.sender_id === currentUserId;
                const alignment = isMine ? 'justify-end' : 'justify-start';
                const bgColor = isMine ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800';

                return `
                    <div class="flex ${alignment} mb-3">
                        <div class="max-w-xs ${bgColor} rounded-lg px-4 py-2">
                            <div class="break-words">${escapeHtml(msg.message)}</div>
                            <div class="text-xs mt-1 ${isMine ? 'text-blue-100' : 'text-gray-500'}">
                                ${formatTime(msg.created_at)}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Send a message
        async function sendMessage() {
            const message = messageInput.value.trim();

            if (!message || !currentChatId) return;

            try {
                sendButton.disabled = true;
                messageInput.disabled = true;

                // Create optimistic message object
                const optimisticMessage = {
                    chat_id: currentChatId,
                    sender_id: currentUserId,
                    message: message,
                    created_at: new Date().toISOString()
                };

                // Clear input immediately for better UX
                messageInput.value = '';

                // Add message to UI immediately (optimistic update)
                appendMessage(optimisticMessage);
                scrollToBottom();

                // Update last message in sidebar
                updateLastMessage(currentChatId, message);

                // Send to Supabase
                const {
                    data,
                    error
                } = await supabase
                    .from('messages')
                    .insert([{
                        chat_id: currentChatId,
                        sender_id: currentUserId,
                        message: message
                    }])
                    .select();

                if (error) throw error;

            } catch (error) {
                console.error('Error sending message:', error);
                alert('Failed to send message. Please try again.');
                // Optionally: reload messages to sync state
                await loadMessages(currentChatId);
            } finally {
                sendButton.disabled = false;
                messageInput.disabled = false;
                messageInput.focus();
            }
        }

        // Subscribe to new messages for current chat
        function subscribeToMessages(chatId) {
            // Unsubscribe from previous chat if any
            if (messageSubscription) {
                supabase.removeChannel(messageSubscription);
            }

            // Subscribe to new messages
            messageSubscription = supabase
                .channel(`chat-${chatId}`)
                .on(
                    'postgres_changes', {
                        event: 'INSERT',
                        schema: 'public',
                        table: 'messages',
                        filter: `chat_id=eq.${chatId}`
                    },
                    (payload) => {
                        const newMessage = payload.new;

                        // Only add message if it's from the OTHER user (not current user)
                        // Current user's messages are added optimistically in sendMessage()
                        if (newMessage.sender_id !== currentUserId) {
                            appendMessage(newMessage);
                            scrollToBottom();

                            // Update last message in sidebar
                            updateLastMessage(chatId, newMessage.message);
                        }
                    }
                )
                .subscribe();
        }

        // Append a single message to the container
        function appendMessage(msg) {
            // Remove "no messages" placeholder if it exists
            const placeholder = messagesContainer.querySelector('.text-gray-500');
            if (placeholder && placeholder.textContent.includes('No messages yet')) {
                messagesContainer.innerHTML = '';
            }

            const isMine = msg.sender_id === currentUserId;
            const alignment = isMine ? 'justify-end' : 'justify-start';
            const bgColor = isMine ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800';

            const messageDiv = document.createElement('div');
            messageDiv.className = `flex ${alignment} mb-3`;
            messageDiv.innerHTML = `
                <div class="max-w-xs ${bgColor} rounded-lg px-4 py-2">
                    <div class="break-words">${escapeHtml(msg.message)}</div>
                    <div class="text-xs mt-1 ${isMine ? 'text-blue-100' : 'text-gray-500'}">
                        ${formatTime(msg.created_at)}
                    </div>
                </div>
            `;

            messagesContainer.appendChild(messageDiv);
        }

        // Update last message in chat list
        function updateLastMessage(chatId, message) {
            const lastMessageEl = document.querySelector(`.last-message-${chatId}`);
            if (lastMessageEl) {
                lastMessageEl.textContent = message.length > 30 ? message.substring(0, 30) + '...' : message;
            }
        }

        // Utility functions
        function scrollToBottom() {
            messagesContainer.scrollTo({
                top: messagesContainer.scrollHeight,
                behavior: 'smooth'
            });
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const diffInHours = (now - date) / (1000 * 60 * 60);

            if (diffInHours < 24) {
                return date.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            } else {
                return date.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                });
            }
        }

        // Function to select and load a chat
        async function selectChat(chatElement) {
            const chatId = parseInt(chatElement.dataset.chatId);
            const otherUserName = chatElement.dataset.otherUserName;
            const otherUserId = parseInt(chatElement.dataset.otherUserId);

            // Update UI
            document.getElementById('chatUserName').textContent = otherUserName;
            document.getElementById('chatUserAvatar').textContent = otherUserName[0].toUpperCase();

            // Update state
            currentChatId = chatId;
            currentOtherUserId = otherUserId;

            // Enable input
            messageInput.disabled = false;
            sendButton.disabled = false;

            // Load messages
            messagesContainer.innerHTML = '<div class="text-gray-500 text-center mt-10">Loading messages...</div>';
            await loadMessages(chatId);

            // Subscribe to real-time updates
            subscribeToMessages(chatId);

            // Highlight active chat
            document.querySelectorAll('.chat-user').forEach(chat => {
                chat.classList.remove('bg-blue-50');
            });
            chatElement.classList.add('bg-blue-50');

            // Focus on input
            messageInput.focus();
        }

        // Event listeners
        document.querySelectorAll('.chat-user').forEach(el => {
            el.addEventListener('click', async () => {
                await selectChat(el);
            });
        });

        sendButton.addEventListener('click', sendMessage);

        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (messageSubscription) {
                supabase.removeChannel(messageSubscription);
            }
        });

        // Auto-select first chat on page load
        document.addEventListener('DOMContentLoaded', () => {
            const firstChat = document.querySelector('.chat-user');
            if (firstChat) {
                selectChat(firstChat);
            }
        });
    </script>
</x-app-layout>
