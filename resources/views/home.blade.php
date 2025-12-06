<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Home - UniPulse</title>

		<!-- Fonts -->
		<link rel="preconnect" href="https://fonts.bunny.net">
		<link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

		<!-- Vite / Assets -->
		@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
			@vite(['resources/css/app.css', 'resources/js/app.js'])
		@endif
	</head>
	<body class="font-sans antialiased">
		<div class="min-h-screen bg-gray-50 text-black/80">
			@include('layouts.header')

			<main class="mx-auto max-w-4xl p-6 text-center">
				<h1 class="text-3xl font-semibold mb-4">Welcome to UniPulse</h1>
				<p class="text-lg text-gray-600 mb-6">A student-centred wellbeing & engagement platform.</p>

				<div class="flex flex-col sm:flex-row sm:justify-center gap-4">
					@if (Route::has('login'))
						@auth
							<a href="{{ url('/dashboard') }}" class="inline-block rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Go to Dashboard</a>
						@else
							<a href="{{ route('login') }}" class="inline-block rounded-md bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Log in</a>
							@if (Route::has('register'))
								<a href="{{ route('register') }}" class="inline-block rounded-md border border-gray-300 px-4 py-2">Register</a>
							@endif
						@endauth
					@endif
				</div>

				<section class="mt-10 text-left">
					<h2 class="text-xl font-semibold mb-2">About UniPulse</h2>
					<p class="text-gray-600">UniPulse helps students discover tailored support and connect with services across campus. Complete the onboarding to personalise your experience.</p>
				</section>
			</main>

			@include('layouts.footer')
		</div>
	</body>
</html>
