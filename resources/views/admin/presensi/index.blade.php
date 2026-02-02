<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Manajemen Data Presensi') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">
                    
                    {{-- Header Section: Judul & Total Count --}}
                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-orange-600 uppercase tracking-tighter">Riwayat Kehadiran Siswa</h3>
                            <p class="text-sm text-gray-500">Total data masuk: {{ $dataPresensi->total() }} Baris</p>
                        </div>
                        
                        {{-- Tombol (Export/Input Manual) - Opsional --}}
                        {{-- <a href="#" class="inline-flex items-center px-6 py-3 bg-orange-600 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-orange-700 transition-all shadow-lg shadow-orange-100">
                            + Input Manual
                        </a> --}}
                    </div>

                    {{-- Alert Success --}}
                    @if (session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 text-sm font-bold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        {{ session('success') }}
                    </div>
                    @endif

                    {{-- Alert Error --}}
                    @if (session('error'))
                    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-sm font-bold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        {{ session('error') }}
                    </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 font-black px-4">Waktu</th>
                                    <th class="pb-4 font-black">Siswa</th>
                                    <th class="pb-4 font-black">Jadwal / Mapel</th>
                                    <th class="pb-4 font-black text-center">Status</th>
                                    <th class="pb-4 font-black text-right px-4">Device</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($dataPresensi as $row)
                                <tr class="group hover:bg-orange-50/30 transition-colors">
                                    
                                    {{-- Kolom Waktu --}}
                                    <td class="py-5 px-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900">
                                                {{ \Carbon\Carbon::parse($row->tanggal)->format('d M Y') }}
                                            </span>
                                            <span class="text-xs text-orange-500 font-bold tracking-wider">
                                                {{ $row->jam_scan }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Kolom Siswa --}}
                                    <td class="py-5">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900">{{ $row->siswa->nama ?? 'Siswa dihapus' }}</span>
                                            <span class="text-xs text-gray-400 font-bold">{{ $row->siswa->nisn ?? '-' }}</span>
                                        </div>
                                    </td>

                                    {{-- Kolom Jadwal --}}
                                    <td class="py-5">
                                        @if($row->jadwal)
                                            <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-xs font-black uppercase">
                                                {{ $row->jadwal->nama_pelajaran }}
                                            </span>
                                            <div class="text-xs text-gray-400 mt-1 pl-1">
                                                {{ $row->jadwal->jam_mulai }} - {{ $row->jadwal->jam_selesai }}
                                            </div>
                                        @else
                                            <span class="text-gray-300 italic text-xs font-bold">- Diluar Jadwal -</span>
                                        @endif
                                    </td>

                                    {{-- Kolom Status --}}
                                    <td class="py-5 text-center">
                                        @php
                                            $badgeClass = match($row->status) {
                                                'Hadir' => 'bg-emerald-50 text-emerald-600',
                                                'Terlambat' => 'bg-amber-50 text-amber-600',
                                                'Alpa' => 'bg-rose-50 text-rose-600',
                                                'Sakit' => 'bg-purple-50 text-purple-600',
                                                'Izin' => 'bg-indigo-50 text-indigo-600',
                                                default => 'bg-gray-50 text-gray-600'
                                            };
                                        @endphp
                                        <span class="px-3 py-1 rounded-lg text-xs font-black uppercase {{ $badgeClass }}">
                                            {{ $row->status }}
                                        </span>
                                    </td>

                                    {{-- Kolom Device --}}
                                    <td class="py-5 px-4 text-right">
                                        <span class="text-xs font-mono text-gray-400 bg-gray-50 px-2 py-1 rounded font-bold">
                                            {{ $row->device->nama_device ?? $row->id_device }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-10 text-center text-gray-400 italic font-bold">
                                        Belum ada data presensi yang terekam.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-8">
                        {{ $dataPresensi->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>