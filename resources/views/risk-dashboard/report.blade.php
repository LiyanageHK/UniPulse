<x-app-layout title="Weekly Report - UniPulse">
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Weekly Risk Report</h1>
            <a href="{{ route('risk-dashboard.history') }}"
               class="bg-gray-600 hover:bg-gray-700 text-white font-semibold px-5 py-2 rounded-lg transition text-sm">
                &larr; Back to History
            </a>
        </div>

        @php
            $riskColor = match ($report->risk_level) {
                'Low'      => 'green',
                'Moderate' => 'yellow',
                'High'     => 'red',
                default    => 'gray',
            };
            $badgeClass = match ($riskColor) {
                'green'  => 'bg-green-100 text-green-800 border-green-300',
                'yellow' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                'red'    => 'bg-red-100 text-red-800 border-red-300',
                default  => 'bg-gray-100 text-gray-600 border-gray-300',
            };
        @endphp

        {{-- Report Header --}}
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- Week Period --}}
                <div>
                    <p class="text-sm text-gray-500 uppercase tracking-wide mb-1">Week Period</p>
                    <p class="text-lg font-semibold text-gray-800">
                        Week #{{ $report->week_index }} &mdash;
                        {{ $report->week_start->format('M d') }} &mdash; {{ $report->week_end->format('M d, Y') }}
                    </p>
                    <p class="text-xs text-gray-400 mt-1">Generated: {{ $report->created_at->format('M d, Y H:i') }}</p>
                </div>

                {{-- LRI Score --}}
                <div class="text-center">
                    <p class="text-sm text-gray-500 uppercase tracking-wide mb-1">LRI Score</p>
                    <p class="text-4xl font-extrabold"
                       style="color: {{ $riskColor === 'yellow' ? '#ca8a04' : $riskColor }}">
                        {{ round($report->lri_score, 2) }}
                    </p>
                </div>

                {{-- Risk Level --}}
                <div class="text-center">
                    <p class="text-sm text-gray-500 uppercase tracking-wide mb-2">Risk Level</p>
                    <span class="inline-block px-5 py-2 rounded-full text-lg font-bold border {{ $badgeClass }}">
                        {{ $report->risk_level }}
                    </span>
                    @if ($report->escalation_flag)
                        <p class="mt-2">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-600 text-white">
                                &#9888; Escalation Flagged
                            </span>
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Factor Breakdown --}}
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Linguistic Factor Breakdown</h2>
            <div class="space-y-4">
                @php
                    $factors = [
                        ['label' => 'Stress Probability', 'value' => $report->stress_score,    'weight' => 0.25, 'color' => 'purple'],
                        ['label' => 'Sentiment Score',    'value' => $report->sentiment_score, 'weight' => 0.25, 'color' => 'blue'],
                        ['label' => 'Pronoun Ratio',      'value' => $report->pronoun_ratio,   'weight' => 0.25, 'color' => 'indigo'],
                        ['label' => 'Absolutist Score',   'value' => $report->absolutist_score,'weight' => 0.25, 'color' => 'amber'],
                    ];
                @endphp

                @foreach ($factors as $f)
                    <div class="flex items-center gap-4">
                        <div class="w-40 text-sm text-gray-600">{{ $f['label'] }}</div>
                        <div class="flex-1">
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-{{ $f['color'] }}-500 h-3 rounded-full transition-all duration-500"
                                     style="width: {{ min($f['value'] * 100, 100) }}%"></div>
                            </div>
                        </div>
                        <div class="w-20 text-right">
                            <span class="text-sm font-bold text-{{ $f['color'] }}-600">
                                {{ number_format($f['value'], 4) }}
                            </span>
                        </div>
                        <div class="w-24 text-right text-xs text-gray-400">
                            Weight: {{ ($f['weight'] * 100) }}%
                            <br>
                            Contribution: {{ number_format($f['value'] * $f['weight'] * 100, 2) }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Summary Text --}}
        @if ($report->summary_text)
            <div class="bg-white rounded-2xl shadow-lg p-6 mb-8">
                <h2 class="text-xl font-semibold text-gray-700 mb-4">Analyzed Journal Text</h2>
                <div class="prose max-w-none text-gray-600 text-sm whitespace-pre-line bg-gray-50 rounded-xl p-4">{{ $report->summary_text }}</div>
            </div>
        @endif

        {{-- Risk Interpretation --}}
        <div class="bg-gray-50 rounded-2xl p-6">
            <h3 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Risk Interpretation</h3>
            <p class="text-gray-700">{{ $report->risk_message }}</p>
            @if ($report->escalation_flag)
                <p class="text-red-600 text-sm mt-2 font-medium">
                    This week was flagged for escalation due to a sustained upward LRI trend across consecutive weeks.
                </p>
            @endif
        </div>
    </div>
</x-app-layout>
