<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gradient-to-br from-orange-500 to-orange-700">
        <div class="mb-8">
            <a href="/" class="flex flex-col items-center">
                <div class="bg-white p-4 rounded-3xl shadow-2xl mb-4 transform hover:rotate-12 transition-transform duration-300">
                    <svg class="w-16 h-16 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 7H7v6h6V7z" />
                        <path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5v10h10V5H5z" clip-rule="evenodd" />
                    </svg>
                </div>
                <h1 class="text-white text-3xl font-black tracking-tighter italic">FINGER<span class="text-orange-200 underline">SYNC</span></h1>
                <p class="text-orange-100 text-xs mt-1 uppercase tracking-widest font-bold">Smart Presence System</p>
            </a>
        </div>

        <div class="w-full sm:max-w-md px-8 py-10 bg-white/95 backdrop-blur-sm shadow-2xl overflow-hidden sm:rounded-3xl border border-white/20">
            {{ $slot }}
        </div>
        
        <p class="mt-8 text-orange-100 text-xs font-medium">© 2026 FingerSync Technology • All Rights Reserved</p>
    </div>
</body>
</html>