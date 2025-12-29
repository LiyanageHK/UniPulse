<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms of Service - UniPulse</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Vite / Assets -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        body {
            font-family: 'Poppins', 'Figtree', sans-serif;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen">
        @include('layouts.header')

        <!-- Terms of Service Content -->
        <section class="py-20 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
                        Terms of <span class="bg-gradient-to-r from-blue-600 to-blue-400 bg-clip-text text-transparent">Service</span>
                    </h1>
                    <p class="text-lg text-gray-600">Last Updated: December 6, 2025</p>
                </div>

                <div class="prose prose-lg max-w-none">
                    <!-- Introduction -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Introduction</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            Welcome to UniPulse. These Terms of Service ("Terms") govern your access to and use of the UniPulse platform, 
                            including our website, mobile applications, and related services (collectively, the "Service"). By accessing or 
                            using our Service, you agree to be bound by these Terms.
                        </p>
                    </div>

                    <!-- Acceptance of Terms -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Acceptance of Terms</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            By creating an account or using UniPulse, you acknowledge that you have read, understood, and agree to be bound 
                            by these Terms and our Privacy Policy. If you do not agree to these Terms, please do not use our Service.
                        </p>
                    </div>

                    <!-- Eligibility -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">3. Eligibility</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            UniPulse is intended for use by students enrolled in participating educational institutions. You must be at 
                            least 18 years old or have parental consent to use our Service. By using UniPulse, you represent and warrant 
                            that you meet these eligibility requirements.
                        </p>
                    </div>

                    <!-- User Accounts -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">4. User Accounts</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            You are responsible for maintaining the confidentiality of your account credentials and for all activities that 
                            occur under your account. You agree to notify us immediately of any unauthorized use of your account. UniPulse 
                            reserves the right to suspend or terminate accounts that violate these Terms.
                        </p>
                    </div>

                    <!-- Use of Service -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Use of Service</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            You agree to use UniPulse only for lawful purposes and in accordance with these Terms. You agree not to:
                        </p>
                        <ul class="list-disc pl-6 mb-4 text-gray-700">
                            <li class="mb-2">Use the Service in any way that violates applicable laws or regulations</li>
                            <li class="mb-2">Impersonate any person or entity or misrepresent your affiliation with any person or entity</li>
                            <li class="mb-2">Interfere with or disrupt the Service or servers or networks connected to the Service</li>
                            <li class="mb-2">Attempt to gain unauthorized access to any portion of the Service</li>
                            <li class="mb-2">Use the Service to harass, abuse, or harm another person</li>
                        </ul>
                    </div>

                    <!-- AI Services and Counseling -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">6. AI Services and Counseling</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            UniPulse provides AI-powered support services and connections to professional counselors. While we strive to 
                            provide accurate and helpful information, our AI services are not a substitute for professional medical advice, 
                            diagnosis, or treatment. In case of emergency, please contact emergency services immediately.
                        </p>
                    </div>

                    <!-- Privacy and Data Protection -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">7. Privacy and Data Protection</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            Your privacy is important to us. Please review our Privacy Policy to understand how we collect, use, and 
                            protect your personal information. By using UniPulse, you consent to our data practices as described in our 
                            Privacy Policy.
                        </p>
                    </div>

                    <!-- Intellectual Property -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">8. Intellectual Property</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            All content, features, and functionality of UniPulse, including but not limited to text, graphics, logos, 
                            and software, are the exclusive property of UniPulse and are protected by copyright, trademark, and other 
                            intellectual property laws.
                        </p>
                    </div>

                    <!-- Limitation of Liability -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">9. Limitation of Liability</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            To the maximum extent permitted by law, UniPulse shall not be liable for any indirect, incidental, special, 
                            consequential, or punitive damages arising out of or relating to your use of the Service.
                        </p>
                    </div>

                    <!-- Changes to Terms -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">10. Changes to Terms</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            We reserve the right to modify these Terms at any time. We will notify you of any changes by posting the new 
                            Terms on this page and updating the "Last Updated" date. Your continued use of the Service after such changes 
                            constitutes your acceptance of the new Terms.
                        </p>
                    </div>

                    <!-- Contact Information -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">11. Contact Us</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            If you have any questions about these Terms, please contact us at:
                        </p>
                        <div class="bg-blue-50 rounded-lg p-6 mt-4">
                            <p class="text-gray-700 mb-2"><strong>Email:</strong> info@unipulse.com</p>
                            <p class="text-gray-700 mb-2"><strong>Phone:</strong> +94 11 284 0971</p>
                            <p class="text-gray-700"><strong>Address:</strong> No. 17, Uni Lane, Colombo 07, Sri Lanka</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @include('layouts.footer')
    </div>
</body>
</html>
