<x-app-layout title="Suggestions - UniPulse">
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        @if ($survey_count >= 5)
            {{-- Page Header --}}
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Your Personalized Suggestions</h1>
                <p class="text-gray-600">Based on your wellbeing assessment, here are our recommendations prioritized by urgency.</p>
            </div>

            {{-- Suggestions Cards --}}
            <div class="space-y-6">
                @foreach ($report as $item)
                    @php
                        $priorityColors = [
                            1 => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'badge' => 'bg-red-100 text-red-800'],
                            2 => ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'badge' => 'bg-orange-100 text-orange-800'],
                            3 => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'badge' => 'bg-yellow-100 text-yellow-800'],
                            4 => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'badge' => 'bg-blue-100 text-blue-800'],
                            5 => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'badge' => 'bg-green-100 text-green-800'],
                        ];
                        $colors = $priorityColors[$item['suggestion_priority']] ?? $priorityColors[3];

                        $areaIcons = [
                            'depression' => '🌧️',
                            'stress' => '😟',
                            'social_isolation' => '🔥',
                            'disengagement' => '🫂',
                            'openness' => '💬',
                        ];
                        $icon = $areaIcons[$item['area']] ?? '📊';
                    @endphp

                    <div class="bg-white rounded-xl shadow-md border-2 {{ $colors['border'] }} overflow-hidden hover:shadow-lg transition-shadow">
                        <div class="{{ $colors['bg'] }} px-6 py-4 border-b {{ $colors['border'] }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-3xl">{{ $icon }}</span>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900">
                                            {{ ucfirst(str_replace('_', ' ', $item['area'])) }}
                                        </h3>
                                        <span class="inline-block {{ $colors['badge'] }} text-xs font-semibold px-3 py-1 rounded-full mt-1">
                                            Priority {{ $item['suggestion_priority'] }}
                                        </span>
                                    </div>
                                </div>
                                @if ($item['risk_level'] == 'High')
                                    <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                                        HIGH RISK
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="p-6">
                            <p class="text-gray-700 leading-relaxed mb-4">
                                {{ $item['suggestion'] }}
                            </p>

                            @if ($item['risk_level'] == 'High')
                                <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                                    <span class="text-sm font-medium text-gray-600">Recommended Action:</span>
                                    @switch($item['area'])
                                        @case('depression')
                                            <a href=""
                                                class="inline-flex items-center gap-2 bg-purple-600 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-purple-700 transition-colors shadow-sm">
                                                💬 Open Conversational AI
                                            </a>
                                            @break

                                        @case('social_isolation')
                                            <a href="{{ route('peer-matchings') }}"
                                                class="inline-flex items-center gap-2 bg-purple-600 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-purple-700 transition-colors shadow-sm">
                                                🤝 Find Peer Support
                                            </a>
                                            @break

                                        @case('stress')
                                            <a href="#"
                                                class="inline-flex items-center gap-2 bg-purple-600 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-purple-700 transition-colors shadow-sm">
                                                🎥 Watch Relaxation Videos
                                            </a>
                                            @break

                                        @case('openness')
                                            <a href="{{ route('chat.view') }}"
                                                class="inline-flex items-center gap-2 bg-purple-600 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-purple-700 transition-colors shadow-sm">
                                                💬 Talk to Someone
                                            </a>
                                            @break

                                        @default
                                            <a href="#"
                                                class="inline-flex items-center gap-2 bg-purple-600 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-purple-700 transition-colors shadow-sm">
                                                ℹ️ Learn More
                                            </a>
                                    @endswitch
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Additional Help Section --}}
            {{-- <div class="mt-8 bg-gradient-to-r from-purple-50 to-blue-50 rounded-xl shadow-md p-6 border border-purple-200">
                <h3 class="text-lg font-bold text-gray-900 mb-2">Need Additional Support?</h3>
                <p class="text-gray-700 mb-4">If you're experiencing any difficulties or need immediate help, don't hesitate to reach out to our support team.</p>
                <div class="flex gap-3">
                    <a href="#" class="bg-purple-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-purple-700 transition-colors shadow-sm">
                        Contact Support
                    </a>
                    <a href="#" class="bg-white text-purple-600 border-2 border-purple-600 px-6 py-2.5 rounded-lg font-semibold hover:bg-purple-50 transition-colors">
                        View Resources
                    </a>
                </div>
            </div> --}}

        @else
            {{-- No Data State --}}
            <div class="max-w-md mx-auto text-center py-16">
                <div class="bg-white rounded-xl shadow-md p-8">
                    <div class="text-6xl mb-4">📊</div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Not Enough Data</h3>
                    <p class="text-gray-600 mb-6">We need at least 5 survey responses to generate personalized suggestions for you. Complete more check-ins to unlock your wellbeing insights.</p>
                    <div class="space-y-3">
                        <a href="#" class="block text-purple-600 font-medium hover:text-purple-700 transition-colors">
                            Get in touch with us →
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
</x-app-layout>
