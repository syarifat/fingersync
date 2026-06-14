<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Kegiatan Sekolah Serentak') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                        <div>
                            <h3 class="text-xl font-bold text-orange-600 uppercase tracking-tighter">Manajemen Kegiatan Sekolah</h3>
                            <p class="text-sm text-gray-500">Buat kegiatan sekolah serentak (misal: Ujian, PORSENI) yang menonaktifkan KBM reguler sehingga siswa melakukan scan khusus datang/pulang saja.</p>
                        </div>
                        <a href="{{ route('admin.kegiatan-sekolah.create') }}" class="w-full md:w-auto px-6 py-3 bg-emerald-600 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100 flex items-center justify-center gap-2 hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Tambah Kegiatan Baru
                        </a>
                    </div>

                    @if (session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center text-emerald-700 font-bold text-sm shadow-sm animate-fade-in">
                        {{ session('success') }}
                    </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 pl-4 font-black w-16">No</th>
                                    <th class="pb-4 font-black">Nama Kegiatan</th>
                                    <th class="pb-4 font-black">Tanggal</th>
                                    <th class="pb-4 font-black text-center">Tipe</th>
                                    <th class="pb-4 font-black text-center">Scan Datang (Mulai - Selesai)</th>
                                    <th class="pb-4 font-black text-center">Scan Pulang (Mulai - Selesai)</th>
                                    <th class="pb-4 pr-4 font-black text-right w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($kegiatanList as $index => $kegiatan)
                                <tr class="group hover:bg-orange-50/10 transition-colors">
                                    <td class="py-5 pl-4 font-medium text-gray-500">{{ $index + 1 }}</td>
                                    <td class="py-5">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900 text-base tracking-tight">{{ $kegiatan->nama_kegiatan }}</span>
                                            @if($kegiatan->keterangan)
                                                <span class="text-xs text-gray-400 font-medium mt-0.5">{{ $kegiatan->keterangan }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-5 text-gray-700 font-semibold">
                                        {{ Carbon\Carbon::parse($kegiatan->tanggal)->isoFormat('DD MMMM YYYY') }}
                                    </td>
                                    <td class="py-5 text-center">
                                        @if($kegiatan->tipe === 'serentak')
                                        <span class="inline-flex items-center px-3 py-1 bg-rose-50 text-rose-700 rounded-full text-xs font-bold ring-1 ring-rose-200">
                                            Serentak (No KBM)
                                        </span>
                                        @else
                                        <span class="inline-flex items-center px-3 py-1 bg-gray-50 text-gray-700 rounded-full text-xs font-bold ring-1 ring-gray-200">
                                            Biasa
                                        </span>
                                        @endif
                                    </td>
                                    <td class="py-5 text-center text-gray-700 font-mono text-sm">
                                        {{ substr($kegiatan->jam_mulai_datang, 0, 5) }} - {{ substr($kegiatan->jam_selesai_datang, 0, 5) }}
                                    </td>
                                    <td class="py-5 text-center text-gray-700 font-mono text-sm">
                                        {{ substr($kegiatan->jam_mulai_pulang, 0, 5) }} - {{ substr($kegiatan->jam_selesai_pulang, 0, 5) }}
                                    </td>
                                    <td class="py-5 pr-4 text-right">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('admin.kegiatan-sekolah.edit', $kegiatan->id) }}" class="p-2 bg-orange-100 text-orange-600 rounded-lg hover:bg-orange-600 hover:text-white transition-all shadow-sm" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>

                                            <form action="{{ route('admin.kegiatan-sekolah.destroy', $kegiatan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data kegiatan ini?')">
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
                                    <td colspan="7" class="py-12 text-center text-gray-400 italic">Belum ada data kegiatan sekolah khusus. Silakan tambahkan kegiatan sekolah baru.</td>
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
