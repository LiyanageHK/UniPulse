<x-app-layout>

<div class="container mx-auto px-4 py-8">
        <div class="max-w-6xl mx-auto">

    <div class="bg-white shadow rounded-xl p-8 border border-gray-100">

        <div class="flex items-center space-x-5 mb-6">
            <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center">
                <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 14a4 4 0 10-8 0v1a2 2 0 002 2h4a2 2 0 002-2v-1z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 7a3 3 0 100-6 3 3 0 000 6z" />
                </svg>
            </div>

            <div>
                <h1 class="text-2xl font-bold">{{ $user->name }}</h1>
                <p class="text-gray-500">{{ $user->profile->university }}</p>
            </div>
        </div>

        {{-- Basic Info --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-8">
            <div>
                <p class="text-sm text-gray-500">Faculty</p>
                <p class="font-semibold">{{ $user->profile->faculty }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">A/L Stream</p>
                <p class="font-semibold">{{ $user->profile->al_stream }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Learning Style</p>
                <p class="font-semibold">{{ $user->profile->learning_style }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Social Setting</p>
                <p class="font-semibold">{{ $user->profile->social_setting }}</p>
            </div>
        </div>

        {{-- Communication --}}
        <h2 class="text-lg font-semibold mb-2">Communication Methods</h2>
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach (json_decode($user->profile->communication_methods, true) as $method)
                <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm">
                    {{ $method }}
                </span>
            @endforeach
        </div>

        {{-- Interests --}}
        <h2 class="text-lg font-semibold mb-2">Top Interests</h2>
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach (json_decode($user->profile->top_interests, true) as $interest)
                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm">
                    {{ $interest }}
                </span>
            @endforeach
        </div>

        {{-- Hobbies --}}
        <h2 class="text-lg font-semibold mb-2">Hobbies</h2>
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach (json_decode($user->profile->hobbies, true) as $hobby)
                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">
                    {{ $hobby }}
                </span>
            @endforeach
        </div>

        {{-- Support Types --}}
        <h2 class="text-lg font-semibold mb-2">Support Types</h2>
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach (json_decode($user->profile->support_types, true) as $support)
                <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm">
                    {{ $support }}
                </span>
            @endforeach
        </div>

        <div class="mt-6">
            <a href="{{ url()->previous() }}" class="text-purple-600 hover:underline">
                ← Back to Matches
            </a>
        </div>

    </div>
</div>
</div>

</x-app-layout>
