<x-app-layout title="Peer Connections - UniPulse">
    <x-peer-macthing-nav />
    <div class="min-h-screen container mx-auto px-4 py-8">
        <div class="max-w-7xl mx-auto">
            <div class="">
                <h1 class="text-2xl font-semibold mb-6">My Connections</h1>

                @if (count($connections) == 0)
                    <p class="text-gray-600">You have no peer connections yet.</p>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    @foreach ($connections as $c)
                        <div class="bg-white p-6 shadow-lg rounded-xl border border-gray-100">

                            {{-- Sports Style Card Layout --}}
                            <div class="flex items-center justify-between">

                                {{-- Left user (You) --}}
                                <div class="flex flex-col items-center w-1/3">
                                    <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 14a4 4 0 10-8 0v1a2 2 0 002 2h4a2 2 0 002-2v-1z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 7a3 3 0 100-6 3 3 0 000 6z" />
                                        </svg>
                                    </div>
                                    <p class="font-semibold mt-2 text-sm">You</p>
                                </div>

                                {{-- VS circle --}}
                                <div
                                    class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 font-bold">
                                    VS
                                </div>

                                {{-- Right user --}}
                                <div class="flex flex-col items-center w-1/3">
                                    <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 14a4 4 0 10-8 0v1a2 2 0 002 2h4a2 2 0 002-2v-1z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 7a3 3 0 100-6 3 3 0 000 6z" />
                                        </svg>
                                    </div>
                                    <p class="font-semibold mt-2 text-sm">{{ $c['user']->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $c['profile']->university }}</p>
                                </div>

                            </div>

                            <div class="border-t border-gray-200 my-4"></div>

                            <div class="space-y-2 text-sm">

                                <p><strong>Rating:</strong> ⭐ {{ $c['rating'] }}/5</p>



                                <p><strong>Common Interests:</strong></p>
                                <div class="flex flex-wrap gap-2 mt-1">
                                    @foreach ($c['interests'] as $int)
                                        <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">
                                            {{ $int }}
                                        </span>
                                    @endforeach
                                </div>

                            </div>

                            <div class="mt-5">
                                <br><br>
                                <a href="{{ route('chat.view') }}"
                                    class="w-full block text-center py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-md">
                                    Open Chat
                                </a>
                            </div>

                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
