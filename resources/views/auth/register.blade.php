<x-guest-layout>
    <div class="min-h-screen flex flex-col justify-center items-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <!-- Header / Title -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-extrabold text-indigo-600 tracking-tight">UniPulse</h1>
            <p class="mt-2 text-lg text-gray-600">Create your account</p>
        </div>

        <!-- Card -->
        <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg">
            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Registration Form -->
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
<div class="mt-4">
    <x-input-label for="name" :value="__('Name')" class="font-medium text-gray-700" />
    <x-text-input id="name" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                  type="text"
                  name="name"
                  :value="old('name')"
                  required
                  autofocus
                  autocomplete="name" />
    <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm text-red-600" />
</div>


                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email')" class="font-medium text-gray-700" />
                    <x-text-input id="email" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                  type="email"
                                  name="email"
                                  :value="old('email')"
                                  required
                                  autofocus
                                  autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-600" />
                </div>

                <!-- Password -->
                <div class="mt-4">
                    <x-input-label for="password" :value="__('Password')" class="font-medium text-gray-700" />
                    <x-text-input id="password" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                  type="password"
                                  name="password"
                                  required
                                  autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-600" />
                </div>

                <!-- Confirm Password -->
                <div class="mt-4">
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="font-medium text-gray-700" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                  type="password"
                                  name="password_confirmation"
                                  required
                                  autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-red-600" />
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between mt-6">
                    <x-primary-button class="ml-3 w-full justify-center">
                        {{ __('Register') }}
                    </x-primary-button>
                </div>
            </form>
            <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">
        Already have an account?
        <a href="{{ route('login') }}"
           class="text-indigo-600 font-semibold hover:underline">
            Sign in
        </a>
            </p>
            </div>
        </div>

        <!-- Footer -->
        <p class="mt-8 text-center text-gray-500 text-sm">
            &copy; {{ date('Y') }} UniPulse. All rights reserved.
        </p>
    </div>
</x-guest-layout>
