<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Jadwal Pelajaran') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">
                    
                    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-orange-600 uppercase tracking-tighter">Agenda Kelas</h3>
                            <p class="text-sm text-gray-500">
                                Tahun Ajar: <span class="font-bold text-black">{{ session('tahun_ajar_nama', '-') }}</span>
                            </p>
                        </div>
                        <a href="{{ route('admin.rombel-jadwal.create') }}" class="px-6 py-3 bg-orange-600 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-orange-700 transition-all shadow-lg shadow-orange-100 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Buat Jadwal
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
                                    <th class="pb-4 pl-4 font-black">Hari & Jam</th>
                                    <th class="pb-4 font-black">Kelas & Mapel</th>
                                    <th class="pb-4 font-black">Ruangan</th>
                                    <th class="pb-4 font-black">Guru Pengajar</th>
                                    <th class="pb-4 pr-4 font-black text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($jadwal as $j)
                                <tr class="group hover:bg-orange-50/20 transition-colors">
                                    <td class="py-5 pl-4">
                                        <div class="font-bold text-orange-600 uppercase tracking-wide">{{ $j->hari }}</div>
                                        <div class="text-sm font-mono text-gray-600">
                                            {{ substr($j->jam_mulai, 0, 5) }} - {{ substr($j->jam_selesai, 0, 5) }}
                                        </div>
                                    </td>
                                    <td class="py-5">
                                        <div class="flex flex-col">
                                            <span class="text-xs font-bold text-gray-500 mb-1">
                                                {{ $j->rombelMapel->kelas->nama ?? '?' }}
                                            </span>
                                            <span class="font-bold text-gray-900 text-lg leading-tight">
                                                {{ $j->rombelMapel->mataPelajaran->nama ?? '-' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="py-5">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                            {{ $j->ruangan->nama_ruangan ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-5">
                                        <div class="text-sm font-semibold text-gray-700">
                                            {{ $j->rombelMapel->guru->nama ?? '-' }}
                                        </div>
                                    </td>
                                    <td class="py-5 pr-4 text-right">
                                        <div class="flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('admin.rombel-jadwal.edit', $j->id) }}" class="text-orange-600 font-bold text-xs uppercase hover:underline">Edit</a>
                                            <form action="{{ route('admin.rombel-jadwal.destroy', $j->id) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 font-bold text-xs uppercase hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-12 text-center text-gray-400 italic">
                                        Belum ada jadwal pelajaran yang dibuat.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-8">{{ $jadwal->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>