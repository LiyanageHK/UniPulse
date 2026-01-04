<x-app-layout>
    @if ($survey_count >= 4)

        <a href="{{ route('suggestions') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-purple-700 transition">See Suggestions</a>

        <div class="bg-white rounded-xl shadow-md p-6 mb-8 mt-8">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Weekly Wellbeing Report</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="px-4 py-2 font-semibold text-gray-700">Area</th>
                            <th class="px-4 py-2 font-semibold text-gray-700">Weekly Scores</th>
                            <th class="px-4 py-2 font-semibold text-gray-700">Weighted Score</th>
                            <th class="px-4 py-2 font-semibold text-gray-700">Risk Level</th>
                            <th class="px-4 py-2 font-semibold text-gray-700">Trend</th>
                            {{-- <th class="px-4 py-2 font-semibold text-gray-700">Suggestion Priority</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($report as $item)
                            <tr class="border-t">
                                <td class="px-4 py-2 font-medium text-gray-800">
                                    {{ ucfirst(str_replace('_', ' ', $item['area'])) }}</td>
                                <td class="px-4 py-2 text-gray-700">
                                    {{ implode(', ', array_map(fn($s) => number_format($s, 1), $item['weekly_scores'])) }}
                                </td>
                                <td class="px-4 py-2 font-semibold text-gray-900">
                                    {{ number_format($item['weighted_score'], 1) }}</td>
                                <td class="px-4 py-2">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs font-semibold
                                        {{ $item['risk_level'] == 'High' ? 'bg-red-200 text-red-800' : ($item['risk_level'] == 'Moderate' ? 'bg-yellow-200 text-yellow-800' : 'bg-green-200 text-green-800') }}">
                                        {{ $item['risk_level'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">{{ $item['trend'] }}</td>
                                {{-- <td class="px-4 py-2">{{ $item['suggestion_priority'] }}</td> --}}
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bg-gray-50 rounded-lg p-6 text-center">
            <h3 class="text-xl font-semibold mb-2">No Enough Data</h3>
            <p class="text-gray-600 mb-4">Get touch with us</p>
        </div>
    @endif
    </x-app-layout>
