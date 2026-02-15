<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Risk Detection - UniPulse</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/UP.jpg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">


    <!-- Vite -->
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
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
            opacity: 0.4;
        }
    </style>
</head>


<body class="bg-gray-50">
<div class="min-h-screen">


@include('layouts.header')


<!-- ================= HERO ================= -->
<section class="hero-gradient text-white py-20 md:py-28 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 relative z-10 text-center">
        <span class="px-5 py-2 bg-white/10 backdrop-blur-sm rounded-full text-sm font-semibold border border-white/20 inline-block mb-6">
            <i class="fas fa-heart-pulse mr-2"></i>Wellbeing Support
        </span>


        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6">
            Understand Your Wellbeing, Week by Week
        </h1>


        <p class="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto leading-relaxed">
            Track stress, mood, and engagement through short weekly check-ins
            and gain supportive insights to help you stay balanced.
        </p>


        <div class="mt-10 flex justify-center gap-4">
            <a href="#" class="bg-white text-blue-600 font-bold px-8 py-4 rounded-xl shadow hover:bg-blue-50 transition">
                Get Started
            </a>
            <a href="#how-it-works" class="border border-white px-8 py-4 rounded-xl font-semibold hover:bg-white/10 transition">
                Learn How It Works
            </a>
        </div>
    </div>


    <!-- SAME WAVE -->
    <div class="absolute bottom-0 left-0 w-full">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H0Z"
                  fill="white"/>
        </svg>
    </div>
</section>


<!-- ================= WHAT THIS SERVICE DOES ================= -->
<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-extrabold mb-4">
                What This <span class="gradient-text">Service Does</span>
            </h2>
            <p class="text-xl text-gray-600">
                Looks at wellbeing over time — not just one week
            </p>
        </div>


        <div class="grid md:grid-cols-4 gap-8 text-center">
            <div class="bg-white rounded-2xl p-8 shadow-md">
                <i class="fas fa-calendar-check text-3xl text-blue-600 mb-4"></i>
                <h3 class="font-bold text-lg">Weekly Check-ins</h3>
                <p class="text-gray-600">Short, simple wellbeing questions</p>
            </div>


            <div class="bg-white rounded-2xl p-8 shadow-md">
                <i class="fas fa-chart-line text-3xl text-blue-600 mb-4"></i>
                <h3 class="font-bold text-lg">Tracks Changes</h3>
                <p class="text-gray-600">Understands trends over weeks</p>
            </div>


            <div class="bg-white rounded-2xl p-8 shadow-md">
                <i class="fas fa-triangle-exclamation text-3xl text-blue-600 mb-4"></i>
                <h3 class="font-bold text-lg">Early Signals</h3>
                <p class="text-gray-600">Detects warning signs early</p>
            </div>


            <div class="bg-white rounded-2xl p-8 shadow-md">
                <i class="fas fa-hand-holding-heart text-3xl text-blue-600 mb-4"></i>
                <h3 class="font-bold text-lg">Supportive Guidance</h3>
                <p class="text-gray-600">Helpful suggestions, not judgment</p>
            </div>
        </div>
    </div>
</section>


<!-- ================= HOW IT WORKS ================= -->
<section id="how-it-works" class="py-24 bg-gray-50">
    <div class="max-w-7xl mx-auto px-6 text-center">
        <h2 class="text-4xl md:text-5xl font-extrabold mb-16">
            How It <span class="gradient-text">Works</span>
        </h2>


        <div class="grid md:grid-cols-4 gap-12">
            <div>
                <div class="w-16 h-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-6">1</div>
                <p class="text-gray-600">Answer a short weekly survey</p>
            </div>
            <div>
                <div class="w-16 h-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-6">2</div>
                <p class="text-gray-600">Your wellbeing score is updated</p>
            </div>
            <div>
                <div class="w-16 h-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-6">3</div>
                <p class="text-gray-600">Trends are reviewed across weeks</p>
            </div>
            <div>
                <div class="w-16 h-16 bg-blue-600 text-white rounded-2xl flex items-center justify-center text-2xl font-bold mx-auto mb-6">4</div>
                <p class="text-gray-600">Insights are shown clearly to you</p>
            </div>
        </div>
    </div>
</section>


<!-- ================= PRIVACY ================= -->
<section class="py-24 bg-white">
    <div class="max-w-4xl mx-auto px-6 text-center">
        <h2 class="text-4xl font-extrabold mb-8">Privacy & Safety</h2>
        <p class="text-gray-600 mb-3">🔒 Your data is private and secure</p>
        <p class="text-gray-600 mb-3">🔒 Used only for wellbeing support</p>
        <p class="text-gray-600 mb-3">🔒 No public sharing</p>
        <p class="text-gray-600">🔒 You control what you submit</p>
    </div>
</section>


<!-- ================= CTA ================= -->
<section class="py-24 bg-gray-50">
    <div class="max-w-4xl mx-auto px-6">
        <div class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-3xl p-12 text-center text-white shadow-2xl">
            <h2 class="text-3xl md:text-4xl font-extrabold mb-6">
                A few minutes each week can make a real difference
            </h2>
            <a href="#" class="bg-white text-blue-600 font-bold px-10 py-4 rounded-xl hover:bg-blue-50 transition">
                Start Your Wellbeing Check
            </a>
        </div>
    </div>
</section>


@include('layouts.footer')


</div>
</body>
</html>
