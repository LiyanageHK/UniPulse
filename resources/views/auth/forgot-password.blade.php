<x-guest-layout title="Forgot Password - UniPulse">
    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <!-- Header / Title -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-indigo-600 tracking-tight">UniPulse</h1>
            <p class="mt-2 text-lg text-gray-600">Forgot your password?</p>
        </div>

        <!-- Card -->
        <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg">
            <!-- Info Text -->
            <div class="mb-4 text-sm text-gray-600">
                {{ __('No worries! Just enter your email address and we will send you a link to reset your password.') }}
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Form -->
            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" class="font-medium text-gray-700" />
                    <x-text-input id="email" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                  type="email"
                                  name="email"
                                  :value="old('email')"
                                  required
                                  autofocus />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-600" />
                </div>

                <!-- Submit Button -->
                <div class="flex items-center justify-end mt-6">
                    <x-primary-button class="w-full justify-center">
                        {{ __('Send Password Reset Link') }}
                    </x-primary-button>
                </div>
            </form>
        </div>

        <!-- Footer -->
        <p class="mt-8 text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} UniPulse. All rights reserved.
        </p>
    </div>
</x-guest-layout>
