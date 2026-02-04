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
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                            Tambah Guru Baru
                        </a>
                    </div>

                    {{-- FILTER SECTION (BARU) --}}
                    <div class="mb-6 bg-gray-50 p-5 rounded-2xl border border-gray-100">
                        <form method="GET" action="{{ route('admin.guru.index') }}" class="flex flex-col md:flex-row gap-4">

                            <div class="flex-1">
                                <label for="search" class="block text-xs font-bold text-gray-500 uppercase mb-1">Cari Guru</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                                        class="pl-10 block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm"
                                        placeholder="Nama atau NIDN...">
                                </div>
                            </div>

                            <div class="md:w-1/5">
                                <label for="is_bk" class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipe Guru</label>
                                <select name="is_bk" id="is_bk" class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                                    <option value="">Semua Tipe</option>
                                    <option value="0" {{ request('is_bk') === '0' ? 'selected' : '' }}>Guru Mapel</option>
                                    <option value="1" {{ request('is_bk') === '1' ? 'selected' : '' }}>Guru BK</option>
                                </select>
                            </div>

                            <div class="md:w-1/6">
                                <label for="status" class="block text-xs font-bold text-gray-500 uppercase mb-1">Status</label>
                                <select name="status" id="status" class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                                    <option value="">Semua</option>
                                    <option value="Aktif" {{ request('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="Nonaktif" {{ request('status') == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                    <option value="Cuti" {{ request('status') == 'Cuti' ? 'selected' : '' }}>Cuti</option>
                                </select>
                            </div>

                            <div class="flex items-end gap-2">
                                <button type="submit" class="px-6 py-2.5 bg-gray-800 text-white text-sm font-bold rounded-xl hover:bg-gray-900 transition-colors shadow-sm">
                                    Filter
                                </button>
                                @if(request()->hasAny(['search', 'status', 'is_bk']))
                                <a href="{{ route('admin.guru.index') }}" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-colors flex items-center justify-center" title="Reset">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    @if (session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center text-emerald-700 font-bold text-sm shadow-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
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
                                            <svg class="w-3 h-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                            </svg>
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
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('admin.guru.edit', $g->id) }}" class="p-2 bg-orange-100 text-orange-600 rounded-lg hover:bg-orange-600 hover:text-white transition-all shadow-sm" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <form action="{{ route('admin.guru.destroy', $g->id) }}" method="POST" onsubmit="return confirm('Hapus guru ini? Akun login juga akan terhapus permanen.')">
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