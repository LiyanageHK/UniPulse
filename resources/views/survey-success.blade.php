<x-app-layout title="Survey Complete - UniPulse">
<div class="m-20 bg-gradient-to-br from-purple-50 via-white to-blue-50 flex items-center justify-center px-4 sm:px-6 lg:px-8">
    <div class="max-w-xl w-full bg-white p-8 rounded-xl shadow-md text-center">
        <!-- Success Icon -->
        <div class="mx-auto mb-6 w-20 h-20 flex items-center justify-center bg-green-100 rounded-full">
            <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>

        <!-- Success Message -->
        <h2 class="text-3xl font-bold text-gray-900 mb-4">Check-In Submitted!</h2>
        <p class="text-gray-600 mb-6">
            Thank you for completing your weekly wellbeing check-in. Your responses have been recorded securely.
        </p>

        <!-- Reminder -->
        <p class="text-gray-600 mb-6">
            Remember to complete your check-in every week to help us monitor your wellbeing and provide timely support when needed.
        </p>

        <!-- Button to Dashboard -->
        <a href="{{ url('/dashboard') }}"
           class="inline-block bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white px-6 py-3 rounded-lg font-semibold transition transform hover:-translate-y-0.5 shadow-lg hover:shadow-xl">
            Go to Dashboard
        </a>
    </div>
</div>
</x-app-layout>
