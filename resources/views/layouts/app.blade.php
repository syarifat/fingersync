<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- <title>{{ config('app.name', 'FingerSync') }}</title> -->
    <title>FingerSync</title>
    <link rel="icon" href="{{ asset('logo.png') }}?v=2" type="image/png">
    <link rel="shortcut icon" href="{{ asset('logo.png') }}?v=2" type="image/png">

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
                        @php
                        $activeTahunId = session('tahun_ajar_id');
                        $activeTahun = collect($globalTahunAjar ?? [])->firstWhere('id', $activeTahunId);
                        $activeLabel = $activeTahun ? $activeTahun->tahun . ' - ' . $activeTahun->semester : 'Pilih Tahun';
                        @endphp

                        <form action="{{ route('admin.tahun-ajar.switch') }}" method="POST" id="formSwitchTahun" class="hidden">
                            @csrf
                            <input type="hidden" name="id_tahun_ajar" id="inputTahunAjar">
                        </form>

                        <div class="hidden sm:block">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="relative flex items-center bg-orange-50 border border-orange-200 rounded-full px-4 py-1.5 hover:shadow-sm transition-all group cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-orange-500">
                                        <div class="flex flex-col items-end mr-3">
                                            <span class="text-[9px] text-orange-400 font-bold uppercase tracking-widest leading-none mb-1">Tahun Aktif</span>
                                            <span class="text-sm font-bold text-gray-700 leading-none">{{ $activeLabel }}</span>
                                        </div>
                                        <div class="h-8 w-8 rounded-full bg-orange-100 flex items-center justify-center text-orange-600 group-hover:bg-orange-200 transition-colors">
                                            <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <div class="px-4 py-2 border-b border-gray-100 text-xs text-gray-400 font-bold uppercase tracking-wider text-right">
                                        Pilih Tahun Ajar
                                    </div>
                                    @foreach($globalTahunAjar ?? [] as $ta)
                                    <x-dropdown-link href="#"
                                        onclick="event.preventDefault(); document.getElementById('inputTahunAjar').value='{{ $ta->id }}'; document.getElementById('formSwitchTahun').submit();"
                                        class="{{ $activeTahunId == $ta->id ? 'bg-orange-50 text-orange-700' : '' }}">
                                        <div class="flex items-center justify-between">
                                            <span>{{ $ta->tahun }} - {{ $ta->semester }}</span>
                                            @if($activeTahunId == $ta->id)
                                            <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            @endif
                                        </div>
                                    </x-dropdown-link>
                                    @endforeach
                                </x-slot>
                            </x-dropdown>
                        </div>
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