<x-app-layout title="Discover Groups - UniPulse">
    <div class="min-h-screen container mx-auto px-4 py-10">
        <div class="max-w-7xl mx-auto">
            <div class="container mx-auto px-4 py-6">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold">Discover Groups</h1>
                    <a href="{{ route('groups.index') }}"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                        My Groups
                    </a>
                </div>

                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                    <form method="GET" action="{{ route('groups.discover') }}" class="flex flex-wrap gap-4">
                        <div class="flex-1 min-w-[200px]">
                            <input type="text" name="search" placeholder="Search groups..."
                                value="{{ request('search') }}"
                                class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div class="min-w-[150px]">
                            <select name="category"
                                class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">All Categories</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg">
                            Search
                        </button>
                        @if (request('search') || request('category'))
                            <a href="{{ route('groups.discover') }}"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>

                @if ($groups->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($groups as $group)
                            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                                <div class="p-6">
                                    <div class="flex items-center mb-4">
                                        <div
                                            class="w-12 h-12 bg-gradient-to-br from-green-500 to-teal-500 rounded-full flex items-center justify-center text-white text-xl font-bold">
                                            {{ strtoupper(substr($group->name, 0, 1)) }}
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <h3 class="font-semibold text-lg">{{ $group->name }}</h3>
                                            <span
                                                class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded">{{ $group->category }}</span>
                                        </div>
                                    </div>

                                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">
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
                                        <div class="text-xs text-gray-500">
                                            Admin: {{ $group->admin->name }}
                                        </div>
                                    </div>

                                    <a href="{{ route('groups.show', $group->id) }}"
                                        class="block w-full text-center bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg transition">
                                        View Group
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
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Groups Found</h3>
                        <p class="text-gray-500 mb-6">Try adjusting your search or create your own group!</p>
                        <a href="{{ route('groups.create') }}"
                            class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg inline-block">
                            Create Group
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
