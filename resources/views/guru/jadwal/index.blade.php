<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Jadwal Mengajar Mingguan') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100">
                <h3 class="text-xl font-black text-gray-800">Daftar Mengajar Aktif</h3>
                <p class="text-gray-500 text-sm mt-1">Seluruh jadwal Anda pada tahun ajaran aktif ini.</p>
            </div>

            @if($jadwalPerHari->isEmpty())
                <div class="py-16 text-center bg-white rounded-3xl shadow-sm border border-dashed border-gray-200">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <h4 class="text-lg font-bold text-gray-700">Belum Ada Jadwal</h4>
                    <p class="text-gray-500 mt-1">Anda belum diplot ke dalam jadwal pelajaran apa pun.</p>
                </div>
            @else
                
                {{-- KELOMPOKKAN PER HARI --}}
                <div class="grid grid-cols-1 gap-8">
                    @foreach($jadwalPerHari as $hari => $jadwals)
                    
                    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                        {{-- Header Hari --}}
                        <div class="bg-gray-800 text-white px-6 py-4 flex items-center gap-3">
                            <div class="p-2 bg-white/20 rounded-lg">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <h3 class="text-xl font-black uppercase tracking-widest">{{ $hari }}</h3>
                            <span class="ml-auto bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full">{{ $jadwals->count() }} Kelas</span>
                        </div>

                        {{-- Daftar Kelas di Hari Tersebut --}}
                        <div class="p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                                @foreach($jadwals as $jadwal)
                                <div class="border border-gray-100 rounded-2xl p-5 hover:border-orange-500 hover:shadow-md transition-all duration-300 relative bg-gray-50/50">
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="bg-white border border-gray-200 text-gray-800 font-bold text-sm px-3 py-1.5 rounded-lg shadow-sm">
                                            {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                        </div>
                                        <span class="text-xs font-bold px-2 py-1 bg-blue-50 text-blue-600 rounded">
                                            {{ $jadwal->ruangan->nama_ruangan }}
                                        </span>
                                    </div>
                                    
                                    <h4 class="font-black text-lg text-gray-800 mb-1 leading-tight">{{ $jadwal->rombelMataPelajaran->mataPelajaran->nama }}</h4>
                                    <p class="text-gray-600 font-medium text-sm mt-2 flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                                        Kelas: {{ $jadwal->rombelMataPelajaran->kelas->nama }}
                                    </p>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    @endforeach
                </div>
            @endif

        </div>
    </div>
</x-app-layout>