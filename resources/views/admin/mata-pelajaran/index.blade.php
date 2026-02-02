<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Master Data Mata Pelajaran') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">

                    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-orange-600 uppercase tracking-tighter">Kurikulum Sekolah</h3>
                            <p class="text-sm text-gray-500">Daftar mata pelajaran yang diajarkan.</p>
                        </div>
                        <a href="{{ route('admin.mata-pelajaran.create') }}" class="px-6 py-3 bg-orange-600 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-orange-700 transition-all shadow-lg shadow-orange-100 flex items-center gap-2 hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-60H6"></path>
                            </svg>
                            Tambah Mapel
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
                                    <th class="pb-4 font-black">Nama Mata Pelajaran</th>
                                    <th class="pb-4 pr-4 font-black text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($mapel as $index => $m)
                                <tr class="group hover:bg-orange-50/20 transition-colors">
                                    <td class="py-5 pl-4 font-medium text-gray-500">
                                        {{ $mapel->firstItem() + $index }}
                                    </td>
                                    <td class="py-5">
                                        <div class="flex items-center gap-3">
                                            <div class="bg-blue-50 text-blue-600 p-2 rounded-lg">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                </svg>
                                            </div>
                                            <span class="font-bold text-gray-900 text-lg">{{ $m->nama }}</span>
                                        </div>
                                    </td>
                                    <td class="py-5 pr-4 text-right">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('admin.mata-pelajaran.edit', $m->id) }}" class="p-2 bg-orange-100 text-orange-600 rounded-lg hover:bg-orange-600 hover:text-white transition-all shadow-sm" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>
                                            <form action="{{ route('admin.mata-pelajaran.destroy', $m->id) }}" method="POST" onsubmit="return confirm('Hapus mata pelajaran {{ $m->nama }}?')">
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
                                    <td colspan="3" class="py-12 text-center text-gray-400 italic">Belum ada data mata pelajaran.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-8">{{ $mapel->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>