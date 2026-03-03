<x-app-layout title="Onboarding Complete - UniPulse">
<div class="m-20 flex items-center justify-center bg-gray-50 px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl w-full bg-white p-8 rounded-xl shadow-md text-center">
        <!-- Success Icon -->
        <div class="mx-auto mb-6 w-20 h-20 flex items-center justify-center bg-green-100 rounded-full">
            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <!-- Success Message -->
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Profile Complete!</h2>
        <p class="text-gray-600 mb-6">
            Thank you for completing your profile, <span class="font-semibold">{{ $username }}</span>! Your responses are recorded securely.
        </p>

        <!-- Journal Prompt -->
        <div class="bg-purple-50 border border-purple-200 rounded-xl p-5 mb-6">
            <div class="text-4xl mb-2">&#9997;</div>
            <h3 class="text-lg font-bold text-purple-800 mb-2">Write Your First Journal Entry</h3>
            <p class="text-sm text-purple-700">
                Start by writing about your day, your feelings, or anything on your mind.
                Our AI will analyze your entry and generate your personalized risk profile.
            </p>
        </div>

        <!-- Button to Journal -->
        <a href="{{ route('journal.index') }}"
           class="inline-block bg-purple-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-purple-700 transition text-lg mb-3">
            Write Your First Journal
        </a>

        <p class="text-xs text-gray-400 mt-3">
            Or <a href="{{ url('/dashboard') }}" class="text-purple-600 hover:underline">skip to dashboard</a>
        </p>
    </div>
</div>
</x-app-layout>
