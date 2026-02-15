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
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Survey Submitted!</h2>
        <p class="text-gray-600 mb-6">
            Thank you for completing your wellbeing survey. Your responses are recorded securely.
        </p>

        <!-- Reminder -->
        <p class="text-gray-600 mb-6">
            Visit every week to take a new survey and track your wellbeing progress.
        </p>

        <!-- Button to Dashboard -->
        <a href="{{ url('/dashboard') }}"
           class="inline-block bg-purple-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-purple-700 transition">
            Go to Dashboard
        </a>
    </div>
</div>
</x-app-layout>
