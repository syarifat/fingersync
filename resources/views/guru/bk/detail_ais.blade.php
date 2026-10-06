<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight">
            {{ __('Rincian Ketidakhadiran Siswa (AIS)') }}
        </h2>
        <p class="text-xs text-gray-500 mt-1 font-medium">
            Panel Bimbingan Konseling (BK) &bull; Riwayat Alpha, Izin, dan Sakit untuk Penanganan Siswa
        </p>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Tombol Kembali --}}
            <div class="flex justify-between items-center">
                <a href="{{ route('guru.bk.presensi.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-200 rounded-xl font-bold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-all gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali Ke Rekap Presensi BK
                </a>
            </div>

            {{-- Kartu Profil Siswa & Statistik AIS --}}
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl flex items-center justify-center text-white text-2xl font-black shadow-md shadow-orange-100">
                                {{ strtoupper(substr($siswa->nama, 0, 2)) }}
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-gray-900">{{ $siswa->nama }}</h3>
                                <p class="text-xs text-gray-500 font-bold mt-1">
                                    NIS: <span class="font-mono text-gray-700">{{ $siswa->nis ?? '-' }}</span> &bull;
                                    Kelas: <span class="text-gray-700">{{ $siswa->rombelKelas->kelas->nama ?? '-' }}</span> &bull;
                                    Wali Murid: <span class="text-gray-700">{{ $siswa->nohp_ortu ?? '-' }}</span>
                                </p>
                            </div>
                        </div>

                        {{-- Ringkasan Statistik AIS --}}
                        <div class="grid grid-cols-3 gap-3 w-full md:w-auto">
                            <div class="bg-rose-50 border border-rose-100 p-4 rounded-2xl text-center min-w-[5.5rem]">
                                <span class="block text-[11px] font-black text-rose-500 uppercase tracking-wider">Alpha</span>
                                <span class="text-2xl font-black text-rose-700 mt-1 block">{{ $counts['Alpha'] }}</span>
                            </div>
                            <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-2xl text-center min-w-[5.5rem]">
                                <span class="block text-[11px] font-black text-indigo-500 uppercase tracking-wider">Izin</span>
                                <span class="text-2xl font-black text-indigo-700 mt-1 block">{{ $counts['Izin'] }}</span>
                            </div>
                            <div class="bg-purple-50 border border-purple-100 p-4 rounded-2xl text-center min-w-[5.5rem]">
                                <span class="block text-[11px] font-black text-purple-500 uppercase tracking-wider">Sakit</span>
                                <span class="text-2xl font-black text-purple-700 mt-1 block">{{ $counts['Sakit'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel Rincian Data AIS --}}
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <div class="mb-6">
                        <h4 class="text-lg font-black text-gray-900">Riwayat Ketidakhadiran Terdata</h4>
                        <p class="text-xs text-gray-400 mt-0.5">Daftar presensi Alpha, Izin, dan Sakit siswa pada tahun ajaran ini.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100 bg-gray-50/50">
                                    <th class="p-4 font-black w-12 text-center">No</th>
                                    <th class="p-4 font-black">Hari &amp; Tanggal</th>
                                    <th class="p-4 font-black text-center">Status</th>
                                    <th class="p-4 font-black">Mata Pelajaran / Sesi</th>
                                    <th class="p-4 font-black">Ruangan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse ($records as $index => $row)
                                <tr class="group hover:bg-orange-50/20 transition-colors">
                                    <td class="p-4 text-center font-bold text-gray-400 text-xs">{{ $index + 1 }}</td>
                                    <td class="p-4 font-bold text-gray-800 text-sm">
                                        {{ \Carbon\Carbon::parse($row->tanggal)->locale('id')->isoFormat('dddd, DD MMMM YYYY') }}
                                        <div class="text-[11px] font-normal text-gray-400 font-mono">{{ substr($row->jam_scan, 0, 5) }} WIB</div>
                                    </td>
                                    <td class="p-4 text-center">
                                        @php
                                        $badgeClass = match($row->status) {
                                            'Alpa', 'Alpha' => 'bg-rose-50 text-rose-700 border-rose-200',
                                            'Sakit' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'Izin' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            default => 'bg-gray-50 text-gray-700 border-gray-200'
                                        };
                                        @endphp
                                        <span class="px-3 py-1 rounded-xl text-xs font-black uppercase border {{ $badgeClass }}">
                                            {{ $row->status }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-sm text-gray-700 font-medium">
                                        @if($row->rombelJadwalPelajaran && $row->rombelJadwalPelajaran->rombelMataPelajaran && $row->rombelJadwalPelajaran->rombelMataPelajaran->mataPelajaran)
                                            {{ $row->rombelJadwalPelajaran->rombelMataPelajaran->mataPelajaran->nama }}
                                        @elseif($row->kegiatanSekolah)
                                            Kegiatan: {{ $row->kegiatanSekolah->nama_kegiatan }}
                                        @else
                                            Presensi Harian / Mandiri
                                        @endif
                                    </td>
                                    <td class="p-4 text-xs font-bold text-gray-500">
                                        {{ $row->device->ruangan->nama_ruangan ?? 'Manual / BK' }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-gray-400 italic text-sm">
                                        Siswa ini tidak memiliki catatan Alpha, Izin, maupun Sakit.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
