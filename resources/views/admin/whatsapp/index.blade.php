<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Konektivitas & Log WhatsApp') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- STATUS FONNTE --}}
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden mb-8">
                <div class="p-8">
                    <h3 class="text-lg font-bold text-orange-600 uppercase tracking-tighter mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        Status Device Fonnte
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
                        <div>
                            <div class="mb-4">
                                <span class="block text-xs font-bold text-gray-400 uppercase">Status Koneksi</span>
                                @if($fonnteStatus == 'connect')
                                    <span class="inline-block mt-1 px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-sm font-black uppercase">TERHUBUNG (ONLINE)</span>
                                @elseif($fonnteStatus == 'disconnected')
                                    <span class="inline-block mt-1 px-3 py-1 bg-rose-50 text-rose-600 rounded-lg text-sm font-black uppercase">TERPUTUS (OFFLINE)</span>
                                @else
                                    <span class="inline-block mt-1 px-3 py-1 bg-gray-50 text-gray-600 rounded-lg text-sm font-black uppercase">ERROR MENGAMBIL STATUS</span>
                                @endif
                            </div>
                            <div class="mb-4">
                                <span class="block text-xs font-bold text-gray-400 uppercase">Nama Device</span>
                                <span class="font-bold text-gray-800">{{ $fonnteName }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-gray-400 uppercase">Nomor WhatsApp</span>
                                <span class="font-bold text-gray-800">{{ $fonnteDevice }}</span>
                            </div>
                        </div>

                        <div class="flex justify-center md:justify-end">
                            @if($fonnteStatus == 'disconnected' && $fonnteQr)
                                <div class="text-center">
                                    <img src="data:image/png;base64, {{ $fonnteQr }}" alt="QR Code" class="w-48 h-48 border-4 border-gray-100 rounded-2xl shadow-sm mb-2">
                                    <span class="block text-xs text-rose-500 font-bold">Scan QR ini dengan WhatsApp Anda</span>
                                </div>
                            @elseif($fonnteStatus == 'connect')
                                <div class="text-center p-6 bg-emerald-50 rounded-3xl border border-emerald-100">
                                    <svg class="w-16 h-16 text-emerald-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="block font-black text-emerald-700">WhatsApp Siap Mengirim Pesan!</span>
                                </div>
                            @else
                                <div class="text-center p-6 bg-gray-50 rounded-3xl border border-gray-200">
                                    <span class="block text-sm font-bold text-gray-500">Silakan periksa Token Fonnte di file .env</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- LOG RIWAYAT --}}
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <h3 class="text-lg font-bold text-orange-600 uppercase tracking-tighter mb-6">Riwayat Pengiriman Pesan</h3>

                    {{-- FILTER SECTION --}}
                    <div class="mb-6 bg-gray-50 p-5 rounded-2xl border border-gray-100">
                        <form method="GET" action="{{ route('admin.whatsapp.index') }}" class="flex flex-col md:flex-row gap-4">
                            
                            <div class="flex-1">
                                <label for="search" class="block text-xs font-bold text-gray-500 uppercase mb-1">Cari Siswa / No WA</label>
                                <input type="text" name="search" id="search" value="{{ request('search') }}"
                                    class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm"
                                    placeholder="Ketik nama atau nomor...">
                            </div>

                            <div class="md:w-1/4">
                                <label for="kelas_id" class="block text-xs font-bold text-gray-500 uppercase mb-1">Filter Kelas</label>
                                <select name="kelas_id" id="kelas_id" class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                                    <option value="">-- Semua Kelas --</option>
                                    @foreach($kelasList as $k)
                                    <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                                        {{ $k->nama }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="md:w-1/6">
                                <label for="status" class="block text-xs font-bold text-gray-500 uppercase mb-1">Status</label>
                                <select name="status" id="status" class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                                    <option value="">-- Semua --</option>
                                    <option value="Berhasil" {{ request('status') == 'Berhasil' ? 'selected' : '' }}>Berhasil</option>
                                    <option value="Gagal" {{ request('status') == 'Gagal' ? 'selected' : '' }}>Gagal</option>
                                </select>
                            </div>

                            <div class="flex items-end gap-2">
                                <button type="submit" class="px-6 py-2.5 bg-gray-800 text-white text-sm font-bold rounded-xl hover:bg-gray-900 transition-colors shadow-sm">
                                    Filter
                                </button>
                                @if(request()->hasAny(['search', 'kelas_id', 'status']))
                                <a href="{{ route('admin.whatsapp.index') }}" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-colors flex items-center justify-center" title="Reset">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </a>
                                @endif
                            </div>

                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 font-black px-4 w-48">Waktu</th>
                                    <th class="pb-4 font-black">Siswa / Tujuan</th>
                                    <th class="pb-4 font-black">Pesan</th>
                                    <th class="pb-4 font-black text-center w-32">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($logs as $log)
                                <tr class="group hover:bg-orange-50/30 transition-colors">
                                    <td class="py-4 px-4 align-top">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900">{{ $log->created_at->format('d M Y') }}</span>
                                            <span class="text-xs text-gray-400 font-bold">{{ $log->created_at->format('H:i:s') }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 align-top">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900">{{ $log->siswa->nama ?? 'Bukan Siswa' }}</span>
                                            <span class="text-xs text-orange-500 font-bold">{{ $log->no_wa }}</span>
                                            @if($log->siswa && $log->siswa->rombelKelas)
                                                <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded mt-1 w-max uppercase">{{ $log->siswa->rombelKelas->kelas->nama }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 align-top">
                                        <p class="text-xs text-gray-600 line-clamp-2 hover:line-clamp-none transition-all cursor-pointer bg-gray-50 p-3 rounded-xl border border-gray-100">
                                            {!! nl2br(e($log->pesan)) !!}
                                        </p>
                                    </td>
                                    <td class="py-4 text-center align-top">
                                        @if($log->status == 'Berhasil')
                                            <span class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-xs font-black uppercase">Berhasil</span>
                                        @else
                                            <span class="px-3 py-1 bg-rose-50 text-rose-600 rounded-lg text-xs font-black uppercase">Gagal</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="py-10 text-center text-gray-400 italic font-bold">
                                        Belum ada riwayat pengiriman WhatsApp.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8">
                        {{ $logs->links() }}
                    </div>

                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>
