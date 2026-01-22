<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>FingerSync</title>
    
    <link rel="icon" href="{{ asset('img/logo.png') }}?v=1" type="image/png">
    <link rel="shortcut icon" href="{{ asset('img/logo.png') }}?v=1" type="image/png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased">
    
    <div class="min-h-screen flex items-center justify-center p-6 bg-gradient-to-br from-orange-500 via-orange-600 to-orange-800">
        
        <div class="w-full max-w-6xl flex flex-col lg:flex-row items-center justify-between gap-10 lg:gap-20">
            
            <div class="lg:w-1/2 flex flex-col items-center lg:items-start text-center lg:text-left text-white space-y-6">
                
                <a href="/" class="group">
                    <img src="{{ asset('img/logo.png') }}" 
                         alt="FingerSync Logo" 
                         class="w-24 h-24 lg:w-32 lg:h-32 mb-6 drop-shadow-2xl filter brightness-110 transform group-hover:scale-110 transition-transform duration-300 object-contain mx-auto lg:mx-0">
                    
                    <h1 class="text-4xl lg:text-6xl font-black tracking-tighter italic drop-shadow-md">
                        FINGER<span class="text-orange-200 underline decoration-4 decoration-orange-400/50">SYNC</span>
                    </h1>
                    <p class="text-orange-100 text-xs lg:text-sm mt-2 uppercase tracking-[0.4em] font-bold opacity-90">Smart Presence System</p>
                </a>

                <p class="hidden lg:block text-orange-50/80 text-lg leading-relaxed max-w-md">
                    Solusi manajemen kehadiran sekolah modern berbasis IoT. <br>
                    <span class="font-bold text-white">Cepat. Akurat. Real-time.</span>
                </p>

                <p class="hidden lg:block pt-8 text-orange-200/60 text-xs font-medium tracking-wide">
                    © {{ date('Y') }} FingerSync Technology • All Rights Reserved
                </p>
            </div>

            <div class="w-full sm:max-w-md lg:w-1/2">
                <div class="px-8 py-10 bg-white/95 backdrop-blur-xl shadow-2xl overflow-hidden rounded-[2.5rem] border border-white/40 relative transform hover:scale-[1.01] transition-transform duration-500">
                    
                    <div class="absolute top-0 left-0 w-full h-1.5 bg-gradient-to-r from-orange-400 via-orange-500 to-orange-600"></div>
                    
                    {{ $slot }}
                </div>

                <p class="lg:hidden mt-8 text-center text-orange-200/80 text-xs font-medium tracking-wide">
                    © {{ date('Y') }} FingerSync Technology
                </p>
            </div>

        </div>
    </div>
</body>
</html>