<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Master Data Device (IoT)') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">
                    
                    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-orange-600 uppercase tracking-tighter">Daftar Perangkat FingerSync</h3>
                            <p class="text-sm text-gray-500">Manajemen Serial Number ESP32 dan Lokasi Pemasangan.</p>
                        </div>
                        <a href="{{ route('admin.device.create') }}" class="px-6 py-3 bg-orange-600 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-orange-700 transition-all shadow-lg shadow-orange-100 flex items-center gap-2 hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                            Tambah Device
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center text-emerald-700 font-bold text-sm shadow-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 pl-4 font-black">ID Device (SN)</th>
                                    <th class="pb-4 font-black">Lokasi / Ruangan</th>
                                    <th class="pb-4 font-black text-center">Status</th>
                                    <th class="pb-4 pr-4 font-black text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($devices as $d)
                                <tr class="group hover:bg-orange-50/20 transition-colors">
                                    <td class="py-5 pl-4">
                                        <div class="flex items-center gap-3">
                                            <div class="bg-gray-800 text-white p-2 rounded-lg shadow-md">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                                            </div>
                                            <span class="font-mono font-bold text-gray-800 text-lg tracking-wider">{{ $d->id_device }}</span>
                                        </div>
                                    </td>
                                    <td class="py-5">
                                        <div class="font-bold text-gray-900">{{ $d->ruangan->nama_ruangan }}</div>
                                        <div class="text-xs text-gray-400">{{ $d->ruangan->keterangan ?? 'Tidak ada detail lokasi' }}</div>
                                    </td>
                                    <td class="py-5 text-center">
                                        @if($d->status == 'Online')
                                            <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold shadow-sm">
                                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span> Online
                                            </span>
                                        @elseif($d->status == 'Maintenance')
                                            <span class="inline-flex items-center px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-bold shadow-sm">
                                                Maintenance
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-400 rounded-full text-xs font-bold">
                                                Offline
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-5 pr-4 text-right">
                                        <div class="flex justify-end gap-3 opacity-0 group-hover:opacity-100 transition-opacity">
                                            <a href="{{ route('admin.device.edit', $d->id) }}" class="text-orange-600 font-bold text-xs uppercase hover:underline">Edit</a>
                                            <form action="{{ route('admin.device.destroy', $d->id) }}" method="POST" onsubmit="return confirm('Hapus device {{ $d->id_device }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-red-600 font-bold text-xs uppercase hover:underline">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-gray-400 italic">Belum ada perangkat terdaftar.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-8">{{ $devices->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>