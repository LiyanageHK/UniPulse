<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Conversational Support
        </h2>
    </x-slot>

    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Hero / Welcome Section --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-8 md:p-12 mb-10">
            <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-blue-600 uppercase tracking-wide">AI Support</span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Conversational Support</h1>
                    <p class="text-gray-500 text-lg max-w-xl leading-relaxed">
                        Your private AI-powered support companion. Talk about anything that's on
                        your mind, academic stress, personal challenges, or just to vent.
                    </p>
                    @if($lastChatTime)
                        <p class="text-gray-400 text-sm mt-4">Last chat activity: {{ $lastChatTime }}</p>
                    @endif
                </div>
                <div class="flex-shrink-0 flex flex-col items-center gap-4">
                    {{-- Animated AI character that tracks cursor --}}
                    <div id="aiCharacter" class="relative select-none" style="width: 140px; height: 140px;">
                        {{-- Glow ring --}}
                        <div class="absolute inset-0 rounded-full bg-blue-100 animate-pulse opacity-50"></div>
                        {{-- Face container --}}
                        <div class="absolute inset-2 rounded-full bg-gradient-to-br from-blue-50 to-indigo-100 border-2 border-blue-200 flex items-center justify-center overflow-hidden">
                            {{-- Eyes --}}
                            <div class="flex gap-5 relative" style="margin-top: -8px;">
                                <div class="relative w-8 h-8 bg-white rounded-full border border-gray-200 flex items-center justify-center shadow-inner">
                                    <div id="leftPupil" class="w-3.5 h-3.5 bg-blue-600 rounded-full transition-transform duration-100" style="transform: translate(0px, 0px);"></div>
                                </div>
                                <div class="relative w-8 h-8 bg-white rounded-full border border-gray-200 flex items-center justify-center shadow-inner">
                                    <div id="rightPupil" class="w-3.5 h-3.5 bg-blue-600 rounded-full transition-transform duration-100" style="transform: translate(0px, 0px);"></div>
                                </div>
                            </div>
                            {{-- Mouth --}}
                            <div id="aiMouth" class="absolute bottom-7 left-1/2 -translate-x-1/2 w-10 h-5 border-b-[3px] border-blue-400 rounded-b-full transition-all duration-300"></div>
                            {{-- Blush dots --}}
                            <div class="absolute bottom-9 left-8 w-4 h-2 bg-pink-200 rounded-full opacity-60"></div>
                            <div class="absolute bottom-9 right-8 w-4 h-2 bg-pink-200 rounded-full opacity-60"></div>
                        </div>
                        {{-- Antenna --}}
                        <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-2 flex flex-col items-center">
                            <div class="w-3 h-3 bg-blue-400 rounded-full animate-bounce shadow-md"></div>
                            <div class="w-0.5 h-3 bg-blue-300"></div>
                        </div>
                    </div>
                    <a href="{{ route('chat.support') }}"
                       class="inline-flex items-center gap-3 bg-blue-600 text-white font-semibold text-lg px-8 py-4 rounded-xl hover:bg-blue-700 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        Start Chatting
                    </a>
                </div>
            </div>
        </div>

        {{-- KPI Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500 hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Total Conversations</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                </div>
                <div class="text-3xl font-bold text-blue-600">{{ $totalConversations }}</div>
                <div class="text-sm text-gray-500 mt-1">All time</div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-indigo-500 hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Active Chats</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                    </svg>
                </div>
                <div class="text-3xl font-bold text-indigo-600">{{ $activeChats }}</div>
                <div class="text-sm text-gray-500 mt-1">Ongoing conversations</div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500 hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Archived Chats</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                </div>
                <div class="text-3xl font-bold text-green-600">{{ $archivedChats }}</div>
                <div class="text-sm text-gray-500 mt-1">Completed &amp; stored</div>
            </div>

            <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500 hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Messages Sent</h3>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div class="text-3xl font-bold text-purple-600">{{ $totalMessagesSent }}</div>
                <div class="text-sm text-gray-500 mt-1">Your messages</div>
            </div>

        </div>

        {{-- Main Content: Recent Conversations + Quick Actions --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">

            {{-- Recent Conversations (2/3 width) --}}
            <div class="lg:col-span-2">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Recent Conversations</h2>

                @if($recentConversations->count() > 0)
                    <div class="bg-white rounded-lg shadow-lg divide-y divide-gray-100">
                        @foreach($recentConversations as $conversation)
                            <a href="{{ route('chat.support') }}?conversation={{ $conversation['id'] }}"
                               class="flex items-center justify-between p-5 hover:bg-blue-50 transition-colors duration-150 group">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-base font-semibold text-gray-800 group-hover:text-blue-700 truncate">
                                        {{ $conversation['title'] }}
                                    </h4>
                                    @if($conversation['last_message_preview'])
                                        <p class="text-sm text-gray-500 mt-1 truncate">
                                            {{ $conversation['last_message_role'] === 'user' ? 'You' : 'AI' }}:
                                            {{ $conversation['last_message_preview'] }}
                                        </p>
                                    @endif
                                    <div class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                                        <span>{{ $conversation['message_count'] }} messages</span>
                                        <span>{{ $conversation['time_ago'] }}</span>
                                    </div>
                                </div>
                                <div class="ml-4 flex-shrink-0">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 group-hover:bg-blue-200">
                                        Resume
                                    </span>
                                </div>
                            </a>
                        @endforeach
                    </div>

                    @if($activeChats > 5)
                        <div class="mt-4 text-center">
                            <a href="{{ route('chat.support') }}"
                               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                View all {{ $activeChats }} active conversations &rarr;
                            </a>
                        </div>
                    @endif
                @else
                    <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-blue-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <h4 class="text-lg font-semibold text-gray-700 mb-2">No conversations yet</h4>
                        <p class="text-gray-500 mb-4">Start your first conversation to get personalized AI support.</p>
                        <a href="{{ route('chat.support') }}"
                           class="inline-flex items-center px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors duration-150">
                            Start Your First Chat
                        </a>
                    </div>
                @endif
            </div>

            {{-- Quick Actions Sidebar (1/3 width) --}}
            <div>
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Quick Actions</h2>
                <div class="space-y-4">

                    <a href="{{ route('chat.support') }}"
                       class="block bg-white rounded-lg shadow-lg p-5 border-l-4 border-blue-500 hover:scale-105 transition-transform duration-200 group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Start New Chat</h4>
                                <p class="text-sm text-gray-500">Begin a new conversation</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('chat.support') }}#archived"
                       class="block bg-white rounded-lg shadow-lg p-5 border-l-4 border-green-500 hover:scale-105 transition-transform duration-200 group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">View Archives</h4>
                                <p class="text-sm text-gray-500">{{ $archivedChats }} archived conversation{{ $archivedChats !== 1 ? 's' : '' }}</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('chat.support') }}#settings-memories"
                       class="block bg-white rounded-lg shadow-lg p-5 border-l-4 border-purple-500 hover:scale-105 transition-transform duration-200 group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Manage Memories</h4>
                                <p class="text-sm text-gray-500">{{ $memoryCount }} memories stored</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('chat.support') }}#settings"
                       class="block bg-white rounded-lg shadow-lg p-5 border-l-4 border-yellow-500 hover:scale-105 transition-transform duration-200 group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center group-hover:bg-yellow-200 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Chat Settings</h4>
                                <p class="text-sm text-gray-500">Manage preferences & data</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('chat.support') }}#search"
                       class="block bg-white rounded-lg shadow-lg p-5 border-l-4 border-cyan-500 hover:scale-105 transition-transform duration-200 group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-cyan-100 rounded-lg flex items-center justify-center group-hover:bg-cyan-200 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-800">Search Conversations</h4>
                                <p class="text-sm text-gray-500">Search across all your chats</p>
                            </div>
                        </div>
                    </a>

                    <a href="{{ route('chat.support') }}"
                       class="block bg-white rounded-lg shadow-lg p-5 border-l-4 border-rose-500 hover:scale-105 transition-transform duration-200 group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-rose-100 rounded-lg flex items-center justify-center group-hover:bg-rose-200 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
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
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">What Our AI Can Help With</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                <div class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition-shadow duration-200">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-2">Academic Stress</h4>
                    <p class="text-sm text-gray-500">Exam anxiety, study strategies, time management, and academic pressure.</p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition-shadow duration-200">
                    <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-2">Emotional Support</h4>
                    <p class="text-sm text-gray-500">Feeling overwhelmed, lonely, anxious, or simply need someone to listen.</p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition-shadow duration-200">
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-2">Social Challenges</h4>
                    <p class="text-sm text-gray-500">Making friends, handling conflicts, adapting to university life.</p>
                </div>

                <div class="bg-white rounded-lg shadow-lg p-6 text-center hover:shadow-xl transition-shadow duration-200">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                        </svg>
                    </div>
                    <h4 class="font-semibold text-gray-800 mb-2">Goal Setting</h4>
                    <p class="text-sm text-gray-500">Career planning, personal goals, building healthy habits and routines.</p>
                </div>

            </div>
        </div>

        {{-- Privacy Notice --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
            <p class="text-sm text-blue-800">
                <strong>Your privacy matters.</strong> All conversations are private and encrypted.
                The AI remembers context to provide better support, but you can
                <a href="{{ route('chat.support') }}#settings-memories" class="underline font-medium hover:text-blue-900">manage your memories</a>
                at any time.
            </p>
        </div>

    </div>

    {{-- AI Character Cursor Tracking Script --}}
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const character = document.getElementById('aiCharacter');
        const leftPupil = document.getElementById('leftPupil');
        const rightPupil = document.getElementById('rightPupil');
        const mouth = document.getElementById('aiMouth');

        if (!character || !leftPupil || !rightPupil) return;

        const maxMove = 5; // max pixels the pupil can move

        document.addEventListener('mousemove', function (e) {
            const rect = character.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;

            const deltaX = e.clientX - centerX;
            const deltaY = e.clientY - centerY;
            const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);

            // Normalize and clamp movement
            const moveX = (deltaX / Math.max(distance, 1)) * Math.min(distance / 20, maxMove);
            const moveY = (deltaY / Math.max(distance, 1)) * Math.min(distance / 20, maxMove);

            leftPupil.style.transform = `translate(${moveX}px, ${moveY}px)`;
            rightPupil.style.transform = `translate(${moveX}px, ${moveY}px)`;

            // Smile wider when cursor is close
            if (distance < 200) {
                mouth.style.width = '2.8rem';
                mouth.style.height = '1.5rem';
                mouth.style.borderBottomWidth = '3px';
            } else {
                mouth.style.width = '2.5rem';
                mouth.style.height = '1.25rem';
                mouth.style.borderBottomWidth = '3px';
            }
        });

        // Blink animation
        function blink() {
            leftPupil.style.transition = 'transform 0.1s, height 0.1s';
            rightPupil.style.transition = 'transform 0.1s, height 0.1s';
            leftPupil.style.height = '2px';
            rightPupil.style.height = '2px';
            setTimeout(() => {
                leftPupil.style.height = '';
                rightPupil.style.height = '';
            }, 150);
        }

        // Blink every 3-5 seconds randomly
        function scheduleBlink() {
            const delay = 3000 + Math.random() * 2000;
            setTimeout(() => {
                blink();
                scheduleBlink();
            }, delay);
        }
        scheduleBlink();
    });
    </script>
</x-app-layout>
