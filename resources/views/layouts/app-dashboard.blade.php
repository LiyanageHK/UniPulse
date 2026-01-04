<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>UniPluse</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script> --}}
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .fade-in {
            animation: fadeIn 0.8s ease-in;
        }

        .active {
            background-color: #6b21a8;
            /* gray-100 */
            color: #fff !important;
            /* purple-700 */
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>

<body>
    <div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-blue-50">
        <div class="flex">
            <aside class="w-64 min-h-screen bg-white shadow-xl">
                <div class="p-6">
                    <div class="flex items-center space-x-3 mb-8">
                        <div
                            class="w-10 h-10 bg-gradient-to-r from-purple-600 to-blue-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                </path>
                            </svg>
                        </div>
                        <a href="{{ route('home') }}">
                            <h1 class="text-xl font-bold text-gray-900">UniPluse</h1>
                        </a>
                    </div>

                    <div class="mb-8 p-4 bg-gradient-to-r from-purple-50 to-blue-50 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div
                                class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ auth()->user()->name ?? 'User' }}</p>
                                <p class="text-sm text-gray-600">Student</p>
                            </div>
                        </div>
                    </div>

                    <nav class="space-y-2">
                        <a href="{{ route('dashboard') }}"
                            class="flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg transition {{ request()->is('dashboard') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                </path>
                            </svg>
                            <span class="font-medium">Dashboard</span>
                        </a>

                        <a href="{{ route('risk-level') }}"
                            class="flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg transition {{ request()->is('risk-level') || request()->is('suggestions') ? 'active' : '' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                <line x1="12" y1="9" x2="12" y2="13" stroke-width="2"
                                    stroke-linecap="round" />
                                <circle cx="12" cy="17" r="1" fill="currentColor" />
                            </svg>

                            <span class="font-medium">Risk Level</span>
                        </a>

                        <a href="{{ route('weekly-checkings') }}"
                            class="flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg transition
                            {{ request()->is('weekly-checkings') || request()->is('weekly-checkings-view/*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <span class="font-medium">Weekly Check-In</span>
                        </a>

                        <a href="{{ route('peer-matchings') }}"
                            class="flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg transition
                            {{ request()->is('peer-matchings') || request()->is('profile/*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20v-1a4 4 0 00-3-3.87M7 20v-1a4 4 0 013-3.87" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 12a4 4 0 100-8 4 4 0 000 8z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 16v-1a4 4 0 00-3-3.87" />
                            </svg>

                            <span class="font-medium">Peer Matchings</span>
                        </a>

                        <a href="{{ route('requests.incoming') }}"
                            class="flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg transition
                            {{ request()->is('requests') ? 'active' : '' }}">

                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2 12a10 10 0 1120 0 10 10 0 01-20 0zm14 5l5 5" />
                            </svg>

                            <span class="font-medium">Requests</span>
                        </a>

                        @if ($hasChats)
                            <a href="{{ route('chat.view') }}"
                                class="flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg transition
                                {{ request()->is('chat') || request()->is('chat/*') || request()->is('chat-view') ? 'active' : '' }}">

                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4-4 7-9 7a9.86 9.86 0 01-4-.8L3 20l1.5-3A6.9 6.9 0 013 12c0-4 4-7 9-7s9 3 9 7z" />
                                </svg>

                                <span class="font-medium">Chats</span>
                            </a>
                        @endif

                        <a href="{{ route('groups.index') }}"
                            class="flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg transition {{ request()->routeIs('groups.index') || request()->is('groups/*') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            <span class="font-medium">Groups</span>
                        </a>

                        {{-- <a href="#"
                            class="flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg transition {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                            <span class="font-medium">My Progress</span>
                        </a>

                        <a href="#"
                            class="flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg transition {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                </path>
                            </svg>
                            <span class="font-medium">Peer Connections</span>
                        </a>

                        <a href="#"
                            class="flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg transition {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                </path>
                            </svg>
                            <span class="font-medium">Resources</span>
                        </a>

                        <a href="#"
                            class="flex items-center space-x-3 px-4 py-3 text-gray-700 rounded-lg transition {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="font-medium">Settings</span>
                        </a> --}}
                    </nav>

                    <div class="mt-8 pt-8 border-t border-gray-200">
                        <a href="{{ route('logout') }}"
                            class="flex items-center space-x-3 px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition w-full">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                            <span class="font-medium">Logout</span>
                        </a>
                    </div>
                </div>
            </aside>

            <main class="flex-1 p-8">
                @yield('content')
            </main>
        </div>
    </div>
    {{-- <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script> --}}

</body>

</html>
