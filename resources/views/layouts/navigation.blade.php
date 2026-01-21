<aside 
    :class="sidebarOpen ? 'w-64' : 'w-20'" 
    class="relative bg-orange-600 h-screen text-white transition-all duration-300 ease-in-out flex flex-col shadow-xl z-40">
    
    <div class="flex items-center justify-center h-20 bg-orange-700 overflow-hidden shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="bg-white p-2 rounded-xl shadow-lg">
                <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M13 7H7v6h6V7z" />
                    <path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5v10h10V5H5z" clip-rule="evenodd" />
                </svg>
            </div>
            <span :class="sidebarOpen ? 'opacity-100' : 'opacity-0'" class="font-bold text-lg tracking-wider transition-opacity duration-300 whitespace-nowrap">
                FINGER<span class="text-orange-200">SYNC</span>
            </span>
        </a>
    </div>

    <nav class="flex-1 mt-6 px-3 space-y-2 overflow-y-auto">
        
        <a href="{{ Auth::user()->role === 'admin' ? route('admin.dashboard') : route('guru.dashboard') }}" 
           class="flex items-center p-3 rounded-xl transition-colors group {{ request()->routeIs('*.dashboard') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
            <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Dashboard</span>
        </a>

        @if(Auth::user()->role === 'admin')
        <div class="pt-4 pb-2">
            <p :class="sidebarOpen ? 'block' : 'hidden'" class="text-[10px] uppercase font-bold text-orange-300 px-4 mb-2 tracking-widest">Master Data</p>
            
            <a href="{{ route('admin.siswa.index') }}" 
               class="flex items-center p-3 rounded-xl transition-colors group {{ request()->routeIs('admin.siswa.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Data Siswa</span>
            </a>
            
            <a href="{{ route('admin.kelas.index') }}"
                class="flex items-center p-3 rounded-xl transition-colors group {{ request()->routeIs('admin.kelas.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Data Kelas</span>
            </a>

            <a href="{{ route('admin.ruangan.index') }}" 
               class="flex items-center p-3 rounded-xl transition-colors group {{ request()->routeIs('admin.ruangan.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Data Ruangan</span>
            </a>

            <a href="{{ route('admin.guru.index') }}" 
                class="flex items-center p-3 rounded-xl transition-colors group {{ request()->routeIs('admin.guru.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Data Guru</span>
            </a>

            <a href="{{ route('admin.jurusan.index') }}" 
                class="flex items-center p-3 rounded-xl transition-colors group {{ request()->routeIs('admin.jurusan.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Data Jurusan</span>
            </a>

            <a href="{{ route('admin.mata-pelajaran.index') }}" 
                class="flex items-center p-3 rounded-xl transition-colors group {{ request()->routeIs('admin.mata-pelajaran.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Mata Pelajaran</span>
            </a>

            <a href="{{ route('admin.tahun-ajar.index') }}" 
                class="flex items-center p-3 rounded-xl transition-colors group {{ request()->routeIs('admin.tahun-ajar.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Tahun Ajar</span>
            </a>

            <a href="{{ route('admin.device.index') }}" 
                class="flex items-center p-3 rounded-xl transition-colors group {{ request()->routeIs('admin.device.*') ? 'bg-orange-700 shadow-inner' : 'hover:bg-orange-500' }}">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                </svg>
                <span :class="sidebarOpen ? 'opacity-100 ml-4' : 'opacity-0 w-0'" class="font-medium transition-all duration-300 overflow-hidden whitespace-nowrap">Data Device</span>
            </a>

        </div>
        @endif

    </nav>

    <div class="p-4 bg-orange-700 overflow-hidden shrink-0">
        <div class="flex items-center">
            <div class="h-8 w-8 rounded-full bg-white text-orange-600 flex items-center justify-center font-bold">
                {{ substr(Auth::user()->nama, 0, 1) }}
            </div>
            <div :class="sidebarOpen ? 'opacity-100 ml-3' : 'opacity-0 w-0'" class="transition-all duration-300 overflow-hidden whitespace-nowrap">
                <p class="text-xs font-bold leading-tight uppercase">{{ Auth::user()->role }}</p>
                <p class="text-[10px] text-orange-200">Online</p>
            </div>
        </div>
    </div>
</aside>