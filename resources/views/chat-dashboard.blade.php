<x-app-layout>

    {{-- Main dashboard container with consistent spacing and page width. --}}
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Hero / Welcome Section --}}
        {{-- Top introduction panel with title, description, and CTA. --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-4 md:p-6 mb-8">
            {{-- Responsive flex layout for hero content and AI character. --}}
            <div class="flex flex-col md:flex-row items-center justify-between gap-4 md:gap-4">
                {{-- Left side: heading and description. --}}
                <div>
                    {{-- Badge and icon row for the AI support label. --}}
                    <div class="flex items-center gap-3 mb-4">
                        {{-- Icon wrapper for the chat symbol. --}}
                        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                            {{-- Chat bubble icon. --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        {{-- Section label for the AI support feature. --}}
                        <span class="text-sm font-medium text-blue-600 uppercase tracking-wide">AI Support</span>
                    </div>
                    {{-- Main page heading. --}}
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Conversational Support</h1>
                    {{-- Descriptive paragraph explaining the purpose of the page. --}}
                    <p class="text-gray-500 text-lg max-w-xl leading-relaxed text-justify">
                        Your private AI-powered support companion! A safe space to share what’s on your mind. Whether it’s academic stress, personal challenges, or just the need to vent, I am here to listen and support you.
                    </p>
                    {{-- Show the last chat activity only when available. --}}
                    @if($lastChatTime)
                        {{-- Relative timestamp for the latest interaction. --}}
                        <p class="text-gray-400 text-sm mt-4">Last chat activity: {{ $lastChatTime }}</p>
                    @endif
                </div>
                {{-- Right side: animated AI character and primary CTA. --}}
                <div class="flex-shrink-0 flex flex-col items-center gap-4">
                    {{-- Animated AI character that tracks cursor --}}
                    {{-- Visual mascot that reacts to cursor movement. --}}
                    <div id="aiCharacter" class="relative select-none" style="width: 140px; height: 140px;">
                        {{-- Glow ring --}}
                        {{-- Soft pulsing ring behind the character. --}}
                        <div class="absolute inset-0 rounded-full bg-blue-100 animate-pulse opacity-50"></div>
                        {{-- Face container --}}
                        {{-- Main face container with gradient background. --}}
                        <div class="absolute inset-2 rounded-full bg-gradient-to-br from-blue-50 to-indigo-100 border-2 border-blue-200 flex items-center justify-center overflow-hidden">
                            {{-- Eyes --}}
                            {{-- Eye group positioned near the top of the face. --}}
                            <div class="flex gap-5 relative" style="margin-top: -8px;">
                                {{-- Left eye. --}}
                                <div class="relative w-8 h-8 bg-white rounded-full border border-gray-200 flex items-center justify-center shadow-inner">
                                    {{-- Left pupil that moves with the cursor. --}}
                                    <div id="leftPupil" class="w-3.5 h-3.5 bg-blue-600 rounded-full transition-transform duration-100" style="transform: translate(0px, 0px);"></div>
                                </div>
                                {{-- Right eye. --}}
                                <div class="relative w-8 h-8 bg-white rounded-full border border-gray-200 flex items-center justify-center shadow-inner">
                                    {{-- Right pupil that moves with the cursor. --}}
                                    <div id="rightPupil" class="w-3.5 h-3.5 bg-blue-600 rounded-full transition-transform duration-100" style="transform: translate(0px, 0px);"></div>
                                </div>
                            </div>
                            {{-- Mouth --}}
                            {{-- Smiling mouth element that changes size on hover. --}}
                            <div id="aiMouth" class="absolute bottom-7 left-1/2 -translate-x-1/2 w-10 h-5 border-b-[3px] border-blue-400 rounded-b-full transition-all duration-300"></div>
                            {{-- Blush dots --}}
                            {{-- Decorative blush marks on both cheeks. --}}
                            <div class="absolute bottom-9 left-8 w-4 h-2 bg-pink-200 rounded-full opacity-60"></div>
                            <div class="absolute bottom-9 right-8 w-4 h-2 bg-pink-200 rounded-full opacity-60"></div>
                        </div>
                    </div>
                    {{-- Friendly greeting below the mascot. --}}
                    <div class="mt-2 text-lg font-semibold text-blue-700 animate-pulse">Hi! Let's chat!</div>
                    {{-- Button that routes the user into the chat page. --}}
                    <a href="{{ route('chat.support') }}"
                       class="inline-flex items-center gap-3 bg-blue-600 text-white font-semibold text-lg px-8 py-4 rounded-xl hover:bg-blue-700 transition-colors duration-200">
                        {{-- Chat icon inside the CTA button. --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        {{-- CTA label. --}}
                        Start Chatting
                    </a>
                </div>
            </div>
        </div>

        <!-- CONVERSATIONAL SUPPORT & FEEDBACK -->
        {{-- KPI / metrics section for the dashboard. --}}
        <div class="mb-12">
            {{-- Heading for the stats cards. --}}
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Important Measures</h2>
            
            {{-- Four-column metrics grid on larger screens. --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Active Chats -->
                {{-- Metric card: active conversations. --}}
                <div class="bg-white rounded-lg shadow-lg p-6" style="border-left: 4px solid #6366f1;">
                    {{-- Card header with title and emoji. --}}
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-800">Active Chats</h3>
                        <span class="text-2xl">💬</span>
                    </div>
                    {{-- Value for active conversations. --}}
                    <div class="text-3xl font-bold text-indigo-600">{{ $activeChatsCount }}</div>
                    {{-- Helper text for the metric. --}}
                    <div class="text-sm text-gray-600">Current conversations</div>
                </div>

                <!-- Archived Chats -->
                {{-- Metric card: archived conversations. --}}
                <div class="bg-white rounded-lg shadow-lg p-6" style="border-left: 4px solid #22c55e;">
                    {{-- Card header. --}}
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-800">Archived Chats</h3>
                        <span class="text-2xl">📦</span>
                    </div>
                    {{-- Value for archived conversations. --}}
                    <div class="text-3xl font-bold text-green-600">{{ $archivedChatsCount }}</div>
                    {{-- Helper text. --}}
                    <div class="text-sm text-gray-600">Stored interactions</div>
                </div>

                <!-- Support Alerts -->
                 {{-- Metric card: crisis/support alerts. --}}
                 <div class="bg-white rounded-lg shadow-lg p-6" style="border-left: 4px solid #ef4444;">
                    {{-- Card header. --}}
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-800">Support Alerts</h3>
                        <span class="text-2xl">🚨</span>
                    </div>
                    {{-- Value for support alerts. --}}
                    <div class="text-3xl font-bold text-red-600">{{ $totalCrisisFlags }}</div>
                    {{-- Helper text. --}}
                    <div class="text-sm text-gray-600">System Alerts Triggered</div>
                </div>

                <!-- Last Interaction -->
                {{-- Metric card: last chat time. --}}
                <div class="bg-white rounded-lg shadow-lg p-6" style="border-left: 4px solid #eab308;">
                    {{-- Card header. --}}
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-lg font-semibold text-gray-800">Last Chat</h3>
                        <span class="text-2xl">🕒</span>
                    </div>
                    {{-- Truncated relative time display. --}}
                    <div class="text-xl font-bold text-yellow-600 truncate" title="{{ $lastChatTime }}">{{ $lastChatTime }}</div>
                    {{-- Helper text. --}}
                    <div class="text-sm text-gray-600">Since last message</div>
                </div>
            </div>
        </div>

        {{-- Main Content: Recent Conversations + Quick Actions --}}
        {{-- Main two-column layout for conversation list and actions. --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">

            {{-- Recent Conversations (2/3 width) --}}
            {{-- Left column for recent conversation history. --}}
            <div class="lg:col-span-2">
                {{-- Section title for recent conversations. --}}
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Recent Conversations</h2>

                {{-- Show list when at least one conversation exists. --}}
                @if($recentConversations->count() > 0)
                    {{-- Conversation list card container. --}}
                    <div class="bg-white rounded-lg shadow-lg divide-y divide-gray-100">
                        {{-- Render each recent conversation row. --}}
                        @foreach($recentConversations as $conversation)
                            {{-- Link to the conversation in chat support. --}}
                            <a href="{{ route('chat.support') }}?conversation={{ $conversation['id'] }}"
                               class="flex items-center justify-between p-5 hover:bg-blue-50 transition-colors duration-150 group">
                                {{-- Conversation details column. --}}
                                <div class="flex-1 min-w-0">
                                    {{-- Conversation title. --}}
                                    <h4 class="text-base font-semibold text-gray-800 group-hover:text-blue-700 truncate">
                                        {{ $conversation['title'] }}
                                    </h4>
                                    {{-- Show a preview only if available. --}}
                                    @if($conversation['last_message_preview'])
                                        {{-- Message preview with sender label. --}}
                                        <p class="text-sm text-gray-500 mt-1 truncate">
                                            {{ $conversation['last_message_role'] === 'user' ? 'You' : 'AI' }}:
                                            {{ $conversation['last_message_preview'] }}
                                        </p>
                                    @endif
                                    {{-- Meta row for message count and age. --}}
                                    <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                                        <span>{{ $conversation['message_count'] }} messages</span>
                                        <span>{{ $conversation['time_ago'] }}</span>
                                    </div>
                                </div>
                                {{-- Resume badge on the right side of each conversation row. --}}
                                <div class="ml-4 flex-shrink-0">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 group-hover:bg-blue-200">
                                        Resume
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    {{-- Show link to all conversations when there are more than five active chats. --}}
                    @if($activeChats > 5)
                        <div class="mt-4 text-center">
                            {{-- Link back to the full chat view. --}}
                            <a href="{{ route('chat.support') }}"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View all {{ $activeChats }} active conversations &rarr;
                            </a>
                        </div>
                    @endif
                @else
                    {{-- Empty state when no conversations exist yet. --}}
                    <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                        {{-- Empty-state icon. --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-blue-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        {{-- Empty state heading. --}}
                        <h4 class="text-lg font-semibold text-gray-700 mb-2">No conversations yet</h4>
                        {{-- Empty state helper text. --}}
                        <p class="text-gray-500 mb-4">Start your first conversation to get personalized AI support.</p>
                        {{-- CTA to begin the first chat. --}}
                        <a href="{{ route('chat.support') }}"
                           class="inline-flex items-center px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors duration-150">
                            Start Your First Chat
                        </a>
                    </div>
                @endif
            </div>

            {{-- Quick Actions Sidebar (1/3 width) --}}
            {{-- Right sidebar with shortcuts to important features. --}}
            <div>
                {{-- Section title for quick actions. --}}
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Quick Actions</h2>
                {{-- Vertical spacing between action cards. --}}
                <div class="space-y-4">

                    {{-- Start new chat action. --}}
                    <a href="{{ route('chat.support') }}"
                       class="block bg-white rounded-lg shadow-lg p-5 border-l-4 border-blue-500 hover:scale-105 transition-transform duration-200 group">
                        {{-- Action row layout. --}}
                        <div class="flex items-center gap-3">
                            {{-- Action icon container. --}}
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                                {{-- Plus icon. --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            {{-- Text labels for the action. --}}
                            <div>
                                <h4 class="font-semibold text-gray-800">Start New Chat</h4>
                                <p class="text-sm text-gray-500">Begin a new conversation</p>
                            </div>
                        </div>
                    </a>

                    {{-- Archived chats shortcut. --}}
                    <a href="{{ route('chat.support') }}#archived"
                       class="block bg-white rounded-lg shadow-lg p-5 border-l-4 border-green-500 hover:scale-105 transition-transform duration-200 group">
                        {{-- Action row layout. --}}
                        <div class="flex items-center gap-3">
                            {{-- Archive icon container. --}}
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
                                {{-- Archive icon. --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            </div>
                            {{-- Action text. --}}
                            <div>
                                <h4 class="font-semibold text-gray-800">View Archives</h4>
                                <p class="text-sm text-gray-500">{{ $archivedChats }} archived conversation{{ $archivedChats !== 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                    </a>

                    {{-- Manage memories shortcut. --}}
                    <a href="{{ route('chat.support') }}#settings-memories"
                       class="block bg-white rounded-lg shadow-lg p-5 border-l-4 border-purple-500 hover:scale-105 transition-transform duration-200 group">
                        {{-- Action row layout. --}}
                        <div class="flex items-center gap-3">
                            {{-- Memory/lightbulb icon container. --}}
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                                {{-- Memory icon. --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            {{-- Action text. --}}
                            <div>
                                <h4 class="font-semibold text-gray-800">Manage Memories</h4>
                                <p class="text-sm text-gray-500">{{ $memoryCount }} memories stored</p>
                            </div>
                        </div>
                    </a>

                    {{-- Settings shortcut. --}}
                    <a href="{{ route('chat.support') }}#settings"
                       class="block bg-white rounded-lg shadow-lg p-5 border-l-4 border-yellow-500 hover:scale-105 transition-transform duration-200 group">
                        {{-- Action row layout. --}}
                        <div class="flex items-center gap-3">
                            {{-- Gear icon container. --}}
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center group-hover:bg-yellow-200 transition-colors">
                                {{-- Settings icon. --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            {{-- Action text. --}}
                            <div>
                                <h4 class="font-semibold text-gray-800">Chat Settings</h4>
                                <p class="text-sm text-gray-500">Manage preferences & data</p>
                            </div>
                        </div>
                    </a>

                    {{-- Search conversations shortcut. --}}
                    <a href="{{ route('chat.support') }}#search"
                       class="block bg-white rounded-lg shadow-lg p-5 border-l-4 border-cyan-500 hover:scale-105 transition-transform duration-200 group">
                        {{-- Action row layout. --}}
                        <div class="flex items-center gap-3">
                            {{-- Search icon container. --}}
                            <div class="w-10 h-10 bg-cyan-100 rounded-lg flex items-center justify-center group-hover:bg-cyan-200 transition-colors">
                                {{-- Search icon. --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            {{-- Action text. --}}
                            <div>
                                <h4 class="font-semibold text-gray-800">Search Conversations</h4>
                                <p class="text-sm text-gray-500">Search across all your chats</p>
                            </div>
                        </div>
                    </a>

                    {{-- Export history shortcut. --}}
                    <a href="{{ route('chat.support') }}"
                       class="block bg-white rounded-lg shadow-lg p-5 border-l-4 border-rose-500 hover:scale-105 transition-transform duration-200 group">
                        {{-- Action row layout. --}}
                        <div class="flex items-center gap-3">
                            {{-- Export icon container. --}}
                            <div class="w-10 h-10 bg-rose-100 rounded-lg flex items-center justify-center group-hover:bg-rose-200 transition-colors">
                                {{-- Export icon. --}}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            {{-- Action text. --}}
                            <div>
                                <h4 class="font-semibold text-gray-800">Export Chat History</h4>
                                <p class="text-sm text-gray-500">Download your conversations</p>
                            </div>
                        </div>
                    </a>

                </div>
            </div>

        </div>

        {{-- What AI Can Help With --}}
        {{-- Informational cards describing supported support topics. --}}
        <div class="mb-10">
            {{-- Section heading. --}}
            <h2 class="text-2xl font-bold text-gray-800 mb-6">What Our AI Can Help With</h2>
            {{-- Grid of topic cards. --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                {{-- Academic support card. --}}
                <div class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition-shadow duration-200">
                    {{-- Icon container for academic stress. --}}
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        {{-- Academic icon. --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    {{-- Topic title. --}}
                    <h4 class="font-semibold text-gray-800 mb-2">Academic Stress</h4>
                    {{-- Topic description. --}}
                    <p class="text-sm text-gray-500">Exam anxiety, study strategies, time management, and academic pressure.</p>
                </div>

                {{-- Emotional support card. --}}
                <div class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition-shadow duration-200">
                    {{-- Icon container for emotional support. --}}
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        {{-- Heart icon. --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    {{-- Topic title. --}}
                    <h4 class="font-semibold text-gray-800 mb-2">Emotional Support</h4>
                    {{-- Topic description. --}}
                    <p class="text-sm text-gray-500">Feeling overwhelmed, lonely, anxious, or simply need someone to listen.</p>
                </div>

                {{-- Social support card. --}}
                <div class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition-shadow duration-200">
                    {{-- Icon container for social challenges. --}}
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        {{-- People icon. --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    {{-- Topic title. --}}
                    <h4 class="font-semibold text-gray-800 mb-2">Social Challenges</h4>
                    {{-- Topic description. --}}
                    <p class="text-sm text-gray-500">Making friends, handling conflicts, adapting to university life.</p>
                </div>

                {{-- Goal setting card. --}}
                <div class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition-shadow duration-200">
                    {{-- Icon container for goals. --}}
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        {{-- Goal icon. --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    {{-- Topic title. --}}
                    <h4 class="font-semibold text-gray-800 mb-2">Goal Setting</h4>
                    {{-- Topic description. --}}
                    <p class="text-sm text-gray-500">Career planning, personal goals, building healthy habits and routines.</p>
                </div>

            </div>
        </div>

        {{-- Privacy Notice --}}
        {{-- Final note about privacy and memory management. --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
            {{-- Privacy statement text. --}}
            <p class="text-sm text-blue-800">
                <strong>Your privacy matters.</strong> All conversations are private and encrypted.
                The AI remembers context to provide better support, but you can
                {{-- Link to memory management section. --}}
                <a href="{{ route('chat.support') }}#settings-memories" class="underline font-medium hover:text-blue-900">manage your memories</a>
                at any time.
            </p>
        </div>

    </div>

    {{-- AI Character Cursor Tracking Script --}}
    {{-- JavaScript that animates the AI mascot based on cursor movement. --}}
    <script>
    // Run after the DOM is fully loaded.
    document.addEventListener('DOMContentLoaded', function () {
        // Grab the mascot and its facial features from the DOM.
        const character = document.getElementById('aiCharacter');
        const leftPupil = document.getElementById('leftPupil');
        const rightPupil = document.getElementById('rightPupil');
        const mouth = document.getElementById('aiMouth');

        // Stop if the expected elements are not found.
        if (!character || !leftPupil || !rightPupil) return;

        // Maximum distance the pupils can travel.
        const maxMove = 5; // max pixels the pupil can move

        // Track mouse movement anywhere on the page.
        document.addEventListener('mousemove', function (e) {
            // Get the mascot's current position.
            const rect = character.getBoundingClientRect();
            // Calculate horizontal center point.
            const centerX = rect.left + rect.width / 2;
            // Calculate vertical center point.
            const centerY = rect.top + rect.height / 2;

            // Determine cursor offset from mascot center.
            const deltaX = e.clientX - centerX;
            // Determine cursor vertical offset from mascot center.
            const deltaY = e.clientY - centerY;
            // Measure cursor distance from the mascot.
            const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);

            // Normalize and clamp movement
            // Limit pupil movement so it looks natural.
            const moveX = (deltaX / Math.max(distance, 1)) * Math.min(distance / 20, maxMove);
            // Limit vertical movement as well.
            const moveY = (deltaY / Math.max(distance, 1)) * Math.min(distance / 20, maxMove);

            // Apply the same transform to both pupils.
            leftPupil.style.transform = `translate(${moveX}px, ${moveY}px)`;
            rightPupil.style.transform = `translate(${moveX}px, ${moveY}px)`;

            // Smile wider when cursor is close
            // Make the mouth react when the cursor approaches the character.
            if (distance < 200) {
                mouth.style.width = '2.8rem';
                mouth.style.height = '1.5rem';
                mouth.style.borderBottomWidth = '3px';
            } else {
                // Reset the mouth to its normal size when the cursor is far away.
                mouth.style.width = '2.5rem';
                mouth.style.height = '1.25rem';
                mouth.style.borderBottomWidth = '3px';
            }
        });

        // Blink animation
        // Briefly shrink the pupils to simulate blinking.
        function blink() {
            // Speed up the transition for the blink.
            leftPupil.style.transition = 'transform 0.1s, height 0.1s';
            rightPupil.style.transition = 'transform 0.1s, height 0.1s';
            // Collapse pupils vertically.
            leftPupil.style.height = '2px';
            rightPupil.style.height = '2px';
            // Restore the pupils shortly after.
            setTimeout(() => {
                leftPupil.style.height = '';
                rightPupil.style.height = '';
            }, 150);
        }

        // Blink every 3-5 seconds randomly
        // Schedule recurring blinks with a random delay.
        function scheduleBlink() {
            // Pick a random delay between 3 and 5 seconds.
            const delay = 3000 + Math.random() * 2000;
            // Trigger blink and reschedule it repeatedly.
            setTimeout(() => {
                blink();
                scheduleBlink();
            }, delay);
        }
        // Start the blinking cycle.
        scheduleBlink();
    });
    </script>
</x-app-layout>
