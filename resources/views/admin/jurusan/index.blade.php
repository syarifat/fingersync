<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Master Data Jurusan') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">
                    
                    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-orange-600 uppercase tracking-tighter">Program Keahlian</h3>
                            <p class="text-sm text-gray-500">Daftar jurusan yang tersedia di sekolah.</p>
                        </div>
                        <a href="{{ route('admin.jurusan.create') }}" class="px-6 py-3 bg-orange-600 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-orange-700 transition-all shadow-lg shadow-orange-100 flex items-center gap-2 hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-60H6"></path></svg>
                            Tambah Jurusan
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
                                    <th class="pb-4 font-black">Nama Jurusan</th>
                                    <th class="pb-4 pr-4 font-black text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($jurusan as $index => $j)
                                <tr class="group hover:bg-orange-50/20 transition-colors">
                                    <td class="py-5 pl-4 font-medium text-gray-500">
                                        {{ $jurusan->firstItem() + $index }}
                                    </td>
                                    <td class="py-5">
                                        <div class="font-bold text-gray-900 text-lg">{{ $j->nama }}</div>
                                    </td>
                                    <td class="py-5 pr-4 text-right">
                                        <div class="flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('admin.jurusan.edit', $j->id) }}" class="text-orange-600 font-bold text-xs uppercase hover:underline">Edit</a>
                                            <form action="{{ route('admin.jurusan.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Hapus jurusan ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 font-bold text-xs uppercase hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="py-12 text-center text-gray-400 italic">Belum ada data jurusan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-8">{{ $jurusan->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>