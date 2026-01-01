<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Conversational Support - UniPulse</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Vite / Assets -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        body {
            font-family: 'Poppins', 'Figtree', sans-serif;
        }
        .hero-gradient {
            background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
            position: relative;
        }
        .hero-gradient::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.4;
        }
        .feature-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid #e5e7eb;
        }
        .feature-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 25px 50px -12px rgba(37, 99, 235, 0.25);
            border-color: #93c5fd;
        }
        .gradient-text {
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        @include('layouts.header')

        <!-- Hero Section -->
        <section class="hero-gradient text-white py-20 md:py-28 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
                <div class="text-center">
                    <div class="inline-block mb-6">
                        <span class="px-5 py-2 bg-white/10 backdrop-blur-sm rounded-full text-sm font-semibold border border-white/20">
                            <i class="fas fa-comments mr-2"></i>24/7 AI-Powered Support
                        </span>
                    </div>
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 leading-tight">
                        Conversational Support
                    </h1>
                    <p class="text-xl md:text-2xl mb-10 text-blue-100 max-w-3xl mx-auto leading-relaxed">
                        Get instant, personalized support from our intelligent AI support, available anytime you need guidance on your university journey.
                    </p>
                </div>
            </div>
            
            <!-- Wave Separator -->
            <div class="absolute bottom-0 left-0 w-full">
                <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="white"/>
                </svg>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
                        Why Choose Our <span class="gradient-text">Conversational Support?</span>
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Advanced AI technology combined with genuine care for your wellbeing
                    </p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="feature-card bg-white rounded-2xl p-8 shadow-md hover:border-blue-300">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-50 rounded-xl flex items-center justify-center mb-6">
                            <i class="fas fa-robot text-3xl text-blue-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">AI-Powered Chat</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Get instant support from our intelligent AI counselor, trained to understand student challenges and provide helpful, empathetic guidance.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="feature-card bg-white rounded-2xl p-8 shadow-md hover:border-blue-300">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-purple-50 rounded-xl flex items-center justify-center mb-6">
                            <i class="fas fa-brain text-3xl text-purple-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Personalized Support</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Our AI remembers your profile and conversation history to provide contextually relevant and truly personalized advice.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="feature-card bg-white rounded-2xl p-8 shadow-md hover:border-blue-300">
                        <div class="w-16 h-16 bg-gradient-to-br from-red-100 to-red-50 rounded-xl flex items-center justify-center mb-6">
                            <i class="fas fa-shield-alt text-3xl text-red-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Crisis Detection</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Advanced crisis detection ensures you get the right resources when you need them most, with automatic professional counselor matching.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="feature-card bg-white rounded-2xl p-8 shadow-md hover:border-blue-300">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-100 to-green-50 rounded-xl flex items-center justify-center mb-6">
                            <i class="fas fa-graduation-cap text-3xl text-green-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Academic Guidance</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Get help with study strategies, time management, exam stress, and navigating university life successfully.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="feature-card bg-white rounded-2xl p-8 shadow-md hover:border-blue-300">
                        <div class="w-16 h-16 bg-gradient-to-br from-indigo-100 to-indigo-50 rounded-xl flex items-center justify-center mb-6">
                            <i class="fas fa-heart text-3xl text-indigo-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Mental Wellbeing</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Talk about stress, anxiety, or any challenges. Our AI provides empathetic support and connects you to professional help when needed.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="feature-card bg-white rounded-2xl p-8 shadow-md hover:border-blue-300">
                        <div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-50 rounded-xl flex items-center justify-center mb-6">
                            <i class="fas fa-lock text-3xl text-gray-700"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">Private & Secure</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Your conversations are completely confidential and secure. We prioritize your privacy and wellbeing above all else.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="py-24 bg-gradient-to-b from-gray-50 to-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
                        How It <span class="gradient-text">Works</span>
                    </h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                        Start chatting in three simple steps
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-12">
                    <!-- Step 1 -->
                    <div class="text-center">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl flex items-center justify-center text-3xl font-black mx-auto mb-6 shadow-xl">
                            1
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Start a Conversation</h3>
                        <p class="text-gray-600 leading-relaxed text-lg">
                            Click the chat button and begin talking about anything that's on your mind - academic or personal.
                        </p>
                    </div>

                    <!-- Step 2 -->
                    <div class="text-center">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl flex items-center justify-center text-3xl font-black mx-auto mb-6 shadow-xl">
                            2
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Get Instant Support</h3>
                        <p class="text-gray-600 leading-relaxed text-lg">
                            Our AI responds immediately with personalized guidance, resources, and practical advice.
                        </p>
                    </div>

                    <!-- Step 3 -->
                    <div class="text-center">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-2xl flex items-center justify-center text-3xl font-black mx-auto mb-6 shadow-xl">
                            3
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 mb-4">Continue Your Journey</h3>
                        <p class="text-gray-600 leading-relaxed text-lg">
                            Return anytime to continue conversations. Our AI remembers your context and tracks your progress.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-24 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-gradient-to-br from-blue-600 via-blue-500 to-blue-700 rounded-3xl p-12 md:p-16 text-center shadow-2xl relative overflow-hidden">
                    <!-- Decorative Elements -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-32 -mt-32"></div>
                    <div class="absolute bottom-0 left-0 w-96 h-96 bg-white opacity-5 rounded-full -ml-48 -mb-48"></div>
                    
                    <div class="relative z-10">
                        <h2 class="text-3xl md:text-5xl font-extrabold text-white mb-6">
                            Ready to Get Started?
                        </h2>
                        <p class="text-xl md:text-2xl mb-10 text-blue-50">
                            Join thousands of students getting support through UniPulse Conversational Support
                        </p>
                        @auth
                            <a href="{{ route('chat.support') }}" class="inline-flex items-center justify-center gap-3 px-12 py-5 bg-white text-blue-600 font-bold text-xl rounded-xl shadow-xl hover:bg-blue-50 transform hover:scale-105 transition-all duration-300">
                                <i class="fas fa-comments"></i>
                                Open Chat Now
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-3 px-12 py-5 bg-white text-blue-600 font-bold text-xl rounded-xl shadow-xl hover:bg-blue-50 transform hover:scale-105 transition-all duration-300">
                                <i class="fas fa-sign-in-alt"></i>
                                Login to Chat
                            </a>
                        @endauth
                        
                        <!-- Emergency Contact -->
                        <div class="mt-10 pt-8 border-t border-white/20">
                            <p class="text-blue-100 text-sm mb-3">
                                <i class="fas fa-phone-alt mr-2"></i>
                                <strong>Need immediate help?</strong>
                            </p>
                            <p class="text-blue-50 text-sm">
                                Call <strong>1333</strong> (National Mental Health Helpline) or <strong>011-2682535</strong> (Sumithrayo)
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @include('layouts.footer')
    </div>
</body>
</html>
