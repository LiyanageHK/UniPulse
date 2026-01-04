<x-guest-layout full title="Register - UniPulse">
    <div class="min-h-screen grid grid-cols-1 lg:grid-cols-2 overflow-hidden">

        <!-- LEFT SIDE: Form Section -->
        <div class="relative flex items-center justify-center bg-white px-6 sm:px-12 lg:px-24 py-8 overflow-y-auto z-10">

            <!-- Decorative Background Elements -->
            <div class="absolute inset-0 overflow-hidden pointer-events-none opacity-20">
                <div class="absolute top-20 right-20 w-72 h-72 bg-gradient-to-br from-blue-200/30 to-indigo-200/30 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute bottom-20 left-20 w-96 h-96 bg-gradient-to-tr from-blue-300/30 to-purple-300/20 rounded-full blur-3xl animate-pulse"></div>
            </div>

            <!-- Mobile Logo -->
            <div class="lg:hidden absolute top-8 left-1/2 transform -translate-x-1/2 z-20">
                <div class="flex items-center gap-3">
                    <div class="p-3 bg-gradient-to-br from-blue-500 to-indigo-500 rounded-xl shadow-lg">
                        <img src="{{ asset('images/UP.jpg') }}" alt="UniPulse Logo" class="w-7 h-7 object-contain">
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900">
                        Uni<span class="text-blue-600">Pulse</span>
                    </h1>
                </div>
            </div>

            <!-- Form Card -->
            <div class="w-full max-w-xl mt-4 lg:mt-0 relative z-10 p-4 md:p-6 lg:p-8 bg-white rounded-xl shadow-lg">
                
                <!-- Form Header -->
                <div class="mb-8">
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-3 leading-tight">
                        Create your <span class="text-blue-600">UniPulse</span> account
                    </h2>
                    <p class="text-gray-600 text-base">Join thousands of students achieving academic excellence</p>
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-6" :status="session('status')" />

                <form method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <!-- Name -->
                    <div class="group">
                        <x-input-label for="name" :value="__('Full Name')" class="font-semibold text-gray-800 text-sm mb-2.5 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            Full Name
                        </x-input-label>
                        <x-text-input id="name" 
                            class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition-all duration-200 text-sm placeholder-gray-400"
                            type="text"
                            name="name"
                            :value="old('name')"
                            placeholder="Enter your full name"
                            required
                            autofocus
                            autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2 text-sm text-red-500" />
                    </div>

                    <!-- Email -->
                    <div class="group">
                        <x-input-label for="email" :value="__('Email Address')" class="font-semibold text-gray-800 text-sm mb-2.5 flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            Email Address
                        </x-input-label>
                        <x-text-input id="email" 
                            class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition-all duration-200 text-sm placeholder-gray-400"
                            type="email"
                            name="email"
                            :value="old('email')"
                            placeholder="you@university.edu"
                            required
                            autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2 text-sm text-red-500" />
                    </div>

                    <!-- Passwords -->
                    <div class="grid grid-cols-1 gap-4">
                        <!-- Password -->
                        <div class="group">
                            <x-input-label for="password" :value="__('Password')" class="font-semibold text-gray-800 text-sm mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                                Password
                            </x-input-label>
                            <div class="relative">
                                <x-text-input id="password" 
                                    class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition-all duration-200 text-sm placeholder-gray-400"
                                    type="password"
                                    name="password"
                                    placeholder="••••••••"
                                    required
                                    autocomplete="new-password" />
                                <button type="button" data-password-toggle="true" data-target="#password" aria-label="Toggle password visibility" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
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
                            <p class="mt-2 text-xs text-gray-500">Min. 8 characters</p>
                            <x-input-error :messages="$errors->get('password')" class="mt-2 text-sm text-red-500" />
                        </div>

                        <!-- Confirm Password -->
                        <div class="group">
                            <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="font-semibold text-gray-800 text-sm mb-2 flex items-center gap-2"/>
                            <div class="relative">
                                <x-text-input id="password_confirmation" 
                                    class="block w-full px-4 py-3 rounded-xl border-2 border-gray-200 bg-white focus:ring-2 focus:ring-blue-200 focus:border-blue-500 transition-all duration-200 text-sm placeholder-gray-400"
                                    type="password"
                                    name="password_confirmation"
                                    placeholder="••••••••"
                                    required
                                    autocomplete="new-password" />
                                <button type="button" data-password-toggle="true" data-target="#password_confirmation" aria-label="Toggle password visibility" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
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
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-sm text-red-500" />
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="flex items-start gap-4 p-5 bg-blue-50 rounded-2xl border-2 border-blue-100 hover:border-blue-200 transition-colors duration-200">
                        <input type="checkbox" id="terms" name="terms" required class="mt-1 w-5 h-5 rounded-lg border-2 border-blue-300 text-blue-600 focus:ring-2 focus:ring-blue-200 cursor-pointer" />
                        <label for="terms" class="text-sm text-gray-700 leading-relaxed cursor-pointer">
                            I agree to the 
                            <a href="{{ route('terms') }}" class="text-blue-600 font-semibold underline decoration-2 underline-offset-2 hover:text-blue-800">Terms of Service</a>
                            and 
                            <a href="{{ route('privacy') }}" class="text-blue-600 font-semibold underline decoration-2 underline-offset-2 hover:text-blue-800">Privacy Policy</a>
                        </label>
                    </div>

                    <!-- Register Button -->
                    <x-primary-button class="w-full justify-center py-4 px-4 bg-gradient-to-r from-[#3182ce] to-blue-600 hover:from-blue-600 hover:to-[#3182ce] text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-200 flex items-center gap-2 text-base">
                        <span>Create My Account</span>
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                        </svg>
                    </x-primary-button>

                    <!-- Divider -->
                    <div class="relative my-8">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-200"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white text-gray-500 font-medium">Already have an account?</span>
                        </div>
                    </div>

                    <!-- Sign Up Link -->
                    <div class="text-center">
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center w-full px-6 py-3.5 border-2 border-gray-200 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 hover:border-gray-300 transition-all duration-200">
                            Sign in
                        </a>
                    </div>

                </form>
            </div>
        </div>

        <!-- RIGHT SIDE: Hero Section -->
        <div class="hidden lg:block relative overflow-hidden">
            <img src="{{ asset('images/Register.jpg') }}" alt="Register" class="absolute inset-0 w-full h-full object-cover" />
            
            <!-- Blue overlay to reduce white intensity -->
            <div class="absolute inset-0 bg-blue-900/30 mix-blend-multiply"></div>
            
            <!-- Additional subtle overlay for better text contrast -->
            <div class="absolute inset-0 bg-gradient-to-r from-blue-900/20 to-blue-800/10"></div>

            <!-- Animated Background -->
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute top-0 right-0 w-96 h-96 bg-gradient-to-br from-white/10 to-blue-100/10 rounded-full blur-3xl animate-pulse"></div>
                <div class="absolute bottom-0 left-0 w-80 h-80 bg-gradient-to-tr from-blue-300/10 to-purple-300/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
            </div>

            <!-- Hero Content -->
            <div class="relative z-10 h-full flex flex-col justify-center p-12 text-white">
                <!-- Logo -->
                <div class="inline-block p-4 bg-white/10 backdrop-blur-sm rounded-2xl mb-8 w-fit">
                    <img src="{{ asset('images/UP.jpg') }}" alt="UniPulse Logo" class="w-12 h-12 object-contain">
                </div>
                
                <h2 class="text-5xl font-bold leading-tight mb-6">Begin Your Journey to <span class="text-blue-100">Academic Excellence</span></h2>
                <p class="text-lg text-white/90 mb-10 max-w-lg">Join a community of ambitious students using AI-powered insights to thrive in university life.</p>

                
            </div>
        </div>
    </div>

    @include('components.password-toggle-script')
</x-guest-layout>