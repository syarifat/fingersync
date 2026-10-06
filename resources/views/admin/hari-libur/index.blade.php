<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Kalender Akademik & Hari Libur') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Sync Card -->
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden mb-6">
                <div class="p-8">
                    <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                        <div>
                            <h3 class="text-lg font-bold text-orange-600 uppercase tracking-tighter">Sinkronisasi Hari Libur Nasional</h3>
                            <p class="text-sm text-gray-500">Ambil otomatis data hari libur nasional resmi (PHBN/PHBI) langsung dari API eksternal.</p>
                        </div>
                        <form action="{{ route('admin.hari-libur.sync') }}" method="POST" class="flex items-center gap-3">
                            @csrf
                            <select name="year" class="border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4 font-bold text-gray-700">
                                @foreach(range(Carbon\Carbon::now()->year - 2, Carbon\Carbon::now()->year + 2) as $y)
                                    <option value="{{ $y }}" {{ $y == $selectedYear ? 'selected' : '' }}>Tahun {{ $y }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-2xl font-bold text-xs uppercase tracking-widest transition-all shadow-lg shadow-orange-100 flex items-center gap-2 hover:-translate-y-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.253 8H18"></path>
                                </svg>
                                Sinkronisasi API
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- List Holidays Card -->
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">

                    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                        <div class="w-full md:w-auto">
                            <form action="{{ route('admin.hari-libur.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
                                <input type="text" name="search" placeholder="Cari nama hari libur..." value="{{ request('search') }}" class="border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4 w-64">
                                <select name="year" onchange="this.form.submit()" class="border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4 font-bold text-gray-700">
                                    @foreach(range(Carbon\Carbon::now()->year - 3, Carbon\Carbon::now()->year + 3) as $y)
                                        <option value="{{ $y }}" {{ $y == $selectedYear ? 'selected' : '' }}>Filter: {{ $y }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-bold transition-all">Cari</button>
                                @if(request('search'))
                                    <a href="{{ route('admin.hari-libur.index', ['year' => $selectedYear]) }}" class="px-4 py-2.5 bg-red-50 text-red-600 hover:bg-red-100 rounded-xl text-sm font-bold transition-all">Reset</a>
                                @endif
                            </form>
                        </div>
                        <a href="{{ route('admin.hari-libur.create') }}" class="w-full md:w-auto px-6 py-3 bg-emerald-600 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100 flex items-center justify-center gap-2 hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Tambah Hari Libur Manual
                        </a>
                    </div>

                    @if (session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center text-emerald-700 font-bold text-sm shadow-sm animate-fade-in">
                        {{ session('success') }}
                    </div>
                    @endif

                    @if (session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center text-red-700 font-bold text-sm shadow-sm animate-fade-in">
                        {{ session('error') }}
                    </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 pl-4 font-black w-20">No</th>
                                    <th class="pb-4 font-black">Nama Hari Libur / Acara</th>
                                    <th class="pb-4 font-black text-center">Tanggal Mulai</th>
                                    <th class="pb-4 font-black text-center">Tanggal Selesai</th>
                                    <th class="pb-4 font-black text-center">Jenis</th>
                                    <th class="pb-4 font-black">Keterangan</th>
                                    <th class="pb-4 pr-4 font-black text-right w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($hari_libur as $index => $libur)
                                <tr class="group hover:bg-orange-50/10 transition-colors">
                                    <td class="py-5 pl-4 font-medium text-gray-500">
                                        {{ $hari_libur->firstItem() + $index }}
                                    </td>
                                    <td class="py-5">
                                        <span class="font-bold text-gray-900 text-base tracking-tight">{{ $libur->nama }}</span>
                                    </td>
                                    <td class="py-5 text-center text-gray-700 font-semibold">
                                        {{ Carbon\Carbon::parse($libur->tanggal_mulai)->isoFormat('DD MMMM YYYY') }}
                                    </td>
                                    <td class="py-5 text-center text-gray-700 font-semibold">
                                        {{ Carbon\Carbon::parse($libur->tanggal_selesai)->isoFormat('DD MMMM YYYY') }}
                                    </td>
                                    <td class="py-5 text-center">
                                        @if($libur->jenis === 'nasional')
                                        <span class="inline-flex items-center px-3 py-1 bg-rose-50 text-rose-700 rounded-full text-xs font-bold ring-1 ring-rose-200">
                                            Nasional (PHBN/I)
                                        </span>
                                        @else
                                        <span class="inline-flex items-center px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold ring-1 ring-emerald-200">
                                            Sekolah
                                        </span>
                                        @endif
                                    </td>
                                    <td class="py-5 text-sm text-gray-500 max-w-xs truncate" title="{{ $libur->keterangan }}">
                                        {{ $libur->keterangan ?? '-' }}
                                    </td>
                                    <td class="py-5 pr-4 text-right">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('admin.hari-libur.edit', $libur->id) }}" class="p-2 bg-orange-100 text-orange-600 rounded-lg hover:bg-orange-600 hover:text-white transition-all shadow-sm" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>

                                            <form action="{{ route('admin.hari-libur.destroy', $libur->id) }}" method="POST" onsubmit="confirmDelete(event, 'Apakah Anda yakin ingin menghapus data hari libur ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-500 hover:text-white transition-all shadow-sm" title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-3a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-gray-400 italic">Belum ada data hari libur untuk tahun {{ $selectedYear }}. Silakan tekan tombol Sinkronisasi API atau tambahkan manual.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-8">{{ $hari_libur->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
