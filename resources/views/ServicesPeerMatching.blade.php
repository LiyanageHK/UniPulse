<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Peer Matching - UniPulse</title>
        <link rel="icon" type="image/jpeg" href="{{ asset('images/UP.jpg') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .hero-gradient {
                background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);
                position: relative;
            }
            .hero-gradient::before {
                content: '';
                position: absolute;
                top: 0; left: 0; right: 0; bottom: 0;
                background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
                opacity: 0.35;
            }
            .feature-card { transition: all 0.4s cubic-bezier(0.4,0,0.2,1); border: 1px solid #e5e7eb; }
            .feature-card:hover { transform: translateY(-12px); box-shadow: 0 25px 50px -12px rgba(37,99,235,0.25); border-color: #93c5fd; }
            .gradient-text { background: linear-gradient(135deg,#2563eb 0%,#3b82f6 100%); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
        </style>
    </head>
    <body class="font-sans antialiased bg-gray-50">
        @include('layouts.header')

<div class="min-h-screen">
    <!-- Hero Section -->
    <section class="hero-gradient text-white py-20 md:py-28 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center">
                <div class="inline-block mb-6">
                    <span class="px-5 py-2 bg-white/10 backdrop-blur-sm rounded-full text-sm font-semibold border border-white/20">
                        <i class="fas fa-users mr-2"></i>Peer Matching
                    </span>
                </div>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 leading-tight"> Peer Matching System</h1>
                <p class="text-xl md:text-2xl mb-10 text-blue-100 max-w-3xl mx-auto leading-relaxed">
                    UniPulse uses algorithms to connect university freshers with peers who match their personality, academic background, interests, and communication preferences. Our system ensures balanced, effective, and personalized peer group formation.
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- COMPATIBILITY SCORING SYSTEM -->
        <div class="mb-16">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">How Our Matching Algorithm Works</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Our system uses a weighted compatibility scoring model that analyzes multiple student characteristics to ensure perfect peer matches.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
                <!-- Interest & Hobby Match -->
                <div class="bg-blue-50 rounded-xl shadow-lg border-t-4 border-blue-600 hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="p-8">
                        <div class="flex items-center mb-4">
                            <div class="text-4xl mr-4">🎮</div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">Interest & Hobby</h3>
                                <p class="text-2xl font-bold text-blue-600">25%</p>
                            </div>
                        </div>
                        <p class="text-gray-700 mb-4">
                            Top interests, preferred learning styles, and group activity participation.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center"><span class="text-blue-500 mr-2">•</span> Common hobbies</li>
                            <li class="flex items-center"><span class="text-blue-500 mr-2">•</span> Extracurricular activities</li>
                            <li class="flex items-center"><span class="text-blue-500 mr-2">•</span> Learning style preferences</li>
                        </ul>
                    </div>
                </div>

                <!-- Academic Compatibility -->
                <div class="bg-blue-50 rounded-xl shadow-lg border-t-4 border-blue-600 hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="p-8">
                        <div class="flex items-center mb-4">
                            <div class="text-4xl mr-4">📖</div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">Academic</h3>
                                <p class="text-2xl font-bold text-blue-600">20%</p>
                            </div>
                        </div>
                        <p class="text-gray-700 mb-4">
                            Faculty, academic stream, and course enrollment alignment.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center"><span class="text-blue-600 mr-2">•</span> Faculty alignment</li>
                            <li class="flex items-center"><span class="text-blue-600 mr-2">•</span> Similar courses</li>
                            <li class="flex items-center"><span class="text-blue-600 mr-2">•</span> Academic level</li>
                        </ul>
                    </div>
                </div>

                <!-- Personality Compatibility -->
                <div class="bg-blue-50 rounded-xl shadow-lg border-t-4 border-blue-600 hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="p-8">
                        <div class="flex items-center mb-4">
                            <div class="text-4xl mr-4">🙂</div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">Personality</h3>
                                <p class="text-2xl font-bold text-blue-600">20%</p>
                            </div>
                        </div>
                        <p class="text-gray-700 mb-4">
                            Introvert-extrovert scale, group comfort level, and stress tolerance.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center"><span class="text-blue-600 mr-2">•</span> Personality type match</li>
                            <li class="flex items-center"><span class="text-blue-600 mr-2">•</span> Social comfort levels</li>
                            <li class="flex items-center"><span class="text-blue-600 mr-2">•</span> Stress management</li>
                        </ul>
                    </div>
                </div>

                <!-- Emotional & Wellbeing -->
                <div class="bg-blue-50 rounded-xl shadow-lg border-t-4 border-blue-600 hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="p-8">
                        <div class="flex items-center mb-4">
                            <div class="text-4xl mr-4">❤️</div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">Emotional & Wellbeing</h3>
                                <p class="text-2xl font-bold text-blue-600">15%</p>
                            </div>
                        </div>
                        <p class="text-gray-700 mb-4">
                            Overall mood, task overwhelm, and sense of belonging from weekly check-ins.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center"><span class="text-blue-600 mr-2">•</span> Weekly mood tracking</li>
                            <li class="flex items-center"><span class="text-blue-600 mr-2">•</span> Wellbeing alignment</li>
                            <li class="flex items-center"><span class="text-blue-600 mr-2">•</span> Support needs</li>
                        </ul>
                    </div>
                </div>

                <!-- Communication Preference -->
                <div class="bg-blue-50 rounded-xl shadow-lg border-t-4 border-blue-600 hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="p-8">
                        <div class="flex items-center mb-4">
                            <div class="text-4xl mr-4">🗪</div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">Communication</h3>
                                <p class="text-2xl font-bold text-blue-600">10%</p>
                            </div>
                        </div>
                        <p class="text-gray-700 mb-4">
                            Preferred communication methods and interaction styles.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center"><span class="text-blue-600 mr-2">•</span> Chat preferences</li>
                            <li class="flex items-center"><span class="text-blue-600 mr-2">•</span> Response time</li>
                            <li class="flex items-center"><span class="text-blue-600 mr-2">•</span> Interaction style</li>
                        </ul>
                    </div>
                </div>

                <!-- Social Preferences -->
                <div class="bg-blue-50 rounded-xl shadow-lg border-t-4 border-blue-600 hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="p-8">
                        <div class="flex items-center mb-4">
                            <div class="text-4xl mr-4">🕺</div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900">Social Preferences</h3>
                                <p class="text-2xl font-bold text-blue-600">10%</p>
                            </div>
                        </div>
                        <p class="text-gray-700 mb-4">
                            Preferred social settings and group interaction types.
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600">
                            <li class="flex items-center"><span class="text-blue-600 mr-2">•</span> Group vs. one-on-one</li>
                            <li class="flex items-center"><span class="text-blue-600 mr-2">•</span> Social setting comfort</li>
                            <li class="flex items-center"><span class="text-blue-600 mr-2">•</span> Event participation</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
<br><br>
        <!-- MAIN FEATURES WORKFLOW -->
        <div class="mb-16">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Your Peer Matching Journey</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto"></p>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    A complete workflow designed to help you find your perfect peer connections and build lasting relationships.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Step 1 -->
                <div class="bg-white rounded-xl p-8 border-l-4 border-blue-500 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <h3 class="text-xl font-bold text-gray-900">Complete Your Profile</h3>
                    <p class="text-gray-700 mt-2">
                        Take a detailed survey covering your personality, academic interests, hobbies, lifestyle, and well-being to create your unique profile.
                    </p>
                </div>

                <!-- Step 2 -->
                <div class="bg-white rounded-xl p-8 border-l-4 border-purple-500 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <h3 class="text-xl font-bold text-gray-900">Weighted Scoring</h3>
                    <p class="text-gray-700 mt-2">
                        Our system analyzes your profile using weighted factors, with personality and academic alignment weighted more heavily for better matches.
                    </p>
                </div>

                <!-- Step 3 -->
                <div class="bg-white rounded-xl p-8 border-l-4 border-indigo-500 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <h3 class="text-xl font-bold text-gray-900">Get Matched Peers</h3>
                    <p class="text-gray-700 mt-2">
                        Receive a ranked list of suggested peers with their compatibility percentage and common traits you share.
                    </p>
                </div>

                <!-- Step 4 -->
                <div class="bg-white rounded-xl p-8 border-l-4 border-green-500 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <h3 class="text-xl font-bold text-gray-900">Filter & Connect</h3>
                    <p class="text-gray-700 mt-2">
                        Filter matches by hobby, faculty, social setting, or communication method. Send connection requests to peers you want to connect with.
                    </p>
                </div>

                <!-- Step 5 -->
                <div class="bg-white rounded-xl p-8 border-l-4 border-orange-500 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <h3 class="text-xl font-bold text-gray-900">Chat & Groups</h3>
                    <p class="text-gray-700 mt-2">
                        Engage in one-to-one chats and form study groups or support circles. Chat begins only after connection acceptance.
                    </p>
                </div>

                <!-- Step 6 -->
                <div class="bg-white rounded-xl p-8 border-l-4 border-pink-500 shadow-lg hover:shadow-xl transition-shadow duration-300">
                    <h3 class="text-xl font-bold text-gray-900">Weekly Check-ins</h3>
                    <p class="text-gray-700 mt-2">
                        Submit weekly updates on stress, mood, and interaction levels. The system dynamically adjusts recommendations based on your wellbeing.
                    </p>
                </div>
            </div>
        </div>
<br><br>
        <!-- CORE FEATURES SECTION -->
        <div class="mb-16">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Core Features</h2>
                <p class="text-lg text-gray-600">Everything you need to build meaningful peer relationships</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Feature 1: Suggestions -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300">
                    <div class="h-16 bg-gradient-to-r from-blue-500 to-blue-600"></div>
                    <div class="p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                            <span class="text-3xl mr-3">💡</span> Suggestions
                        </h3>
                        <p class="text-gray-700 mb-6">
                            Browse peer recommendations with compatibility scores, filter by your preferences, and discover study groups.
                        </p>
                        <ul class="space-y-3 text-sm text-gray-700">
                            <li class="flex items-start">
                                <span class="text-blue-500 font-bold mr-3">✓</span>
                                <span>Ranked peer suggestions with compatibility %</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-blue-500 font-bold mr-3">✓</span>
                                <span>Advanced filtering options</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-blue-500 font-bold mr-3">✓</span>
                                <span>Create & join peer groups</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-blue-500 font-bold mr-3">✓</span>
                                <span>Re-matching algorithm</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Feature 2: Connections -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300">
                    <div class="h-16 bg-gradient-to-r from-purple-500 to-purple-600"></div>
                    <div class="p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                            <span class="text-3xl mr-3">🌐</span> Connections
                        </h3>
                        <p class="text-gray-700 mb-6">
                            View your social network with visual graphs showing active connections and strength of relationships.
                        </p>
                        <ul class="space-y-3 text-sm text-gray-700">
                            <li class="flex items-start">
                                <span class="text-purple-500 font-bold mr-3">✓</span>
                                <span>Social network visualization</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-purple-500 font-bold mr-3">✓</span>
                                <span>Active peer connections</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-purple-500 font-bold mr-3">✓</span>
                                <span>Top Interaction Badges</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-purple-500 font-bold mr-3">✓</span>
                                <span>Suggested connections</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Feature 3: Chat -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300">
                    <div class="h-16 bg-gradient-to-r from-green-500 to-green-600"></div>
                    <div class="p-8">
                        <h3 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                            <span class="text-3xl mr-3">💬</span> Chat
                        </h3>
                        <p class="text-gray-700 mb-6">
                            Secure one-to-one and group chat with peers. Connection requests require acceptance before messaging begins.
                        </p>
                        <ul class="space-y-3 text-sm text-gray-700">
                            <li class="flex items-start">
                                <span class="text-green-500 font-bold mr-3">✓</span>
                                <span>One-to-one messaging</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 font-bold mr-3">✓</span>
                                <span>Group chat conversations</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 font-bold mr-3">✓</span>
                                <span>Secure encryption</span>
                            </li>
                            <li class="flex items-start">
                                <span class="text-green-500 font-bold mr-3">✓</span>
                                <span>File sharing</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>



    </div>
</div>

@include('layouts.footer')

    </body>
</html>
