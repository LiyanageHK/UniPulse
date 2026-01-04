<x-app-layout title="Weekly Check-Ins - UniPulse">
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <div class="container mx-auto">

            <h2 class="text-2xl font-semibold text-gray-800 mb-6">My Weekly Check-Ins</h2>
            @if (!$hasSubmittedWeeklyCheck)
                <div class="my-5 text-right">
                    {{-- <a href="{{ route('survey') }}"
                        class="bg-purple-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-purple-700 transition">
                        Take Survey
                    </a> --}}
            @endif
        </div>
        @if ($checkings->isEmpty())
            {{-- <div class="bg-blue-100 text-blue-700 p-4 rounded-lg">
                You have not submitted any weekly check-ins yet.
            </div> --}}
        @else
            <!-- Card wrapper -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-700">Weekly Check-Ins</h3>
                    <hr class="my-5">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">
                        <form action="{{ route('weekly-checkings') }}" method="GET" class="flex gap-10 flex-wrap">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Week</label>
                                <input type="number" name="week" min="1" max="53" value="{{ request('week') }}"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">From Date</label>
                                <input type="date" name="from_date" value="{{ request('from_date') }}"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">To Date</label>
                                <input type="date" name="to_date" value="{{ request('to_date') }}"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 sm:text-sm">
                            </div>

                            <div class="self-end">
                                <button type="submit"
                                    class="bg-purple-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-purple-700 transition">
                                    Filter
                                </button>
                            </div>
                        </form>

                        @if (request()->hasAny(['week', 'from_date', 'to_date']))
                            <a href="{{ route('weekly-checkings') }}" class="text-purple-600 hover:underline text-sm">Reset
                                Filter</a>
                        @endif
                    </div>
                </div>



                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Week</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Overall Mood</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Felt Supported</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Filled Date</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($checkings as $key => $check)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">Week
                                        {{ count($checkings) - $key }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-3 py-1 rounded-full
                                                {{ $check->overall_mood >= 4 ? 'bg-green-100 text-green-800' : ($check->overall_mood == 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                            @switch($check->overall_mood)
                                                @case(1)
                                                    Very Negative
                                                @break

                                                @case(2)
                                                    Negative
                                                @break

                                                @case(3)
                                                    Normal
                                                @break

                                                @case(4)
                                                    Positive
                                                @break

                                                @case(5)
                                                    Very positive
                                                @break

                                                @default
                                            @endswitch
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @switch($check->felt_supported)
                                            @case(1)
                                                Very Negative
                                            @break

                                            @case(2)
                                                Negative
                                            @break

                                            @case(3)
                                                Normal
                                            @break

                                            @case(4)
                                                Positive
                                            @break

                                            @case(5)
                                                Very positive
                                            @break


                                            @default
                                        @endswitch
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $check->created_at->format('d M Y') }}</td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap text-sm text-gray-700">
                                        <a href="{{ route('weekly-checkings.view', $check->id) }}"
                                            class="text-white px-4 py-2 rounded-lg font-semibold hover:bg-purple-700 transition" style ="background-color:rgb(76, 76, 255);">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
        </div>
    </div>
</div>
</x-app-layout>
