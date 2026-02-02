<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Manajemen Data Siswa') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100">
                <div class="p-8">

                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-orange-600 uppercase tracking-tighter">Daftar Siswa Aktif</h3>
                            <p class="text-sm text-gray-500">Kelola informasi siswa dan sinkronisasi biometrik perangkat.</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('admin.siswa.create') }}" class="inline-flex items-center px-5 py-2.5 bg-orange-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-orange-700 focus:outline-none focus:ring-4 focus:ring-orange-100 transition-all duration-200 shadow-sm shadow-orange-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Tambah Siswa Baru
                            </a>
                        </div>
                    </div>

                    @if (session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center text-emerald-700">
                        <svg class="w-5 h-5 mr-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 font-semibold pl-4">Identitas Siswa</th>
                                    <th class="pb-4 font-semibold text-center">Jurusan</th>
                                    <th class="pb-4 font-semibold text-center">Biometric ID</th>
                                    <th class="pb-4 font-semibold text-center">Status</th>
                                    <th class="pb-4 font-semibold text-right pr-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($siswa as $s)
                                <tr class="group hover:bg-gray-50/50 transition-colors">
                                    <td class="py-5 pl-4">
                                        <div class="flex items-center gap-4">
                                            <div class="h-12 w-12 rounded-full overflow-hidden border-2 border-orange-100 shadow-sm flex-shrink-0 bg-gray-100 flex items-center justify-center">
                                                @if($s->image)
                                                <img src="{{ asset('img/siswa/'.$s->image) }}" alt="{{ $s->nama }}" class="w-full h-full object-cover">
                                                @else
                                                <span class="text-orange-600 font-bold text-lg">{{ substr($s->nama, 0, 1) }}</span>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="text-sm font-bold text-gray-900">{{ $s->nama }}</div>
                                                <div class="text-xs text-gray-400 font-mono italic">NIS: {{ $s->nis }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-5 text-center">
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-md bg-gray-100 text-gray-600">
                                            {{ $s->jurusan->nama ?? 'Umum' }}
                                        </span>
                                    </td>
                                    <td class="py-5 text-center">
                                        <div class="inline-flex items-center px-2 py-1 bg-amber-50 rounded-md border border-amber-100">
                                            <svg class="w-3 h-3 text-amber-500 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2V7a5 5 0 00-5-5zM7 7a3 3 0 016 0v2H7V7z"></path>
                                            </svg>
                                            <span class="text-xs font-mono font-bold text-amber-700">ID-{{ $s->fingerprint_id }}</span>
                                        </div>
                                    </td>
                                    <td class="py-5 text-center">
                                        @if($s->status == 'Aktif')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                            <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-emerald-500"></span>
                                            Aktif
                                        </span>
                                        @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $s->status }}
                                        </span>
                                        @endif
                                    </td>
                                    <td class="py-5 text-right pr-4">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('admin.siswa.edit', $s->id) }}" class="p-2 bg-orange-100 text-orange-600 rounded-lg hover:bg-orange-600 hover:text-white transition-all shadow-sm" title="Edit Data">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                </svg>
                                            </a>
                                            <form action="{{ route('admin.siswa.destroy', $s->id) }}" method="POST" onsubmit="return confirm('Hapus data siswa {{ $s->nama }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-500 hover:text-white transition-all shadow-sm" title="Hapus Data">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center">
                                        <div class="flex flex-col items-center">
                                            <svg class="w-12 h-12 text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                            </svg>
                                            <p class="text-gray-400 font-medium">Belum ada data siswa yang terdaftar.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-10 border-t border-gray-100 pt-6">
                        {{ $siswa->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>