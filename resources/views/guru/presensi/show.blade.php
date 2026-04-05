<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('guru.dashboard') }}" class="p-2 bg-white rounded-full shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
                {{ __('Pantau Absensi Kelas') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- HEADER INFO KELAS --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-gray-100 p-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h3 class="text-2xl font-black text-gray-800">{{ $jadwal->rombelMataPelajaran->mataPelajaran->nama }}</h3>
                    <p class="text-gray-500 font-medium mt-1">
                        Kelas: <span class="text-orange-600 font-bold">{{ $jadwal->rombelMataPelajaran->kelas->nama }}</span> | 
                        Waktu: {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }} WIB
                    </p>
                </div>
                <div class="bg-orange-50 border border-orange-100 px-6 py-3 rounded-2xl text-center">
                    <span class="block text-sm text-orange-600 font-bold uppercase tracking-widest mb-1">Tanggal Absensi</span>
                    <span class="block text-lg font-black text-gray-800">{{ \Carbon\Carbon::parse($tanggalHariIni)->isoFormat('DD MMMM YYYY') }}</span>
                </div>
            </div>

            {{-- TABEL SISWA --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-gray-100 p-8">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                <th class="pb-4 pl-4 font-black w-16">No</th>
                                <th class="pb-4 font-black">NIS</th>
                                <th class="pb-4 font-black">Nama Siswa</th>
                                <th class="pb-4 font-black text-center">Jam Scan</th>
                                <th class="pb-4 pr-4 font-black text-right">Status Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse ($rombelSiswa as $index => $rs)
                                @php
                                    // Cek apakah siswa ini ada di data presensi hari ini
                                    $absenSiswa = $presensiHariIni->get($rs->id_siswa);
                                @endphp
                            <tr class="group hover:bg-gray-50/50 transition-colors">
                                <td class="py-5 pl-4 font-medium text-gray-500">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-5 font-bold text-gray-700">
                                    {{ $rs->siswa->nis }}
                                </td>
                                <td class="py-5">
                                    <div class="font-bold text-gray-900">{{ $rs->siswa->nama }}</div>
                                    <div class="text-xs text-gray-400">{{ $rs->siswa->gender }}</div>
                                </td>
                                <td class="py-5 text-center font-mono text-sm font-bold {{ $absenSiswa ? 'text-emerald-600' : 'text-gray-400' }}">
                                    {{ $absenSiswa ? \Carbon\Carbon::parse($absenSiswa->jam_scan)->format('H:i:s') : '--:--:--' }}
                                </td>
                                <td class="py-5 pr-4 text-right">
                                    @if($absenSiswa)
                                        @if($absenSiswa->status == 'Hadir')
                                            <span class="px-4 py-1.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold shadow-sm uppercase tracking-wider">Hadir</span>
                                        @else
                                            <span class="px-4 py-1.5 bg-blue-100 text-blue-700 rounded-full text-xs font-bold shadow-sm uppercase tracking-wider">{{ $absenSiswa->status }}</span>
                                        @endif
                                    @else
                                        <span class="px-4 py-1.5 bg-red-50 text-red-500 rounded-full text-xs font-bold shadow-sm uppercase tracking-wider">Belum Hadir</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-gray-400 italic">Belum ada siswa yang diplot ke kelas ini.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>