<x-app-layout title="Group Chat - UniPulse">
 {{-- <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script> --}}
    <div class="flex bg-gray-100" style="height: calc(100vh - 64px)">

        <!-- Left Sidebar: Group Info & Members -->
        <div class="w-1/4 bg-white border-r overflow-y-auto">
            <!-- Group Header -->
            <div class="p-4 border-b">
                <a href="{{ route('groups.index') }}" class="text-blue-500 hover:text-blue-600 text-sm mb-3 inline-block">
                    ← Back to Groups
                </a>
                <div class="flex items-center mb-3">
                    <div
                        class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-xl font-bold">
                        {{ strtoupper(substr($group->name, 0, 1)) }}
                    </div>
                    <div class="ml-3">
                        <h2 class="font-bold text-lg">{{ $group->name }}</h2>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ $group->category }}</span>
                    </div>
                </div>
                <p class="text-sm text-gray-600">{{ $group->description }}</p>
            </div>

            <!-- Members List -->
            <div class="p-4">
                <h3 class="font-semibold mb-3 flex items-center justify-between">
                    <span>Members ({{ $group->members->count() }})</span>
                    @if ($isAdmin)
                        <button onclick="toggleInviteModal()"
                            class="text-xs bg-green-500 text-white px-2 py-1 rounded hover:bg-green-600">
                            + Invite
                        </button>
                    @endif
                </h3>
                <ul class="space-y-2">
                    @foreach ($group->members as $member)
                        <li class="flex items-center justify-between p-2 hover:bg-gray-50 rounded">
                            <div class="flex items-center">
                                <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-sm">
                                    {{ strtoupper(substr($member->name, 0, 1)) }}
                                </div>
                                <div class="ml-2">
                                    <p class="text-sm font-medium">{{ $member->name }}</p>
                                    @if ($group->isAdmin($member->id))
                                        <span class="text-xs text-yellow-600">Admin</span>
                                    @endif
                                </div>
                            </div>
                            @if ($isAdmin && !$group->isAdmin($member->id))
                                <form method="POST" action="{{ route('groups.removeMember', [$group->id, $member->id]) }}"
                                    onsubmit="return confirm('Remove this member?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs">
                                        Remove
                                    </button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Pending Requests (Admin Only) -->
            @if ($isAdmin && $group->pendingRequests->count() > 0)
                <div class="p-4 border-t">
                    <h3 class="font-semibold mb-3">Pending Requests ({{ $group->pendingRequests->count() }})</h3>
                    <ul class="space-y-2">
                        @foreach ($group->pendingRequests as $request)
                            <li class="p-2 bg-yellow-50 rounded">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-sm">
                                        {{ strtoupper(substr($request->user->name, 0, 1)) }}
                                    </div>
                                    <span class="ml-2 text-sm font-medium">{{ $request->user->name }}</span>
                                </div>
                                <div class="flex space-x-2">
                                    <form method="POST" action="{{ route('groups.acceptRequest', $request->id) }}"
                                        class="flex-1">
                                        @csrf
                                        <button type="submit"
                                            class="w-full bg-green-500 hover:bg-green-600 text-white text-xs py-1 rounded">
                                            Accept
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('groups.rejectRequest', $request->id) }}"
                                        class="flex-1">
                                        @csrf
                                        <button type="submit"
                                            class="w-full bg-red-500 hover:bg-red-600 text-white text-xs py-1 rounded">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Group Actions -->
            <div class="p-4 border-t">
                @if ($isAdmin)
                    <form method="POST" action="{{ route('groups.destroy', $group->id) }}"
                        onsubmit="return confirm('Are you sure you want to delete this group? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded">
                            Delete Group
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('groups.leave', $group->id) }}"
                        onsubmit="return confirm('Are you sure you want to leave this group?')">
                        @csrf
                        <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white py-2 rounded">
                            Leave Group
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Right Side: Chat Window -->
        <div class="flex-1 flex flex-col">
            <!-- Chat Header -->
            <div class="p-4 bg-white border-b">
                <h3 class="font-semibold text-lg">Group Chat</h3>
                <p class="text-sm text-gray-500">{{ $group->members->count() }} members</p>
            </div>

            <!-- Messages Container -->
            <div id="messagesContainer" class="flex-1 p-4 overflow-y-auto bg-gray-50"
                style="max-height: calc(100vh - 200px); overflow-y: scroll;">
                <div class="text-gray-500 text-center mt-10">Loading messages...</div>
            </div>

            <!-- Message Input -->
            <div class="p-4 bg-white border-t flex">
                <input id="messageInput" type="text" placeholder="Type a message"
                    class="flex-1 border rounded-full px-4 py-2 focus:outline-none focus:ring">
                <button id="sendButton"
                    class="ml-3 bg-blue-500 text-white px-4 py-2 rounded-full hover:bg-blue-600">Send</button>
            </div>
        </div>
    </div>

    <!-- Invite Modal (Admin Only) -->
    @if ($isAdmin)
        <div id="inviteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold">Invite Members</h3>
                    <button onclick="toggleInviteModal()" class="text-gray-500 hover:text-gray-700">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                @if ($availableUsers->count() > 0)
                    <form method="POST" action="{{ route('groups.inviteUser', $group->id) }}">
                        @csrf
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Select User</label>
                            <select name="user_id" required
                                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Choose a user...</option>
                                @foreach ($availableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex justify-end space-x-2">
                            <button type="button" onclick="toggleInviteModal()"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                                Cancel
                            </button>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                                Invite
                            </button>
                        </div>
                    </form>
                @else
                    <p class="text-gray-600 mb-4">No users available to invite. All users are already members.</p>
                    <button onclick="toggleInviteModal()"
                        class="w-full bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                        Close
                    </button>
                @endif
            </div>
        </div>
    @endif
    <script>
        // Initialize Supabase
        const SUPABASE_URL = '{{ $supabaseUrl }}';
        const SUPABASE_KEY = '{{ $supabaseKey }}';
        const supabase = window.supabase.createClient(SUPABASE_URL, SUPABASE_KEY);

        // Current user and group state
        const currentUserId = {{ auth()->id() }};
        const currentGroupId = {{ $group->id }};
        let messageSubscription = null;

        // DOM elements
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        const messagesContainer = document.getElementById('messagesContainer');

        // User names cache
        const userNames = {
            @foreach ($group->members as $member)
                {{ $member->id }}: '{{ $member->name }}',
            @endforeach
        };

        // Load messages for the group
        async function loadMessages() {
            try {
                const {
                    data,
                    error
                } = await supabase
                    .from('group_messages')
                    .select('*')
                    .eq('group_id', currentGroupId)
                    .order('created_at', {
                        ascending: true
                    });

                if (error) throw error;

                displayMessages(data);

                setTimeout(() => {
                    scrollToBottom();
                }, 100);

            } catch (error) {
                console.error('Error loading messages:', error);
                messagesContainer.innerHTML =
                '<div class="text-red-500 text-center mt-10">Error loading messages</div>';
            }
        }

        // Display messages
        function displayMessages(messages) {
            if (messages.length === 0) {
                messagesContainer.innerHTML =
                    '<div class="text-gray-500 text-center mt-10">No messages yet. Start the conversation!</div>';
                return;
            }

            messagesContainer.innerHTML = messages.map(msg => {
                const isMine = msg.sender_id === currentUserId;
                const senderName = userNames[msg.sender_id] || 'Unknown';
                const alignment = isMine ? 'justify-end' : 'justify-start';
                const bgColor = isMine ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800';

                return `
                <div class="flex ${alignment} mb-3">
                    <div class="max-w-xs ${bgColor} rounded-lg px-4 py-2">
                        ${!isMine ? `<div class="text-xs font-semibold mb-1 ${isMine ? 'text-blue-100' : 'text-gray-600'}">${escapeHtml(senderName)}</div>` : ''}
                        <div class="break-words">${escapeHtml(msg.message)}</div>
                        <div class="text-xs mt-1 ${isMine ? 'text-blue-100' : 'text-gray-500'}">
                            ${formatTime(msg.created_at)}
                        </div>
                    </div>
                </div>
            `;
            }).join('');
        }

        // Send message
        async function sendMessage() {
            const message = messageInput.value.trim();

            if (!message) return;

            try {
                sendButton.disabled = true;
                messageInput.disabled = true;

                const optimisticMessage = {
                    group_id: currentGroupId,
                    sender_id: currentUserId,
                    message: message,
                    created_at: new Date().toISOString()
                };

                messageInput.value = '';
                appendMessage(optimisticMessage);
                scrollToBottom();

                const {
                    data,
                    error
                } = await supabase
                    .from('group_messages')
                    .insert([{
                        group_id: currentGroupId,
                        sender_id: currentUserId,
                        message: message
                    }])
                    .select();

                if (error) throw error;

            } catch (error) {
                console.error('Error sending message:', error);
                alert('Failed to send message. Please try again.');
                await loadMessages();
            } finally {
                sendButton.disabled = false;
                messageInput.disabled = false;
                messageInput.focus();
            }
        }

        // Subscribe to new messages
        function subscribeToMessages() {
            messageSubscription = supabase
                .channel(`group-${currentGroupId}`)
                .on(
                    'postgres_changes', {
                        event: 'INSERT',
                        schema: 'public',
                        table: 'group_messages',
                        filter: `group_id=eq.${currentGroupId}`
                    },
                    (payload) => {
                        const newMessage = payload.new;

                        if (newMessage.sender_id !== currentUserId) {
                            appendMessage(newMessage);
                            scrollToBottom();
                        }
                    }
                )
                .subscribe();
        }

        // Append message
        function appendMessage(msg) {
            const placeholder = messagesContainer.querySelector('.text-gray-500');
            if (placeholder && placeholder.textContent.includes('No messages yet')) {
                messagesContainer.innerHTML = '';
            }

            const isMine = msg.sender_id === currentUserId;
            const senderName = userNames[msg.sender_id] || 'Unknown';
            const alignment = isMine ? 'justify-end' : 'justify-start';
            const bgColor = isMine ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-800';

            const messageDiv = document.createElement('div');
            messageDiv.className = `flex ${alignment} mb-3`;
            messageDiv.innerHTML = `
            <div class="max-w-xs ${bgColor} rounded-lg px-4 py-2">
                ${!isMine ? `<div class="text-xs font-semibold mb-1 ${isMine ? 'text-blue-100' : 'text-gray-600'}">${escapeHtml(senderName)}</div>` : ''}
                <div class="break-words">${escapeHtml(msg.message)}</div>
                <div class="text-xs mt-1 ${isMine ? 'text-blue-100' : 'text-gray-500'}">
                    ${formatTime(msg.created_at)}
                </div>
            </div>
        `;

            messagesContainer.appendChild(messageDiv);
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

        // Toggle invite modal
        function toggleInviteModal() {
            const modal = document.getElementById('inviteModal');
            modal.classList.toggle('hidden');
        }

        // Event listeners
        sendButton.addEventListener('click', sendMessage);

        messageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', async () => {
            await loadMessages();
            subscribeToMessages();
            messageInput.focus();
        });

        // Cleanup
        window.addEventListener('beforeunload', () => {
            if (messageSubscription) {
                supabase.removeChannel(messageSubscription);
            }
        });
    </script>
</x-app-layout>
