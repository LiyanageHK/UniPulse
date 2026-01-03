<x-app-layout>
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

            <!-- Info Sections -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-blue-50 rounded-lg p-6">
                    <h3 class="font-semibold mb-2">About this Page</h3>
                    <p class="text-sm text-gray-700">This page summarizes your wellbeing across 5 categories, showing your
                        current scores and trends. It helps you understand your emotional, social, and mental health
                        patterns over the past weeks.</p>
                </div>

                <div class="bg-green-50 rounded-lg p-6">
                    <h3 class="font-semibold mb-2">Privacy Notice</h3>
                    <p class="text-sm text-gray-700">All your responses are kept completely private and are not shared. The
                        scores and trends are generated anonymously to ensure your confidentiality.</p>
                </div>
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
                                $risk_level =
                                    $item['weighted_score'] < 2
                                        ? 'Low'
                                        : ($item['weighted_score'] < 3.5
                                            ? 'Moderate'
                                            : 'High');
                            @endphp

                            <div onclick="openChartModal('{{ $item['area'] }}')"
                                class="border rounded-lg p-6 hover:shadow-lg transition-shadow cursor-pointer hover:border-blue-500">
                                @switch($item['area'])
                                    @case('depression')
                                        <div class="text-4xl mb-3">🌧️</div>
                                        <h3 class="font-semibold text-lg mb-2">Mood</h3>
                                    @break

                                    @case('stress')
                                        <div class="text-4xl mb-3">😟</div>
                                        <h3 class="font-semibold text-lg mb-2">Emotional Stress</h3>
                                    @break

                                    @case('social_isolation')
                                        <div class="text-4xl mb-3">🔥</div>
                                        <h3 class="font-semibold text-lg mb-2">Burnout & Fatigue</h3>
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
                                                    <h2 class="text-3xl font-bold mb-2">Mood Analysis</h2>
                                                    <p>This measures feelings of sadness or low motivation. Trend: Stable</p>
                                                @break

                                                @case('stress')
                                                    <div class="text-5xl mb-3">😟</div>
                                                    <h2 class="text-3xl font-bold mb-2">Emotional Stress Analysis</h2>
                                                    <p>This measures how overwhelmed or tense you feel. Trend: Worsening</p>
                                                @break

                                                @case('social_isolation')
                                                    <div class="text-5xl mb-3">🔥</div>
                                                    <h2 class="text-3xl font-bold mb-2">Burnout & Fatigue Analysis</h2>
                                                    <p>This measures fatigue or disengagement from studies. Trend: Worsening</p>
                                                @break

                                                @case('disengagement')
                                                    <div class="text-5xl mb-3">🫂</div>
                                                    <h2 class="text-3xl font-bold mb-2">Disengagement Analysis</h2>
                                                    <p>This measures how connected or isolated you feel socially. Trend: Improving</p>
                                                @break

                                                @case('openness')
                                                    <div class="text-5xl mb-3">💬</div>
                                                    <h2 class="text-3xl font-bold mb-2">Openness to Support Analysis</h2>
                                                    <p>This measures your openness to seeking help when needed. Trend: Stable</p>
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

            <div class="text-center">
                <a href="{{ route('suggestions') }}"
                    class="bg-purple-600 text-white px-6 py-2 rounded-lg hover:bg-purple-700 transition my-6" style="background-color: blue">
                    View Suggestions
                </a>
            </div>
        </div>
    </div>

    @if ($survey_count >= 5)
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
        </script>
    @endif
</x-app-layout>
