<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Home - UniPulse</title>

		<!-- Fonts -->
		<link rel="preconnect" href="https://fonts.bunny.net">
		<link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
		<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

		<!-- Vite / Assets -->
		@vite(['resources/css/app.css', 'resources/js/app.js'])

		<!-- Premium Home Page Styling -->
		<link rel="stylesheet" href="{{ asset('css/home-improvements.css') }}">

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
			.service-card {
				transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
				background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
			}
			.service-card:hover {
				transform: scale(1.05);
				box-shadow: 0 30px 60px -12px rgba(37, 99, 235, 0.3);
			}
			.pulse-animation {
				animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
			}
			@keyframes pulse {
				0%, 100% {
					opacity: 1;
				}
				50% {
					opacity: .7;
				}
			}
			.stat-number {
				font-family: 'Poppins', sans-serif;
				font-weight: 800;
			}
			.glow-effect {
				box-shadow: 0 0 30px rgba(37, 99, 235, 0.3);
			}
			.gradient-text {
				background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
				-webkit-background-clip: text;
				-webkit-text-fill-color: transparent;
				background-clip: text;
			}
			@keyframes float {
				0%, 100% {
					transform: translateY(0px);
				}
				50% {
					transform: translateY(-20px);
				}
			}
			.float-animation {
				animation: float 6s ease-in-out infinite;
			}
			.testimonial-card {
				transition: all 0.3s ease;
			}
			.testimonial-card:hover {
				transform: scale(1.02);
				box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
			}
		</style>
	</head>
	<body class="font-sans antialiased bg-gray-50">
		<div class="min-h-screen">
			@include('layouts.header')

			<!-- Hero Section -->
			<section class="hero-gradient text-white py-20 md:py-32 relative overflow-hidden">
				<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
					<div class="grid md:grid-cols-2 gap-12 items-center">
						<!-- Left Content -->
						<div class="text-center md:text-left">
							<div class="inline-block mb-4">
								<span class="px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-sm font-semibold border border-white/20">
									🎓 Your Wellbeing Companion
								</span>
							</div>
							<h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold mb-6 leading-tight">
								Welcome to <br><span class="text-white drop-shadow-lg">UniPulse</span>
							</h1>
							<p class="text-xl md:text-2xl mb-6 text-blue-50 font-medium">
								Empowering Student Wellbeing & Academic Excellence
							</p>
							<p class="text-lg mb-10 text-blue-100 leading-relaxed max-w-lg">
								A comprehensive AI-powered platform designed to support your mental health, 
								enhance engagement, and connect you with personalized campus resources.
							</p>
							
							<!-- CTA Buttons -->
							<div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
								@if (Route::has('login'))
									@auth
										<a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-blue-600 font-bold rounded-xl shadow-2xl hover:bg-blue-50 hover:shadow-xl transform hover:scale-105 transition-all duration-300">
											<i class="fas fa-tachometer-alt"></i>
											Go to Dashboard
										</a>
									@else
										<a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-blue-600 font-bold rounded-xl shadow-2xl hover:bg-blue-50 hover:shadow-xl transform hover:scale-105 transition-all duration-300">
											<i class="fas fa-sign-in-alt"></i>
											Log In
										</a>
										@if (Route::has('register'))
											<a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-transparent text-white font-bold rounded-xl border-2 border-white hover:bg-white hover:text-blue-600 transform hover:scale-105 transition-all duration-300">
												<i class="fas fa-user-plus"></i>
												Get Started Free
											</a>
										@endif
									@endauth
								@endif
							</div>
						</div>

						<!-- Right Content - Logo/Image -->
						<div class="flex justify-center md:justify-end">
							<div class="relative float-animation">
								<div class="absolute inset-0 bg-white rounded-full blur-3xl opacity-20 pulse-animation"></div>
								<div class="relative bg-white/10 backdrop-blur-sm p-8 rounded-3xl border border-white/20">
									<img src="{{ asset('images/UP.jpg') }}" alt="UniPulse Logo" class="h-64 w-64 md:h-80 md:w-80 rounded-2xl shadow-2xl transform hover:rotate-3 transition-transform duration-500">
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<!-- Wave Separator -->
				<div class="absolute bottom-0 left-0 w-full">
					<svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="white"/>
					</svg>
				</div>
			</section>

			<!-- Statistics Section -->
			<section class="py-20 bg-white">
				<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
					<div class="grid grid-cols-2 md:grid-cols-4 gap-8">
						<div class="text-center p-6 rounded-xl bg-gradient-to-br from-blue-50 to-white">
							<div class="stat-number text-5xl md:text-6xl font-bold text-blue-600 mb-2">5000+</div>
							<p class="text-gray-700 font-semibold text-lg">Active Students</p>
							<p class="text-gray-500 text-sm mt-1">Supported Daily</p>
						</div>
						<div class="text-center p-6 rounded-xl bg-gradient-to-br from-blue-50 to-white">
							<div class="stat-number text-5xl md:text-6xl font-bold text-blue-600 mb-2">100+</div>
							<p class="text-gray-700 font-semibold text-lg">Expert Counselors</p>
							<p class="text-gray-500 text-sm mt-1">Ready to Help</p>
						</div>
						<div class="text-center p-6 rounded-xl bg-gradient-to-br from-blue-50 to-white">
							<div class="stat-number text-5xl md:text-6xl font-bold text-blue-600 mb-2">24/7</div>
							<p class="text-gray-700 font-semibold text-lg">Support Available</p>
							<p class="text-gray-500 text-sm mt-1">Always Here</p>
						</div>
						<div class="text-center p-6 rounded-xl bg-gradient-to-br from-blue-50 to-white">
							<div class="stat-number text-5xl md:text-6xl font-bold text-blue-600 mb-2">98%</div>
							<p class="text-gray-700 font-semibold text-lg">Satisfaction Rate</p>
							<p class="text-gray-500 text-sm mt-1">User Feedback</p>
						</div>
					</div>
				</div>
			</section>

			<!-- Our Services Section -->
			<section class="py-24 bg-gradient-to-b from-white to-gray-50">
				<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
					<div class="text-center mb-16">
						<h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
							Our <span class="gradient-text">Services</span>
						</h2>
						<p class="text-xl text-gray-600 max-w-3xl mx-auto">
							Comprehensive solutions designed to support every aspect of your university journey
						</p>
					</div>

					<div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
						<!-- Service 1: Student Profiling -->
						<div class="service-card rounded-2xl p-8 shadow-lg border border-gray-100">
							<div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-6 glow-effect">
								<i class="fas fa-user-graduate text-4xl text-white"></i>
							</div>
							<h3 class="text-2xl font-bold text-gray-900 mb-4">Student Profiling</h3>
							<p class="text-gray-600 leading-relaxed mb-6">
								Personalized profiles that understand your unique needs, preferences, and academic journey to deliver tailored support.
							</p>
							<a href="#" class="inline-flex items-center gap-2 text-blue-600 font-semibold hover:gap-3 transition-all">
								Learn More <i class="fas fa-arrow-right"></i>
							</a>
						</div>

						<!-- Service 2: Risk Detection -->
						<div class="service-card rounded-2xl p-8 shadow-lg border border-gray-100">
							<div class="w-20 h-20 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl flex items-center justify-center mb-6 glow-effect">
								<i class="fas fa-shield-alt text-4xl text-white"></i>
							</div>
							<h3 class="text-2xl font-bold text-gray-900 mb-4">Risk Detection</h3>
							<p class="text-gray-600 leading-relaxed mb-6">
								Advanced AI algorithms monitor wellbeing indicators to provide early intervention and ensure your safety and mental health.
							</p>
							<a href="#" class="inline-flex items-center gap-2 text-blue-600 font-semibold hover:gap-3 transition-all">
								Learn More <i class="fas fa-arrow-right"></i>
							</a>
						</div>

						<!-- Service 3: Conversational Support -->
						<div class="service-card rounded-2xl p-8 shadow-lg border border-gray-100">
							<div class="w-20 h-20 bg-gradient-to-br from-green-500 to-teal-600 rounded-2xl flex items-center justify-center mb-6 glow-effect">
								<i class="fas fa-comments text-4xl text-white"></i>
							</div>
							<h3 class="text-2xl font-bold text-gray-900 mb-4">Conversational Support</h3>
							<p class="text-gray-600 leading-relaxed mb-6">
								24/7 AI-powered chatbot providing instant, empathetic responses and guidance whenever you need someone to talk to.
							</p>
							<a href="{{ route('chat.info') }}" class="inline-flex items-center gap-2 text-blue-600 font-semibold hover:gap-3 transition-all">
								Learn More <i class="fas fa-arrow-right"></i>
							</a>
						</div>

						<!-- Service 4: Peer Matching -->
						<div class="service-card rounded-2xl p-8 shadow-lg border border-gray-100">
							<div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-2xl flex items-center justify-center mb-6 glow-effect">
								<i class="fas fa-users text-4xl text-white"></i>
							</div>
							<h3 class="text-2xl font-bold text-gray-900 mb-4">Peer Matching</h3>
							<p class="text-gray-600 leading-relaxed mb-6">
								Connect with fellow students who share similar experiences, interests, and challenges for mutual support and friendship.
							</p>
							<a href="{{url('chat-support')}}" class="inline-flex items-center gap-2 text-blue-600 font-semibold hover:gap-3 transition-all">
								Learn More <i class="fas fa-arrow-right"></i>
							</a>
						</div>
					</div>
				</div>
			</section>

			<!-- Features Section -->
			<section class="py-24 bg-gradient-to-br from-blue-50 via-white to-blue-50">
				<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
					<div class="text-center mb-16">
						<h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
							Why Choose <span class="gradient-text">UniPulse?</span>
						</h2>
						<p class="text-xl text-gray-600 max-w-3xl mx-auto">
							Cutting-edge features designed to support your complete wellbeing journey
						</p>
					</div>

					<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
						<!-- Feature 1 -->
						<div class="feature-card bg-white rounded-2xl p-8 shadow-md hover:border-blue-300">
							<div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-50 rounded-xl flex items-center justify-center mb-6">
								<i class="fas fa-robot text-3xl text-blue-600"></i>
							</div>
							<h3 class="text-2xl font-bold text-gray-900 mb-3">AI-Powered Insights</h3>
							<p class="text-gray-600 leading-relaxed">
								Intelligent algorithms analyze your wellbeing patterns and provide personalized recommendations for improvement.
							</p>
						</div>

						<!-- Feature 2 -->
						<div class="feature-card bg-white rounded-2xl p-8 shadow-md hover:border-blue-300">
							<div class="w-16 h-16 bg-gradient-to-br from-green-100 to-green-50 rounded-xl flex items-center justify-center mb-6">
								<i class="fas fa-user-md text-3xl text-green-600"></i>
							</div>
							<h3 class="text-2xl font-bold text-gray-900 mb-3">Professional Counseling</h3>
							<p class="text-gray-600 leading-relaxed">
								Access qualified mental health professionals who provide confidential, expert guidance tailored to your needs.
							</p>
						</div>

						<!-- Feature 3 -->
						<div class="feature-card bg-white rounded-2xl p-8 shadow-md hover:border-blue-300">
							<div class="w-16 h-16 bg-gradient-to-br from-red-100 to-red-50 rounded-xl flex items-center justify-center mb-6">
								<i class="fas fa-bell text-3xl text-red-600"></i>
							</div>
							<h3 class="text-2xl font-bold text-gray-900 mb-3">Emergency Response</h3>
							<p class="text-gray-600 leading-relaxed">
								Immediate crisis detection and intervention system ensures help is always available when you need it most.
							</p>
						</div>

						<!-- Feature 4 -->
						<div class="feature-card bg-white rounded-2xl p-8 shadow-md hover:border-blue-300">
							<div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-purple-50 rounded-xl flex items-center justify-center mb-6">
								<i class="fas fa-chart-line text-3xl text-purple-600"></i>
							</div>
							<h3 class="text-2xl font-bold text-gray-900 mb-3">Progress Tracking</h3>
							<p class="text-gray-600 leading-relaxed">
								Visualize your mental health journey with comprehensive analytics and celebrate your achievements.
							</p>
						</div>

						<!-- Feature 5 -->
						<div class="feature-card bg-white rounded-2xl p-8 shadow-md hover:border-blue-300">
							<div class="w-16 h-16 bg-gradient-to-br from-indigo-100 to-indigo-50 rounded-xl flex items-center justify-center mb-6">
								<i class="fas fa-graduation-cap text-3xl text-indigo-600"></i>
							</div>
							<h3 class="text-2xl font-bold text-gray-900 mb-3">Academic Integration</h3>
							<p class="text-gray-600 leading-relaxed">
								Seamlessly connect your wellbeing with academic performance for a holistic university experience.
							</p>
						</div>

						<!-- Feature 6 -->
						<div class="feature-card bg-white rounded-2xl p-8 shadow-md hover:border-blue-300">
							<div class="w-16 h-16 bg-gradient-to-br from-gray-100 to-gray-50 rounded-xl flex items-center justify-center mb-6">
								<i class="fas fa-lock text-3xl text-gray-700"></i>
							</div>
							<h3 class="text-2xl font-bold text-gray-900 mb-3">Privacy & Security</h3>
							<p class="text-gray-600 leading-relaxed">
								Enterprise-grade encryption ensures your conversations and data remain completely confidential and secure.
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
							Get started with UniPulse in three simple steps
						</p>
					</div>

					<div class="grid md:grid-cols-3 gap-12 relative">
						<!-- Connection Lines (Desktop) -->
						<div class="hidden md:block absolute top-1/4 left-0 w-full h-1">
							<div class="relative h-full">
								<div class="absolute left-1/6 right-5/6 h-full border-t-4 border-dashed border-blue-300"></div>
								<div class="absolute left-1/2 right-1/6 h-full border-t-4 border-dashed border-blue-300"></div>
							</div>
						</div>

						<!-- Step 1 -->
						<div class="text-center relative">
							<div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-3xl flex items-center justify-center text-4xl font-black mx-auto mb-8 shadow-2xl glow-effect relative z-10">
								1
							</div>
							<h3 class="text-2xl font-bold text-gray-900 mb-4">Create Your Profile</h3>
							<p class="text-gray-600 leading-relaxed text-lg">
								Sign up in minutes and complete a personalized onboarding assessment to help us understand your unique needs.
							</p>
						</div>

						<!-- Step 2 -->
						<div class="text-center relative">
							<div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-3xl flex items-center justify-center text-4xl font-black mx-auto mb-8 shadow-2xl glow-effect relative z-10">
								2
							</div>
							<h3 class="text-2xl font-bold text-gray-900 mb-4">Access Support</h3>
							<p class="text-gray-600 leading-relaxed text-lg">
								Engage with our AI chatbot, schedule counselor sessions, or connect with peers for immediate support.
							</p>
						</div>

						<!-- Step 3 -->
						<div class="text-center relative">
							<div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-3xl flex items-center justify-center text-4xl font-black mx-auto mb-8 shadow-2xl glow-effect relative z-10">
								3
							</div>
							<h3 class="text-2xl font-bold text-gray-900 mb-4">Track & Grow</h3>
							<p class="text-gray-600 leading-relaxed text-lg">
								Monitor your progress with detailed analytics and achieve your personal wellbeing and academic goals.
							</p>
						</div>
					</div>
				</div>
			</section>

			<!-- Testimonials Section -->
			<section class="py-24 bg-gradient-to-br from-blue-600 to-blue-700">
				<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
				<div class="text-center mb-16">
					<h2 class="text-4xl md:text-5xl font-extrabold text-white mb-4">
						What Students Say
					</h2>
					<p class="text-xl text-blue-100 max-w-3xl mx-auto">
						Real experiences from students who transformed their university life with UniPulse
					</p>
				</div>

				<div class="grid md:grid-cols-3 gap-8">
					<!-- Testimonial 1 -->
					<div class="testimonial-card bg-white rounded-2xl p-8 shadow-lg">
						<div class="flex items-center mb-6">
							<div class="flex text-yellow-400">
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
								</div>
							</div>
							<p class="text-gray-700 text-lg leading-relaxed mb-6 italic">
								"UniPulse has been a lifesaver during my studies. The AI chatbot is always there when I need someone to talk to, and the counselors are incredibly supportive."
							</p>
							<div class="flex items-center gap-4">
								<div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-xl">
									S
								</div>
								<div>
									<p class="font-bold text-gray-900">Sarah Johnson</p>
									<p class="text-gray-600 text-sm">Psychology Major, Year 3</p>
								</div>
							</div>
						</div>

					<!-- Testimonial 2 -->
					<div class="testimonial-card bg-white rounded-2xl p-8 shadow-lg">
						<div class="flex items-center mb-6">
							<div class="flex text-yellow-400">
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
								</div>
							</div>
							<p class="text-gray-700 text-lg leading-relaxed mb-6 italic">
								"The peer matching feature helped me find friends who understood what I was going through. I no longer feel alone in my struggles."
							</p>
							<div class="flex items-center gap-4">
								<div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center text-white font-bold text-xl">
									M
								</div>
								<div>
									<p class="font-bold text-gray-900">Michael Chen</p>
									<p class="text-gray-600 text-sm">Engineering Student, Year 2</p>
								</div>
							</div>
						</div>

					<!-- Testimonial 3 -->
					<div class="testimonial-card bg-white rounded-2xl p-8 shadow-lg">
						<div class="flex items-center mb-6">
							<div class="flex text-yellow-400">
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
									<i class="fas fa-star"></i>
								</div>
							</div>
							<p class="text-gray-700 text-lg leading-relaxed mb-6 italic">
								"The progress tracking tools helped me understand my mental health patterns and celebrate small victories. It's incredibly empowering!"
							</p>
							<div class="flex items-center gap-4">
								<div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center text-white font-bold text-xl">
									E
								</div>
								<div>
									<p class="font-bold text-gray-900">Emily Rodriguez</p>
									<p class="text-gray-600 text-sm">Business Major, Year 4</p>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>

			<!-- FAQ Section -->
			<section class="py-24 bg-gradient-to-b from-gray-50 to-white">
				<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
					<div class="text-center mb-16">
						<h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
							Frequently Asked <span class="gradient-text">Questions</span>
						</h2>
						<p class="text-xl text-gray-600">
							Everything you need to know about UniPulse
						</p>
					</div>

					<div class="space-y-6">
						<!-- FAQ 1 -->
						<div class="bg-white rounded-xl p-6 shadow-md border border-gray-100">
							<h3 class="text-xl font-bold text-gray-900 mb-3 flex items-start gap-3">
								<i class="fas fa-question-circle text-blue-600 mt-1"></i>
								Is UniPulse confidential?
							</h3>
							<p class="text-gray-600 leading-relaxed pl-8">
								Absolutely. All conversations and data are encrypted with enterprise-grade security. Your privacy is our top priority, and we comply with all data protection regulations.
							</p>
						</div>

						<!-- FAQ 2 -->
						<div class="bg-white rounded-xl p-6 shadow-md border border-gray-100">
							<h3 class="text-xl font-bold text-gray-900 mb-3 flex items-start gap-3">
								<i class="fas fa-question-circle text-blue-600 mt-1"></i>
								How much does it cost?
							</h3>
							<p class="text-gray-600 leading-relaxed pl-8">
								UniPulse is completely free for all enrolled students. Your university subscription covers all features including AI chat, counselor sessions, and crisis support.
							</p>
						</div>

						<!-- FAQ 3 -->
						<div class="bg-white rounded-xl p-6 shadow-md border border-gray-100">
							<h3 class="text-xl font-bold text-gray-900 mb-3 flex items-start gap-3">
								<i class="fas fa-question-circle text-blue-600 mt-1"></i>
								What if I'm in crisis?
							</h3>
							<p class="text-gray-600 leading-relaxed pl-8">
								Our crisis detection system monitors conversations for urgent situations and immediately connects you with professional help. Emergency support is available 24/7, 365 days a year.
							</p>
						</div>

						<!-- FAQ 4 -->
						<div class="bg-white rounded-xl p-6 shadow-md border border-gray-100">
							<h3 class="text-xl font-bold text-gray-900 mb-3 flex items-start gap-3">
								<i class="fas fa-question-circle text-blue-600 mt-1"></i>
								Can I use UniPulse on my phone?
							</h3>
							<p class="text-gray-600 leading-relaxed pl-8">
								Yes! UniPulse is fully responsive and works seamlessly on all devices - smartphones, tablets, and computers. Access support wherever you are, whenever you need it.
							</p>
						</div>
					</div>
				</div>
			</section>

			<!-- Final CTA Section - White Background for Contrast -->
			<section class="py-24 bg-white">
				<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
					<div class="bg-gradient-to-br from-blue-600 via-blue-500 to-blue-700 rounded-3xl p-12 md:p-16 text-center shadow-2xl relative overflow-hidden">
						<!-- Decorative Elements -->
						<div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-32 -mt-32"></div>
						<div class="absolute bottom-0 left-0 w-96 h-96 bg-white opacity-5 rounded-full -ml-48 -mb-48"></div>
						
						<div class="relative z-10">
							<h2 class="text-3xl md:text-5xl font-extrabold text-white mb-6">
								Ready to Transform Your University Experience?
							</h2>
							<p class="text-xl md:text-2xl mb-10 text-blue-50">
								Join thousands of students prioritizing their mental health and academic success.
							</p>
							@if (Route::has('login'))
								@guest
									<div class="flex flex-col sm:flex-row gap-4 justify-center">
										<a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-3 px-10 py-5 bg-white text-blue-600 font-bold text-lg rounded-xl shadow-xl hover:bg-blue-50 transform hover:scale-105 transition-all duration-300">
											<i class="fas fa-rocket"></i>
											Start Your Journey Today
										</a>
										<a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-3 px-10 py-5 bg-transparent text-white font-bold text-lg rounded-xl border-2 border-white hover:bg-white hover:text-blue-600 transform hover:scale-105 transition-all duration-300">
											<i class="fas fa-sign-in-alt"></i>
											Sign In
										</a>
									</div>
								@else
									<a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center gap-3 px-12 py-6 bg-white text-blue-600 font-bold text-xl rounded-xl shadow-xl hover:bg-blue-50 transform hover:scale-105 transition-all duration-300">
										<i class="fas fa-arrow-right"></i>
										Continue to Dashboard
									</a>
								@endguest
							@endif
							<p class="text-blue-100 mt-8 text-sm">
								<i class="fas fa-shield-alt mr-2"></i>
								Secure • Confidential • Professional
							</p>
						</div>
					</div>
				</div>
			</section>

			@include('layouts.footer')
		</div>
	</body>
</html>
