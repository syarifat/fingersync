<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Master Data Tahun Ajar') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">
                    
                    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-orange-600 uppercase tracking-tighter">Periode Akademik</h3>
                            <p class="text-sm text-gray-500">Kelola tahun ajaran dan status semester aktif.</p>
                        </div>
                        <a href="{{ route('admin.tahun-ajar.create') }}" class="px-6 py-3 bg-orange-600 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-orange-700 transition-all shadow-lg shadow-orange-100 flex items-center gap-2 hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Tambah Tahun Ajar
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center text-emerald-700 font-bold text-sm shadow-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-center text-red-700 font-bold text-sm shadow-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 pl-4 font-black w-20">No</th>
                                    <th class="pb-4 font-black">Tahun Pelajaran</th>
                                    <th class="pb-4 font-black">Semester</th>
                                    <th class="pb-4 font-black text-center">Status</th>
                                    <th class="pb-4 pr-4 font-black text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($tahun_ajar as $index => $ta)
                                <tr class="group hover:bg-orange-50/20 transition-colors {{ $ta->status_aktif ? 'bg-orange-50/40' : '' }}">
                                    <td class="py-5 pl-4 font-medium text-gray-500">
                                        {{ $tahun_ajar->firstItem() + $index }}
                                    </td>
                                    <td class="py-5">
                                        <span class="font-bold text-gray-900 text-lg tracking-tight">{{ $ta->tahun }}</span>
                                    </td>
                                    <td class="py-5">
                                        <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase {{ $ta->semester == 'Ganjil' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600' }}">
                                            {{ $ta->semester }}
                                        </span>
                                    </td>
                                    <td class="py-5 text-center">
                                        @if($ta->status_aktif)
                                            <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold shadow-sm ring-1 ring-green-200">
                                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                                AKTIF
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-400 rounded-full text-xs font-bold">
                                                Non-Aktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-5 pr-4 text-right">
                                        <div class="flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('admin.tahun-ajar.edit', $ta->id) }}" class="text-orange-600 font-bold text-xs uppercase hover:underline">Edit</a>
                                            
                                            @if(!$ta->status_aktif)
                                            <form action="{{ route('admin.tahun-ajar.destroy', $ta->id) }}" method="POST" onsubmit="return confirm('Hapus tahun ajar {{ $ta->tahun }} {{ $ta->semester }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 font-bold text-xs uppercase hover:underline">Hapus</button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-400 italic">Belum ada data tahun ajar.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-8">{{ $tahun_ajar->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>