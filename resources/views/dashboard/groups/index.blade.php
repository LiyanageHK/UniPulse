<x-app-layout title="Groups - UniPulse">
    <x-peer-macthing-nav />
    <div class="min-h-screen container mx-auto px-4 py-10">
        <div class="max-w-6xl mx-auto">
            <div class="">
                <div class="flex justify-between items-center mb-6 mt-6">
                    <h1 class="text-3xl font-bold">My Groups</h1>
                    <div class="flex space-x-3">
                        <a href="{{ route('peer-matching.index') }}"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                            </svg>
                            AI Peer Matching
                        </a>
                        <a href="{{ route('groups.discover') }}"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                            Discover Groups
                        </a>
                        <a href="{{ route('groups.create') }}"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                            Create Group
                        </a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($myGroups->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($myGroups as $group)
                            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                                <div class="p-6">
                                    <div class="flex items-center mb-4">
                                        <div
                                            class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white text-xl font-bold">
                                            {{ strtoupper(substr($group->name, 0, 1)) }}
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <h3 class="font-semibold text-lg">{{ $group->name }}</h3>
                                            <span
                                                class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">{{ $group->category }}</span>
                                        </div>
                                    </div>

                                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                        {{ $group->description ?? 'No description' }}
                                    </p>

                                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                                        <div class="flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                            </svg>
                                            {{ $group->members->count() }} members
                                        </div>
                                        @if ($group->isAdmin(auth()->id()))
                                            <span
                                                class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">Admin</span>
                                        @endif
                                    </div>

                                    @if ($group->last_message)
                                        <div class="text-xs text-gray-500 mb-4 border-t pt-2">
                                            <p class="truncate">{{ $group->last_message }}</p>
                                        </div>
                                    @endif

                                    <a href="{{ route('groups.show', $group->id) }}"
                                        class="block w-full text-center bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg transition">
                                        Open Chat
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white rounded-lg shadow-md p-12 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Groups Yet</h3>
                        <p class="text-gray-500 mb-6">You haven't joined any groups. Discover groups or create your own!</p>
                        <div class="flex justify-center space-x-4">
                            <a href="{{ route('groups.discover') }}"
                                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                                Discover Groups
                            </a>
                            <a href="{{ route('groups.create') }}"
                                class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg">
                                Create Group
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
