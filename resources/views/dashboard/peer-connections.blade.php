<x-app-layout title="Peer Connections - UniPulse">
    <x-peer-macthing-nav />

    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 py-10">

            {{-- Page header --}}
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">My Connections</h1>
                <p class="text-gray-500 text-sm mt-1">Students you are connected with through peer matching</p>
            </div>

            @if (count($connections) == 0)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-16 text-center">
                    <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-1">No connections yet</h3>
                    <p class="text-gray-400 text-sm">Use Peer Matching to find and connect with compatible students.</p>
                </div>
            @else

                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach ($connections as $index => $c)
                        @php
                            $gradients = [
                                ['from-blue-500','to-indigo-600'],
                                ['from-violet-500','to-purple-600'],
                                ['from-emerald-500','to-teal-600'],
                                ['from-rose-500','to-pink-600'],
                                ['from-amber-500','to-orange-600'],
                                ['from-cyan-500','to-blue-600'],
                            ];
                            $grad = $gradients[$index % count($gradients)];

                            $peerName     = $c['user']->name ?? 'Unknown';
                            $peerInitials = strtoupper(substr(trim($peerName), 0, 1) . (strpos(trim($peerName), ' ') !== false ? substr(trim($peerName), strpos(trim($peerName),' ')+1, 1) : ''));
                            $university   = $c['profile']->university ?? '';
                            $rating       = (int)($c['rating'] ?? 0);
                            $interests    = $c['interests'] ?? [];
                        @endphp

                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden flex flex-col">

                            {{-- Card top accent bar --}}
                            <div class="h-1 w-full bg-gradient-to-r {{ $grad[0] }} {{ $grad[1] }}"></div>

                            <div class="p-6 flex flex-col flex-1">

                                {{-- Peer info row --}}
                                <div class="flex items-center gap-4 mb-5">

                                    {{-- Avatar --}}
                                    <div class="flex-shrink-0 w-14 h-14 rounded-full bg-gradient-to-br {{ $grad[0] }} {{ $grad[1] }}
                                                flex items-center justify-center text-white font-bold text-lg shadow-sm select-none">
                                        {{ $peerInitials ?: '?' }}
                                    </div>

                                    {{-- Name + university --}}
                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-gray-900 text-base leading-tight truncate">{{ $peerName }}</p>
                                        @if ($university)
                                            <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $university }}</p>
                                        @endif
                                        {{-- Connected badge --}}
                                        <span class="inline-flex items-center gap-1 mt-1.5 bg-green-50 text-green-700 text-xs font-semibold px-2 py-0.5 rounded-full border border-green-200">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Connected
                                        </span>
                                    </div>

                                </div>

                                {{-- Divider --}}
                                <div class="border-t border-gray-100 mb-4"></div>

                                {{-- Rating --}}
                                <div class="flex items-center justify-between mb-4">
                                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Rating</span>
                                    <div class="flex items-center gap-0.5">
                                        @for ($s = 1; $s <= 5; $s++)
                                            @if ($s <= $rating)
                                                <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 text-gray-200" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endif
                                        @endfor
                                        <span class="text-xs text-gray-500 font-medium ml-1.5">{{ $rating }}/5</span>
                                    </div>
                                </div>

                                {{-- Common Interests --}}
                                <div class="mb-5 flex-1">
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Common Interests</p>
                                    @if (count($interests) > 0)
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach ($interests as $int)
                                                <span class="bg-indigo-50 text-indigo-700 text-xs font-medium px-2.5 py-1 rounded-full border border-indigo-100">
                                                    {{ $int }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-xs text-gray-400 italic">No common interests found</p>
                                    @endif
                                </div>

                                {{-- Action button --}}
                                <a href="{{ route('chat.view') }}"
                                    class="mt-auto w-full flex items-center justify-center gap-2 py-2.5 px-4 bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold rounded-xl transition-colors duration-150 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                    </svg>
                                    Open Chat
                                </a>

                            </div>
                        </div>
                    @endforeach
                </div>

            @endif
        </div>
    </div>
</x-app-layout>
