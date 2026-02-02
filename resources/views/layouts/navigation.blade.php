<style>
    /* Kustomisasi Scrollbar */
    .sidebar-scrollbar::-webkit-scrollbar { width: 5px; }
    .sidebar-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .sidebar-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(255, 255, 255, 0.2); border-radius: 20px; }
    .sidebar-scrollbar::-webkit-scrollbar-thumb:hover { background-color: rgba(255, 255, 255, 0.5); }
    .sidebar-scrollbar { scrollbar-width: thin; scrollbar-color: rgba(255, 255, 255, 0.3) transparent; }
</style>

<aside 
    :class="sidebarOpen ? 'w-64' : 'w-20'" 
    class="relative bg-orange-600 h-screen text-white transition-all duration-300 ease-in-out flex flex-col shadow-xl z-40">
    
    <div class="flex items-center justify-center h-20 bg-orange-700 overflow-hidden shrink-0 shadow-sm z-10">
        <a href="{{ route('dashboard') }}" class="flex items-center transition-all duration-300">
            
            <img src="{{ asset('logo.png') }}" alt="Logo" 
                 class="w-10 h-10 object-contain drop-shadow-md transform hover:scale-110 transition-transform duration-300">

            <span :class="sidebarOpen ? 'opacity-100 w-auto ml-3' : 'opacity-0 w-0 ml-0'" 
                  class="font-bold text-lg tracking-wider transition-all duration-300 whitespace-nowrap overflow-hidden">
                FINGER<span class="text-orange-200">SYNC</span>
            </span>
        </a>
    </div>

    <nav class="flex-1 mt-6 px-3 space-y-1 overflow-y-auto sidebar-scrollbar">
        
        <a href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : route('guru.dashboard') }}" 
           class="flex items-center p-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('*.dashboard') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
            <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Dashboard</span>
        </a>

        @if(Auth::user()->role === 'admin')
        
        <div class="pt-6 pb-2">
            <div :class="sidebarOpen ? 'px-4' : 'px-0 text-center'" class="transition-all duration-300">
                <p :class="sidebarOpen ? 'text-left' : 'text-center text-[8px]'" class="text-[10px] uppercase font-bold text-orange-200 tracking-widest border-b border-orange-500 pb-1 mb-2">Master Data</p>
            </div>
            
            <a href="{{ route('admin.siswa.index') }}" 
               class="flex items-center p-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.siswa.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Data Siswa</span>
            </a>
            
            <a href="{{ route('admin.guru.index') }}" 
                class="flex items-center p-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.guru.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Data Guru</span>
            </a>

            <a href="{{ route('admin.kelas.index') }}"
                class="flex items-center p-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.kelas.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Data Kelas</span>
            </a>

            <a href="{{ route('admin.jurusan.index') }}" 
                class="flex items-center p-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.jurusan.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Data Jurusan</span>
            </a>

            <a href="{{ route('admin.mata-pelajaran.index') }}" 
                class="flex items-center p-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.mata-pelajaran.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Mata Pelajaran</span>
            </a>

            <a href="{{ route('admin.ruangan.index') }}" 
                class="flex items-center p-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.ruangan.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Data Ruangan</span>
            </a>

            <a href="{{ route('admin.tahun-ajar.index') }}" 
                class="flex items-center p-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.tahun-ajar.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Tahun Ajar</span>
            </a>

            <a href="{{ route('admin.device.index') }}" 
                class="flex items-center p-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.device.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Data Device</span>
            </a>
        </div>

        <div class="pt-2 pb-2">
            <div :class="sidebarOpen ? 'px-4' : 'px-0 text-center'" class="transition-all duration-300">
                <p :class="sidebarOpen ? 'text-left' : 'text-center text-[8px]'" class="text-[10px] uppercase font-bold text-orange-200 tracking-widest border-b border-orange-500 pb-1 mb-2">Akademik</p>
            </div>

            <a href="{{ route('admin.rombel-kelas.index') }}" 
                class="flex items-center p-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.rombel-kelas.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Rombel Siswa</span>
            </a>

            <a href="{{ route('admin.rombel-mata-pelajaran.index') }}" 
                class="flex items-center p-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.rombel-mata-pelajaran.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Plotting Guru</span>
            </a>

            <a href="{{ route('admin.rombel-jadwal.index') }}" 
                class="flex items-center p-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.rombel-jadwal.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Jadwal Pelajaran</span>
            </a>

            <a href="{{ route('admin.presensi.index') }}" 
                class="flex items-center p-3 rounded-xl transition-all duration-200 group {{ request()->routeIs('admin.presensi.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">
                    Data Absensi
                </span>
            </a>

        </div>
        @endif

    </nav>

    <div class="p-4 bg-orange-700 overflow-hidden shrink-0 shadow-inner z-10">
        <div class="flex items-center">
            <div class="h-9 w-9 rounded-full bg-white text-orange-600 flex items-center justify-center font-bold shadow-md transform group-hover:scale-110 transition-transform">
                {{ substr(Auth::user()->nama, 0, 1) }}
            </div>
            <div :class="sidebarOpen ? 'opacity-100 ml-3' : 'opacity-0 w-0'" class="transition-all duration-300 overflow-hidden whitespace-nowrap">
                <p class="text-xs font-bold leading-tight uppercase tracking-wider">{{ Auth::user()->role }}</p>
                <div class="flex items-center mt-0.5">
                    <span class="block h-2 w-2 rounded-full bg-green-400 mr-1 animate-pulse"></span>
                    <p class="text-[10px] text-orange-200 font-medium">Online</p>
                </div>
            </div>
        </div>
    </div>
</aside>