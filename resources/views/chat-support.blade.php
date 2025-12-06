<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Conversational Support - UniPulse</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Vite / Assets -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        body { margin: 0; font-family: 'Figtree', sans-serif; }
        
        /* Layout */
        .chat-container {
            display: flex;
            height: 100vh;
            background: #f7f7f8;
        }

        /* Sidebar */
        .conversation-sidebar {
            width: 260px;
            background: #202123;
            color: white;
            display: flex;
            flex-direction: column;
            border-right: 1px solid #444654;
        }

        .sidebar-header {
            padding: 12px;
            border-bottom: 1px solid #444654;
        }

        .new-chat-btn {
            width: 100%;
            padding: 12px 16px;
            background: transparent;
            border: 1px solid #565869;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: background 0.2s;
        }

        .new-chat-btn:hover {
            background: #2A2B32;
        }

        .conversation-list {
            flex: 1;
            overflow-y: auto;
            padding: 8px;
        }

        .conversation-item {
            padding: 12px;
            margin-bottom: 4px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
            color: #ececf1;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .conversation-item:hover {
            background: #2A2B32;
        }

        .conversation-item:hover .conversation-actions {
            opacity: 1;
        }

        .conversation-item.active {
            background: #343541;
        }

        .conversation-content {
            flex: 1;
        }

        .conversation-title {
            font-weight: 500;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding-right: 60px;
        }

        .conversation-meta {
            font-size: 12px;
            color: #8e8ea0;
        }

        .conversation-actions {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            gap: 4px;
            opacity: 0;
            transition: opacity 0.2s;
        }

        .action-btn {
            width: 24px;
            height: 24px;
            border: none;
            background: #40414f;
            color: #ececf1;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: background 0.2s;
        }

        .action-btn:hover {
            background: #565869;
        }

        .action-btn.delete:hover {
            background: #dc2626;
            color: white;
        }

        /* Main Chat Area */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        .chat-header {
            padding: 16px 24px;
            background: white;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-title {
            font-size: 18px;
            font-weight: 600;
            color: #111827;
        }

        .logout-btn {
            padding: 8px 16px;
            background: #f3f4f6;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            color: #374151;
        }

        .logout-btn:hover {
            background: #e5e7eb;
        }

        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
        }

        .message {
            margin-bottom: 24px;
            display: flex;
            gap: 16px;
        }

        .message-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-weight: 600;
            font-size: 14px;
        }

        .message.user .message-avatar {
            background: #6366f1;
            color: white;
        }

        .message.assistant .message-avatar {
            background: #10a37f;
            color: white;
        }

        .message-content {
            flex: 1;
            line-height: 1.6;
            color: #374151;
        }

        .message-time {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 4px;
        }

        .counselor-cards {
            margin-top: 16px;
            padding: 16px;
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 6px;
        }

        .counselor-cards-title {
            font-weight: 600;
            color: #92400e;
            margin-bottom: 12px;
        }

        .counselor-card {
            background: white;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 8px;
        }

        .counselor-name {
            font-weight: 600;
            color: #111827;
        }

        .counselor-title {
            font-size: 13px;
            color: #6b7280;
            margin: 4px 0;
        }

        .counselor-contact {
            font-size: 13px;
            color: #4b5563;
            margin-top: 8px;
        }

        .crisis-resources {
            margin-top: 16px;
            padding: 16px;
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            border-radius: 6px;
        }

        .crisis-resources-title {
            font-weight: 600;
            color: #7f1d1d;
            margin-bottom: 12px;
        }

        .resource-item {
            margin-bottom: 8px;
            font-size: 14px;
            color: #991b1b;
        }

        .input-container {
            padding: 16px 24px;
            background: white;
            border-top: 1px solid #e5e7eb;
        }

        .input-form {
            display: flex;
            gap: 12px;
            max-width: 900px;
            margin: 0 auto;
        }

        .message-input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            resize: none;
            font-family: inherit;
        }

        .message-input:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .send-btn {
            padding: 12px 24px;
            background: #6366f1;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: background 0.2s;
        }

        .send-btn:hover {
            background: #4f46e5;
        }

        .send-btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }

        .typing-indicator {
            display: none;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .typing-indicator.active {
            display: flex;
        }

        .typing-dots {
            display: flex;
            gap: 4px;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: #9ca3af;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }

        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes typing {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-10px); }
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #6b7280;
        }

        .empty-state h2 {
            font-size: 24px;
            margin-bottom: 8px;
            color: #111827;
        }

        .empty-state p {
            font-size: 14px;
            margin-bottom: 24px;
        }

        .example-prompts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            max-width: 700px;
            width: 100%;
        }

        .example-prompt {
            padding: 16px;
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
            text-align: left;
        }

        .example-prompt:hover {
            border-color: #6366f1;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .example-prompt-title {
            font-weight: 600;
            color: #111827;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <!-- Sidebar -->
        <div class="conversation-sidebar">
            <div class="sidebar-header">
                <button class="new-chat-btn" onclick="startNewConversation()">
                    <span>+</span>
                    <span>New Chat</span>
                </button>
            </div>
            <div class="conversation-list" id="conversationList">
                <!-- Conversations will be loaded here -->
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="chat-main">
            <div class="chat-header">
                <div class="chat-title">Conversational Support</div>
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>

            <div class="messages-container" id="messagesContainer">
                <div class="empty-state" id="emptyState">
                    <h2>👋 Hello, {{ Auth::user()->name }}!</h2>
                    <p>How can I support you today?</p>
                    <div class="example-prompts">
                        <div class="example-prompt" onclick="sendExampleMessage('I\'m feeling stressed about upcoming exams')">
                            <div class="example-prompt-title">Academic Stress</div>
                        </div>
                        <div class="example-prompt" onclick="sendExampleMessage('I need help managing my time better')">
                            <div class="example-prompt-title">Time Management</div>
                        </div>
                        <div class="example-prompt" onclick="sendExampleMessage('I\'m having trouble sleeping')">
                            <div class="example-prompt-title">Sleep Issues</div>
                        </div>
                        <div class="example-prompt" onclick="sendExampleMessage('I want to talk about my career goals')">
                            <div class="example-prompt-title">Career Guidance</div>
                        </div>
                    </div>
                </div>

                <!-- Messages will appear here -->
                <div id="messagesContent" style="display: none;"></div>

                <div class="typing-indicator" id="typingIndicator">
                    <div class="message-avatar" style="background: #10a37f; color: white;">AI</div>
                    <div class="typing-dots">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                    </div>
                </div>
            </div>

            <div class="input-container">
                <form class="input-form" onsubmit="sendMessage(event)">
                    <textarea 
                        class="message-input" 
                        id="messageInput" 
                        placeholder="Type your message here..." 
                        rows="1"
                        onkeydown="handleKeyPress(event)"
                    ></textarea>
                    <button type="submit" class="send-btn" id="sendBtn">Send</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let currentConversationId = null;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Auto-resize textarea
        const textarea = document.getElementById('messageInput');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 200) + 'px';
        });

        // Handle Enter key
        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage(event);
            }
        }

        // Load conversations on page load
        window.addEventListener('DOMContentLoaded', () => {
            // Just load the conversation list in sidebar, don't open any conversation
            loadConversations();
            
            // Make sure we start with empty state (no conversation loaded)
            currentConversationId = null;
            document.getElementById('emptyState').style.display = 'flex';
            document.getElementById('messagesContent').style.display = 'none';
        });

        // Load conversation list
        async function loadConversations() {
            try {
                const response = await fetch('/chat/conversations', {
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                const data = await response.json();

                const listContainer = document.getElementById('conversationList');
                if (data.success && data.conversations.length > 0) {
                    listContainer.innerHTML = data.conversations.map(conv => `
                        <div class="conversation-item ${conv.id === currentConversationId ? 'active' : ''}" 
                             data-conversation-id="${conv.id}">
                            <div class="conversation-content" onclick="loadConversation(${conv.id})">
                                <div class="conversation-title">${conv.title}</div>
                                <div class="conversation-meta">${formatDate(conv.last_message_at || conv.created_at)}</div>
                            </div>
                            <div class="conversation-actions">
                                <button class="action-btn" onclick="event.stopPropagation(); renameConversation(${conv.id}, '${conv.title.replace(/'/g, "\\'")}' )" title="Rename">✎</button>
                                <button class="action-btn delete" onclick="event.stopPropagation(); deleteConversation(${conv.id})" title="Delete">×</button>
                            </div>
                        </div>
                    `).join('');
                } else {
                    listContainer.innerHTML = '<div style="padding: 16px; color: #8e8ea0; font-size: 13px; text-align: center;">No conversations yet</div>';
                }
            } catch (error) {
                console.error('Failed to load conversations:', error);
            }
        }

        // Rename conversation
        async function renameConversation(convId, currentTitle) {
            const newTitle = prompt('Enter new conversation title:', currentTitle);
            
            if (!newTitle || newTitle === currentTitle) {
                return; // User cancelled or didn't change
            }

            try {
                const response = await fetch(`/chat/conversation/${convId}/rename`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ title: newTitle })
                });

                const data = await response.json();

                if (data.success) {
                    // Reload conversation list to show new title
                    await loadConversations();
                    
                    // Re-highlight if this was the active conversation
                    if (currentConversationId === convId) {
                        document.querySelectorAll('.conversation-item').forEach(item => {
                            if (item.dataset.conversationId == convId) {
                                item.classList.add('active');
                            }
                        });
                    }
                } else {
                    alert('Failed to rename conversation: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Rename error:', error);
                alert('Failed to rename conversation. Please try again.');
            }
        }

        // Delete conversation
        async function deleteConversation(convId) {
            if (!confirm('Are you sure you want to delete this conversation? This will remove all messages, embeddings, and related data. This action cannot be undone.')) {
                return;
            }

            try {
                const response = await fetch(`/chat/conversation/${convId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // If we deleted the current conversation, reset to empty state
                    if (currentConversationId === convId) {
                        startNewConversation();
                    }
                    
                    // Reload conversation list
                    await loadConversations();
                    
                    // Show success message
                    console.log('Conversation deleted:', data.deleted);
                } else {
                    alert('Failed to delete conversation: ' + (data.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('Delete error:', error);
                alert('Failed to delete conversation. Please try again.');
            }
        }

        // Start new conversation
        function startNewConversation() {
            // Reset conversation ID - next message will create new conversation
            currentConversationId = null;
            console.log('New Chat clicked - conversation ID reset');
            
            // Show empty state
            document.getElementById('emptyState').style.display = 'flex';
            document.getElementById('messagesContent').style.display = 'none';
            document.getElementById('messagesContent').innerHTML = '';
            document.getElementById('messageInput').value = '';
            
            // Remove active class from all conversations
            document.querySelectorAll('.conversation-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Focus on input
            document.getElementById('messageInput').focus();
        }

        // Send example message
        function sendExampleMessage(message) {
            document.getElementById('messageInput').value = message;
            sendMessage(new Event('submit'));
        }

        // Send message
        async function sendMessage(event) {
            event.preventDefault();

            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message) return;

            const sendBtn = document.getElementById('sendBtn');
            sendBtn.disabled = true;

            // Hide empty state
            document.getElementById('emptyState').style.display = 'none';
            document.getElementById('messagesContent').style.display = 'block';

            // Add user message to UI
            addMessageToUI('user', message, new Date());

            // Clear input
            input.value = '';
            input.style.height = 'auto';

            // Show typing indicator
            document.getElementById('typingIndicator').classList.add('active');

            try {
                let response;
                
                console.log('Sending message. Current conversation ID:', currentConversationId); // Debug
                
                if (currentConversationId) {
                    // Send to existing conversation
                    console.log('Sending to existing conversation:', currentConversationId); // Debug
                    response = await fetch('/chat/message', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            conversation_id: currentConversationId,
                            message: message
                        })
                    });
                } else {
                    // Start new conversation
                    console.log('Starting new conversation'); // Debug
                    response = await fetch('/chat/conversation/start', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            initial_message: message,
                            topic: 'General Support'
                        })
                    });
                }

                const data = await response.json();

                if (data.success) {
                    // Set conversation ID if new
                    if (data.conversation) {
                        currentConversationId = data.conversation.id;
                        console.log('Set conversation ID to:', currentConversationId); // Debug
                    }

                    // Add AI response to UI
                    addMessageToUI('assistant', data.response.message, new Date());

                    // Show counselor recommendations if any
                    if (data.response.counselor_recommendations && data.response.counselor_recommendations.length > 0) {
                        addCounselorCards(data.response.counselor_recommendations);
                    }

                    // Show crisis resources if any
                    if (data.response.crisis_resources && Object.keys(data.response.crisis_resources).length > 0) {
                        addCrisisResources(data.response.crisis_resources);
                    }

                    // Reload conversation list (but don't change current view)
                    await loadConversations();
                    
                    // Re-highlight current conversation after sidebar refresh
                    document.querySelectorAll('.conversation-item').forEach(item => {
                        item.classList.remove('active');
                        if (item.dataset.conversationId == currentConversationId) {
                            item.classList.add('active');
                        }
                    });
                } else {
                    addMessageToUI('system', 'Error: ' + (data.error || 'Failed to send message'), new Date());
                }
            } catch (error) {
                console.error('Send message error:', error);
                addMessageToUI('system', 'Failed to send message. Please try again.', new Date());
            } finally {
                document.getElementById('typingIndicator').classList.remove('active');
                sendBtn.disabled = false;
                input.focus();
            }
        }

        // Load conversation
        async function loadConversation(convId) {
            currentConversationId = convId;

            try {
                const response = await fetch(`/chat/conversation/${convId}`, {
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                const data = await response.json();

                if (data.success) {
                    document.getElementById('emptyState').style.display = 'none';
                    const messagesContent = document.getElementById('messagesContent');
                    messagesContent.style.display = 'block';
                    messagesContent.innerHTML = '';

                    data.messages.forEach(msg => {
                        addMessageToUI(msg.role, msg.content, new Date(msg.created_at));
                    });

                    // Update active conversation in sidebar
                    document.querySelectorAll('.conversation-item').forEach(item => {
                        item.classList.remove('active');
                        if (item.dataset.conversationId == convId) {
                            item.classList.add('active');
                        }
                    });

                    console.log('Loaded conversation:', convId); // Debug
                }
            } catch (error) {
                console.error('Load conversation error:', error);
            }
        }

        // Add message to UI
        function addMessageToUI(role, content, timestamp) {
            const messagesContent = document.getElementById('messagesContent');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${role}`;

            const avatar = role === 'user' ? '{{ substr(Auth::user()->name, 0, 1) }}' : 'AI';
            
            messageDiv.innerHTML = `
                <div class="message-avatar">${avatar}</div>
                <div class="message-content">
                    ${content.replace(/\n/g, '<br>')}
                    <div class="message-time">${formatTime(timestamp)}</div>
                </div>
            `;

            messagesContent.appendChild(messageDiv);
            scrollToBottom();
        }

        // Add counselor cards
        function addCounselorCards(counselors) {
            const messagesContent = document.getElementById('messagesContent');
            const cardsDiv = document.createElement('div');
            cardsDiv.className = 'counselor-cards';

            cardsDiv.innerHTML = `
                <div class="counselor-cards-title">📞 Recommended Counselors</div>
                ${counselors.map(c => `
                    <div class="counselor-card">
                        <div class="counselor-name">${c.name}</div>
                        <div class="counselor-title">${c.title}</div>
                        <div class="counselor-contact">
                            ${c.email ? `📧 ${c.email}` : ''}
                            ${c.phone ? `<br>📞 ${c.phone}` : ''}
                            ${c.office_location ? `<br>📍 ${c.office_location}` : ''}
                            ${c.online_booking_url ? `<br><a href="${c.online_booking_url}" target="_blank" style="color: #6366f1;">Book Appointment →</a>` : ''}
                        </div>
                    </div>
                `).join('')}
            `;

            messagesContent.appendChild(cardsDiv);
            scrollToBottom();
        }

        // Add crisis resources
        function addCrisisResources(resources) {
            const messagesContent = document.getElementById('messagesContent');
            const resourcesDiv = document.createElement('div');
            resourcesDiv.className = 'crisis-resources';

            let content = '<div class="crisis-resources-title">🆘 Crisis Support Resources</div>';
            
            if (resources.hotlines) {
                for (const [name, number] of Object.entries(resources.hotlines)) {
                    content += `<div class="resource-item"><strong>${name}:</strong> ${number}</div>`;
                }
            }

            resourcesDiv.innerHTML = content;
            messagesContent.appendChild(resourcesDiv);
            scrollToBottom();
        }

        // Scroll to bottom
        function scrollToBottom() {
            const container = document.getElementById('messagesContainer');
            container.scrollTop = container.scrollHeight;
        }

        // Format date
        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));

            if (days === 0) return 'Today';
            if (days === 1) return 'Yesterday';
            if (days < 7) return `${days} days ago`;
            return date.toLocaleDateString();
        }

        // Format time
        function formatTime(date) {
            return new Date(date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }
    </script>
</body>
</html>