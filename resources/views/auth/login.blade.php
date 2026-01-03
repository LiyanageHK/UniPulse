<x-guest-layout full>
    <div class="min-h-screen h-screen grid grid-cols-1 lg:grid-cols-2 overflow-hidden">
        
        <!-- LEFT SIDE: Image Section -->
        <div class="hidden lg:block relative overflow-hidden bg-transparent">
            <img src="{{ asset('images/Login.jpg') }}" alt="Login" class="absolute inset-0 w-full h-full object-cover" />
            
            <!-- Blue overlay to reduce white intensity -->
            <div class="absolute inset-0 bg-blue-900/30 mix-blend-multiply"></div>
            
            <!-- Additional subtle overlay for better text contrast -->
            <div class="absolute inset-0 bg-gradient-to-r from-blue-900/20 to-blue-800/10"></div>
            
            <!-- Decorative elements -->
            <div class="absolute -top-40 -left-40 w-96 h-96 bg-white/6 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-32 -right-32 w-80 h-80 bg-blue-400/6 rounded-full blur-3xl"></div>
            
            <!-- Content Overlay -->
            <div class="relative z-10 h-full flex flex-col justify-center px-12 lg:px-16 text-white">
                <div class="max-w-lg">
                    <!-- Logo -->
                    <div class="inline-block p-4 bg-white/10 backdrop-blur-sm rounded-2xl mb-8">
                        <img src="{{ asset('images/UP.jpg') }}" alt="UniPulse Logo" class="w-12 h-12 object-contain">
                    </div>
                    
                    <h1 class="text-4xl font-bold leading-tight mb-4">
                        Welcome Back to<br>
                        <span class="text-white">Uni<span class="text-blue-200">Pulse</span></span>
                    </h1>
                    <p class="text-lg text-white/90 leading-relaxed">
                        Continue your journey towards academic excellence and personal growth. Your dashboard awaits with personalized insights and recommendations.
                    </p>
                    
                    <!-- Feature Highlights -->
                    <div class="mt-12 space-y-4">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <span class="text-lg">Personalized student profiles tailored to your unique journey</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.282 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                </svg>
                            </div>
                            <span class="text-lg">AI-powered early detection for your wellbeing and safety</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <span class="text-lg">24/7 AI chat support for instant, empathetic guidance</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <span class="text-lg">Connect with peers who share your experiences and challenges</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE: Form Section -->
        <div class="relative flex items-center justify-center bg-white px-6 sm:px-12 lg:px-16 py-12 overflow-y-auto">
            
            <!-- Mobile Logo (visible on small screens) -->
            <div class="lg:hidden absolute top-6 left-1/2 transform -translate-x-1/2">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-gradient-to-r from-[#3182ce] to-blue-600 rounded-lg">
                        <img src="{{ asset('images/UP.jpg') }}" alt="UniPulse Logo" class="w-6 h-6 object-contain">
                    </div>
                    <h1 class="text-2xl font-bold">
                        Uni<span class="text-[#3182ce]">Pulse</span>
                    </h1>
                </div>
            </div>

            <div class="w-full max-w-md mt-8 lg:mt-0">
                <!-- Form Header -->
                <div class="mb-10">
                    <h2 class="text-3xl font-bold text-gray-900 mb-2 mt-3">Welcome back</h2>
                    <p class="text-gray-600">Sign in to continue to your account</p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-6" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Email -->
                    <div>
                        <x-input-label for="email" :value="__('Email Address')" class="font-semibold text-gray-700 text-sm mb-2" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                            <x-text-input id="email" 
                                class="block w-full pl-12 pr-4 py-3.5 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-[#3182ce] focus:border-[#3182ce] transition duration-200" 
                                type="email" 
                                name="email" 
                                :value="old('email')" 
                                placeholder="you@example.com" 
                                required 
                                autofocus 
                                autocomplete="username" />
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <x-input-label for="password" :value="__('Password')" class="font-semibold text-gray-700 text-sm mb-2" />
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <x-text-input id="password" 
                                class="block w-full pl-12 pr-12 py-3.5 rounded-xl border-2 border-gray-200 focus:ring-2 focus:ring-[#3182ce] focus:border-[#3182ce] transition duration-200"
                                type="password"
                                name="password"
                                placeholder="Enter your password"
                                required 
                                autocomplete="current-password" />
                            <button type="button" data-password-toggle="true" data-target="#password" aria-label="Toggle password visibility" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600">
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
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                            <input id="remember_me" type="checkbox" class="rounded border-2 border-gray-300 text-[#3182ce] focus:ring-2 focus:ring-[#3182ce] transition duration-200" name="remember">
                            <span class="ml-2 text-sm text-gray-600 group-hover:text-gray-800 transition duration-200">Remember me</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-[#3182ce] hover:text-blue-700 font-semibold transition duration-200" href="{{ route('password.request') }}">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <!-- Login Button -->
                    <div class="pt-2">
                        <x-primary-button class="w-full justify-center py-4 px-4 bg-gradient-to-r from-[#3182ce] to-blue-600 hover:from-blue-600 hover:to-[#3182ce] text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200 flex items-center gap-2 text-base">
                            Sign in
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </x-primary-button>
                    </div>

                    <!-- Divider -->
                    <div class="relative my-8">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white text-gray-500 font-medium">New to UniPulse?</span>
                        </div>
                    </div>

                    <!-- Sign Up Link -->
                    <div class="text-center">
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center w-full px-6 py-3.5 border-2 border-gray-200 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 hover:border-gray-300 transition-all duration-200">
                            Create an account
                        </a>
                    </div>
                </form>

                <!-- Footer -->
                <p class="mt-8 text-center text-xs text-gray-500">
                    &copy; {{ date('Y') }} UniPulse. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    @include('components.password-toggle-script')
</x-guest-layout>