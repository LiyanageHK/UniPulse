<x-app-layout title="AI Wellbeing Suggestions - UniPulse">
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">

        {{-- Back Navigation --}}
        <div class="mb-6">
            <a href="{{ route('risk-dashboard.index') }}"
                class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-700 transition text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Risk Dashboard
            </a>
        </div>

        {{-- Page Header --}}
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <span class="text-3xl">💡</span>
                <h1 class="text-3xl font-bold text-gray-800">AI Wellbeing Suggestions</h1>
            </div>
            <p class="text-gray-500 ml-12">Personalized supportive suggestions based on your journal entries</p>
        </div>

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg mb-6">
                {{ session('success') }}</div>
        @endif

        @if (!$riskProfile)
            {{-- No Data State --}}
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <div class="text-6xl mb-4">📝</div>
                <h2 class="text-2xl font-bold text-gray-700 mb-2">No Risk Data Yet</h2>
                <p class="text-gray-500 mb-6">
                    Start writing daily journal entries. Your entries will be analyzed to generate
                    your risk profile and personalized suggestions.
                </p>
                <a href="{{ route('journal.index') }}"
                    class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-semibold px-8 py-3 rounded-lg transition">
                    Start Journaling
                </a>
            </div>
        @else
            {{-- Risk Level Summary Card --}}
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex items-center gap-4">
                        @php
                            $bgColors = [
                                'green' => 'bg-green-100 text-green-800 border-green-300',
                                'yellow' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                'red' => 'bg-red-100 text-red-800 border-red-300',
                                'gray' => 'bg-gray-100 text-gray-600 border-gray-300',
                            ];
                            $badgeClass = $bgColors[$riskProfile['risk_color']] ?? $bgColors['gray'];
                        @endphp
                        <span class="inline-block px-4 py-2 rounded-full text-sm font-bold border {{ $badgeClass }}">
                            {{ $riskProfile['risk_level'] }} Risk
                        </span>
                        <div>
                            <p class="text-sm text-gray-500">LRI Score: <span
                                    class="font-semibold text-gray-700">{{ $riskProfile['lri_score'] }}</span></p>
                            <p class="text-xs text-gray-400">Week #{{ $riskProfile['week_index'] }} &mdash;
                                {{ $riskProfile['week_start'] }} to {{ $riskProfile['week_end'] }}</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 font-medium">{{ $riskProfile['risk_message'] }}</p>
                </div>
            </div>

            @if ($riskLevel === 'Low')
                {{-- ═══════════════════════════════════════════════
                LOW RISK: Action Buttons
                ═══════════════════════════════════════════════ --}}
                <div class="bg-white rounded-2xl shadow-lg p-8 mb-6">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                            <span class="text-3xl">🌟</span>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800 mb-2">You're Doing Great!</h2>
                        <p class="text-gray-500 max-w-md mx-auto">
                            Your wellbeing indicators are looking positive. Keep up the great work!
                            Stay connected and continue building positive relationships.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-2xl mx-auto">
                        {{-- Start Conversation Button --}}
                        <a href="{{ route('chat.support') }}" id="btn-start-conversation"
                            class="group block bg-gradient-to-br from-purple-50 to-indigo-50 border-2 border-purple-200 rounded-2xl p-6 text-center hover:shadow-lg hover:border-purple-400 transition-all transform hover:-translate-y-1">
                            <div
                                class="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-xl mb-4 shadow-lg group-hover:shadow-xl transition">
                                <span class="text-2xl text-white">💬</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-1">Start Conversation</h3>
                            <p class="text-sm text-gray-500">Chat with our AI-powered conversational support system</p>
                        </a>

                        {{-- Find Peer Match Button --}}
                        <a href="{{ route('peer-matchings') }}" id="btn-find-peer-match"
                            class="group block bg-gradient-to-br from-emerald-50 to-teal-50 border-2 border-emerald-200 rounded-2xl p-6 text-center hover:shadow-lg hover:border-emerald-400 transition-all transform hover:-translate-y-1">
                            <div
                                class="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl mb-4 shadow-lg group-hover:shadow-xl transition">
                                <span class="text-2xl text-white">🤝</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800 mb-1">Find Peer Match</h3>
                            <p class="text-sm text-gray-500">Connect with students who share similar interests and experiences
                            </p>
                        </a>
                    </div>
                </div>

            @elseif (in_array($riskLevel, ['Moderate', 'High']))
                {{-- ═══════════════════════════════════════════════
                MODERATE / HIGH RISK: AI Suggestions
                ═══════════════════════════════════════════════ --}}


                <div class="bg-white rounded-2xl shadow-lg p-6 mb-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-700">Your Personalized Suggestions</h2>
                        <button id="refresh-suggestions-btn" onclick="refreshSuggestions()"
                            class="inline-flex items-center gap-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 font-medium px-4 py-2 rounded-lg transition text-sm">
                            <svg id="refresh-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span id="refresh-text">Refresh Suggestions</span>
                        </button>
                    </div>

                    <div id="suggestions-container" class="space-y-4">
                        @forelse ($aiSuggestions as $index => $suggestion)
                            @php
                                $icons = ['🎥', '🧘', '✨'];
                                $gradients = [
                                    'from-violet-50 to-purple-50 border-violet-200',
                                    'from-blue-50 to-cyan-50 border-blue-200',
                                    'from-emerald-50 to-green-50 border-emerald-200',
                                ];
                                $iconBgs = ['bg-violet-100', 'bg-blue-100', 'bg-emerald-100'];
                                $labels = ['Watch & Learn', 'Mindfulness Activity', 'Wellbeing Exercise'];
                            @endphp
                            <div class="flex items-start gap-4 p-5 rounded-xl border bg-gradient-to-r {{ $gradients[$index] ?? $gradients[0] }} transition-all hover:shadow-md"
                                style="animation: fadeInUp 0.4s ease-out {{ $index * 0.15 }}s both;">
                                <div
                                    class="flex-shrink-0 w-14 h-14 {{ $iconBgs[$index] ?? $iconBgs[0] }} rounded-xl flex items-center justify-center text-2xl">
                                    {{ $icons[$index] ?? '💡' }}
                                </div>
                                <div class="flex-1">
                                    <span
                                        class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1 block">{{ $labels[$index] ?? 'Suggestion' }}</span>
                                    <p class="text-gray-700 leading-relaxed font-medium">{{ $suggestion }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8 text-gray-400">
                                <div class="text-4xl mb-3">💡</div>
                                <p class="font-medium">No suggestions available right now.</p>
                                <p class="text-sm mt-1">Click "Refresh Suggestions" to generate new ones.</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Loading state --}}
                    <div id="suggestions-loading" class="hidden text-center py-10">
                        <div class="inline-flex flex-col items-center gap-3 text-indigo-600">
                            <svg class="animate-spin h-8 w-8" xmlns="http://www.w3.org/2000/svg" fill="none"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <span class="font-medium text-sm">Generating personalized suggestions...</span>
                        </div>
                    </div>
                </div>

                {{-- Helpful Resources Section --}}
                <div
                    class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-2xl shadow-lg p-6 mb-6 border border-purple-100">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Access</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <a href="{{ route('chat.support') }}"
                            class="flex items-center gap-3 bg-white rounded-xl p-4 border border-purple-100 hover:shadow-md hover:border-purple-300 transition-all">
                            <div class="flex-shrink-0 w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <span class="text-lg">💬</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-700 text-sm">Talk to AI Support</p>
                                <p class="text-xs text-gray-400">Start a supportive conversation</p>
                            </div>
                        </a>
                        <a href="{{ route('peer-matchings') }}"
                            class="flex items-center gap-3 bg-white rounded-xl p-4 border border-purple-100 hover:shadow-md hover:border-purple-300 transition-all">
                            <div class="flex-shrink-0 w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                <span class="text-lg">🤝</span>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-700 text-sm">Find Peer Support</p>
                                <p class="text-xs text-gray-400">Connect with similar students</p>
                            </div>
                        </a>
                    </div>
                </div>

            @endif

        @endif
    </div>

    {{-- CSS Animation --}}
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    {{-- JS: Refresh AI Suggestions via AJAX --}}
    @if (isset($riskLevel) && in_array($riskLevel, ['Moderate', 'High']))
        <script>
            function refreshSuggestions() {
                const container = document.getElementById('suggestions-container');
                const loading = document.getElementById('suggestions-loading');
                const btn = document.getElementById('refresh-suggestions-btn');
                const btnText = document.getElementById('refresh-text');
                const icon = document.getElementById('refresh-icon');

                container.classList.add('hidden');
                loading.classList.remove('hidden');
                btn.disabled = true;
                btnText.textContent = 'Generating...';
                icon.classList.add('animate-spin');

                fetch('{{ route("risk.api.suggestions") }}', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                    .then(res => res.json())
                    .then(data => {
                        const suggestions = data.suggestions || [];
                        const icons = ['🎥', '🧘', '✨'];
                        const gradients = [
                            'from-violet-50 to-purple-50 border-violet-200',
                            'from-blue-50 to-cyan-50 border-blue-200',
                            'from-emerald-50 to-green-50 border-emerald-200',
                        ];
                        const iconBgs = ['bg-violet-100', 'bg-blue-100', 'bg-emerald-100'];
                        const labels = ['Watch & Learn', 'Mindfulness Activity', 'Wellbeing Exercise'];

                        if (suggestions.length > 0) {
                            container.innerHTML = suggestions.map((s, i) => `
                                <div class="flex items-start gap-4 p-5 rounded-xl border bg-gradient-to-r ${gradients[i] || gradients[0]} transition-all hover:shadow-md"
                                     style="animation: fadeInUp 0.4s ease-out ${i * 0.15}s both;">
                                    <div class="flex-shrink-0 w-14 h-14 ${iconBgs[i] || iconBgs[0]} rounded-xl flex items-center justify-center text-2xl">
                                        ${icons[i] || '💡'}
                                    </div>
                                    <div class="flex-1">
                                        <span class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-1 block">${labels[i] || 'Suggestion'}</span>
                                        <p class="text-gray-700 leading-relaxed font-medium">${s}</p>
                                    </div>
                                </div>
                            `).join('');
                        } else {
                            container.innerHTML = `
                                <div class="text-center py-8 text-gray-400">
                                    <div class="text-4xl mb-3">💡</div>
                                    <p class="font-medium">No suggestions could be generated.</p>
                                    <p class="text-sm mt-1">Please try again later.</p>
                                </div>
                            `;
                        }
                        container.classList.remove('hidden');
                        loading.classList.add('hidden');
                    })
                    .catch(err => {
                        console.error('Failed to refresh suggestions:', err);
                        container.innerHTML = `
                            <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-700 flex items-center gap-2">
                                <span>⚠️</span>
                                <span>Unable to refresh suggestions right now. Please try again later.</span>
                            </div>
                        `;
                        container.classList.remove('hidden');
                        loading.classList.add('hidden');
                    })
                    .finally(() => {
                        btn.disabled = false;
                        btnText.textContent = 'Refresh Suggestions';
                        icon.classList.remove('animate-spin');
                    });
            }
        </script>
    @endif
</x-app-layout>