<x-app-layout title="Peer Matching - UniPulse">
    <x-peer-macthing-nav/>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <div class="">
                <h1 class="text-2xl font-semibold mb-6">Peer Matching</h1>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($matches as $match)
                        <div class="bg-white shadow rounded-xl p-5 border border-gray-100">

                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                    <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 14a4 4 0 10-8 0v1a2 2 0 002 2h4a2 2 0 002-2v-1z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 7a3 3 0 100-6 3 3 0 000 6z" />
                                    </svg>
                                </div>

                                <div>
                                    <p class="text-lg font-semibold">{{ $match['user']->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $match['profile']->university }}</p>
                                </div>
                            </div>

                            <div class="mt-4">
                                <p class="text-gray-600 text-sm">Matching Score</p>
                                <p class="text-xl font-bold text-blue-600">{{ $match['percentage'] }}%</p>
                            </div>

                            @php
                                $alreadySent = \App\Models\PeerRequest::where('sender_id', auth()->id())
                                    ->where('receiver_id', $match['user']->id)
                                    ->first();

                                $alreadyReceived = \App\Models\PeerRequest::where('sender_id', $match['user']->id)
                                    ->where('receiver_id', auth()->id())
                                    ->first();

                                $peered = \App\Models\PeerRequest::where(function ($query) use ($match) {
                                    $query
                                        ->where(function ($q) use ($match) {
                                            $q->where('sender_id', auth()->id())->where(
                                                'receiver_id',
                                                $match['user']->id,
                                            );
                                        })
                                        ->orWhere(function ($q) use ($match) {
                                            $q->where('sender_id', $match['user']->id)->where(
                                                'receiver_id',
                                                auth()->id(),
                                            );
                                        });
                                })
                                    ->where('status', 'accepted')
                                    ->exists();
                            @endphp

                            <!-- Star Rating Form -->
                            @if ($peered)
                                <div class="rating-card" data-saved-rating="{{ (int) $match['my_rating'] }}">
                                    <form action="{{ route('peer.rating', $match['user']->id) }}" method="POST">
                                        @csrf
                                        <div class="rating-stars flex items-center space-x-1">
                                            @for ($i = 1; $i <= 5; $i++)
                                                <button type="submit" name="rating" value="{{ $i }}"
                                                    class="star-btn" data-value="{{ $i }}">
                                                    <svg class="star w-6 h-6 transition
                        {{ (int) $match['my_rating'] >= $i ? 'text-yellow-400' : 'text-gray-300' }}"
                                                        fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0
                                                            1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755
                                                            1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1
                                                            1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0
                                                            00.951-.69l1.07-3.292z" />
                                                    </svg>
                                                </button>
                                            @endfor
                                        </div>
                                    </form>
                                </div>
                            @endif


                            <div class="mt-5 flex justify-between items-center">
                                <a href="{{ route('profile.view', $match['user']->id) }}"
                                    class="text-sm text-blue-600 hover:underline">
                                    View Profile
                                </a>


                                @if (!$alreadySent && !$alreadyReceived)
                                    <form method="POST" action="{{ route('peer.send', $match['user']->id) }}">
                                        @csrf
                                        <button type="submit"
                                            class="px-4 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors duration-200 shadow-md">
                                            Send Request
                                        </button>
                                    </form>
                                @else
                                    @if ($alreadySent)
                                        <span @class([
                                            'px-2 py-1 rounded font-semibold',
                                            'text-yellow-800 bg-yellow-100' => $alreadySent->status === 'pending',
                                            'text-green-800 bg-green-100' => $alreadySent->status === 'accepted',
                                            'text-red-800 bg-red-100' => $alreadySent->status === 'rejected',
                                        ])>
                                            {{ ucfirst($alreadySent->status) }} (Sent)
                                        </span>
                                    @endif
                                    @if ($alreadyReceived)
                                        <span @class([
                                            'px-2 py-1 rounded font-semibold',
                                            'text-yellow-800 bg-yellow-100' => $alreadyReceived->status === 'pending',
                                            'text-green-800 bg-green-100' => $alreadyReceived->status === 'accepted',
                                            'text-red-800 bg-red-100' => $alreadyReceived->status === 'rejected',
                                        ])>
                                            {{ ucfirst($alreadyReceived->status) }} (Received)
                                        </span>
                                    @endif
                                @endif

                            </div>

                        </div>
                    @endforeach
                </div>
            </div>
            <script>
                document.querySelectorAll('.rating-card').forEach(card => {

                    const stars = card.querySelectorAll('.star');
                    const buttons = card.querySelectorAll('.star-btn');

                    let savedRating = Number(card.getAttribute('data-saved-rating')) || 0;

                    function highlightStars(rating) {
                        stars.forEach(star => {
                            const value = Number(star.parentElement.getAttribute('data-value'));
                            if (value <= rating) {
                                star.classList.add('text-yellow-400');
                                star.classList.remove('text-gray-300');
                            } else {
                                star.classList.remove('text-yellow-400');
                                star.classList.add('text-gray-300');
                            }
                        });
                    }

                    buttons.forEach(btn => {
                        btn.addEventListener('mouseover', () => {
                            highlightStars(Number(btn.dataset.value));
                        });

                        btn.addEventListener('mouseout', () => {
                            highlightStars(savedRating);
                        });
                    });

                    highlightStars(savedRating);
                });
            </script>
        </div>
    </div>
</x-app-layout>
