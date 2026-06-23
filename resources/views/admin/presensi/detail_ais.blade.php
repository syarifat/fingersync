<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Rincian Ketidakhadiran Siswa (AIS)') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Tombol Kembali --}}
            <div class="flex justify-between items-center">
                <a href="{{ route('admin.presensi.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-xl font-bold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali Ke Riwayat
                </a>
            </div>

            {{-- Kartu Info Siswa --}}
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-orange-100 rounded-full flex items-center justify-center text-orange-600 text-2xl font-black">
                                {{ strtoupper(substr($siswa->nama, 0, 2)) }}
                            </div>
                            <div>
                                <h3 class="text-xl font-black text-gray-900">{{ $siswa->nama }}</h3>
                                <p class="text-sm text-gray-500 font-bold">NIS: {{ $siswa->nis ?? '-' }} | Kelas: {{ $siswa->rombelKelas->kelas->nama ?? '-' }}</p>
                            </div>
                        </div>
                        
                        {{-- Ringkasan Statistik --}}
                        <div class="grid grid-cols-3 gap-3 md:w-auto w-full">
                            <div class="bg-rose-50 border border-rose-100 p-4 rounded-2xl text-center w-28">
                                <span class="block text-xs font-bold text-rose-500 uppercase tracking-wider">Alpha</span>
                                <span class="text-2xl font-black text-rose-700 mt-1 block">{{ $counts['Alpha'] }}</span>
                            </div>
                            <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-2xl text-center w-28">
                                <span class="block text-xs font-bold text-indigo-500 uppercase tracking-wider">Izin</span>
                                <span class="text-2xl font-black text-indigo-700 mt-1 block">{{ $counts['Izin'] }}</span>
                            </div>
                            <div class="bg-purple-50 border border-purple-100 p-4 rounded-2xl text-center w-28">
                                <span class="block text-xs font-bold text-purple-500 uppercase tracking-wider">Sakit</span>
                                <span class="text-2xl font-black text-purple-700 mt-1 block">{{ $counts['Sakit'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel Detail --}}
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <div class="mb-6">
                        <h4 class="text-lg font-black text-orange-600 uppercase tracking-tighter">Riwayat Ketidakhadiran Resmi</h4>
                        <p class="text-sm text-gray-500">Daftar semua data ketidakhadiran (Alpa, Izin, Sakit) untuk siswa ini pada tahun ajaran berjalan.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 font-black px-4">No</th>
                                    <th class="pb-4 font-black">Tanggal</th>
                                    <th class="pb-4 font-black text-center">Status</th>
                                    <th class="pb-4 font-black">Mata Pelajaran / Kegiatan</th>
                                    <th class="pb-4 font-black px-4">Ruang</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($records as $index => $row)
                                <tr class="group hover:bg-orange-50/10 transition-colors">
                                    <td class="py-5 px-4 font-bold text-gray-400 text-sm w-12">{{ $index + 1 }}</td>
                                    
                                    {{-- Kolom Tanggal --}}
                                    <td class="py-5">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900">
                                                {{ \Carbon\Carbon::parse($row->tanggal)->locale('id')->isoFormat('dddd, DD MMMM YYYY') }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Kolom Status --}}
                                    <td class="py-5 text-center">
                                        @php
                                        $badgeClass = match($row->status) {
                                            'Alpa', 'Alpha' => 'bg-rose-50 text-rose-600 border border-rose-200',
                                            'Sakit' => 'bg-purple-50 text-purple-600 border border-purple-200',
                                            'Izin' => 'bg-indigo-50 text-indigo-600 border border-indigo-200',
                                            default => 'bg-gray-50 text-gray-600 border border-gray-200'
                                        };
                                        @endphp
                                        <span class="px-3 py-1 rounded-lg text-xs font-black uppercase {{ $badgeClass }}">
                                            {{ $row->status }}
                                        </span>
                                    </td>

                                    {{-- Kolom Mapel --}}
                                    <td class="py-5">
                                        @if($row->rombelJadwalPelajaran)
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900">
                                                {{ $row->rombelJadwalPelajaran->rombelMataPelajaran->mataPelajaran->nama ?? 'Mapel Tidak Ditemukan' }}
                                            </span>
                                            <span class="text-xs text-gray-400 font-bold mt-0.5">
                                                KBM Reguler: {{ substr($row->rombelJadwalPelajaran->jam_mulai, 0, 5) }} - {{ substr($row->rombelJadwalPelajaran->jam_selesai, 0, 5) }}
                                            </span>
                                        </div>
                                        @elseif($row->kegiatanSekolah)
                                        <div class="flex flex-col">
                                            <span class="font-bold text-orange-600">
                                                Kegiatan: {{ $row->kegiatanSekolah->nama_kegiatan }}
                                            </span>
                                            <span class="text-xs text-gray-400 font-bold mt-0.5">
                                                Tipe: {{ ucfirst($row->kegiatanSekolah->tipe) }}
                                            </span>
                                        </div>
                                        @else
                                        <span class="text-gray-400 italic text-xs font-bold">- Diluar Jadwal / Input Manual -</span>
                                        @endif
                                    </td>

                                    {{-- Kolom Ruang --}}
                                    <td class="py-5 px-4">
                                        <span class="text-xs text-gray-700 bg-gray-100 px-2.5 py-1 rounded-lg font-bold">
                                            {{ $row->device->ruangan->nama_ruangan ?? 'Manual/Sistem' }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-400 italic font-bold">
                                        Siswa ini tidak memiliki riwayat ketidakhadiran (AIS) pada tahun ajaran ini.
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
