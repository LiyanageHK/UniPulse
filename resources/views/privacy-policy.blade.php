<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy - UniPulse</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/UP.jpg') }}">

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

        <!-- Privacy Policy Content -->
        <section class="py-20 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
                        Privacy <span class="bg-gradient-to-r from-blue-600 to-blue-400 bg-clip-text text-transparent">Policy</span>
                    </h1>
                    <p class="text-lg text-gray-600">Last Updated: December 6, 2025</p>
                </div>

                <div class="prose prose-lg max-w-none">
                    <!-- Introduction -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">1. Introduction</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            At UniPulse, we take your privacy seriously. This Privacy Policy explains how we collect, use, disclose, and 
                            safeguard your information when you use our platform. Please read this privacy policy carefully. If you do not 
                            agree with the terms of this privacy policy, please do not access the Service.
                        </p>
                    </div>

                    <!-- Information We Collect -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">2. Information We Collect</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            We collect information that you provide directly to us, including:
                        </p>
                        <ul class="list-disc pl-6 mb-4 text-gray-700">
                            <li class="mb-2"><strong>Personal Information:</strong> Name, email address, student ID, and contact information</li>
                            <li class="mb-2"><strong>Profile Information:</strong> Academic details, interests, preferences, and wellbeing data</li>
                            <li class="mb-2"><strong>Communication Data:</strong> Messages exchanged with our AI chatbot and counselors</li>
                            <li class="mb-2"><strong>Usage Data:</strong> Information about how you interact with our Service</li>
                            <li class="mb-2"><strong>Health Information:</strong> Mental health assessments and wellbeing check-ins</li>
                        </ul>
                    </div>

                    <!-- How We Use Your Information -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">3. How We Use Your Information</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            We use the information we collect to:
                        </p>
                        <ul class="list-disc pl-6 mb-4 text-gray-700">
                            <li class="mb-2">Provide, maintain, and improve our Service</li>
                            <li class="mb-2">Personalize your experience and deliver tailored support</li>
                            <li class="mb-2">Connect you with appropriate counselors and peer support</li>
                            <li class="mb-2">Monitor and analyze usage patterns to enhance our Service</li>
                            <li class="mb-2">Detect and prevent potential crisis situations</li>
                            <li class="mb-2">Communicate with you about the Service</li>
                            <li class="mb-2">Comply with legal obligations</li>
                        </ul>
                    </div>

                    <!-- Data Security -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">4. Data Security</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            We implement enterprise-grade security measures to protect your personal information. All data is encrypted 
                            both in transit and at rest. We use secure servers and follow industry best practices to prevent unauthorized 
                            access, disclosure, or modification of your information.
                        </p>
                    </div>

                    <!-- Confidentiality -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">5. Confidentiality</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            Your conversations with our AI chatbot and counselors are confidential. However, we may be required to break 
                            confidentiality if there is an imminent risk of harm to yourself or others, or if required by law. In such 
                            cases, we will disclose only the minimum necessary information to appropriate parties.
                        </p>
                    </div>

                    <!-- Data Sharing -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">6. Data Sharing and Disclosure</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            We do not sell your personal information. We may share your information with:
                        </p>
                        <ul class="list-disc pl-6 mb-4 text-gray-700">
                            <li class="mb-2"><strong>Your Educational Institution:</strong> Aggregated, anonymized data for program evaluation</li>
                            <li class="mb-2"><strong>Professional Counselors:</strong> When you choose to connect with them through our Service</li>
                            <li class="mb-2"><strong>Service Providers:</strong> Third-party vendors who assist in operating our Service</li>
                            <li class="mb-2"><strong>Legal Authorities:</strong> When required by law or to protect rights and safety</li>
                        </ul>
                    </div>

                    <!-- Your Rights -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">7. Your Privacy Rights</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            You have the right to:
                        </p>
                        <ul class="list-disc pl-6 mb-4 text-gray-700">
                            <li class="mb-2">Access and review your personal information</li>
                            <li class="mb-2">Request correction of inaccurate data</li>
                            <li class="mb-2">Request deletion of your data (subject to legal requirements)</li>
                            <li class="mb-2">Opt-out of certain data collection and processing</li>
                            <li class="mb-2">Export your data in a portable format</li>
                        </ul>
                    </div>

                    <!-- Data Retention -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">8. Data Retention</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            We retain your personal information for as long as necessary to provide our Service and comply with legal 
                            obligations. When you delete your account, we will delete or anonymize your personal information within 30 days, 
                            except where we are required to retain it for legal or regulatory purposes.
                        </p>
                    </div>

                    <!-- Cookies and Tracking -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">9. Cookies and Tracking Technologies</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            We use cookies and similar tracking technologies to improve your experience, analyze usage patterns, and 
                            maintain security. You can control cookie preferences through your browser settings, though some features 
                            may not function properly if cookies are disabled.
                        </p>
                    </div>

                    <!-- Children's Privacy -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">10. Children's Privacy</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            Our Service is not intended for individuals under 18 without parental consent. We do not knowingly collect 
                            personal information from children under 18 without proper authorization. If we become aware that we have 
                            collected personal information from a child without verification of parental consent, we will take steps to 
                            remove that information.
                        </p>
                    </div>

                    <!-- Changes to Policy -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">11. Changes to This Privacy Policy</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new 
                            Privacy Policy on this page and updating the "Last Updated" date. We encourage you to review this Privacy 
                            Policy periodically for any changes.
                        </p>
                    </div>

                    <!-- Contact Information -->
                    <div class="mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">12. Contact Us</h2>
                        <p class="text-gray-700 leading-relaxed text-justify mb-4">
                            If you have any questions or concerns about this Privacy Policy or our data practices, please contact us at:
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
