<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FingerSync - Sistem Presensi Cerdas</title>
    
    <link rel="icon" href="{{ asset('img/logo.png') }}?v=1" type="image/png">
    <link rel="shortcut icon" href="{{ asset('img/logo.png') }}?v=1" type="image/png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-600 bg-white">

    <nav class="fixed w-full z-50 bg-white/80 backdrop-blur-md border-b border-gray-100 transition-all duration-300" x-data="{ scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/logo.png') }}" alt="FingerSync Logo" class="h-10 w-10 drop-shadow-sm">
                    <span class="font-extrabold text-2xl tracking-tight text-gray-800">
                        FINGER<span class="text-orange-600">SYNC</span>
                    </span>
                </div>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="#fitur" class="text-sm font-medium text-gray-600 hover:text-orange-600 transition">Fitur Utama</a>
                    <a href="#cara-kerja" class="text-sm font-medium text-gray-600 hover:text-orange-600 transition">Cara Kerja</a>
                    <a href="#tentang" class="text-sm font-medium text-gray-600 hover:text-orange-600 transition">Tentang</a>
                </div>

                <div class="flex items-center gap-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-6 py-2.5 text-sm font-bold text-white bg-orange-600 rounded-full hover:bg-orange-700 transition shadow-lg shadow-orange-200">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="px-6 py-2.5 text-sm font-bold text-white bg-gray-900 rounded-full hover:bg-orange-600 transition shadow-lg">
                                Masuk
                            </a>
                            <!-- @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-6 py-2.5 text-sm font-bold text-white bg-gray-900 rounded-full hover:bg-orange-600 transition shadow-lg">
                                    Daftar Sekolah
                                </a>
                            @endif -->
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <section class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
        <div class="absolute top-0 right-0 -z-10 opacity-30 translate-x-1/3 -translate-y-1/4">
            <div class="w-[800px] h-[800px] bg-orange-100 rounded-full blur-3xl"></div>
        </div>
        <div class="absolute bottom-0 left-0 -z-10 opacity-30 -translate-x-1/3 translate-y-1/4">
            <div class="w-[600px] h-[600px] bg-orange-50 rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="inline-block py-1 px-3 rounded-full bg-orange-50 border border-orange-100 text-orange-600 text-xs font-bold uppercase tracking-widest mb-6 animate-pulse">
                Sistem Presensi Generasi Baru
            </span>
            <h1 class="text-5xl md:text-7xl font-extrabold text-gray-900 tracking-tight mb-8 leading-tight">
                Absensi Siswa <br class="hidden md:block" />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-600 to-yellow-500">Cepat, Akurat & Realtime</span>
            </h1>
            <p class="text-lg md:text-xl text-gray-500 max-w-2xl mx-auto mb-10 leading-relaxed">
                FingerSync mengintegrasikan teknologi biometrik sidik jari dengan sistem cloud. Pantau kehadiran siswa, guru, dan staf sekolah secara langsung dari mana saja.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('login') }}" class="px-8 py-4 text-base font-bold text-white bg-orange-600 rounded-2xl hover:bg-orange-700 transition shadow-xl shadow-orange-200 transform hover:-translate-y-1">
                    Mulai Sekarang
                </a>
                <a href="#fitur" class="px-8 py-4 text-base font-bold text-gray-700 bg-white border border-gray-200 rounded-2xl hover:bg-gray-50 transition transform hover:-translate-y-1">
                    Pelajari Fitur
                </a>
            </div>
        </div>
    </section>

    <div class="bg-orange-600 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center text-white">
                <div>
                    <div class="text-4xl font-black mb-1">99.9%</div>
                    <div class="text-orange-200 text-sm font-medium uppercase tracking-wider">Akurasi Data</div>
                </div>
                <div>
                    <div class="text-4xl font-black mb-1">0.5s</div>
                    <div class="text-orange-200 text-sm font-medium uppercase tracking-wider">Kecepatan Scan</div>
                </div>
                <div>
                    <div class="text-4xl font-black mb-1">24/7</div>
                    <div class="text-orange-200 text-sm font-medium uppercase tracking-wider">Akses Cloud</div>
                </div>
                <div>
                    <div class="text-4xl font-black mb-1">IoT</div>
                    <div class="text-orange-200 text-sm font-medium uppercase tracking-wider">Integrasi Device</div>
                </div>
            </div>
        </div>
    </div>

    <section id="fitur" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Mengapa Memilih FingerSync?</h2>
                <p class="text-gray-500 max-w-2xl mx-auto">Kami menyediakan solusi manajemen kehadiran yang dirancang khusus untuk kebutuhan sekolah modern.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl hover:border-orange-200 transition duration-300 group">
                    <div class="w-14 h-14 bg-orange-50 rounded-2xl flex items-center justify-center text-orange-600 mb-6 group-hover:bg-orange-600 group-hover:text-white transition">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Biometrik Anti-Curang</h3>
                    <p class="text-gray-500 leading-relaxed">Mencegah titip absen. Identifikasi siswa menggunakan sidik jari yang unik dan tidak dapat dipalsukan.</p>
                </div>

                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl hover:border-orange-200 transition duration-300 group">
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 mb-6 group-hover:bg-blue-600 group-hover:text-white transition">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Sinkronisasi Realtime</h3>
                    <p class="text-gray-500 leading-relaxed">Data kehadiran langsung terkirim ke server cloud detik itu juga. Guru dan Admin bisa memantau live.</p>
                </div>

                <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 hover:shadow-xl hover:border-orange-200 transition duration-300 group">
                    <div class="w-14 h-14 bg-green-50 rounded-2xl flex items-center justify-center text-green-600 mb-6 group-hover:bg-green-600 group-hover:text-white transition">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Laporan Otomatis</h3>
                    <p class="text-gray-500 leading-relaxed">Rekap kehadiran harian, bulanan, dan semester digenerate otomatis. Export ke PDF/Excel dengan mudah.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="cara-kerja" class="py-24 bg-white overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row items-center gap-16">
                <div class="lg:w-1/2">
                    <div class="relative">
                        <div class="absolute -inset-4 bg-gradient-to-r from-orange-600 to-yellow-500 rounded-2xl blur-lg opacity-30"></div>
                        <div class="relative bg-gray-900 rounded-2xl border-4 border-gray-900 shadow-2xl overflow-hidden aspect-video flex items-center justify-center">
                            <div class="text-center p-8">
                                <svg class="w-20 h-20 text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                <p class="text-gray-500 text-sm">Dashboard Admin Preview</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:w-1/2">
                    <span class="text-orange-600 font-bold tracking-wider uppercase text-sm mb-2 block">Dashboard Intuitif</span>
                    <h2 class="text-4xl font-bold text-gray-900 mb-6">Kelola Data Sekolah dalam Satu Genggaman</h2>
                    <p class="text-lg text-gray-500 mb-8">
                        Tidak perlu lagi rekap manual yang melelahkan. FingerSync menyediakan dashboard admin yang powerful untuk mengelola data siswa, guru, kelas, dan jadwal pelajaran.
                    </p>
                    
                    <ul class="space-y-4">
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center mt-1 mr-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            <span class="text-gray-600">Manajemen Rombel & Jadwal Fleksibel</span>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center mt-1 mr-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            <span class="text-gray-600">Monitoring Device IoT Status</span>
                        </li>
                        <li class="flex items-start">
                            <span class="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center mt-1 mr-3">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                            </span>
                            <span class="text-gray-600">Filter Data per Tahun Ajaran</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-gray-900 text-white py-12 border-t border-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo" class="w-8 h-8 grayscale opacity-80">
                    <span class="font-bold text-xl tracking-wider">FINGER<span class="text-orange-500">SYNC</span></span>
                </div>
                <div class="text-gray-400 text-sm">
                    &copy; {{ date('Y') }} FingerSync. All rights reserved.
                </div>
                <div class="flex gap-6">
                    <a href="#" class="text-gray-400 hover:text-white transition">Privacy</a>
                    <a href="#" class="text-gray-400 hover:text-white transition">Terms</a>
                    <a href="#" class="text-gray-400 hover:text-white transition">Contact</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>