<x-app-layout title="Risk Dashboard - UniPulse">
    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Social Inclusion Risk Dashboard</h1>
            <div class="flex gap-3">
                <a href="{{ route('risk-dashboard.history') }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-5 py-2 rounded-lg transition text-sm">
                    View History
                </a>
                <form action="{{ route('test.weekly-summary') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-lg transition text-sm"
                        onclick="this.disabled=true; this.innerText='Processing...'; this.form.submit();">
                        Recalculate Now
                    </button>
                </form>
            </div>
        </div>

        {{-- AI Service Status --}}
        @if (isset($aiHealthy) && !$aiHealthy)
            <div class="bg-amber-50 border border-amber-300 text-amber-800 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                <span class="text-lg">&#9888;</span>
                <span>AI service is currently unavailable. Risk scores may use fallback values.</span>
            </div>
        @endif

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg mb-6">{{ session('success') }}</div>
        @endif
        @if (session('warning'))
            <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 px-4 py-3 rounded-lg mb-6">{{ session('warning') }}</div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg mb-6">{{ session('error') }}</div>
        @endif

        @if ($riskProfile)
            {{-- ═══════════════════════════════════════════════
                 A. CURRENT WEEK RISK
                 ═══════════════════════════════════════════════ --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                {{-- LRI Score Card --}}
                <div class="bg-white rounded-2xl shadow-lg p-6 text-center">
                    <p class="text-sm text-gray-500 uppercase tracking-wide mb-2">Linguistic Risk Index</p>
                    <p class="text-5xl font-extrabold"
                       style="color: {{ $riskProfile['risk_color'] === 'yellow' ? '#ca8a04' : $riskProfile['risk_color'] }}">
                        {{ $riskProfile['lri_score'] }}
                    </p>
                    <p class="text-gray-400 text-sm mt-1">out of 1.0</p>
                </div>

                {{-- Risk Level Badge --}}
                <div class="bg-white rounded-2xl shadow-lg p-6 text-center flex flex-col justify-center">
                    <p class="text-sm text-gray-500 uppercase tracking-wide mb-3">Risk Level</p>
                    @php
                        $bgColors = [
                            'green'  => 'bg-green-100 text-green-800 border-green-300',
                            'yellow' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                            'orange' => 'bg-orange-100 text-orange-800 border-orange-300',
                            'red'    => 'bg-red-100 text-red-800 border-red-300',
                            'gray'   => 'bg-gray-100 text-gray-600 border-gray-300',
                        ];
                        $badgeClass = $bgColors[$riskProfile['risk_color']] ?? $bgColors['gray'];
                    @endphp
                    <span class="inline-block px-5 py-2 rounded-full text-lg font-bold border {{ $badgeClass }}">
                        {{ $riskProfile['risk_level'] }} Risk
                    </span>

                    {{-- Escalation Flag --}}
                    @if ($riskProfile['escalation_flag'])
                        <span class="mt-3 inline-block px-4 py-1 rounded-full text-sm font-semibold bg-red-600 text-white animate-pulse">
                            &#9888; Escalating Risk
                        </span>
                    @endif
                </div>

                {{-- Trend Indicator --}}
                <div class="bg-white rounded-2xl shadow-lg p-6 text-center flex flex-col justify-center">
                    <p class="text-sm text-gray-500 uppercase tracking-wide mb-3">Weekly Trend</p>
                    @php
                        $trendColors = [
                            'increasing' => 'text-red-600',
                            'decreasing' => 'text-green-600',
                            'stable'     => 'text-blue-600',
                        ];
                        $trendColor = $trendColors[$trend['direction']] ?? 'text-gray-600';
                    @endphp
                    <p class="text-4xl font-bold {{ $trendColor }}">{{ $trend['symbol'] }}</p>
                    <p class="text-sm font-medium {{ $trendColor }} mt-1">{{ $trend['label'] }}</p>
                    @if ($trend['delta'] !== null)
                        <p class="text-xs text-gray-400 mt-1">
                            {{ $trend['delta'] > 0 ? '+' : '' }}{{ $trend['delta'] }} points
                        </p>
                    @endif
                </div>

                {{-- Risk Interpretation --}}
                <div class="bg-white rounded-2xl shadow-lg p-6 flex flex-col justify-center">
                    <p class="text-sm text-gray-500 uppercase tracking-wide mb-2">Interpretation</p>
                    <p class="text-lg font-medium text-gray-700">{{ $riskProfile['risk_message'] }}</p>
                    <p class="text-xs text-gray-400 mt-3">
                        Week #{{ $riskProfile['week_index'] }} &mdash;
                        {{ $riskProfile['week_start'] }} &mdash; {{ $riskProfile['week_end'] }}
                    </p>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════
                 B. RISK HISTORY — LRI Trend Chart + Table
                 ═══════════════════════════════════════════════ --}}
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-700">
                        Risk Level Trend &mdash;
                        Last {{ count($trendData['scores']) }} {{ Str::plural('Week', count($trendData['scores'])) }}
                    </h2>
                    <a href="{{ route('risk-dashboard.history') }}" class="text-sm text-indigo-600 hover:underline">View all &rarr;</a>
                </div>

                @if ($trendData['has_data'] && count($trendData['scores']) >= 2)
                    <canvas id="lriTrendChart" height="120"></canvas>

                    {{-- Inline History Table --}}
                    <div class="mt-6 overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2">Week</th>
                                    <th class="px-4 py-2 text-center">LRI Score</th>
                                    <th class="px-4 py-2 text-center">Risk Level</th>
                                    <th class="px-4 py-2 text-center">Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = count($trendData['scores']) - 1; $i >= 0; $i--)
                                    @php
                                        $score = $trendData['scores'][$i];
                                        $level = $trendData['levels'][$i];
                                        $label = $trendData['labels'][$i];
                                        $summaryId = $trendData['ids'][$i] ?? null;
                                        $prevScore = $i > 0 ? $trendData['scores'][$i - 1] : null;
                                        $delta = $prevScore !== null ? $score - $prevScore : null;
                                        $rowTrend = $delta === null ? '&mdash;' : ($delta > 0.02 ? '<span class="text-red-600">&#8593;</span>' : ($delta < -0.02 ? '<span class="text-green-600">&#8595;</span>' : '<span class="text-blue-600">&#8594;</span>'));
                                        $riskColor = match ($level) {
                                            'Low'      => 'green',
                                            'Moderate' => 'yellow',
                                            'High'     => 'red',
                                            default    => 'gray',
                                        };
                                        $pillClass = match ($riskColor) {
                                            'green'  => 'bg-green-100 text-green-800',
                                            'yellow' => 'bg-yellow-100 text-yellow-800',
                                            'red'    => 'bg-red-100 text-red-800',
                                            default  => 'bg-gray-100 text-gray-600',
                                        };
                                    @endphp
                                    <tr class="border-b {{ $i === count($trendData['scores']) - 1 ? 'bg-indigo-50' : '' }}">
                                        <td class="px-4 py-2 font-medium">{{ $label }}</td>
                                        <td class="px-4 py-2 text-center font-semibold">{{ $score }}</td>
                                        <td class="px-4 py-2 text-center">
                                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $pillClass }}">{{ $level }}</span>
                                        </td>
                                        <td class="px-4 py-2 text-center text-lg">{!! $rowTrend !!}</td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center text-gray-400 py-8">
                        <p>Not enough data for trend analysis yet.</p>
                        <p class="text-sm mt-1">Keep writing daily journals to build your trend history.</p>
                    </div>
                @endif
            </div>

            {{-- LRI Formula Reference --}}
            <div class="bg-gray-50 rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">LRI Formula</h3>
                <p class="text-sm text-gray-500 font-mono">
                    LRI = (Stress + Sentiment + Pronoun + Absolutist) &divide; 4
                </p>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mt-4 text-xs text-gray-500">
                    <div><span class="inline-block w-3 h-3 rounded-full bg-green-400 mr-1"></span> Low (&lt; 0.3)</div>
                    <div><span class="inline-block w-3 h-3 rounded-full bg-yellow-400 mr-1"></span> Moderate (0.3&ndash;0.6)</div>
                    <div><span class="inline-block w-3 h-3 rounded-full bg-red-500 mr-1"></span> High (&ge; 0.6)</div>
                </div>
            </div>

        @else
            {{-- No Data State --}}
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <div class="text-6xl mb-4">&#128214;</div>
                <h2 class="text-2xl font-bold text-gray-700 mb-2">No Risk Data Yet</h2>
                <p class="text-gray-500 mb-6">
                    Start writing daily journal entries. Your entries will be analyzed to generate
                    your social inclusion risk profile automatically.
                </p>
                <a href="{{ route('journal.index') }}"
                   class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-semibold px-8 py-3 rounded-lg transition">
                    Start Journaling
                </a>
            </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════
         JOURNAL SECTION
         ═══════════════════════════════════════════════ --}}
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">

        {{-- ── TODAY'S ENTRY (always visible at top) ── --}}
        <div id="journal-write" class="bg-white rounded-2xl shadow-lg p-6 mb-6 border-l-4 {{ $todayEntry ? 'border-purple-500' : 'border-green-500' }}">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">
                        {{ $todayEntry ? "✏️ Update This Week's Journal Entry" : "📝 Write This Week's Journal Entry" }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-0.5">{{ now()->format('l, F j, Y') }}</p>
                </div>
                @if ($todayEntry)
                    <span class="inline-flex items-center gap-1 text-xs font-semibold bg-purple-100 text-purple-700 px-3 py-1 rounded-full">
                        ✅ Written today
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-xs font-semibold bg-green-100 text-green-700 px-3 py-1 rounded-full animate-pulse">
                        ✍️ No entry this week
                    </span>
                @endif
            </div>

            {{-- Success message --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('journal.store') }}" method="POST">
                @csrf
                <textarea id="journalContent" name="content" rows="5"
                    class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-purple-500 focus:border-purple-500 resize-y"
                    placeholder="How are you feeling today? Write freely about your thoughts, experiences, and emotions..."
                    required minlength="10" maxlength="5000">{{ old('content', $todayEntry?->content) }}</textarea>

                @error('content')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror

                <div class="flex justify-end mt-4">
                    <button type="submit"
                        class="bg-purple-600 hover:bg-purple-700 text-white font-semibold px-6 py-2 rounded-lg transition">
                        {{ $todayEntry ? 'Update Entry' : 'Save Entry' }}
                    </button>
                </div>
            </form>
        </div>

        {{-- ── PREVIOUS ENTRIES ── --}}
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-800 mb-4">📋 Previous Entries</h2>

            @forelse ($journals as $journal)
                <div class="bg-gray-50 rounded-xl border border-gray-100 p-5 mb-4 hover:shadow-md transition">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-purple-600">
                            {{ $journal->entry_date->format('l, M d, Y') }}
                        </span>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('journal.show', $journal->id) }}"
                                class="text-sm text-blue-600 hover:underline">View</a>
                            <form action="{{ route('journal.destroy', $journal->id) }}" method="POST"
                                onsubmit="return confirm('Delete this entry?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-red-500 hover:underline">Delete</button>
                            </form>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm line-clamp-3">{{ $journal->content }}</p>
                </div>
            @empty
                <div class="bg-gray-50 rounded-xl p-8 text-center text-gray-400 border border-gray-100">
                    <p class="text-lg">No journal entries yet.</p>
                    <p class="text-sm mt-1">Start writing above to track your feelings over time.</p>
                </div>
            @endforelse

            <div class="mt-6">{{ $journals->links() }}</div>
        </div>
    </div>

    {{-- Chart.js for Risk Level trend --}}
    @if (isset($riskProfile) && $riskProfile && $trendData['has_data'] && count($trendData['scores']) >= 2)
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                // ── Risk Level Trend Line Chart ────────────────────────────
                const ctx = document.getElementById('lriTrendChart').getContext('2d');

                const labels = @json($trendData['labels']);
                const levels = @json($trendData['levels']);

                // Map risk level strings to numeric values
                const levelMap = { 'Low': 1, 'Moderate': 2, 'High': 3 };
                const levelColors = { 'Low': '#22c55e', 'Moderate': '#eab308', 'High': '#ef4444' };

                const levelValues = levels.map(l => levelMap[l] ?? 1);
                const pointColors = levels.map(l => levelColors[l] ?? '#6b7280');

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Risk Level',
                            data: levelValues,
                            borderColor: '#7c3aed',
                            backgroundColor: 'rgba(124,58,237,0.07)',
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: pointColors,
                            pointBorderColor: pointColors,
                            pointRadius: 7,
                            pointHoverRadius: 9,
                            borderWidth: 2.5,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(ctx) {
                                        const names = ['', 'Low', 'Moderate', 'High'];
                                        return ' Risk Level: ' + (names[ctx.raw] ?? ctx.raw);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                min: 0.5,
                                max: 3.5,
                                ticks: {
                                    stepSize: 1,
                                    callback: function(val) {
                                        return ['', 'Low', 'Moderate', 'High'][val] ?? '';
                                    }
                                },
                                title: { display: true, text: 'Risk Level' },
                                grid: { color: 'rgba(0,0,0,0.05)' }
                            },
                            x: {
                                title: { display: true, text: 'Rolling Week' },
                                grid: { display: false }
                            }
                        }
                    }
                });
            });
        </script>
    @endif
</x-app-layout>
