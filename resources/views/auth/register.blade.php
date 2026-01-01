<x-guest-layout>
    <div class="min-h-screen flex flex-col justify-center items-center bg-gradient-to-br from-blue-50 via-white to-blue-50 py-12 px-4 sm:px-6 lg:px-8 relative overflow-hidden">
        
        <!-- Decorative Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <!-- Large Circle Top Right -->
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-gradient-to-br from-[#3182ce]/10 to-blue-300/10 rounded-full blur-3xl"></div>
            
            <!-- Medium Circle Bottom Left -->
            <div class="absolute -bottom-32 -left-32 w-80 h-80 bg-gradient-to-tr from-blue-400/10 to-[#3182ce]/10 rounded-full blur-3xl"></div>
            
            <!-- Small Circle Top Left -->
            <div class="absolute top-20 left-20 w-64 h-64 bg-gradient-to-br from-blue-300/5 to-[#3182ce]/5 rounded-full blur-2xl"></div>
            
            <!-- Small Circle Bottom Right -->
            <div class="absolute bottom-40 right-32 w-48 h-48 bg-gradient-to-tl from-[#3182ce]/5 to-blue-400/5 rounded-full blur-2xl"></div>
            
            <!-- Animated Dots Pattern -->
            <div class="absolute inset-0 opacity-30">
                <div class="absolute top-1/4 left-1/4 w-2 h-2 bg-[#3182ce] rounded-full animate-pulse"></div>
                <div class="absolute top-1/3 right-1/3 w-2 h-2 bg-blue-400 rounded-full animate-pulse" style="animation-delay: 0.5s;"></div>
                <div class="absolute bottom-1/3 left-1/2 w-2 h-2 bg-[#3182ce] rounded-full animate-pulse" style="animation-delay: 1s;"></div>
                <div class="absolute bottom-1/4 right-1/4 w-2 h-2 bg-blue-400 rounded-full animate-pulse" style="animation-delay: 1.5s;"></div>
                <div class="absolute top-1/2 left-1/3 w-1.5 h-1.5 bg-[#3182ce] rounded-full animate-pulse" style="animation-delay: 2s;"></div>
                <div class="absolute top-2/3 right-1/2 w-1.5 h-1.5 bg-blue-400 rounded-full animate-pulse" style="animation-delay: 2.5s;"></div>
            </div>
            
            <!-- Grid Pattern Overlay -->
            <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
        </div>
        
        <!-- Logo / Project Name -->
        <div class="text-center mb-8 animate-fade-in relative z-10">
            <div class="inline-block p-4 bg-gradient-to-r from-[#3182ce] to-blue-600 rounded-2xl shadow-lg mb-4">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h1 class="text-4xl font-extrabold text-gray-800 tracking-tight mb-2">
                Uni<span class="text-[#3182ce]">Pulse</span>
            </h1>
            <p class="text-lg text-gray-600">Create your account and get started</p>
        </div>

        <div class="w-full max-w-md bg-white p-8 rounded-3xl shadow-2xl border border-gray-100 relative z-10 backdrop-blur-sm">
            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <!-- Registration Form -->
            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf

                <!-- Name -->
                <div>
                    <x-input-label for="name" :value="__('Full Name')" class="font-semibold text-gray-700 text-sm" />
                    <div class="relative mt-2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <x-text-input id="name" class="block w-full pl-10 pr-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:ring-2 focus:ring-[#3182ce] focus:border-[#3182ce] transition duration-200"
                                      type="text"
                                      name="name"
                                      :value="old('name')"
                                      placeholder="Enter your full name"
                                      required
                                      autofocus
                                      autocomplete="name" />
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm text-red-600 flex items-center gap-1" />
                </div>

                <!-- Email Address -->
                <div>
                    <x-input-label for="email" :value="__('Email Address')" class="font-semibold text-gray-700 text-sm" />
                    <div class="relative mt-2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <x-text-input id="email" class="block w-full pl-10 pr-4 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:ring-2 focus:ring-[#3182ce] focus:border-[#3182ce] transition duration-200"
                                      type="email"
                                      name="email"
                                      :value="old('email')"
                                      placeholder="Enter your email"
                                      required
                                      autocomplete="username" />
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-600 flex items-center gap-1" />
                </div>

                <!-- Password -->
                <div>
                    <x-input-label for="password" :value="__('Password')" class="font-semibold text-gray-700 text-sm" />
                    <div class="relative mt-2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <x-text-input id="password" class="block w-full pl-10 pr-12 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:ring-2 focus:ring-[#3182ce] focus:border-[#3182ce] transition duration-200"
                                      type="password"
                                      name="password"
                                      placeholder="Create a password"
                                      required
                                      autocomplete="new-password" />
                        <button type="button" data-password-toggle="true" data-target="#password" aria-label="Toggle password visibility" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500">
                            <svg class="w-5 h-5 eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="w-5 h-5 eye-off-icon hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.269-2.943-9.543-7a9.965 9.965 0 012.229-3.417M3 3l18 18" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.477 10.477A3 3 0 0113.523 13.523" />
                            </svg>
                        </button>
                    </div>
                    <p class="mt-1.5 text-xs text-gray-500">Must be at least 8 characters</p>
                    <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-600 flex items-center gap-1" />
                </div>

                <!-- Confirm Password -->
                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="font-semibold text-gray-700 text-sm" />
                    <div class="relative mt-2">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <x-text-input id="password_confirmation" class="block w-full pl-10 pr-12 py-3 rounded-xl border-2 border-gray-200 shadow-sm focus:ring-2 focus:ring-[#3182ce] focus:border-[#3182ce] transition duration-200"
                                      type="password"
                                      name="password_confirmation"
                                      placeholder="Confirm your password"
                                      required
                                      autocomplete="new-password" />
                        <button type="button" data-password-toggle="true" data-target="#password_confirmation" aria-label="Toggle password visibility" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500">
                            <svg class="w-5 h-5 eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="w-5 h-5 eye-off-icon hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.269-2.943-9.543-7a9.965 9.965 0 012.229-3.417M3 3l18 18" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.477 10.477A3 3 0 0113.523 13.523" />
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-red-600 flex items-center gap-1" />
                </div>

                <!-- Register Button -->
                <div>
                    <x-primary-button class="w-full justify-center py-3 px-4 bg-gradient-to-r from-[#3182ce] to-blue-600 hover:from-blue-600 hover:to-[#3182ce] text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200 flex items-center gap-2">
                        {{ __('Create Account') }}
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </x-primary-button>
                </div>

                <!-- Terms & Privacy Notice -->
                <p class="text-xs text-center text-gray-500">
                    By creating an account, you agree to our
                    <a href="{{ route('terms') }}" class="text-[#3182ce] hover:underline">Terms of Service</a> and
                    <a href="{{ route('privacy') }}" class="text-[#3182ce] hover:underline">Privacy Policy</a>
                </p>
            </form>

            <!-- Divider -->
            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-200"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-4 bg-white text-gray-500 font-medium">Already have an account?</span>
                </div>
            </div>

            <!-- Sign In Link -->
            <div class="text-center">
                <p class="text-sm text-gray-600">
                    <a href="{{ route('login') }}"
                       class="text-[#3182ce] font-bold hover:text-blue-700 hover:underline transition duration-200">
                        Sign in instead
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <p class="mt-8 text-center text-gray-500 text-sm relative z-10">
            &copy; {{ date('Y') }} UniPulse. All rights reserved.
        </p>
    </div>

    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in {
            animation: fade-in 0.6s ease-out;
        }

        .bg-grid-pattern {
            background-image: 
                linear-gradient(to right, #3182ce08 1px, transparent 1px),
                linear-gradient(to bottom, #3182ce08 1px, transparent 1px);
            background-size: 40px 40px;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 0.4;
                transform: scale(1);
            }
            50% {
                opacity: 1;
                transform: scale(1.2);
            }
        }
    </style>
    @include('components.password-toggle-script')
</x-guest-layout>