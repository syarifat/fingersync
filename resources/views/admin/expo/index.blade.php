<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 leading-tight">
            {{ __('Panel Kontrol Expo & Live Demo') }}
        </h2>
    </x-slot>

    <div class="space-y-6 pb-8">

            {{-- HERO BANNER --}}
            <div class="bg-gradient-to-r from-orange-600 via-amber-600 to-orange-500 rounded-3xl p-6 text-white shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-xs font-bold uppercase tracking-wider mb-2">
                        <span class="w-2 h-2 rounded-full bg-yellow-300 animate-ping"></span>
                        Showcase Mode Live
                    </div>
                    <h1 class="text-2xl font-black">Panel Kontrol Expo & Live Demo</h1>
                    <p class="text-xs text-orange-100 mt-1 max-w-xl">
                        Atur respon LCD & buzzer ESP32 secara instan, lakukan simulasi tap jari, reset database 1-klik, dan sinkronkan jam KBM agar selalu aktif saat pameran.
                    </p>
                </div>
                <div class="bg-black/20 backdrop-blur-md border border-white/20 rounded-2xl p-4 shrink-0">
                    <span class="text-[10px] text-orange-200 uppercase font-bold tracking-widest block">Mode LCD Saat Ini:</span>
                    <span class="text-sm font-extrabold text-yellow-300 block mt-0.5">{{ $selectedModeDetails['title'] }}</span>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-[10px] text-white px-2 py-0.5 bg-white/20 rounded-lg font-mono">Buzzer: {{ $selectedModeDetails['beep'] }}</span>
                    </div>
                </div>
            </div>

            {{-- ALERT NOTIFIKASI --}}
            @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl shadow-sm flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-sm font-semibold">{{ session('error') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            @endif

            {{-- ROW 1: AKSI CEPAT (1-KLIK) --}}
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            <span class="p-1.5 bg-orange-100 text-orange-600 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </span>
                            <span>Aksi Cepat Demo (1-Klik)</span>
                        </h3>
                        <p class="text-xs text-gray-500">Tombol penyelamat saat pameran untuk me-refresh dan membersihkan data secara kilat.</p>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-orange-100 text-orange-700 rounded-lg">Instant Tools</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    {{-- 1. Hapus Presensi Hari Ini --}}
                    <form action="{{ route('admin.expo.reset-presensi') }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus seluruh log presensi hari ini? Pengunjung bisa mencoba tap dari awal.');">
                        @csrf
                        <button type="submit" class="w-full h-full text-left p-4 rounded-2xl border border-rose-200 bg-rose-50/50 hover:bg-rose-100/70 transition group flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-base group-hover:scale-110 transition">
                                        🧹
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-rose-200 text-rose-800">Presensi Hari Ini: {{ $stats['total'] }}</span>
                                </div>
                                <h4 class="font-bold text-sm text-gray-900 group-hover:text-rose-700">Hapus Presensi Hari Ini</h4>
                                <p class="text-xs text-gray-500 mt-1">Bersihkan entri presensi hari ini agar pengunjung/juri bisa tap ulang tanpa terblokir "Sudah Absen".</p>
                            </div>
                            <div class="mt-3 text-[11px] font-bold text-rose-600 flex items-center gap-1">
                                <span>Bersihkan Sekarang</span>
                                <span>&rarr;</span>
                            </div>
                        </button>
                    </form>

                    {{-- 2. Sinkronkan Jadwal KBM ke Jam Sekarang --}}
                    <form action="{{ route('admin.expo.sync-jadwal') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full h-full text-left p-4 rounded-2xl border border-sky-200 bg-sky-50/50 hover:bg-sky-100/70 transition group flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-600 flex items-center justify-center font-bold text-base group-hover:scale-110 transition">
                                        ⏰
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-sky-200 text-sky-800">Hari: {{ $hariIndo }}</span>
                                </div>
                                <h4 class="font-bold text-sm text-gray-900 group-hover:text-sky-700">Set KBM ke Jam Sekarang</h4>
                                <p class="text-xs text-gray-500 mt-1">Ubah jam mulai KBM jadi jam sekarang (-20m s/d +4 jam) agar status KBM selalu <b>Sedang Berlangsung</b>.</p>
                            </div>
                            <div class="mt-3 text-[11px] font-bold text-sky-600 flex items-center gap-1">
                                <span>Sinkronkan Jam</span>
                                <span>&rarr;</span>
                            </div>
                        </button>
                    </form>

                    {{-- 3. Quick Migrate Fresh & Seed --}}
                    <form action="{{ route('admin.expo.migrate-seed') }}" method="POST" onsubmit="return confirm('PERINGATAN: Ini akan me-reset database dan men-seed ulang semua data awal demo! Lanjutkan?');">
                        @csrf
                        <button type="submit" class="w-full h-full text-left p-4 rounded-2xl border border-purple-200 bg-purple-50/50 hover:bg-purple-100/70 transition group flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center font-bold text-base group-hover:scale-110 transition">
                                        🔄
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-purple-200 text-purple-800">Hard Reset</span>
                                </div>
                                <h4 class="font-bold text-sm text-gray-900 group-hover:text-purple-700">Migrate Fresh & Seed</h4>
                                <p class="text-xs text-gray-500 mt-1">Kembalikan seluruh database ke data demo awal (guru, siswa, plotting, jadwal) dalam hitungan detik.</p>
                            </div>
                            <div class="mt-3 text-[11px] font-bold text-purple-600 flex items-center gap-1">
                                <span>Reset Database</span>
                                <span>&rarr;</span>
                            </div>
                        </button>
                    </form>
                </div>
            </div>

            {{-- ROW 2: PEMILIH SKENARIO RESPON LCD ESP32 --}}
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-6 border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            <span class="p-1.5 bg-indigo-100 text-indigo-600 rounded-lg">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                            <span>Pilih Respon LCD & Buzzer ESP32</span>
                        </h3>
                        <p class="text-xs text-gray-500">Pilih salah satu skenario di bawah ini. Begitu jari siapapun menempel di sensor, ESP32 akan langsung merespon sesuai pilihan ini!</p>
                    </div>
                </div>

                <form action="{{ route('admin.expo.set-mode') }}" method="POST" id="modeForm">
                    @csrf

                    {{-- FILTER & PILIHAN SISWA TARGET --}}
                    <div class="bg-slate-50 p-4 rounded-2xl border border-gray-200 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
                        <div class="w-full md:w-1/2">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Target Siswa Simulasi:</label>
                            <select name="target_student_id" onchange="document.getElementById('modeForm').submit();" class="w-full text-xs font-medium rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 bg-white">
                                <option value="auto" {{ $currentTargetStudentId === 'auto' ? 'selected' : '' }}>🔄 Otomatis (Gunakan Siswa yang Di-tap / Siswa Pertama)</option>
                                @foreach($siswas as $siswa)
                                @php
                                    $namaKls = ($siswa->rombelKelas && $siswa->rombelKelas->kelas) ? $siswa->rombelKelas->kelas->nama : (($siswa->kelas) ? $siswa->kelas->nama : 'Tanpa Kelas');
                                @endphp
                                <option value="{{ $siswa->id }}" {{ $currentTargetStudentId == $siswa->id ? 'selected' : '' }}>
                                    {{ $siswa->nama }} (ID: {{ $siswa->fingerprint_id ?? '-' }} - {{ $namaKls }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-full md:w-1/2 flex items-center md:justify-end">
                            <label class="inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="auto_guest" value="1" {{ $autoGuest ? 'checked' : '' }} onchange="document.getElementById('modeForm').submit();" class="sr-only peer">
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                                <span class="ms-3 text-xs font-bold text-gray-700">Terima Semua Jari Tamu (Bypass Sensor)</span>
                            </label>
                        </div>
                    </div>

                    {{-- GRID PILIHAN SKENARIO --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($modes as $code => $mode)
                        @php
                            $isSelected = ($currentMode === $code);
                        @endphp
                        <label class="relative flex flex-col p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 
                            {{ $isSelected ? 'border-amber-500 bg-amber-50/40 shadow-md ring-2 ring-amber-200' : 'border-gray-200 hover:border-gray-300 bg-white' }}">
                            
                            <input type="radio" name="mode" value="{{ $code }}" {{ $isSelected ? 'checked' : '' }} class="sr-only" onchange="document.getElementById('modeForm').submit();">

                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-4 h-4 rounded-full border flex items-center justify-center {{ $isSelected ? 'border-amber-600 bg-amber-600' : 'border-gray-400 bg-white' }}">
                                        @if($isSelected)
                                        <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                        @endif
                                    </span>
                                    <span class="font-bold text-xs text-gray-900 leading-tight">{{ $mode['title'] }}</span>
                                </div>
                                <span class="text-[9px] font-black px-1.5 py-0.5 rounded border shrink-0 {{ $mode['badge'] }}">
                                    {{ $mode['beep'] }}
                                </span>
                            </div>

                            <p class="text-[11px] text-gray-500 mb-3 flex-grow leading-relaxed">{{ $mode['desc'] }}</p>

                            {{-- SIMULASI TAMPILAN LCD 20x4 --}}
                            <div class="bg-slate-950 border border-slate-800 rounded-xl p-2.5 font-mono text-[10px] text-emerald-400 shadow-inner leading-tight">
                                <div class="flex items-center justify-between text-[8px] text-gray-500 border-b border-slate-800 pb-1 mb-1">
                                    <span>[LCD 20x4]</span>
                                    <span class="text-amber-400 font-bold">{{ $mode['beep'] }}</span>
                                </div>
                                <div class="font-bold text-white uppercase truncate">{{ $mode['lcd_title'] }}</div>
                                <div class="truncate">{{ $mode['lcd_line1'] }}</div>
                                @if(isset($mode['lcd_line2']))
                                <div class="text-emerald-300 truncate">{{ $mode['lcd_line2'] }}</div>
                                @endif
                                @if(isset($mode['lcd_line3']))
                                <div class="text-gray-400 truncate">{{ $mode['lcd_line3'] }}</div>
                                @endif
                            </div>
                        </label>
                        @endforeach
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="submit" class="px-5 py-2 bg-amber-600 text-white text-xs font-bold rounded-xl hover:bg-amber-700 shadow-sm transition">
                            Simpan Perubahan Mode
                        </button>
                    </div>
                </form>
            </div>

            {{-- ROW 3: VIRTUAL SCANNER (EMERGENCY TESTER DI BROWSER) & STATUS REALTIME --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- KOLOM KIRI: VIRTUAL SCANNER --}}
                <div class="lg:col-span-1 bg-white rounded-3xl p-6 shadow-sm border border-gray-100 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                            <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                <span class="p-1.5 bg-emerald-100 text-emerald-600 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                    </svg>
                                </span>
                                <span>Virtual Scanner</span>
                            </h3>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">Simulasi</span>
                        </div>
                        <p class="text-xs text-gray-500 mb-4">
                            Uji coba respon tanpa menyalakan alat fisik. Berguna jika di booth pameran WiFi alat sempat terputus!
                        </p>

                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Pilih Device:</label>
                                <select id="virtualDevice" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white">
                                    @foreach($devices as $dev)
                                    <option value="{{ $dev->id_device }}">{{ $dev->id_device }} - {{ $dev->nama_device }}</option>
                                    @endforeach
                                    @if($devices->isEmpty())
                                    <option value="TKJ1">TKJ1 (Default Device)</option>
                                    @endif
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Pilih Siswa / Fingerprint ID:</label>
                                <select id="virtualFingerprintId" class="w-full text-xs rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 bg-white">
                                    <option value="999">ID 999 (Jari Tamu Expo / Belum Terdaftar)</option>
                                    @foreach($siswas as $s)
                                    <option value="{{ $s->fingerprint_id ?? $s->id }}">{{ $s->nama }} (ID: {{ $s->fingerprint_id ?? $s->id }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="button" onclick="kirimSimulasiScan()" id="btnSimulasi" class="w-full mt-2 py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"></path></svg>
                                <span>Simulasikan Tap Jari Sekarang</span>
                            </button>
                        </div>
                    </div>

                    {{-- OUTPUT SIMULASI LCD LIVE --}}
                    <div class="mt-5">
                        <label class="block text-[11px] font-bold text-gray-700 uppercase tracking-wider mb-2">Respon Layar LCD ESP32:</label>
                        <div id="simulasiLcdOutput" class="bg-slate-950 border border-slate-800 rounded-2xl p-3.5 font-mono text-xs text-emerald-400 shadow-inner min-h-[95px] flex flex-col justify-center">
                            <div class="text-gray-500 text-center italic text-xs">Klik tombol di atas untuk melihat respon langsung alat...</div>
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: LIVE LOG PRESENSI HARI INI --}}
                <div class="lg:col-span-2 bg-white rounded-3xl p-6 shadow-sm border border-gray-100">
                    <div class="flex items-center justify-between mb-4 border-b border-gray-100 pb-3">
                        <div>
                            <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                                <span class="p-1.5 bg-blue-100 text-blue-600 rounded-lg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </span>
                                <span>Log Presensi Hari Ini</span>
                            </h3>
                            <p class="text-xs text-gray-500">Pantau data kehadiran yang berhasil masuk secara langsung.</p>
                        </div>
                        <a href="{{ route('admin.expo.index') }}" class="text-xs font-semibold px-2.5 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Refresh</span>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-gray-600">
                            <thead class="text-[10px] font-bold text-gray-400 uppercase bg-gray-50 border-y border-gray-100">
                                <tr>
                                    <th class="px-3 py-2.5">Waktu</th>
                                    <th class="px-3 py-2.5">Siswa</th>
                                    <th class="px-3 py-2.5">Kelas</th>
                                    <th class="px-3 py-2.5">Status</th>
                                    <th class="px-3 py-2.5">Tipe</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($presensiHariIni->take(8) as $row)
                                @php
                                    $namaKls = ($row->siswa && $row->siswa->rombelKelas && $row->siswa->rombelKelas->kelas) ? $row->siswa->rombelKelas->kelas->nama : (($row->siswa && $row->siswa->kelas) ? $row->siswa->kelas->nama : '-');
                                @endphp
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-3 py-2 font-mono font-semibold text-gray-800">{{ $row->jam_scan }}</td>
                                    <td class="px-3 py-2 font-bold text-gray-900">{{ $row->siswa->nama ?? 'Siswa Tamu' }}</td>
                                    <td class="px-3 py-2">{{ $namaKls }}</td>
                                    <td class="px-3 py-2">
                                        @if($row->status === 'Hadir')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Hadir</span>
                                        @elseif($row->status === 'Terlambat')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Terlambat</span>
                                        @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-800">{{ $row->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2">
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold {{ $row->tipe_scan === 'pulang' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-800' }}">
                                            {{ ucfirst($row->tipe_scan ?? 'masuk') }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-3 py-6 text-center text-gray-400 italic">
                                        Belum ada data presensi untuk hari ini. Silakan coba tap sidik jari di alat atau gunakan tombol simulasi di samping.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

    {{-- JAVASCRIPT SIMULATOR --}}
    <script>
        function kirimSimulasiScan() {
            const device = document.getElementById('virtualDevice').value;
            const fingerprintId = document.getElementById('virtualFingerprintId').value;
            const output = document.getElementById('simulasiLcdOutput');
            const btn = document.getElementById('btnSimulasi');

            btn.disabled = true;
            btn.innerHTML = 'Memproses...';
            output.innerHTML = '<div class="text-amber-300 animate-pulse text-center">Menghubungi API /api/scan...</div>';

            fetch('{{ route("admin.expo.simulate-scan") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id_device: device,
                    fingerprint_id: parseInt(fingerprintId)
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = 'Simulasikan Tap Jari Sekarang';

                let html = '';
                if (data.status === 'SUCCESS') {
                    html = `
                        <div class="text-emerald-400 font-bold uppercase text-center border-b border-slate-800 pb-1">ABSENSI BERHASIL!</div>
                        <div class="text-white font-bold mt-1">${data.nama || '-'}</div>
                        <div class="text-emerald-300">Status: ${data.stat || 'Hadir'}</div>
                        <div class="text-gray-400 text-[10px]">${data.mapel || ''}</div>
                    `;
                } else if (data.status === 'WARN') {
                    html = `
                        <div class="text-amber-400 font-bold uppercase text-center border-b border-slate-800 pb-1">SUDAH ABSEN!</div>
                        <div class="text-white font-bold mt-1">${data.nama || '-'}</div>
                        <div class="text-amber-200 text-[10px]">Peringatan duplikasi scan</div>
                    `;
                } else {
                    html = `
                        <div class="text-rose-400 font-bold uppercase text-center border-b border-slate-800 pb-1">AKSES DITOLAK!</div>
                        <div class="text-white text-[11px] mt-1">${data.message || 'Error'}</div>
                    `;
                }
                output.innerHTML = html;
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = 'Simulasikan Tap Jari Sekarang';
                output.innerHTML = `<div class="text-rose-500 text-center font-bold">Gagal terhubung ke API!</div>`;
            });
        }
    </script>
</x-app-layout>
