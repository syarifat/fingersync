<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Master Data Ruangan') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">
                    
                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 text-orange-600 uppercase tracking-tighter">Lokasi & Ruangan</h3>
                            <p class="text-sm text-gray-500">Kelola lokasi fisik penempatan perangkat IoT.</p>
                        </div>
                        <a href="{{ route('admin.ruangan.create') }}" class="inline-flex items-center px-6 py-3 bg-orange-600 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-orange-700 transition-all shadow-lg shadow-orange-100 hover:-translate-y-0.5">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-60H6"></path></svg>
                            Tambah Ruangan
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center gap-3 text-emerald-700 text-sm font-bold shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center gap-3 text-red-700 text-sm font-bold shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 font-black px-4 w-16">No</th>
                                    <th class="pb-4 font-black px-4">Nama Ruangan</th>
                                    <th class="pb-4 font-black px-4">Keterangan / Lokasi</th>
                                    <th class="pb-4 font-black text-right px-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($ruangan as $index => $r)
                                <tr class="group hover:bg-orange-50/40 transition-colors duration-200">
                                    <td class="py-5 px-4 font-medium text-gray-500">
                                        {{ $ruangan->firstItem() + $index }}
                                    </td>
                                    <td class="py-5 px-4">
                                        <div class="font-bold text-gray-900 text-base">{{ $r->nama_ruangan }}</div>
                                    </td>
                                    <td class="py-5 px-4">
                                        @if($r->keterangan)
                                            <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-600 rounded-lg text-xs font-semibold">
                                                {{ $r->keterangan }}
                                            </span>
                                        @else
                                            <span class="text-gray-300 text-xs italic">- Tidak ada keterangan -</span>
                                        @endif
                                    </td>
                                    <td class="py-5 px-4 text-right">
                                        <div class="flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('admin.ruangan.edit', $r->id) }}" class="text-orange-600 font-bold text-xs uppercase hover:underline flex items-center gap-1">
                                                Edit
                                            </a>
                                            <form action="{{ route('admin.ruangan.destroy', $r->id) }}" method="POST" onsubmit="return confirm('Hapus ruangan {{ $r->nama_ruangan }}? Pastikan tidak ada alat yang terhubung.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 font-bold text-xs uppercase hover:underline flex items-center gap-1">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-gray-400">
                                            <svg class="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                            <span class="italic font-medium">Belum ada data ruangan.</span>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-8">
                        {{ $ruangan->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>