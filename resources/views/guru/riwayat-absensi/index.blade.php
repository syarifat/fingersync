<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Cari Riwayat Absensi') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- FILTER BOX --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-gray-100 p-8">
                <form method="GET" action="{{ route('guru.riwayat-absensi.index') }}" class="flex flex-col md:flex-row gap-6 items-end">
                    <div class="md:w-1/3 w-full">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Pilih Tanggal</label>
                        <input type="date" name="tanggal" value="{{ $tanggalFilter }}" class="w-full rounded-xl border-gray-200 bg-gray-50 focus:border-orange-500 focus:ring-orange-500 shadow-sm font-bold text-gray-700" onchange="this.form.submit()">
                    </div>
                    <div class="flex-1 w-full">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Filter Kelas (Opsional)</label>
                        <select name="kelas_id" class="w-full rounded-xl border-gray-200 bg-gray-50 font-bold text-gray-700 focus:border-orange-500 focus:ring-orange-500 shadow-sm" onchange="this.form.submit()">
                            <option value="">Semua Kelas</option>
                            @foreach($kelasList as $k)
                                <option value="{{ $k->id }}" {{ $kelasFilter == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            {{-- KOTAK JADWAL --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-gray-100 p-8">
                <div class="mb-6 border-b border-gray-100 pb-4">
                    <h3 class="text-lg font-black text-orange-600 uppercase tracking-wide">Jadwal: Hari {{ $hariFilter }}</h3>
                    <p class="text-sm text-gray-500 font-medium">{{ \Carbon\Carbon::parse($tanggalFilter)->isoFormat('DD MMMM YYYY') }}</p>
                </div>

                @if($jadwalList->isEmpty())
                    <div class="py-12 text-center bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <p class="text-gray-500 font-bold">Tidak ada jadwal mengajar pada tanggal ini.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($jadwalList as $jadwal)
                        <div class="group bg-white border border-gray-200 rounded-2xl p-5 hover:border-orange-500 hover:shadow-lg transition-all duration-300 relative overflow-hidden flex flex-col justify-between">
                            <div class="absolute top-0 left-0 w-1 h-full bg-orange-500"></div>
                            
                            <div>
                                <div class="flex justify-between items-start mb-4">
                                    <div class="bg-orange-100 text-orange-700 font-black text-sm px-3 py-1 rounded-lg">
                                        {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                    </div>
                                    <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded">{{ $jadwal->ruangan->nama_ruangan }}</span>
                                </div>
                                <h4 class="font-black text-xl text-gray-800 mb-1">{{ $jadwal->rombelMataPelajaran->mataPelajaran->nama }}</h4>
                                <p class="text-gray-500 font-medium text-sm flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-orange-500"></span> Kelas: {{ $jadwal->rombelMataPelajaran->kelas->nama }}
                                </p>
                            </div>

                            <div class="mt-5 pt-4 border-t border-gray-100 flex justify-end">
                                {{-- Link menuju halaman Show dengan membawa parameter Tanggal --}}
                                <a href="{{ route('guru.riwayat-absensi.show', $jadwal->id) }}?tanggal={{ $tanggalFilter }}" class="px-4 py-2 bg-gray-800 text-white rounded-xl text-sm font-bold hover:bg-orange-600 transition-colors shadow-sm flex items-center gap-2">
                                    Kelola Absensi
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>