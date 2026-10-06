<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
                {{ __('Konektivitas & Log WhatsApp (Baileys Gateway)') }}
            </h2>
            <div class="flex items-center gap-2">
                <button onclick="window.location.reload()" 
                        class="px-4 py-2 bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-xl hover:bg-gray-50 shadow-sm flex items-center gap-1.5 transition-all">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Refresh Status
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            {{-- Flash Alert --}}
            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm font-bold text-emerald-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm font-bold text-rose-800">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            {{-- STATUS BAILEYS GATEWAY --}}
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden" id="baileys-status-card">
                <div class="p-8">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between pb-6 mb-6 border-b border-gray-100 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-orange-600 uppercase tracking-tighter flex items-center gap-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                Status Device WhatsApp (Baileys)
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">Multi-Device Baileys Gateway Server lokal (Port 3000)</p>
                        </div>

                        <div class="flex items-center gap-3">
                            @if($waStatus === 'connected')
                                <form action="{{ route('admin.whatsapp.logout') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memutuskan sesi WhatsApp ini?')">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs font-black rounded-xl transition-all uppercase tracking-wider">
                                        Putus Sesi (Logout)
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.whatsapp.restart') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-600 border border-gray-200 text-xs font-black rounded-xl transition-all uppercase tracking-wider">
                                        Restart Service
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        
                        {{-- Kiri: Detail Informasi --}}
                        <div class="lg:col-span-5 space-y-5">
                            <div>
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Status Koneksi</span>
                                <div id="status-badge-container">
                                    @if($waStatus === 'connected')
                                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-xs font-black uppercase tracking-wider shadow-sm">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            TERHUBUNG (ONLINE)
                                        </span>
                                    @elseif($waStatus === 'connecting')
                                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-amber-50 border border-amber-200 text-amber-700 rounded-xl text-xs font-black uppercase tracking-wider shadow-sm">
                                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                            MENGHUBUNGKAN...
                                        </span>
                                    @elseif($waStatus === 'disconnected')
                                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-rose-50 border border-rose-200 text-rose-700 rounded-xl text-xs font-black uppercase tracking-wider shadow-sm">
                                            <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                            TERPUTUS (OFFLINE)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-gray-100 border border-gray-300 text-gray-700 rounded-xl text-xs font-black uppercase tracking-wider shadow-sm">
                                            SERVICE TIDAK AKTIF
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 space-y-3">
                                <div>
                                    <span class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider">Nama Akun WhatsApp</span>
                                    <span id="val-name" class="font-black text-gray-800 text-base">{{ $waName ?: '-' }}</span>
                                </div>
                                <div class="pt-2 border-t border-gray-200/60">
                                    <span class="block text-[11px] font-bold text-gray-400 uppercase tracking-wider">Nomor Pengirim</span>
                                    <span id="val-phone" class="font-black text-gray-800 text-base font-mono">{{ $waPhone ?: '-' }}</span>
                                </div>
                            </div>

                            @if($waStatus === 'connected')
                                <div class="p-4 bg-emerald-50/70 border border-emerald-100 rounded-2xl flex items-start gap-3">
                                    <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <p class="text-xs text-emerald-800 font-semibold leading-relaxed">
                                        Gateway WhatsApp aktif dan siap mengirimkan notifikasi presensi otomatis serta rekap harian ke grup kelas.
                                    </p>
                                </div>
                            @elseif($waStatus === 'error')
                                <div class="p-4 bg-amber-50 border border-amber-200 rounded-2xl">
                                    <p class="text-xs text-amber-800 font-bold leading-relaxed mb-2">
                                        ⚠️ Service Baileys belum menyala pada port 3000.
                                    </p>
                                    <p class="text-[11px] text-gray-600 font-mono bg-white p-2 rounded border border-amber-200">
                                        npm run wa:server
                                    </p>
                                </div>
                            @endif
                        </div>

                        {{-- Kanan: Card Koneksi (QR & Pairing Code) --}}
                        <div class="lg:col-span-7">
                            @if($waStatus === 'connected')
                                <div class="p-8 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl text-white shadow-lg text-center flex flex-col items-center justify-center min-h-[260px]">
                                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                    <h4 class="text-xl font-black mb-1">WhatsApp Siap Digunakan!</h4>
                                    <p class="text-emerald-100 text-xs max-w-md">Perangkat Anda telah tersambung via Baileys Multi-Device. Pengiriman pesan ke grup dan siswa akan berjalan otomatis.</p>
                                </div>
                            @else
                                <div class="bg-gray-50 border border-gray-200 rounded-3xl p-6" x-data="{ tab: 'qr', phone: '', loadingPair: false, pairCode: '{{ $pairingCode ?? '' }}', pairMsg: '' }">
                                    
                                    {{-- Tab Selector --}}
                                    <div class="flex p-1 bg-white rounded-2xl border border-gray-200 mb-6 shadow-sm">
                                        <button type="button" @click="tab = 'qr'" 
                                                :class="tab === 'qr' ? 'bg-orange-600 text-white shadow' : 'text-gray-600 hover:text-gray-900'"
                                                class="flex-1 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                                            📷 Scan QR Code
                                        </button>
                                        <button type="button" @click="tab = 'pair'" 
                                                :class="tab === 'pair' ? 'bg-orange-600 text-white shadow' : 'text-gray-600 hover:text-gray-900'"
                                                class="flex-1 py-2 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                                            🔢 Pairing Code (8 Digit)
                                        </button>
                                    </div>

                                    {{-- Tab 1: QR Code --}}
                                    <div x-show="tab === 'qr'" class="flex flex-col items-center text-center">
                                        <div id="qr-wrapper" class="relative bg-white p-3 rounded-2xl border border-gray-200 shadow-sm mb-3">
                                            @if($waQr)
                                                <img id="qr-img" src="{{ $waQr }}" alt="QR Code WhatsApp" class="w-56 h-56 rounded-xl">
                                            @else
                                                <div id="qr-placeholder" class="w-56 h-56 flex flex-col items-center justify-center bg-gray-100 rounded-xl text-gray-400">
                                                    <svg class="w-8 h-8 animate-spin mb-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                                    <span class="text-xs font-bold">Membuat QR Code...</span>
                                                </div>
                                            @endif
                                        </div>
                                        <p class="text-xs font-bold text-gray-700">Buka WhatsApp di HP > Menu Titik Tiga / Setelan > Perangkat Tertaut > Tautkan Perangkat</p>
                                        <p class="text-[11px] text-gray-400 mt-1">Halaman ini otomatis mendeteksi saat Anda berhasil scan.</p>
                                    </div>

                                    {{-- Tab 2: Pairing Code --}}
                                    <div x-show="tab === 'pair'" class="space-y-4">
                                        <div class="bg-white p-5 rounded-2xl border border-gray-200">
                                            <label class="block text-xs font-black text-gray-700 uppercase tracking-wider mb-2">
                                                Nomor WhatsApp Pengirim
                                            </label>
                                            <div class="flex gap-2">
                                                <input type="text" x-model="phone" placeholder="Contoh: 08123456789 atau 628123..." 
                                                       class="flex-1 rounded-xl border-gray-200 text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm font-mono">
                                                <button type="button" 
                                                        @click="
                                                            if(!phone) { alert('Masukkan nomor telepon!'); return; }
                                                            loadingPair = true; pairMsg = '';
                                                            fetch('{{ route('admin.whatsapp.pair_code') }}', {
                                                                method: 'POST',
                                                                headers: {
                                                                    'Content-Type': 'application/json',
                                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                                                },
                                                                body: JSON.stringify({ phone: phone })
                                                            })
                                                            .then(r => r.json())
                                                            .then(data => {
                                                                loadingPair = false;
                                                                if(data.success && data.code) {
                                                                    pairCode = data.code;
                                                                    pairMsg = 'Kode siap dimasukkan ke WhatsApp Anda!';
                                                                } else {
                                                                    pairMsg = data.message || 'Gagal membuat kode';
                                                                }
                                                            })
                                                            .catch(e => {
                                                                loadingPair = false;
                                                                pairMsg = 'Terjadi kesalahan koneksi';
                                                            });
                                                        "
                                                        :disabled="loadingPair"
                                                        class="px-5 py-2.5 bg-orange-600 hover:bg-orange-700 disabled:opacity-50 text-white font-bold text-xs rounded-xl transition-all shrink-0">
                                                    <span x-show="!loadingPair">Dapatkan Kode</span>
                                                    <span x-show="loadingPair">Memproses...</span>
                                                </button>
                                            </div>

                                            {{-- Display Pairing Code --}}
                                            <div x-show="pairCode" class="mt-5 p-5 bg-orange-50/70 border border-orange-200 rounded-2xl text-center">
                                                <span class="block text-[11px] font-bold text-orange-600 uppercase tracking-widest mb-1">Kode Pairing Anda</span>
                                                <span class="text-3xl font-black font-mono tracking-widest text-gray-900 select-all" x-text="pairCode"></span>
                                                <p class="text-xs text-gray-600 mt-2 font-medium">
                                                    Buka WhatsApp > <strong>Perangkat Tertaut</strong> > <strong>Tautkan Perangkat</strong> > <strong>Tautkan dengan nomor telepon</strong> > Masukkan 8 kode di atas.
                                                </p>
                                            </div>

                                            <div x-show="pairMsg && !pairCode" class="mt-3 text-xs font-bold text-rose-600" x-text="pairMsg"></div>
                                        </div>
                                    </div>

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

                            <div class="md:w-1/5">
                                <label for="jenis" class="block text-xs font-bold text-gray-500 uppercase mb-1">Jenis Pesan</label>
                                <select name="jenis" id="jenis" class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                                    <option value="">-- Semua Jenis --</option>
                                    <option value="rekap_sore" {{ request('jenis') == 'rekap_sore' ? 'selected' : '' }}>Grup - Rekap Sore</option>
                                    <option value="absen_pulang" {{ request('jenis') == 'absen_pulang' ? 'selected' : '' }}>Grup - Notifikasi Pulang</option>
                                </select>
                            </div>

                            <div class="md:w-1/5">
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
                                @if(request()->hasAny(['search', 'kelas_id', 'status', 'jenis']))
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
                                    <th class="pb-4 font-black w-48">Siswa</th>
                                    <th class="pb-4 font-black w-48">Tujuan Pesan</th>
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
                                    <td class="py-4 align-top pr-4">
                                        <div class="flex flex-col">
                                            @if($log->siswa)
                                                <span class="font-bold text-gray-900">{{ $log->siswa->nama }}</span>
                                                @if($log->siswa->rombelKelas)
                                                    <span class="text-[10px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded mt-1.5 w-max uppercase">{{ $log->siswa->rombelKelas->kelas->nama }}</span>
                                                @endif
                                            @else
                                                <span class="font-bold text-gray-900">Grup / Kolektif</span>
                                                <span class="text-[10px] bg-teal-50 text-teal-600 px-2 py-0.5 rounded mt-1.5 w-max uppercase">Daftar Terlampir</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="py-4 align-top pr-4">
                                        <div class="flex flex-col">
                                            @if(strpos($log->pesan, 'PEMBERITAHUAN PULANG') !== false)
                                                <span class="font-bold text-gray-900">Grup WA Kelas (Pulang)</span>
                                            @elseif(strpos($log->pesan, 'LAPORAN SISWA BELUM PRESENSI PAGI') !== false)
                                                <span class="font-bold text-gray-900">Grup WA Kelas (Belum Hadir)</span>
                                            @elseif(strpos($log->pesan, 'REKAP PRESENSI HARIAN') !== false)
                                                <span class="font-bold text-gray-900">Grup WA Kelas (Rekap Sore)</span>
                                            @else
                                                <span class="font-bold text-gray-900">Grup / Wali Kelas</span>
                                            @endif
                                            
                                            <span class="text-xs text-orange-600 font-bold mt-1">{{ $log->no_wa }}</span>
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
                                <tr class="border-b border-gray-50">
                                    <td colspan="5" class="py-10 text-center text-gray-400 italic font-bold">
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

    {{-- Realtime Polling Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const currentStatus = '{{ $waStatus }}';

            // Polling status jika belum terhubung
            if (currentStatus !== 'connected') {
                const pollInterval = setInterval(() => {
                    fetch('{{ route('admin.whatsapp.status') }}')
                        .then(res => res.json())
                        .then(data => {
                            // Jika berhasil connect, reload halaman
                            if (data.status === 'connected') {
                                clearInterval(pollInterval);
                                window.location.reload();
                                return;
                            }

                            // Update QR code jika ada perubahan QR baru
                            if (data.qr) {
                                const qrImg = document.getElementById('qr-img');
                                const qrPlaceholder = document.getElementById('qr-placeholder');
                                if (qrImg) {
                                    if (qrImg.src !== data.qr) {
                                        qrImg.src = data.qr;
                                    }
                                } else if (qrPlaceholder) {
                                    qrPlaceholder.outerHTML = `<img id="qr-img" src="${data.qr}" alt="QR Code WhatsApp" class="w-56 h-56 rounded-xl">`;
                                }
                            }
                        })
                        .catch(() => {});
                }, 4000);
            }
        });
    </script>
</x-app-layout>
