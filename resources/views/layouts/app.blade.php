<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'UniPulse') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

 <!-- Survey CTA Bar -->
    @auth
        @if (!request()->is('survey') && !auth()->user()->on_boarding_required && !$hasSubmittedWeeklyCheck)
            <div
                class="bg-gradient-to-r from-yellow-400 to-yellow-500 text-white flex justify-center items-center py-3 shadow-lg">
                <span class="mr-4 font-semibold text-lg">Take your new survey this week!</span>
                <a href="{{ url('/survey') }}"
                    class="bg-white text-purple-700 px-6 py-2 rounded-full font-bold shadow-lg transform transition hover:scale-105 animate-heartbeat">
                    Take Survey
                </a>
            </div>
        @endif
    @endauth

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
            @include('layouts.footer')
        </div>
    </body>
</html>
