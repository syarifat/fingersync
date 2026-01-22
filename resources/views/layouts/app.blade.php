<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- <title>{{ config('app.name', 'FingerSync') }}</title> -->
    <title>FingerSync</title>
    <link rel="icon" href="{{ asset('img/logo.png') }}?v=1" type="image/png">
    <link rel="shortcut icon" href="{{ asset('img/logo.png') }}?v=1" type="image/png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: true }">
    <div class="flex h-screen overflow-hidden">
        
        @include('layouts.navigation')

        <div class="flex-1 flex flex-col overflow-y-auto overflow-x-hidden transition-all duration-300">
            
            <header class="bg-white border-b border-gray-100 sticky top-0 z-30">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    
                    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 focus:outline-none hover:text-orange-600 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                    </button>

                    <div class="flex-1 ml-4 hidden md:block">
                        @isset($header)
                            <div class="text-gray-800">
                                {{ $header }}
                            </div>
                        @endisset
                    </div>

                    <div class="flex items-center gap-4">

                        @if(Auth::user()->role === 'admin')
                        <form action="{{ route('admin.tahun-ajar.switch') }}" method="POST" id="formSwitchTahun" class="hidden sm:block">
                            @csrf
                            <div class="relative flex items-center bg-orange-50 border border-orange-200 rounded-full px-4 py-1.5 hover:shadow-sm transition-all group cursor-pointer">
                                
                                <div class="flex flex-col items-end mr-3">
                                    <span class="text-[9px] text-orange-400 font-bold uppercase tracking-widest leading-none mb-1">Tahun Aktif</span>
                                    
                                    <select name="id_tahun_ajar" onchange="document.getElementById('formSwitchTahun').submit()" 
                                        class="appearance-none bg-transparent border-none p-0 text-sm font-bold text-gray-700 focus:ring-0 cursor-pointer text-right w-full pr-0 leading-none outline-none">
                                        @foreach($globalTahunAjar ?? [] as $ta)
                                            <option value="{{ $ta->id }}" {{ session('tahun_ajar_id') == $ta->id ? 'selected' : '' }}>
                                                {{ $ta->tahun }} - {{ $ta->semester }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="h-8 w-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 group-hover:bg-orange-200 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>

                            </div>
                        </form>
                        @endif

                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="flex items-center text-sm font-medium text-gray-500 hover:text-orange-600 transition duration-150 ease-in-out bg-gray-50 px-3 py-1.5 rounded-full border border-gray-200">
                                    <div class="h-6 w-6 rounded-full bg-orange-500 text-white flex items-center justify-center mr-2 text-[10px] font-bold">
                                        {{ substr(Auth::user()->nama, 0, 1) }}
                                    </div>
                                    <div class="hidden sm:block">{{ Auth::user()->nama }}</div>
                                    <div class="ml-1">
                                        <svg class="fill-current h-4 w-4" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="px-4 py-2 border-b border-gray-100 text-xs text-gray-400 font-bold uppercase tracking-wider text-right">
                                    Logged as {{ Auth::user()->role }}
                                </div>

                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profile') }}
                                </x-dropdown-link>
                                
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                        {{ __('Log Out') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>

                    </div>
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>