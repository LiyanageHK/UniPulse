<x-app-layout>
    <div class="min-h-screen container mx-auto px-4 py-10">
        <div class="max-w-7xl mx-auto">
            <div class="mx-auto px-4 py-6">
                <div class="flex items-center mb-6">
                    <a href="{{ route('groups.discover') }}" class="text-gray-600 hover:text-gray-800 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                    </a>
                    <h1 class="text-3xl font-bold">Group Preview</h1>
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

                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Group Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-purple-500 p-8 text-white">
                        <div class="flex items-center">
                            <div
                                class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-3xl font-bold">
                                {{ strtoupper(substr($group->name, 0, 1)) }}
                            </div>
                            <div class="ml-4">
                                <h2 class="text-3xl font-bold">{{ $group->name }}</h2>
                                <div class="flex items-center mt-2 space-x-4">
                                    <span
                                        class="bg-white bg-opacity-20 px-3 py-1 rounded text-sm">{{ $group->category }}</span>
                                    <span class="text-sm">{{ $group->members->count() }} members</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Group Info -->
                    <div class="p-6">
                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-2">About</h3>
                            <p class="text-gray-600">{{ $group->description ?? 'No description available.' }}</p>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-2">Admin</h3>
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                    {{ strtoupper(substr($group->admin->name, 0, 1)) }}
                                </div>
                                <div class="ml-3">
                                    <p class="font-semibold">{{ $group->admin->name }}</p>
                                    <p class="text-sm text-gray-500">Group Creator</p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h3 class="text-xl font-semibold mb-3">Members ({{ $group->members->count() }})</h3>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                @foreach ($group->members->take(6) as $member)
                                    <div class="flex items-center p-2 bg-gray-50 rounded">
                                        <div
                                            class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-sm">
                                            {{ strtoupper(substr($member->name, 0, 1)) }}
                                        </div>
                                        <span class="ml-2 text-sm truncate">{{ $member->name }}</span>
                                    </div>
                                @endforeach
                                @if ($group->members->count() > 6)
                                    <div class="flex items-center p-2 bg-gray-50 rounded text-gray-500 text-sm">
                                        +{{ $group->members->count() - 6 }} more
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Join Action -->
                        <div class="bg-gray-50 rounded-lg p-6 text-center">
                            @if ($hasPendingRequest)
                                <div class="bg-yellow-100 text-yellow-800 py-3 px-4 rounded-lg inline-block">
                                    <svg class="w-5 h-5 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Request Pending
                                </div>
                                <p class="text-gray-600 mt-2">Your join request is waiting for admin approval</p>
                            @else
                                <h3 class="text-xl font-semibold mb-2">Join this group?</h3>
                                <p class="text-gray-600 mb-4">Send a request to join and start chatting with members</p>
                                <form method="POST" action="{{ route('groups.sendRequest', $group->id) }}">
                                    @csrf
                                    <button type="submit"
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-8 py-3 rounded-lg text-lg font-semibold">
                                        Request to Join
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
