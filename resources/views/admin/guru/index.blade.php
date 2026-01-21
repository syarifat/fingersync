<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Manajemen Data Guru') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-gray-100">
                <div class="p-8">
                    
                    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-orange-600 uppercase tracking-tighter">Daftar Tenaga Pendidik</h3>
                            <p class="text-sm text-gray-500">Kelola akun dan profil guru.</p>
                        </div>
                        <a href="{{ route('admin.guru.create') }}" class="px-6 py-3 bg-orange-600 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-orange-700 transition-all shadow-lg shadow-orange-100 flex items-center gap-2 hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                            Tambah Guru Baru
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center text-emerald-700 font-bold text-sm shadow-sm">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 pl-4 font-black">Profil Guru</th>
                                    <th class="pb-4 font-black">NIDN / Kontak</th>
                                    <th class="pb-4 font-black text-center">Status</th>
                                    <th class="pb-4 pr-4 font-black text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($guru as $g)
                                <tr class="group hover:bg-orange-50/20 transition-colors duration-200">
                                    <td class="py-5 pl-4">
                                        <div class="flex items-center gap-4">
                                            <div class="h-12 w-12 rounded-full overflow-hidden border-2 border-orange-100 bg-gray-100 flex items-center justify-center shrink-0 shadow-sm">
                                                @if($g->image)
                                                    <img src="{{ asset('img/guru/'.$g->image) }}" class="w-full h-full object-cover">
                                                @else
                                                    <span class="text-orange-600 font-bold text-lg">{{ substr($g->nama, 0, 1) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-bold text-gray-900 flex items-center gap-2 text-base">
                                                    {{ $g->nama }}
                                                    @if($g->is_bk)
                                                        <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-[10px] rounded-full uppercase tracking-wider font-bold">BK</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs text-gray-400 font-medium">{{ $g->gender }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-5">
                                        <div class="text-sm font-bold text-gray-700">{{ $g->nidn }}</div>
                                        <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                            <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                            {{ $g->nohp }}
                                        </div>
                                    </td>
                                    <td class="py-5 text-center">
                                        @if($g->status == 'Aktif')
                                            <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold shadow-sm">Aktif</span>
                                        @else
                                            <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold shadow-sm">{{ $g->status }}</span>
                                        @endif
                                    </td>
                                    <td class="py-5 pr-4 text-right">
                                        <div class="flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('admin.guru.edit', $g->id) }}" class="text-orange-600 font-bold text-xs uppercase hover:underline">Edit</a>
                                            <form action="{{ route('admin.guru.destroy', $g->id) }}" method="POST" onsubmit="return confirm('Hapus guru ini? Akun login juga akan terhapus permanen.')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 font-bold text-xs uppercase hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-gray-400 italic bg-gray-50 rounded-xl m-4">Belum ada data guru.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-8">{{ $guru->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>