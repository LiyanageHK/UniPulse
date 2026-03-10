<x-app-layout title="Risk Detection - UniPulse">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-3xl font-bold mb-2">Your Wellbeing Report</h1>
            <p class="text-gray-600 mb-8">Here is your weekly risk overview with charts and insights.</p>
            <div class="mb-8">
                <a href="{{ route('weekly-checkings') }}"
                    class="text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition " style="background-color: blue">
                    Weekly Checkins
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Check-Ins Completed</h3>
                    <p class="text-3xl font-bold">{{ $checkins_count ?? 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">Out of 12 weeks</p>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Current Mood</h3>
                    <p class="text-3xl font-bold">{{ number_format($current_mood ?? 0, 1) }}</p>
                    <p class="text-sm {{ $mood_change >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                        {{ $mood_change >= 0 ? '↑' : '↓' }} {{ abs($mood_change) ?? 0 }} from last week
                    </p>
                </div>

                {{-- <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Peer Connections</h3>
                    <p class="text-3xl font-bold">{{ $peer_connections ?? 0 }}</p>
                    <p class="text-sm text-gray-500 mt-1">Active connections</p>
                </div> --}}
            </div>

            <!-- Overall Score -->
            {{-- <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h2 class="text-2xl font-bold mb-4">Overall Score</h2>
                <p class="text-4xl font-bold text-blue-600">3.0</p>
            </div> --}}

            <!-- Risk Summary -->
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h2 class="text-2xl font-bold mb-4">Risk Summary</h2>
                <p class="text-gray-600 mb-6">Here's your wellbeing overview for this week</p>

                @if ($survey_count >= 5)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach ($report as $item)
                            @php
                                $score_range = implode(
                                    ', ',
                                    array_map(fn($s) => number_format($s, 1), $item['weekly_scores']),
                                );
                                $risk_level = $item['risk_level'];
                                    // $item['weighted_score'] < 2
                                    //     ? 'Low'
                                    //     : ($item['weighted_score'] < 3.5
                                    //         ? 'Moderate'
                                    //         : 'High');
                            @endphp

                            <div onclick="openChartModal('{{ $item['area'] }}')"
                                class="border rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer hover:border-blue-500">
                                @switch($item['area'])
                                    @case('depression')
                                        <div class="text-4xl mb-3">🌧️</div>
                                        <h3 class="font-semibold text-lg mb-2">Depression</h3>
                                    @break

                                    @case('stress')
                                        <div class="text-4xl mb-3">😟</div>
                                        <h3 class="font-semibold text-lg mb-2">Emotional Stress</h3>
                                    @break

                                    @case('social_isolation')
                                        <div class="text-4xl mb-3">🔥</div>
                                        <h3 class="font-semibold text-lg mb-2">Social Isolation</h3>
                                    @break

                                    @case('disengagement')
                                        <div class="text-4xl mb-3">🫂</div>
                                        <h3 class="font-semibold text-lg mb-2">Disengagement</h3>
                                    @break

                                    @case('openness')
                                        <div class="text-4xl mb-3">💬</div>
                                        <h3 class="font-semibold text-lg mb-2">Openness to Support</h3>
                                    @break
                                @endswitch

                                <p class="text-2xl font-bold mb-1">{{ number_format($item['weighted_score'], 1) }}</p>
                                <p class="text-sm text-gray-600 mb-3">Risk: {{ $risk_level }}</p>
                                <p class="text-sm font-medium mb-4">Trend: {{ $item['trend'] }}</p>

                                <p class="text-xs text-blue-600 font-medium">Click to view detailed chart →</p>
                            </div>

                            <!-- Individual Modal for Each Chart -->
                            <div id="modal-{{ $item['area'] }}"
                                class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 overflow-y-auto">
                                <div class="flex items-center justify-center min-h-screen p-4">
                                    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full relative p-8">
                                        <button
                                            onclick="closeChartModal('{{ $item['area'] }}')"
                                            style="
                                                position: absolute;
                                                top: 16px;
                                                right: 16px;
                                                z-index: 9999;
                                                font-size: 32px;
                                                font-weight: bold;
                                                color: #6b7280;
                                            ">
                                            &times;
                                        </button>

                                        <div class="mb-6">
                                            @switch($item['area'])
                                                @case('depression')
                                                    <div class="text-5xl mb-3">🌧️</div>
                                                    <h2 class="text-3xl font-bold mb-2">Depression Analysis</h2>
                                                    <p class="text-gray-500">This measures feelings of sadness, hopelessness, or low motivation over recent weeks.</p>
                                                @break

                                                @case('stress')
                                                    <div class="text-5xl mb-3">😟</div>
                                                    <h2 class="text-3xl font-bold mb-2">Emotional Stress Analysis</h2>
                                                    <p class="text-gray-500">This measures how overwhelmed or emotionally tense you have been feeling.</p>
                                                @break

                                                @case('social_isolation')
                                                    <div class="text-5xl mb-3">🔥</div>
                                                    <h2 class="text-3xl font-bold mb-2">Social Isolation Analysis</h2>
                                                    <p class="text-gray-500">This measures your social connections and how isolated or included you feel around others.</p>
                                                @break

                                                @case('disengagement')
                                                    <div class="text-5xl mb-3">🫂</div>
                                                    <h2 class="text-3xl font-bold mb-2">Disengagement Analysis</h2>
                                                    <p class="text-gray-500">This measures your level of academic engagement, motivation, and study participation.</p>
                                                @break

                                                @case('openness')
                                                    <div class="text-5xl mb-3">💬</div>
                                                    <h2 class="text-3xl font-bold mb-2">Openness to Support Analysis</h2>
                                                    <p class="text-gray-500">This measures your willingness to seek or accept help and support when needed.</p>
                                                @break
                                            @endswitch

                                            <div class="flex items-center gap-6 mt-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">Current Score</p>
                                                    <p class="text-3xl font-bold">
                                                        {{ number_format($item['weighted_score'], 1) }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-gray-500">Risk Level</p>
                                                    <p class="text-xl font-semibold">{{ $risk_level }}</p>
                                                </div>
                                                <div>
                                                    <p class="text-sm text-gray-500">Trend</p>
                                                    <p class="text-xl font-semibold">{{ $item['trend'] }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="bg-gray-50 rounded-lg p-6">
                                            <h3 class="font-semibold mb-4">Weekly Trend</h3>
                                            <div style="height: 300px;">
                                                <canvas id="modal-chart-{{ $item['area'] }}"></canvas>
                                            </div>
                                        </div>

                                        <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                                            @foreach ($item['weekly_scores'] as $index => $score)
                                                <div class="bg-white border rounded p-3 text-center">
                                                    <p class="text-xs text-gray-500">Week {{ $index + 1 }}</p>
                                                    <p class="text-xl font-bold">{{ number_format($score, 1) }}</p>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12">
                        <p class="text-xl font-semibold mb-2">Not Enough Data</p>
                        <p class="text-gray-600 mb-4">Complete more check-ins to see your wellbeing trends</p>
                        <a href="#"
                            class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
                            Get in touch with us
                        </a>
                    </div>
                @endif
            </div>

            {{-- ═══════════════════════════════════════
                 JOURNAL-BASED LINGUISTIC RISK ANALYSIS
                 ═══════════════════════════════════════ --}}
            @if ($journalRisk)
                <div class="bg-white rounded-lg shadow p-6 mb-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                                📓 Journal-Based Risk Analysis
                                @if ($journalRisk['escalation_flag'])
                                    <span class="ml-2 inline-block px-3 py-1 text-xs font-bold rounded-full bg-red-600 text-white animate-pulse">
                                        ⚠ Escalating Risk
                                    </span>
                                @endif
                            </h2>
                            <p class="text-sm text-gray-500 mt-1">
                                NLP analysis of your journal entries &mdash;
                                Week #{{ $journalRisk['week_index'] }}:
                                {{ $journalRisk['week_start'] }} to {{ $journalRisk['week_end'] }}
                            </p>
                        </div>
                        <div class="flex gap-3 mt-4 md:mt-0">
                            <a href="{{ route('journal.index') }}"
                               class="text-sm px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                ✏ Write Journal
                            </a>
                            <a href="{{ route('risk-dashboard.index') }}"
                               class="text-sm px-4 py-2 border border-blue-600 text-blue-600 rounded-lg hover:bg-blue-50 transition">
                                Full Dashboard →
                            </a>
                        </div>
                    </div>

                    {{-- LRI Score + Risk Level + Interpretation --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="bg-gray-50 rounded-xl p-5 text-center">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">Linguistic Risk Index</p>
                            @php
                                $lriColor = match(true) {
                                    $journalRisk['lri_score'] >= 80 => '#ef4444',
                                    $journalRisk['lri_score'] >= 60 => '#f97316',
                                    $journalRisk['lri_score'] >= 30 => '#ca8a04',
                                    default => '#16a34a',
                                };
                            @endphp
                            <p class="text-5xl font-extrabold" style="color: {{ $lriColor }}">
                                {{ $journalRisk['lri_score'] }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1">out of 100</p>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-5 text-center flex flex-col justify-center">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Risk Level</p>
                            @php
                                $badgeCls = match($journalRisk['risk_color']) {
                                    'green'  => 'bg-green-100 text-green-800 border-green-200',
                                    'yellow' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                    'orange' => 'bg-orange-100 text-orange-800 border-orange-200',
                                    'red'    => 'bg-red-100 text-red-800 border-red-200',
                                    default  => 'bg-gray-100 text-gray-600 border-gray-200',
                                };
                            @endphp
                            <span class="inline-block px-5 py-2 rounded-full text-lg font-bold border {{ $badgeCls }}">
                                {{ $journalRisk['risk_level'] }} Risk
                            </span>
                        </div>

                        <div class="bg-gray-50 rounded-xl p-5 flex flex-col justify-center">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-2">Interpretation</p>
                            <p class="text-base font-medium text-gray-700">{{ $journalRisk['risk_message'] }}</p>
                            @if ($journalRisk['summary_text'])
                                <p class="text-xs text-gray-400 mt-2 italic line-clamp-2">
                                    "{{ \Illuminate\Support\Str::limit($journalRisk['summary_text'], 130) }}"
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Linguistic Factor Breakdown --}}
                    <div class="mb-6">
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">Linguistic Factor Breakdown</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            @php
                                $jFactors = [
                                    ['label' => 'Stress Probability',  'key' => 'stress_score',      'weight' => '25%', 'color' => 'purple'],
                                    ['label' => 'Sentiment Score',     'key' => 'sentiment_score',   'weight' => '25%', 'color' => 'blue'],
                                    ['label' => 'Pronoun Ratio',       'key' => 'pronoun_ratio',     'weight' => '25%', 'color' => 'indigo'],
                                    ['label' => 'Absolutist Language', 'key' => 'absolutist_score',  'weight' => '25%', 'color' => 'amber'],
                                ];
                            @endphp
                            @foreach ($jFactors as $f)
                                <div class="bg-white border border-gray-100 rounded-xl p-4 text-center shadow-sm">
                                    <p class="text-xs text-gray-500 mb-1">{{ $f['label'] }}</p>
                                    <p class="text-xl font-bold text-{{ $f['color'] }}-600">
                                        {{ number_format($journalRisk[$f['key']], 4) }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-1">Weight: {{ $f['weight'] }}</p>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-2">
                                        <div class="bg-{{ $f['color'] }}-500 h-1.5 rounded-full"
                                             style="width: {{ min($journalRisk[$f['key']] * 100, 100) }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if (count($journalTrend) > 1)
                        {{-- LRI Weekly Trend Chart --}}
                        <div class="bg-gray-50 rounded-xl p-5 mb-4">
                            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">
                                LRI Trend — Last {{ count($journalTrend) }} Weeks
                            </h3>
                            <canvas id="lriTrendChart" height="100"></canvas>
                        </div>
                    @endif

                    {{-- LRI Formula Reference --}}
                    <div class="text-xs text-gray-400 font-mono bg-gray-50 rounded-lg p-3 mt-2">
                        LRI = (Stress + Sentiment + Pronoun + Absolutist) ÷ 4
                        &nbsp;&nbsp;
                        <span class="inline-block w-2 h-2 rounded-full bg-green-400 mr-0.5"></span>Low &lt;0.3
                        <span class="inline-block w-2 h-2 rounded-full bg-yellow-400 mr-0.5 ml-2"></span>Moderate 0.3–0.6
                        <span class="inline-block w-2 h-2 rounded-full bg-red-500 mr-0.5 ml-2"></span>High &ge;0.6
                    </div>
                </div>
            @else
                <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-6 mb-8 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-indigo-800">📓 Journal-Based Risk Analysis</h3>
                        <p class="text-sm text-indigo-700 mt-1">
                            Write daily journal entries to unlock AI-powered linguistic risk analysis. Your entries are
                            analyzed for stress patterns, sentiment, and withdrawal indicators.
                        </p>
                    </div>
                    <a href="{{ route('journal.index') }}"
                       class="shrink-0 bg-indigo-600 text-white text-sm font-semibold px-5 py-2.5 rounded-lg hover:bg-indigo-700 transition">
                        Start Journaling
                    </a>
                </div>
            @endif

            <div class="text-center">
                <a href="{{ route('suggestions') }}"
                    class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition my-6" style="background-color: blue">
                    View Suggestions
                </a>
            </div>
        </div>
    </div>

    @if ($survey_count >= 5 || count($journalTrend) > 1)
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
        <script>
            const modalCharts = {};
            const reportData = @json($report);

            function openChartModal(area) {
                const modal = document.getElementById('modal-' + area);
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                // Initialize chart if not already created
                if (!modalCharts[area]) {
                    setTimeout(() => initializeModalChart(area), 100);
                }
            }

            function closeChartModal(area) {
                const modal = document.getElementById('modal-' + area);
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }

            function initializeModalChart(area) {
                const item = reportData.find(r => r.area === area);
                if (!item) return;

                const ctx = document.getElementById('modal-chart-' + area);
                if (!ctx) return;

                const weekLabels = item.weekly_scores.map((_, index) => `Week ${index + 1}`);

                modalCharts[area] = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: weekLabels,
                        datasets: [{
                            label: 'Score',
                            data: item.weekly_scores,
                            borderColor: item.weighted_score < 2 ? 'rgb(34, 197, 94)' : (item.weighted_score <
                                3.5 ? 'rgb(234, 179, 8)' : 'rgb(239, 68, 68)'),
                            backgroundColor: item.weighted_score < 2 ? 'rgba(34, 197, 94, 0.1)' : (item
                                .weighted_score < 3.5 ? 'rgba(234, 179, 8, 0.1)' : 'rgba(239, 68, 68, 0.1)'
                            ),
                            tension: 0.3,
                            fill: true,
                            pointBackgroundColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: {
                                    size: 14
                                },
                                bodyFont: {
                                    size: 13
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 5,
                                ticks: {
                                    stepSize: 1
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // Close modals when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target.id && e.target.id.startsWith('modal-')) {
                    closeChartModal(e.target.id.replace('modal-', ''));
                }
            });

            // Close modals on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    reportData.forEach(item => {
                        const modal = document.getElementById('modal-' + item.area);
                        if (modal && !modal.classList.contains('hidden')) {
                            closeChartModal(item.area);
                        }
                    });
                }
            });

            // ─── LRI Trend Chart (Journal-Based) ───────────────────
            @if (count($journalTrend) > 1)
            (function () {
                const ctx = document.getElementById('lriTrendChart');
                if (!ctx) return;

                const trendData = @json($journalTrend);
                const labels = trendData.map(d => d.label);
                const scores = trendData.map(d => d.lri_score);

                const pointColors = scores.map(s => {
                    if (s >= 80) return '#ef4444';
                    if (s >= 60) return '#f97316';
                    if (s >= 30) return '#ca8a04';
                    return '#16a34a';
                });

                const riskZonePlugin = {
                    id: 'riskZones',
                    beforeDraw(chart) {
                        const { ctx, chartArea: { left, right }, scales: { y } } = chart;
                        const zones = [
                            { min: 0,  max: 30,  color: 'rgba(34,197,94,0.06)' },
                            { min: 30, max: 60,  color: 'rgba(234,179,8,0.06)' },
                            { min: 60, max: 80,  color: 'rgba(249,115,22,0.06)' },
                            { min: 80, max: 100, color: 'rgba(239,68,68,0.06)' },
                        ];
                        zones.forEach(z => {
                            const yTop    = y.getPixelForValue(z.max);
                            const yBottom = y.getPixelForValue(z.min);
                            ctx.fillStyle = z.color;
                            ctx.fillRect(left, yTop, right - left, yBottom - yTop);
                        });
                    }
                };

                new Chart(ctx, {
                    type: 'line',
                    plugins: [riskZonePlugin],
                    data: {
                        labels,
                        datasets: [{
                            label: 'LRI Score',
                            data: scores,
                            borderColor: '#7c3aed',
                            backgroundColor: 'rgba(124,58,237,0.08)',
                            fill: true,
                            tension: 0.35,
                            pointBackgroundColor: pointColors,
                            pointBorderColor: pointColors,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            borderWidth: 2.5,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ` LRI: ${ctx.parsed.y}`,
                                    afterLabel: ctx => {
                                        const s = ctx.parsed.y;
                                        if (s >= 0.6) return 'Risk: High';
                                        if (s >= 0.3) return 'Risk: Moderate';
                                        return 'Risk: Low';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                min: 0, max: 1.0,
                                ticks: { stepSize: 0.2 },
                                grid: { color: 'rgba(0,0,0,0.04)' }
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });
            })();
            @endif
        </script>
    @endif
</x-app-layout>
