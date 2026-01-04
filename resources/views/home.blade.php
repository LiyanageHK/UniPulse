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
								enhance engagement, and connect you with personalized university peers.
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
							<div class="stat-number text-5xl md:text-6xl font-bold text-blue-600 mb-2">250+</div>
							<p class="text-gray-700 font-semibold text-lg">Active Students</p>
							<p class="text-gray-500 text-sm mt-1">Supported Daily</p>
						</div>
						<div class="text-center p-6 rounded-xl bg-gradient-to-br from-blue-50 to-white">
							<div class="stat-number text-5xl md:text-6xl font-bold text-blue-600 mb-2">20+</div>
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
			<section class="py-24 relative overflow-hidden" style="background: linear-gradient(135deg, #1e40af 0%, #2563eb 50%, #3b82f6 100%);">
				<!-- Background pattern same as hero -->
				<div class="absolute inset-0">
					<div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg%20width%3D%2260%22%20height%3D%2260%22%20viewBox%3D%220%200%2060%2060%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cg%20fill%3D%22none%22%20fill-rule%3D%22evenodd%22%3E%3Cg%20fill%3D%22%23ffffff%22%20fill-opacity%3D%220.05%22%3E%3Cpath%20d%3D%22M36%2034v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6%2034v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6%204V0H4v4H0v2h4v4h2V6h4V4H6z%22%2F%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E')] opacity-40"></div>
				</div>
				
				<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
					<div class="text-center mb-16">
						<span class="inline-block px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full text-sm font-semibold text-white mb-4 border border-white/20">
							<i class="fas fa-heart mr-2 text-pink-300"></i>Student Testimonials
						</span>
						<h2 class="text-4xl md:text-5xl font-extrabold text-white mb-4">
							What Students Say About Us
						</h2>
						<p class="text-xl text-blue-100 max-w-3xl mx-auto">
							Real experiences from students who transformed their university life with UniPulse
						</p>
					</div>

					<div class="feedback-carousel-container relative">
						<!-- Carousel Track -->
						<div class="feedback-carousel-wrapper overflow-hidden">
							<div class="feedback-carousel-track" id="testimonialsContainer">
								<!-- Loading skeleton -->
								<div class="feedback-carousel-slide">
									<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
										@for ($i = 0; $i < 6; $i++)
										<div class="feedback-card animate-pulse">
											<div class="h-4 bg-gray-200 rounded w-24 mb-4"></div>
											<div class="h-16 bg-gray-100 rounded mb-4"></div>
											<div class="flex items-center gap-3">
												<div class="w-10 h-10 bg-gray-200 rounded-full"></div>
												<div class="h-4 bg-gray-200 rounded w-20"></div>
											</div>
										</div>
										@endfor
									</div>
								</div>
							</div>
						</div>

						<!-- Navigation Arrows -->
						<button id="prevBtn" class="feedback-nav-btn feedback-nav-prev disabled" aria-label="Previous testimonials">
							<i class="fas fa-chevron-left"></i>
						</button>
						<button id="nextBtn" class="feedback-nav-btn feedback-nav-next" aria-label="Next testimonials">
							<i class="fas fa-chevron-right"></i>
						</button>

						<!-- Pagination Dots -->
						<div class="feedback-pagination" id="paginationDots">
							<!-- Dots will be generated dynamically -->
						</div>
					</div>
				</div>
			</section>

			<style>
				/* Modern Feedback Carousel Styles - Blue Background with White Cards */
				.feedback-carousel-container {
					position: relative;
					padding: 0 60px;
				}

				.feedback-carousel-wrapper {
					border-radius: 1.5rem;
				}

				.feedback-carousel-track {
					display: flex;
					transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
				}

				.feedback-carousel-slide {
					min-width: 100%;
					flex-shrink: 0;
					padding: 0.5rem;
				}

				/* Card style matching home page feature-card and service-card */
				.feedback-card {
					background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
					border: 1px solid #e5e7eb;
					border-radius: 1rem;
					padding: 2rem;
					height: 100%;
					display: flex;
					flex-direction: column;
					transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
					position: relative;
					overflow: hidden;
					box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
				}

				.feedback-card:hover {
					transform: translateY(-12px);
					border-color: #93c5fd;
					box-shadow: 0 25px 50px -12px rgba(37, 99, 235, 0.25);
				}

				.feedback-stars {
					display: flex;
					gap: 0.25rem;
					margin-bottom: 1rem;
				}

				.feedback-stars i {
					color: #fbbf24;
					font-size: 1rem;
				}

				.feedback-content {
					color: #4b5563;
					font-size: 1rem;
					line-height: 1.75;
					flex-grow: 1;
					margin-bottom: 1.5rem;
					position: relative;
					padding-left: 0;
				}

				/* Quote icon styling - modern look */
				.feedback-quote-icon {
					width: 2.5rem;
					height: 2.5rem;
					background: linear-gradient(135deg, #3b82f6, #1d4ed8);
					border-radius: 50%;
					display: flex;
					align-items: center;
					justify-content: center;
					margin-bottom: 1rem;
					box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
				}

				.feedback-quote-icon i {
					color: white;
					font-size: 1rem;
				}

				.feedback-author {
					display: flex;
					align-items: center;
					gap: 1rem;
					padding-top: 1rem;
					border-top: 1px solid #e5e7eb;
				}

				.feedback-avatar {
					width: 3rem;
					height: 3rem;
					border-radius: 50%;
					display: flex;
					align-items: center;
					justify-content: center;
					font-weight: 700;
					font-size: 1.25rem;
					color: white;
					flex-shrink: 0;
				}

				.feedback-avatar.gradient-1 { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
				.feedback-avatar.gradient-2 { background: linear-gradient(135deg, #10b981, #059669); }
				.feedback-avatar.gradient-3 { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
				.feedback-avatar.gradient-4 { background: linear-gradient(135deg, #f472b6, #db2777); }
				.feedback-avatar.gradient-5 { background: linear-gradient(135deg, #f59e0b, #d97706); }
				.feedback-avatar.gradient-6 { background: linear-gradient(135deg, #06b6d4, #0891b2); }

				.feedback-author-info h4 {
					color: #1f2937;
					font-weight: 700;
					font-size: 1rem;
					margin-bottom: 0.125rem;
				}

				.feedback-author-info span {
					color: #6b7280;
					font-size: 0.875rem;
				}

				/* Navigation Buttons */
				.feedback-nav-btn {
					position: absolute;
					top: 50%;
					transform: translateY(-50%);
					width: 3.5rem;
					height: 3.5rem;
					border-radius: 50%;
					background: white;
					color: #2563eb;
					border: none;
					cursor: pointer;
					display: flex;
					align-items: center;
					justify-content: center;
					font-size: 1.25rem;
					transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
					box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
					z-index: 10;
				}

				.feedback-nav-prev { left: 0; }
				.feedback-nav-next { right: 0; }

				.feedback-nav-btn:hover:not(.disabled) {
					background: #2563eb;
					color: white;
					transform: translateY(-50%) scale(1.1);
					box-shadow: 0 15px 30px rgba(37, 99, 235, 0.4);
				}

				.feedback-nav-btn:active:not(.disabled) {
					transform: translateY(-50%) scale(0.95);
				}

				.feedback-nav-btn.disabled {
					opacity: 0.5;
					cursor: not-allowed;
				}

				/* Pagination Dots */
				.feedback-pagination {
					display: flex;
					justify-content: center;
					gap: 0.75rem;
					margin-top: 2.5rem;
				}

				.feedback-dot {
					width: 0.75rem;
					height: 0.75rem;
					border-radius: 50%;
					background: rgba(255, 255, 255, 0.4);
					border: none;
					cursor: pointer;
					transition: all 0.3s ease;
					padding: 0;
				}

				.feedback-dot:hover {
					background: rgba(255, 255, 255, 0.7);
					transform: scale(1.2);
				}

				.feedback-dot.active {
					background: white;
					width: 2.5rem;
					border-radius: 0.5rem;
				}

				/* Responsive adjustments */
				@media (max-width: 1024px) {
					.feedback-carousel-container {
						padding: 0 50px;
					}
					
					.feedback-carousel-slide .grid {
						grid-template-columns: repeat(2, 1fr) !important;
					}
				}

				@media (max-width: 768px) {
					.feedback-carousel-container {
						padding: 0 15px;
					}
					
					.feedback-carousel-slide .grid {
						grid-template-columns: 1fr !important;
						gap: 1rem !important;
					}

					.feedback-nav-btn {
						width: 2.75rem;
						height: 2.75rem;
						font-size: 0.875rem;
					}

					.feedback-nav-prev { left: 5px; }
					.feedback-nav-next { right: 5px; }

					.feedback-card {
						padding: 1.5rem;
					}
				}
			</style>

			<script>
				// Modern Carousel logic with 2 rows x 3 cols per slide
				document.addEventListener('DOMContentLoaded', async function() {
					const container = document.getElementById('testimonialsContainer');
					const prevBtn = document.getElementById('prevBtn');
					const nextBtn = document.getElementById('nextBtn');
					const paginationContainer = document.getElementById('paginationDots');
					
					const avatarGradients = ['gradient-1', 'gradient-2', 'gradient-3', 'gradient-4', 'gradient-5', 'gradient-6'];
					const ITEMS_PER_SLIDE = 6; // 2 rows x 3 cols
					
					let currentSlide = 0;
					let totalSlides = 0;
					let testimonials = [];

					const defaultTestimonials = [
						{
							content: "UniPulse has been a lifesaver during my studies. The AI chatbot is always there when I need someone to talk to, and the counselors are incredibly supportive.",
							rating: 5,
							display_name: "Sarah J.",
							display_initial: "S",
							approved_at: "2 days ago"
						},
						{
							content: "The peer matching feature helped me find friends who understood what I was going through. I no longer feel alone in my struggles.",
							rating: 5,
							display_name: "Michael C.",
							display_initial: "M",
							approved_at: "1 week ago"
						},
						{
							content: "The progress tracking tools helped me understand my mental health patterns and celebrate small victories. It's incredibly empowering!",
							rating: 5,
							display_name: "Emily R.",
							display_initial: "E",
							approved_at: "3 days ago"
						},
						{
							content: "I love how easy it is to track my academic and mental health goals in one place. Truly a holistic approach to student wellness.",
							rating: 5,
							display_name: "David K.",
							display_initial: "D",
							approved_at: "5 days ago"
						},
						{
							content: "The 24/7 availability of support is amazing. Whether it's midnight anxiety or early morning stress, UniPulse is always there.",
							rating: 5,
							display_name: "Priya M.",
							display_initial: "P",
							approved_at: "1 week ago"
						},
						{
							content: "Connecting with professional counselors through UniPulse helped me navigate a really difficult semester. Highly recommended!",
							rating: 5,
							display_name: "James L.",
							display_initial: "J",
							approved_at: "4 days ago"
						},
						{
							content: "The AI understands context so well. It remembers our previous conversations and provides personalized advice that actually helps.",
							rating: 5,
							display_name: "Aisha N.",
							display_initial: "A",
							approved_at: "3 days ago"
						},
						{
							content: "Finally, a mental health app that feels modern and doesn't make me feel awkward using it. Great UI and even better support!",
							rating: 5,
							display_name: "Tom R.",
							display_initial: "T",
							approved_at: "6 days ago"
						},
						{
							content: "UniPulse helped me build healthy habits and stay consistent with self-care routines. My productivity has improved so much!",
							rating: 5,
							display_name: "Nina S.",
							display_initial: "N",
							approved_at: "1 week ago"
						},
						{
							content: "The crisis detection feature gave me peace of mind. Knowing there's a safety net makes me feel secure while using the platform.",
							rating: 5,
							display_name: "Chris W.",
							display_initial: "C",
							approved_at: "2 weeks ago"
						},
						{
							content: "Being able to track my mood over time has helped me identify patterns I never noticed before. Super insightful!",
							rating: 5,
							display_name: "Maya P.",
							display_initial: "M",
							approved_at: "5 days ago"
						},
						{
							content: "I was skeptical at first, but UniPulse genuinely changed how I approach my mental health. It's now part of my daily routine.",
							rating: 5,
							display_name: "Alex G.",
							display_initial: "A",
							approved_at: "1 week ago"
						}
					];

					function getItemsPerSlide() {
						if (window.innerWidth >= 1024) return 6; // 3 cols x 2 rows
						if (window.innerWidth >= 768) return 4;  // 2 cols x 2 rows
						return 2; // 1 col x 2 rows
					}

					function updateCarousel() {
						container.style.transform = `translateX(-${currentSlide * 100}%)`;
						
						// Update buttons
						prevBtn.classList.toggle('disabled', currentSlide === 0);
						nextBtn.classList.toggle('disabled', currentSlide >= totalSlides - 1);
						
						// Update pagination dots
						document.querySelectorAll('.feedback-dot').forEach((dot, index) => {
							dot.classList.toggle('active', index === currentSlide);
						});
					}

					function createPagination() {
						paginationContainer.innerHTML = '';
						for (let i = 0; i < totalSlides; i++) {
							const dot = document.createElement('button');
							dot.className = `feedback-dot ${i === 0 ? 'active' : ''}`;
							dot.setAttribute('aria-label', `Go to slide ${i + 1}`);
							dot.addEventListener('click', () => {
								currentSlide = i;
								updateCarousel();
							});
							paginationContainer.appendChild(dot);
						}
					}

					nextBtn.addEventListener('click', () => {
						if (currentSlide < totalSlides - 1) {
							currentSlide++;
							updateCarousel();
						}
					});

					prevBtn.addEventListener('click', () => {
						if (currentSlide > 0) {
							currentSlide--;
							updateCarousel();
						}
					});

					// Auto-play carousel
					let autoPlayInterval = setInterval(() => {
						if (currentSlide < totalSlides - 1) {
							currentSlide++;
						} else {
							currentSlide = 0;
						}
						updateCarousel();
					}, 6000);

					// Pause on hover
					container.addEventListener('mouseenter', () => clearInterval(autoPlayInterval));
					container.addEventListener('mouseleave', () => {
						autoPlayInterval = setInterval(() => {
							if (currentSlide < totalSlides - 1) {
								currentSlide++;
							} else {
								currentSlide = 0;
							}
							updateCarousel();
						}, 6000);
					});

					window.addEventListener('resize', () => {
						renderTestimonials();
						updateCarousel();
					});

					async function loadTestimonials() {
						try {
							const response = await fetch('/api/feedback/approved?limit=24');
							const data = await response.json();
							
							if (data.success && data.feedbacks && data.feedbacks.length > 0) {
								testimonials = data.feedbacks;
							} else {
								testimonials = defaultTestimonials;
							}
						} catch (error) {
							console.error('Failed to load testimonials:', error);
							testimonials = defaultTestimonials;
						}

						renderTestimonials();
						createPagination();
						updateCarousel();
					}

					function renderTestimonials() {
						const itemsPerSlide = getItemsPerSlide();
						totalSlides = Math.ceil(testimonials.length / itemsPerSlide);
						
						// Ensure currentSlide is valid
						if (currentSlide >= totalSlides) {
							currentSlide = totalSlides - 1;
						}
						
						let slidesHTML = '';
						
						for (let slideIndex = 0; slideIndex < totalSlides; slideIndex++) {
							const startIndex = slideIndex * itemsPerSlide;
							const slideItems = testimonials.slice(startIndex, startIndex + itemsPerSlide);
							
							// Determine grid cols based on screen size
							let gridCols = 'lg:grid-cols-3 md:grid-cols-2 grid-cols-1';
							
							const cardsHTML = slideItems.map((feedback, index) => {
								const globalIndex = startIndex + index;
								const gradientClass = avatarGradients[globalIndex % avatarGradients.length];
								const stars = Array(feedback.rating).fill('<i class="fas fa-star"></i>').join('');
								
								return `
									<div class="feedback-card">
										<div class="feedback-stars">${stars}</div>
										<p class="feedback-content">${feedback.content}</p>
										<div class="feedback-author">
											<div class="feedback-avatar ${gradientClass}">${feedback.display_initial}</div>
											<div class="feedback-author-info">
												<h4>${feedback.display_name}</h4>
												<span>${feedback.approved_at}</span>
											</div>
										</div>
									</div>
								`;
							}).join('');

							slidesHTML += `
								<div class="feedback-carousel-slide">
									<div class="grid ${gridCols} gap-6">
										${cardsHTML}
									</div>
								</div>
							`;
						}
						
						container.innerHTML = slidesHTML;
					}

					loadTestimonials();
				});
			</script>

			<!-- Share Your Feedback Section (Guest) -->
			<section class="py-20 bg-white">
				<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
					<div class="text-center mb-12">
						<h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-4">
							Share Your <span class="gradient-text">Experience</span>
						</h2>
						<p class="text-lg text-gray-600">
							Used UniPulse? We'd love to hear from you! Your feedback helps others discover our platform.
						</p>
					</div>

					<div class="bg-gradient-to-br from-blue-50 to-white rounded-2xl p-8 shadow-lg border border-blue-100" id="guestFeedbackCard">
						<form id="guestFeedbackForm" onsubmit="return submitGuestFeedback(event)">
							<input type="hidden" name="_token" value="{{ csrf_token() }}">
							
							<!-- Star Rating -->
							<div class="mb-6">
								<label class="block text-gray-700 font-semibold mb-3">Your Rating</label>
								<div class="flex gap-2" id="guestStarRating">
									<span class="star-btn text-3xl cursor-pointer text-gray-300 hover:text-yellow-400 transition-colors" data-rating="1">★</span>
									<span class="star-btn text-3xl cursor-pointer text-gray-300 hover:text-yellow-400 transition-colors" data-rating="2">★</span>
									<span class="star-btn text-3xl cursor-pointer text-gray-300 hover:text-yellow-400 transition-colors" data-rating="3">★</span>
									<span class="star-btn text-3xl cursor-pointer text-gray-300 hover:text-yellow-400 transition-colors" data-rating="4">★</span>
									<span class="star-btn text-3xl cursor-pointer text-gray-300 hover:text-yellow-400 transition-colors" data-rating="5">★</span>
								</div>
								<input type="hidden" id="guestRating" name="rating" value="0">
							</div>

							<!-- Name Input -->
							<div class="mb-6">
								<label class="block text-gray-700 font-semibold mb-2" for="guestName">Your Name *</label>
								<input type="text" id="guestName" name="guest_name" required 
									class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
									placeholder="Enter your name">
							</div>

							<!-- Email Input (Optional) -->
							<div class="mb-6">
								<label class="block text-gray-700 font-semibold mb-2" for="guestEmail">Email (Optional)</label>
								<input type="email" id="guestEmail" name="guest_email" 
									class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all"
									placeholder="email@university.com">
							</div>

							<!-- Feedback Content -->
							<div class="mb-6">
								<label class="block text-gray-700 font-semibold mb-2" for="guestContent">Your Feedback *</label>
								<textarea id="guestContent" name="content" required rows="4"
									class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all resize-none"
									placeholder="Share your experience with UniPulse"></textarea>
							</div>

							<!-- Show Name Option -->
							<div class="mb-6">
								<label class="flex items-center gap-3 cursor-pointer">
									<input type="checkbox" id="guestShowName" name="show_name" checked 
										class="w-5 h-5 rounded text-blue-600 border-gray-300 focus:ring-blue-500">
									<span class="text-gray-600">Display my name with my feedback</span>
								</label>
							</div>

							<!-- Submit Button -->
							<button type="submit" id="guestSubmitBtn"
								class="w-full py-4 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] transition-all duration-300 flex items-center justify-center gap-2">
								<i class="fas fa-paper-plane"></i>
								Submit Feedback
							</button>
						</form>

						<!-- Success Message (Hidden by default) -->
						<div id="guestFeedbackSuccess" class="hidden text-center py-12">
							<div class="mb-4">
								<span class="text-6xl animate-bounce inline-block">🎉</span>
							</div>
							<h3 class="text-2xl font-bold text-gray-900 mb-2">Thank you for the feedback!</h3>
							<p class="text-gray-600 mb-8">We appreciate you taking the time to share your experience.</p>
							
							<button onclick="resetGuestFeedback()" 
								class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-blue-600 font-bold rounded-xl border-2 border-blue-100 hover:border-blue-300 hover:bg-blue-50 transition-all duration-300">
								<i class="fas fa-plus"></i>
								Submit Another Response
							</button>
						</div>
					</div>
				</div>
			</section>

			<script>
				// Guest feedback star rating
				let guestRating = 0;
				document.querySelectorAll('#guestStarRating .star-btn').forEach(star => {
					star.addEventListener('click', function() {
						guestRating = parseInt(this.dataset.rating);
						document.getElementById('guestRating').value = guestRating;
						updateGuestStars();
					});
				});

				function updateGuestStars() {
					document.querySelectorAll('#guestStarRating .star-btn').forEach((star, index) => {
						if (index < guestRating) {
							star.classList.remove('text-gray-300');
							star.classList.add('text-yellow-400');
						} else {
							star.classList.add('text-gray-300');
							star.classList.remove('text-yellow-400');
						}
					});
				}

				async function submitGuestFeedback(event) {
					event.preventDefault();
					
					const content = document.getElementById('guestContent').value.trim();
					const name = document.getElementById('guestName').value.trim();
					const email = document.getElementById('guestEmail').value.trim();
					const showName = document.getElementById('guestShowName').checked;
					
					// Validation
					if (guestRating === 0) {
						alert('Please select a star rating.');
						return false;
					}
					if (content.length < 1) {
						alert('Please write something about your experience.');
						return false;
					}

					const submitBtn = document.getElementById('guestSubmitBtn');
					submitBtn.disabled = true;
					submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

					try {
						const response = await fetch('/api/feedback/guest', {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json',
								'X-CSRF-TOKEN': '{{ csrf_token() }}'
							},
							body: JSON.stringify({
								content: content,
								rating: guestRating,
								guest_name: name,
								guest_email: email,
								show_name: showName
							})
						});

						const data = await response.json();

						if (data.success) {
							document.getElementById('guestFeedbackForm').classList.add('hidden');
							document.getElementById('guestFeedbackSuccess').classList.remove('hidden');
						} else {
							alert(data.error || 'Failed to submit feedback. Please try again.');
							submitBtn.disabled = false;
							submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Feedback';
						}
					} catch (error) {
						console.error('Feedback error:', error);
						alert('Something went wrong. Please try again.');
						submitBtn.disabled = false;
						submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Feedback';
					}

					return false;
				}

				function resetGuestFeedback() {
					// Hide success message and show form
					document.getElementById('guestFeedbackSuccess').classList.add('hidden');
					document.getElementById('guestFeedbackForm').classList.remove('hidden');
					
					// Reset form fields
					document.getElementById('guestFeedbackForm').reset();
					document.getElementById('guestRating').value = 0;
					guestRating = 0;
					
					// Reset stars
					updateGuestStars();
					
					// Reset button state just in case
					const submitBtn = document.getElementById('guestSubmitBtn');
					submitBtn.disabled = false;
					submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Feedback';
				}
			</script>

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
								How does the AI chatbot work?
							</h3>
							<p class="text-gray-600 leading-relaxed pl-8">
								Our AI chatbot uses advanced natural language processing to understand your concerns and provide empathetic, personalized responses. It learns from your interactions to offer increasingly relevant support tailored to your unique situation.
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
								Can counselors see my conversation history?
							</h3>
							<p class="text-gray-600 leading-relaxed pl-8">
								Your AI chat conversations remain completely private unless you choose to share them. Professional counselors are there to help you.
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
