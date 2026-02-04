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

                    {{-- FILTER SECTION --}}
                    <div class="mb-6 bg-gray-50 p-5 rounded-2xl border border-gray-100">
                        <form method="GET" action="{{ route('admin.presensi.index') }}" class="flex flex-col md:flex-row gap-4">

                            <div class="flex-1">
                                <label for="search" class="block text-xs font-bold text-gray-500 uppercase mb-1">Cari Siswa</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                                        class="pl-10 block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm"
                                        placeholder="Nama Siswa atau NISN...">
                                </div>
                            </div>

                            <div class="md:w-1/6">
                                <label for="tanggal" class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal</label>
                                <input type="date" name="tanggal" id="tanggal" value="{{ request('tanggal') }}"
                                    class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                            </div>

                            <div class="md:w-1/6">
                                <label for="kelas_id" class="block text-xs font-bold text-gray-500 uppercase mb-1">Filter Kelas</label>
                                <select name="kelas_id" id="kelas_id" class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                                    <option value="">-- Semua Kelas --</option>
                                    @foreach($kelasList as $k)
                                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:w-1/6">
                                <label for="status" class="block text-xs font-bold text-gray-500 uppercase mb-1">Status</label>
                                <select name="status" id="status" class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                                    <option value="">-- Semua Status --</option>
                                    @foreach(['Hadir', 'Izin', 'Sakit', 'Terlambat', 'Alpa'] as $s)
                                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                                        {{ $s }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex items-end gap-2">
                                <button type="submit" class="px-6 py-2.5 bg-gray-800 text-white text-sm font-bold rounded-xl hover:bg-gray-900 transition-colors shadow-sm">
                                    Filter
                                </button>
                                @if(request()->hasAny(['search', 'tanggal', 'kelas_id', 'status']))
                                <a href="{{ route('admin.presensi.index') }}" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-colors flex items-center justify-center" title="Reset">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </a>
                                @endif
                            </div>

                        </form>
                    </div>

                    {{-- Alert Success --}}
                    @if (session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 text-sm font-bold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                    @endif

                    {{-- Alert Error --}}
                    @if (session('error'))
                    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-sm font-bold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
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