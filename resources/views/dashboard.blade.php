<x-app-layout title="Dashboard - UniPulse">

<div class="max-w-7xl mx-auto py-8 px-4">

    <!-- HEADER -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Dashboard</h1>
        <p class="text-gray-600">Welcome back, <strong>{{ Auth::user()->name }}</strong>!</p>
        
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mt-4">
                {{ session('success') }}
            </div>
        @endif
        
        @if(session('info'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mt-4">
                {{ session('info') }}
            </div>
        @endif

        @if(!empty($isFirstWeek))
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-6 py-6 rounded mt-4">
                <div class="flex items-start gap-4">
                    <div class="text-3xl">🎉</div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-800">Welcome — your dashboard is getting set up</h3>
                        <p class="mt-1 text-gray-600">KPIs and charts will appear after your first weekly check-in on <strong>{{ $kpiAvailableDate }}</strong>.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>


    <!-- KPI CARDS -->
    @if(!empty($isFirstWeek))
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">

        <!-- MOTIVATION PLACEHOLDER -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Motivation KPI</h3>
                <span class="text-3xl">🎯</span>
            </div>
            <div class="mb-4">
                <div class="text-3xl font-medium text-blue-600">Not available yet</div>
                <div class="text-sm text-gray-600 mt-1">KPIs will appear after your first weekly check-in</div>
            </div>
            <div class="mb-4 p-3 rounded-lg bg-blue-50">
                <p class="text-sm text-gray-700">First check-in available from <strong>{{ $kpiAvailableDate }}</strong>.</p>
            </div>
        </div>

        <!-- SOCIAL PLACEHOLDER -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Social Inclusion KPI</h3>
                <span class="text-3xl">👥</span>
            </div>
            <div class="mb-4">
                <div class="text-3xl font-medium text-purple-600">Not available yet</div>
                <div class="text-sm text-gray-600 mt-1">KPIs will appear after your first weekly check-in</div>
            </div>
            <div class="mb-4 p-3 rounded-lg bg-purple-50">
                <p class="text-sm text-gray-700">Try connecting with peers or joining a club to build social connections.</p>
            </div>
        </div>

        <!-- EMOTIONAL PLACEHOLDER -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Emotional Status KPI</h3>
                <span class="text-3xl">💭</span>
            </div>
            <div class="mb-4">
                <div class="text-3xl font-medium text-green-600">Not available yet</div>
                <div class="text-sm text-gray-600 mt-1">KPIs will appear after your first weekly check-in</div>
            </div>
            <div class="mb-4 p-3 rounded-lg bg-green-50">
                <p class="text-sm text-gray-700">If you're feeling anxious, consider exploring our support resources.</p>
            </div>
        </div>

    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">

        <!-- MOTIVATION -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-blue-500 hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Motivation KPI</h3>
                <span class="text-3xl">🎯</span>
            </div>
            <div class="mb-4">
                <div class="text-4xl font-bold text-blue-600">{{ number_format($motivationScore, 2) }}</div>
                <div class="text-sm text-gray-600 mt-1">out of 5.0</div>
            </div>
            <div class="mb-4 p-3 rounded-lg" style="background-color: {{ $motivationInterpretation === 'High' ? '#dcfce7' : ($motivationInterpretation === 'Moderate' ? '#fef3c7' : '#fee2e2') }}">
                <p class="font-semibold" style="color: {{ $motivationInterpretation === 'High' ? '#15803d' : ($motivationInterpretation === 'Moderate' ? '#b45309' : '#991b1b') }}">
                    {{ $motivationInterpretation }} Motivation
                </p>
            </div>
        </div>

        <!-- SOCIAL INCLUSION -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-purple-500 hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Social Inclusion KPI</h3>
                <span class="text-3xl">👥</span>
            </div>
            <div class="mb-4">
                <div class="text-4xl font-bold text-purple-600">{{ number_format($socialScore, 2) }}</div>
                <div class="text-sm text-gray-600 mt-1">out of 5.0</div>
            </div>
            <div class="mb-4 p-3 rounded-lg" style="background-color: {{ $socialInterpretation === 'Integrated' ? '#dcfce7' : ($socialInterpretation === 'Moderate' ? '#fef3c7' : '#fee2e2') }}">
                <p class="font-semibold" style="color: {{ $socialInterpretation === 'Integrated' ? '#15803d' : ($socialInterpretation === 'Moderate' ? '#b45309' : '#991b1b') }}">
                    {{ $socialInterpretation }}
                </p>
            </div>
        </div>

        <!-- EMOTIONAL STATUS -->
        <div class="bg-white rounded-lg shadow-lg p-6 border-l-4 border-green-500 hover:scale-105 transition-transform duration-200">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Emotional Status KPI</h3>
                <span class="text-3xl">💭</span>
            </div>
            <div class="mb-4">
                <div class="text-4xl font-bold text-green-600">{{ number_format($emotionalScore, 2) }}</div>
                <div class="text-sm text-gray-600 mt-1">out of 5.0</div>
            </div>
            <div class="mb-4 p-3 rounded-lg" style="background-color: {{ $emotionalInterpretation === 'Stable' ? '#dcfce7' : ($emotionalInterpretation === 'Moderate' ? '#fef3c7' : '#fee2e2') }}">
                <p class="font-semibold" style="color: {{ $emotionalInterpretation === 'Stable' ? '#15803d' : ($emotionalInterpretation === 'Moderate' ? '#b45309' : '#991b1b') }}">
                    {{ $emotionalInterpretation }}
                </p>
            </div>
        </div>

    </div>
    @endif

    <!-- AI RECOMMENDATION -->
    @if(isset($aiRecommendation))
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Recommended Actions</h2>
            <div class="bg-white p-6 rounded-lg shadow-lg border-l-4 border-yellow-500">
                <p class="mb-2 text-gray-700">
                    @if($aiRecommendation['type'] === 'risk_detection')
                        ⚠️ Your emotional status requires attention. 
                        @if($aiRecommendation['link'] && $aiRecommendation['link'] !== '#')
                            Please check the <a href="{{ $aiRecommendation['link'] }}" class="text-blue-600 font-semibold underline">Risk Detection Component</a>.
                        @else
                            Please check the Risk Detection component (link not available).
                        @endif
                    @elseif($aiRecommendation['type'] === 'encouragement')
                        💡 {{ $aiRecommendation['message'] ?? 'Keep up your good progress! Stay motivated.' }}
                    @elseif($aiRecommendation['type'] === 'conversational_support')
                        🤝 We recommend using Conversation Support to talk through this. 
                        <div class="mt-3">
                            @php
                                $chatLink = ($aiRecommendation['link'] && $aiRecommendation['link'] !== '#') ? $aiRecommendation['link'] : (route('chat.support') . '?tab=active');
                            @endphp
                            <a href="{{ $chatLink }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Open Conversation Support
                            </a>
                        </div>
                    @else
                        💡 Based on your KPIs, we recommend exploring: 
                        @if($aiRecommendation['link'] && $aiRecommendation['link'] !== '#')
                            <a href="{{ $aiRecommendation['link'] }}" class="text-blue-600 font-semibold underline">{{ ucfirst($aiRecommendation['type']) }}</a>.
                        @else
                            {{ ucfirst($aiRecommendation['type']) }} (link not available).
                        @endif
                    @endif
                </p>
            </div>
        </div>
    @endif



    @if($kpiHistory->count() > 0)
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3 mb-12">

        <!-- Motivation Chart -->
        <div class="bg-white p-6 rounded-lg shadow-lg border-t-4 border-blue-500">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Motivation Trend</h3>
            <canvas id="motivationChart" class="w-full h-64"></canvas>
        </div>

        <!-- Social Inclusion Chart -->
        <div class="bg-white p-6 rounded-lg shadow-lg border-t-4 border-purple-500">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Social Inclusion Trend</h3>
            <canvas id="socialChart" class="w-full h-64"></canvas>
        </div>

        <!-- Emotional Status Chart -->
        <div class="bg-white p-6 rounded-lg shadow-lg border-t-4 border-green-500">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Emotional Status Trend</h3>
            <canvas id="emotionalChart" class="w-full h-64"></canvas>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const labels = {!! json_encode($kpiHistory->pluck('week_start')->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))) !!};

        const chartOptions = {
            responsive: true,
            plugins: {
                legend: { position: 'top', labels: { font: { size: 14 } } },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                y: { min: 0, max: 5, ticks: { stepSize: 1 } }
            },
            tension: 0.3
        };

        new Chart(document.getElementById('motivationChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Motivation',
                    data: {!! json_encode($kpiHistory->pluck('motivation_kpi')) !!},
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.3
                }]
            },
            options: chartOptions
        });

        new Chart(document.getElementById('socialChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Social Inclusion',
                    data: {!! json_encode($kpiHistory->pluck('social_kpi')) !!},
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.2)',
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.3
                }]
            },
            options: chartOptions
        });

        new Chart(document.getElementById('emotionalChart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Emotional Status',
                    data: {!! json_encode($kpiHistory->pluck('emotional_kpi')) !!},
                    borderColor: '#16a34a',
                    backgroundColor: 'rgba(22, 163, 74, 0.2)',
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    tension: 0.3
                }]
            },
            options: chartOptions
        });
    </script>
    @else
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3 mb-12">

            <div class="bg-white p-6 rounded-lg shadow-lg border-t-4 border-blue-500 flex flex-col items-start justify-center h-64">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Motivation Trend</h3>
                <div class="flex-1 flex items-center justify-center text-sm text-gray-500">No trend data yet</div>
                <div class="text-sm text-gray-500 mt-4">First check-in available from <strong>{{ $kpiAvailableDate ?? 'next week' }}</strong></div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-lg border-t-4 border-purple-500 flex flex-col items-start justify-center h-64">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Social Inclusion Trend</h3>
                <div class="flex-1 flex items-center justify-center text-sm text-gray-500">No trend data yet</div>
                <div class="text-sm text-gray-500 mt-4">Connect with peers and submit your first check-in.</div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-lg border-t-4 border-green-500 flex flex-col items-start justify-center h-64">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Emotional Status Trend</h3>
                <div class="flex-1 flex items-center justify-center text-sm text-gray-500">No trend data yet</div>
                <div class="text-sm text-gray-500 mt-4">If you need support, visit <a href="{{ route('chat.support') }}" class="text-blue-600 underline">Conversation Support</a>.</div>
            </div>

        </div>
    @endif

    <!-- CONVERSATIONAL SUPPORT & FEEDBACK -->
    <div class="mb-12">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Important Measures</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Active Chats -->
            <!-- Active Chats -->
            <div class="bg-white rounded-lg shadow-lg p-6" style="border-left: 4px solid #6366f1;">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-800">Active Chats</h3>
                    <span class="text-2xl">💬</span>
                </div>
                <div class="text-3xl font-bold text-indigo-600">{{ $activeChatsCount }}</div>
                <div class="text-sm text-gray-600">Current conversations</div>
            </div>

            <!-- Archived Chats -->
            <!-- Archived Chats -->
            <div class="bg-white rounded-lg shadow-lg p-6" style="border-left: 4px solid #22c55e;">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-800">Archived Chats</h3>
                    <span class="text-2xl">📦</span>
                </div>
                <div class="text-3xl font-bold text-green-600">{{ $archivedChatsCount }}</div>
                <div class="text-sm text-gray-600">Stored interactions</div>
            </div>

            <!-- Support Alerts -->
            <!-- Support Alerts -->
             <div class="bg-white rounded-lg shadow-lg p-6" style="border-left: 4px solid #ef4444;">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-800">Support Alerts</h3>
                    <span class="text-2xl">🚨</span>
                </div>
                <div class="text-3xl font-bold text-red-600">{{ $totalCrisisFlags }}</div>
                <div class="text-sm text-gray-600">System Alerts Triggered</div>
            </div>

            <!-- Last Interaction -->
            <!-- Last Interaction -->
            <div class="bg-white rounded-lg shadow-lg p-6" style="border-left: 4px solid #eab308;">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-800">Last Chat</h3>
                    <span class="text-2xl">🕒</span>
                </div>
                <div class="text-xl font-bold text-yellow-600 truncate" title="{{ $lastChatTime }}">{{ $lastChatTime }}</div>
                <div class="text-sm text-gray-600">Since last message</div>
            </div>
        </div>
    </div>

    <!-- PROFILE SUMMARY -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Your Profile</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Academic Information</h3>
                <div class="space-y-2 text-gray-600">
                    <p><strong>University:</strong> {{ $user->university ?? 'Not specified' }}</p>
                    <p><strong>Faculty:</strong> {{ $user->faculty ?? 'Not specified' }}</p>
                    <p><strong>A/L Stream:</strong> {{ $user->al_stream ?? 'Not specified' }}</p>
                    <p><strong>Employment Status:</strong> {{ $user->is_employed ? 'Employed' : 'Not Employed' }}</p>
                </div>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-3">Personal Preferences</h3>
                <div class="space-y-2 text-gray-600">
                    <p>
                        <strong>Learning Style:</strong>
                        {{ is_array($user->learning_style) 
                            ? implode(', ', $user->learning_style) 
                            : ($user->learning_style ?? 'Not specified') }}
                    </p>

                    <p>
                        <strong>Living Arrangement:</strong>
                        {{ $user->living_arrangement ?? 'Not specified' }}
                    </p>

                    <p>
                        <strong>Support Types:</strong>
                        {{ is_array($user->preferred_support_types) 
                            ? implode(', ', $user->preferred_support_types) 
                            : ($user->preferred_support_types ?? 'Not specified') }}
                    </p>

                </div>
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <a href="{{ route('profile.show') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                View Full Profile
            </a>
            <a href="{{ route('profile.edit') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg">
                Edit Profile
            </a>
        </div>
    </div>

</div>


</x-app-layout>
