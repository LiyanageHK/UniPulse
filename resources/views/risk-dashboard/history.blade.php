<x-app-layout title="Risk History - UniPulse">
    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-800">Risk History</h1>
            <a href="{{ route('risk-dashboard.index') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-lg transition text-sm">
                &larr; Back to Dashboard
            </a>
        </div>

        @if ($history->isEmpty())
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <div class="text-6xl mb-4">&#128202;</div>
                <h2 class="text-2xl font-bold text-gray-700 mb-2">No History Yet</h2>
                <p class="text-gray-500 mb-6">
                    Weekly reports are generated from your journal entries. Start journaling to build your risk history.
                </p>
                <a href="{{ route('journal.index') }}"
                   class="inline-block bg-purple-600 hover:bg-purple-700 text-white font-semibold px-8 py-3 rounded-lg transition">
                    Start Journaling
                </a>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3">Week</th>
                            <th class="px-6 py-3 text-center">LRI Score</th>
                            <th class="px-6 py-3 text-center">Risk Level</th>
                            <th class="px-6 py-3 text-center">Escalation</th>
                            <th class="px-6 py-3 text-center">Stress</th>
                            <th class="px-6 py-3 text-center">Sentiment</th>
                            <th class="px-6 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $index => $summary)
                            @php
                                $riskColor = match ($summary->risk_level) {
                                    'Low'      => 'green',
                                    'Medium'   => 'yellow',
                                    'High'     => 'orange',
                                    'Critical' => 'red',
                                    default    => 'gray',
                                };
                                $pillClass = match ($riskColor) {
                                    'green'  => 'bg-green-100 text-green-800',
                                    'yellow' => 'bg-yellow-100 text-yellow-800',
                                    'orange' => 'bg-orange-100 text-orange-800',
                                    'red'    => 'bg-red-100 text-red-800',
                                    default  => 'bg-gray-100 text-gray-600',
                                };

                                // Trend vs next row (previous week)
                                $nextSummary = $history[$index + 1] ?? null;
                                $trendSymbol = '&mdash;';
                                if ($nextSummary) {
                                    $delta = $summary->lri_score - $nextSummary->lri_score;
                                    if ($delta > 2) $trendSymbol = '<span class="text-red-600 text-lg">&#8593;</span>';
                                    elseif ($delta < -2) $trendSymbol = '<span class="text-green-600 text-lg">&#8595;</span>';
                                    else $trendSymbol = '<span class="text-blue-600 text-lg">&#8594;</span>';
                                }
                            @endphp
                            <tr class="border-b hover:bg-gray-50 transition {{ $index === 0 ? 'bg-indigo-50' : '' }}">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">
                                        Week #{{ $summary->week_index }} &mdash;
                                        {{ $summary->week_start->format('M d') }} &mdash; {{ $summary->week_end->format('M d, Y') }}
                                    </div>
                                    @if ($index === 0)
                                        <span class="text-xs text-indigo-600 font-semibold">Current</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-lg font-bold" style="color: {{ $riskColor === 'yellow' ? '#ca8a04' : $riskColor }}">
                                        {{ round($summary->lri_score, 2) }}
                                    </span>
                                    <span class="block text-xs text-gray-400">{!! $trendSymbol !!}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $pillClass }}">
                                        {{ $summary->risk_level }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($summary->escalation_flag)
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                            &#9888; Yes
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">No</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-gray-600">{{ number_format($summary->stress_score, 3) }}</td>
                                <td class="px-6 py-4 text-center text-gray-600">{{ number_format($summary->sentiment_score, 3) }}</td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('risk-dashboard.report', $summary->id) }}"
                                       class="text-indigo-600 hover:underline text-sm font-medium">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $history->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
