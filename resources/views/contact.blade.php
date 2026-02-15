<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contact Us - UniPulse</title>
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


<!-- ================= HERO (EXACT SAME) ================= -->
<section class="hero-gradient text-white py-20 md:py-28 relative overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 relative z-10 text-center">
        <span class="px-5 py-2 bg-white/10 backdrop-blur-sm rounded-full text-sm font-semibold border border-white/20 inline-block mb-6">
            <i class="fas fa-envelope mr-2"></i>Get in Touch
        </span>


        <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6">
            Contact Us
        </h1>


        <p class="text-xl md:text-2xl text-blue-100 max-w-3xl mx-auto leading-relaxed">
            Have questions, feedback, or partnership ideas?
            UniPulse is always ready to listen.
        </p>
    </div>


    <!-- EXACT SAME WAVE -->
    <div class="absolute bottom-0 left-0 w-full">
        <svg viewBox="0 0 1440 120" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z"
                  fill="white"/>
        </svg>
    </div>
</section>
<!-- ================= END HERO ================= -->



<!-- ================= CONTACT INFO ================= -->
<section class="py-24 bg-white">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16">
            <h2 class="text-4xl md:text-5xl font-extrabold mb-4">
                Get in <span class="gradient-text">Touch</span>
            </h2>
            <p class="text-xl text-gray-600">
                Multiple ways to reach UniPulse support and partnerships team.
            </p>
        </div>


        <div class="grid md:grid-cols-3 gap-8">
            <div class="bg-white rounded-2xl p-8 shadow-md text-center">
                <i class="fas fa-map-marker-alt text-3xl text-blue-600 mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Address</h3>
                <p class="text-gray-600">
                    No.17, Uni Lane<br>Colombo 07, Sri Lanka
                </p>
            </div>


            <div class="bg-white rounded-2xl p-8 shadow-md text-center">
                <i class="fas fa-phone-alt text-3xl text-blue-600 mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Phone</h3>
                <p class="text-gray-600">
                    +94 11 284 0971<br>Mon – Fri (9AM – 5PM)
                </p>
            </div>


            <div class="bg-white rounded-2xl p-8 shadow-md text-center">
                <i class="fas fa-envelope text-3xl text-blue-600 mb-4"></i>
                <h3 class="text-xl font-bold mb-2">Email</h3>
                <p class="text-gray-600">
                    info@unipulse.com<br>support@unipulse.com
                </p>
            </div>
        </div>
    </div>
</section>



<!-- ================= CONTACT FORM (OPTION 1) ================= -->
<section class="py-24 bg-gray-50">
    <div class="max-w-3xl mx-auto px-6">
        <div class="bg-white rounded-3xl shadow-xl p-10">
            <h3 class="text-3xl font-extrabold text-center mb-8">
                Send Us a Message
            </h3>


            <form onsubmit="sendMail(event)">
                <div class="grid gap-6">
                    <input id="name" class="border rounded-xl px-4 py-3" placeholder="Your Name" required>
                    <input id="email" type="email" class="border rounded-xl px-4 py-3" placeholder="Your Email" required>
                    <input id="subject" class="border rounded-xl px-4 py-3" placeholder="Subject">
                    <textarea id="message" rows="5" class="border rounded-xl px-4 py-3" placeholder="Message" required></textarea>


                    <button class="bg-blue-600 text-white font-bold py-4 rounded-xl hover:bg-blue-700 transition">
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>


@include('layouts.footer')


</div>


<!-- ================= MAILTO SCRIPT ================= -->
<script>
function sendMail(e) {
    e.preventDefault();


    const name = document.getElementById("name").value;
    const email = document.getElementById("email").value;
    const subject = document.getElementById("subject").value || "Contact from UniPulse Website";
    const message = document.getElementById("message").value;


    const body =
        `Name: ${name}\n` +
        `Email: ${email}\n\n` +
        `Message:\n${message}`;


    window.location.href =
        `mailto:info@unipulse.com?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
}
</script>


</body>
</html>



