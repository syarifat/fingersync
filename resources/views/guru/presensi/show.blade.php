<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('guru.dashboard') }}" class="p-2 bg-white rounded-full shadow-sm border border-gray-200 hover:bg-gray-50 transition-colors">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
                {{ __('Kelola Presensi Kelas') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- ALERT SUCCESS & ERROR --}}
            @if (session('success'))
            <div class="p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-xl font-bold text-sm shadow-sm">
                {{ session('success') }}
            </div>
            @endif
            @if (session('error'))
            <div class="p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl font-bold text-sm shadow-sm">
                {{ session('error') }}
            </div>
            @endif

            {{-- HEADER INFO KELAS --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-gray-100 p-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h3 class="text-2xl font-black text-gray-800">{{ $jadwal->rombelMapel->mataPelajaran->nama }}</h3>
                    <p class="text-gray-500 font-medium mt-1">
                        Kelas: <span class="text-orange-600 font-bold">{{ $jadwal->rombelMapel->kelas->nama }}</span> | 
                        Waktu: {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }} WIB
                    </p>
                </div>
                <div class="bg-orange-50 border border-orange-100 px-6 py-3 rounded-2xl text-center">
                    <span class="block text-sm text-orange-600 font-bold uppercase tracking-widest mb-1">Tanggal Presensi</span>
                    <span class="block text-lg font-black text-gray-800">{{ \Carbon\Carbon::parse($tanggalHariIni)->isoFormat('DD MMMM YYYY') }}</span>
                </div>
            </div>

            {{-- TABEL FORM SISWA --}}
            <form action="{{ route('guru.presensi.update', $jadwal->id) }}" method="POST">
                @csrf
                <div class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-gray-100 p-8">
                    <div class="mb-6 flex justify-between items-center border-b border-gray-100 pb-4">
                        <p class="text-sm text-gray-500">Ubah status pada kolom dropdown, lalu klik tombol <b>Simpan Presensi</b> di bawah.</p>
                        <button type="submit" class="px-6 py-2.5 bg-orange-600 text-white rounded-xl text-sm font-bold hover:bg-orange-700 transition-colors shadow-lg">
                            Simpan Presensi
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 pl-4 font-black w-16">No</th>
                                    <th class="pb-4 font-black">NIS</th>
                                    <th class="pb-4 font-black">Nama Siswa</th>
                                    <th class="pb-4 font-black text-center">Waktu Scan</th>
                                    <th class="pb-4 pr-4 font-black text-right w-48">Status Kehadiran</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($rombelSiswa as $index => $rs)
                                    @php
                                        // Cari data absensi siswa ini
                                        $absenSiswa = $presensiHariIni->get($rs->id_siswa);
                                        // Tentukan status saat ini
                                        $statusSaatIni = $absenSiswa ? $absenSiswa->status : 'Belum Hadir';
                                    @endphp
                                <tr class="group hover:bg-gray-50/50 transition-colors">
                                    <td class="py-4 pl-4 font-medium text-gray-500">
                                        {{ $index + 1 }}
                                    </td>
                                    <td class="py-4 font-bold text-gray-700">
                                        {{ $rs->siswa->nis }}
                                    </td>
                                    <td class="py-4">
                                        <div class="font-bold text-gray-900">{{ $rs->siswa->nama }}</div>
                                    </td>
                                    <td class="py-4 text-center font-mono text-sm font-bold {{ $absenSiswa ? 'text-emerald-600' : 'text-gray-400' }}">
                                        {{ $absenSiswa ? \Carbon\Carbon::parse($absenSiswa->jam_scan)->format('H:i:s') : '--:--:--' }}
                                    </td>
                                    <td class="py-4 pr-4 text-right">
                                        {{-- DROPDOWN STATUS --}}
                                        <select name="status[{{ $rs->id_siswa }}]" class="w-full rounded-xl text-sm font-bold shadow-sm focus:ring-orange-500 border-gray-200 
                                            {{ $statusSaatIni == 'Hadir' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : '' }}
                                            {{ $statusSaatIni == 'Sakit' ? 'bg-blue-50 text-blue-700 border-blue-200' : '' }}
                                            {{ $statusSaatIni == 'Izin' ? 'bg-orange-50 text-orange-700 border-orange-200' : '' }}
                                            {{ $statusSaatIni == 'Alpha' ? 'bg-red-50 text-red-700 border-red-200' : '' }}
                                            {{ $statusSaatIni == 'Belum Hadir' ? 'bg-gray-50 text-gray-500' : '' }}
                                        ">
                                            <option value="Belum Hadir" {{ $statusSaatIni == 'Belum Hadir' ? 'selected' : '' }}>Belum Hadir</option>
                                            <option value="Hadir" {{ $statusSaatIni == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                            <option value="Sakit" {{ $statusSaatIni == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                            <option value="Izin" {{ $statusSaatIni == 'Izin' ? 'selected' : '' }}>Izin</option>
                                            <option value="Alpha" {{ $statusSaatIni == 'Alpha' ? 'selected' : '' }}>Alpha</option>
                                        </select>
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
                    
                    <div class="mt-8 flex justify-end border-t border-gray-100 pt-6">
                        <button type="submit" class="px-8 py-3 bg-orange-600 text-white rounded-xl text-sm font-black tracking-widest uppercase hover:bg-orange-700 transition-colors shadow-lg">
                            SIMPAN SEMUA PERUBAHAN
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>