<x-app-layout title="Profile - UniPulse">

<div class="max-w-6xl mx-auto py-8" x-data="{ showDeleteModal: false }">

    <!-- HEADER -->
    <h1 class="text-3xl font-bold mb-6 text-gray-800">My Profile</h1>

    <!-- SUCCESS MESSAGE -->
    @if (session('status'))
        <div x-data="{ show: true }" x-show="show" x-transition
            x-init="setTimeout(() => show = false, 3000)"
            class="bg-green-100 text-green-700 px-4 py-2 rounded-md mb-4">
            {{ session('status') }}
        </div>
    @endif

    <!-- -------------- BASIC DETAILS CARD -------------- -->
    <div class="bg-white shadow-md rounded-xl p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
            <span class="material-icons text-blue-500">Basic</span> Information
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <p><strong>Name:</strong> {{ $user->name }}</p>
            <p><strong>Email:</strong> {{ $user->email }}</p>
            <p><strong>University:</strong> {{ $user->university }}</p>
            <p><strong>Faculty:</strong> {{ $user->faculty }}</p>
            <p><strong>A/L Stream:</strong> {{ $user->al_stream }}</p>
            <p><strong>Onboarded On:</strong> {{ $user->onboarding_completed_at ?? '—' }}</p>
        </div>
    </div>

    <!-- -------------- AL RESULTS -------------- -->
    @php
        $al = is_array($user->al_results) ? $user->al_results : json_decode($user->al_results ?? '[]', true);
    @endphp

    <div class="bg-white shadow-md rounded-xl p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
            <span class="material-icons text-blue-500">AL</span> Results
        </h2>

        @if($al)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($al as $row)
                <div class="border rounded-lg p-3 bg-gray-50">
                    <p><strong>Subject:</strong> {{ $row['subject'] }}</p>
                    <p><strong>Grade:</strong> {{ $row['grade'] }}</p>
                </div>
            @endforeach
        </div>
        @else
            <p class="text-gray-500">No A/L results provided.</p>
        @endif
    </div>

    <!-- -------------- LEARNING & SOCIAL PROFILE -------------- -->
    @php
        $learning_style = is_array($user->learning_style) ? $user->learning_style : json_decode($user->learning_style ?? '[]', true);
        $communication = is_array($user->communication_preferences) ? $user->communication_preferences : json_decode($user->communication_preferences ?? '[]', true);
    @endphp

    <div class="bg-white shadow-md rounded-xl p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
            <span class="material-icons text-blue-500">Learning</span> & Social Profile
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <strong>Learning Style:</strong>
                <div class="flex flex-wrap gap-2 mt-1">
                    @foreach($learning_style ?? [] as $ls)
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm">{{ $ls }}</span>
                    @endforeach
                </div>
            </div>

            <div>
                <strong>Communication Preferences:</strong>
                <div class="flex flex-wrap gap-2 mt-1">
                    @foreach($communication ?? [] as $c)
                        <span class="px-3 py-1 bg-pink-100 text-pink-700 rounded-full text-sm">{{ $c }}</span>
                    @endforeach
                </div>
            </div>

            <p><strong>Transition Confidence:</strong> {{ $user->transition_confidence ?? '—' }}/5</p>
            <p><strong>Social Preference:</strong> {{ $user->social_preference }}</p>
            <p><strong>Introvert/Extrovert Scale:</strong> {{ $user->introvert_extrovert_scale }}/10</p>
        </div>
    </div>

    <!-- -------------- MOTIVATION + SUPPORT NEEDS -------------- -->
    <div class="bg-white shadow-md rounded-xl p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
            <span class="material-icons text-blue-500">Motivation</span> & Support Profile
        </h2>

        @php
            $interests = $user->interests ?? [];
            $hobbies = $user->hobbies ?? [];
            $support = $user->preferred_support_types ?? [];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <p><strong>Primary Motivator:</strong> {{ $user->primary_motivator }}</p>
            <p><strong>Goal Clarity:</strong> {{ $user->goal_clarity }}/5</p>

            <div>
                <strong>Interests:</strong>
                <div class="flex flex-wrap gap-2 mt-1">
                    @foreach($interests as $i)
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">{{ $i }}</span>
                    @endforeach
                </div>
            </div>

            <div>
                <strong>Hobbies:</strong>
                <div class="flex flex-wrap gap-2 mt-1">
                    @foreach($hobbies as $h)
                        <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm">{{ $h }}</span>
                    @endforeach
                </div>
            </div>

            <p><strong>Living Arrangement:</strong> {{ $user->living_arrangement }}</p>
            <p><strong>Employment:</strong> {{ $user->is_employed ? 'Yes' : 'No' }}</p>
            <p><strong>Openness to AI Support:</strong> {{ $user->ai_openness }}/5</p>

            <div>
                <strong>Preferred Support Types:</strong>
                <div class="flex flex-wrap gap-2 mt-1">
                    @foreach($support as $s)
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">{{ $s }}</span>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    <!-- -------------- WEEKLY CHECK-IN DATA -------------- -->
    <div class="bg-white shadow-md rounded-xl p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
            <span class="material-icons text-blue-500">Dynamic</span> Data
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <p><strong>Stress Level:</strong> {{ $user->stress_level ?? '—' }} <span class="ml-2 text-sm text-gray-500">(Auto-updated)</span></p>
            <p><strong>Group Work Comfort:</strong> {{ $user->group_work_comfort ?? '—' }}/5 <span class="ml-2 text-sm text-gray-500">(Auto-updated)</span></p>
            <p><strong>Overwhelm Level:</strong> {{ $user->overwhelm_level ?? '—' }}/5 <span class="ml-2 text-sm text-gray-500">(Auto-updated)</span></p>
            <p><strong>Peer Struggle:</strong> {{ $user->peer_struggle ?? '—' }}/5 <span class="ml-2 text-sm text-gray-500">(Auto-updated)</span></p>
        </div>

        @if($user->last_checkin_at)
    <p class="text-sm text-gray-500 mt-2">
        Last Weekly Check-In:
        {{ $user->last_checkin_at->timezone('Asia/Colombo')->format('j M Y, g:ia') }}
    </p>
@endif
    </div>

    <!-- -------------- ACTION BUTTONS -------------- -->
    <div class="flex gap-3">

    <!-- Edit Profile -->
    <a href="{{ route('profile.edit') }}"
        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow transition-colors duration-200">
        Edit Profile
    </a>

    <!-- Update Password (separate page) -->
    <a href="{{ route('profile.password') }}"
        class="px-4 py-2 bg-gray-600 hover:bg-gray-400 text-white font-medium rounded-lg shadow transition-colors duration-200">
        Update Password
    </a>

    <!-- Delete Account Button -->
    <button @click="showDeleteModal = true" type="button"
        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow transition-colors duration-200">
        Delete Account
    </button>

</div>

    <!-- Delete Account Modal -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 bg-white bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50" @click.self="showDeleteModal = false">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full mx-4 relative z-10">
            <h2 class="text-2xl font-bold text-red-600 mb-3">Delete Account</h2>
            
            <p class="text-gray-700 mb-4">
                <strong>⚠️ Warning:</strong> This action will <strong>permanently delete</strong> your account and all associated data from our system. This cannot be undone.
            </p>
            
            <p class="text-gray-600 text-sm mb-6">
                To confirm deletion, please enter your password:
            </p>

            <form action="{{ route('profile.destroy') }}" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 shadow-sm focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3 justify-end pt-4 border-t">
                    <button type="button" @click="showDeleteModal = false"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
                        Delete Permanently
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

</x-app-layout>