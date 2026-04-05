<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Dashboard Tenaga Pendidik') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- KARTU UCAPAN SELAMAT DATANG --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-gray-100 p-8 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-6">
                    <div class="h-20 w-20 rounded-full overflow-hidden border-4 border-orange-100 bg-gray-100 flex items-center justify-center shrink-0 shadow-md">
                        @if($guru->image)
                            <img src="{{ asset('img/guru/'.$guru->image) }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-orange-600 font-bold text-3xl">{{ substr($guru->nama, 0, 1) }}</span>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-2xl font-black text-gray-800">Selamat datang, {{ $guru->nama }}!</h3>
                        <p class="text-gray-500 font-medium mt-1">NIDN: {{ $guru->nidn }}</p>
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <div class="bg-orange-50 px-6 py-3 rounded-2xl border border-orange-100 text-center">
                        <span class="block text-2xl font-black text-orange-600">{{ $totalJadwalSeminggu }}</span>
                        <span class="block text-xs font-bold text-orange-800 uppercase tracking-widest mt-1">Total Kelas</span>
                    </div>
                    @if($isWaliKelas)
                    <div class="bg-blue-50 px-6 py-3 rounded-2xl border border-blue-100 text-center">
                        <span class="block text-2xl font-black text-blue-600">{{ $isWaliKelas->kelas->nama }}</span>
                        <span class="block text-xs font-bold text-blue-800 uppercase tracking-widest mt-1">Wali Kelas</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- JADWAL HARI INI --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-gray-100 p-8">
                <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="text-lg font-black text-orange-600 uppercase tracking-wide">Jadwal Mengajar Hari Ini</h3>
                        <p class="text-sm text-gray-500 font-medium">{{ $hariIni }}, {{ \Carbon\Carbon::now()->format('d M Y') }}</p>
                    </div>
                </div>

                @if($jadwalHariIni->isEmpty())
                    <div class="py-12 text-center bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-gray-500 font-bold">Alhamdulillah, Anda tidak ada jadwal mengajar hari ini.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($jadwalHariIni as $jadwal)
                        <div class="group bg-white border border-gray-200 rounded-2xl p-5 hover:border-orange-500 hover:shadow-lg transition-all duration-300 relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-1 h-full bg-orange-500"></div>
                            
                            <div class="flex justify-between items-start mb-4">
                                <div class="bg-orange-100 text-orange-700 font-black text-sm px-3 py-1 rounded-lg">
                                    {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                </div>
                                <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded">
                                    {{ $jadwal->ruangan->nama_ruangan }}
                                </span>
                            </div>
                            
                            {{-- Nama Mata Pelajaran --}}
                            <h4 class="font-black text-xl text-gray-800 mb-1">{{ $jadwal->rombelMataPelajaran->mataPelajaran->nama }}</h4>
                            
                            {{-- Nama Kelas --}}
                            <p class="text-gray-500 font-medium text-sm flex items-center gap-2">
                                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                Kelas: {{ $jadwal->rombelMataPelajaran->kelas->nama }}
                            </p>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>