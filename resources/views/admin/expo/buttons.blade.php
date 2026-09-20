<!DOCTYPE html>
<html lang="id" class="h-full overflow-hidden bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Remote Tombol Respon ESP32 - FingerSync Expo</title>
    <link rel="icon" href="{{ asset('logo.png') }}?v=2" type="image/png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600,700,800,900|jetbrains-mono:500,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        * {
            box-sizing: border-box;
            user-select: none;
            -webkit-user-select: none;
        }
        body {
            font-family: 'Figtree', sans-serif;
            touch-action: manipulation;
        }
        .font-mono-lcd {
            font-family: 'JetBrains Mono', monospace;
        }
        /* Custom scrollbar hidden */
        ::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>
<body class="h-full w-full overflow-hidden bg-gradient-to-br from-slate-100 via-gray-50 to-slate-200 text-gray-900 flex flex-col justify-between">

    {{-- TOP BAR COMPACT (PUTIH BERSIH) --}}
    <header class="h-14 shrink-0 px-3 sm:px-5 flex items-center justify-between border-b border-gray-200 bg-white/95 backdrop-blur-md shadow-sm z-20">
        {{-- Left: Brand & Status --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.expo.index') }}" class="px-2.5 py-1.5 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 hover:text-gray-900 border border-gray-200 transition flex items-center gap-1.5 text-xs font-bold shadow-sm" title="Kembali ke Dashboard Expo">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span class="hidden sm:inline">Dashboard</span>
            </a>

            <div class="h-5 w-px bg-gray-200"></div>

            <div class="flex items-center gap-2">
                <span class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <div class="flex items-center gap-1.5">
                    <span class="text-[10px] uppercase tracking-wider text-gray-400 font-extrabold hidden sm:inline">MODE AKTIF:</span>
                    <span id="headerActiveTitle" class="text-xs sm:text-sm font-black text-indigo-700 bg-indigo-50 border border-indigo-200 px-2.5 py-0.5 rounded-lg truncate max-w-[180px] sm:max-w-xs md:max-w-md shadow-sm">
                        {{ $selectedModeDetails['title'] }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Right: Actions & Tools --}}
        <div class="flex items-center gap-2">
            {{-- LCD Clue --}}
            <div id="headerLcdClue" class="hidden md:flex items-center gap-1.5 px-3 py-1 rounded-xl bg-emerald-50 border border-emerald-200 text-[11px] font-mono-lcd text-emerald-800 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span id="headerLcdText">LCD: {{ $selectedModeDetails['lcd_title'] }}</span>
                <span class="text-emerald-300">|</span>
                <span id="headerBeepText" class="font-bold text-amber-700">{{ $selectedModeDetails['beep'] }}</span>
            </div>

            {{-- Sound Toggle --}}
            <button id="soundToggleBtn" onclick="toggleSound()" class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-200 transition text-xs font-semibold flex items-center shadow-sm" title="Toggle Suara Feedback">
                <span id="soundIcon">🔊</span>
            </button>

            {{-- Instant Test Scan Trigger --}}
            <button onclick="triggerSimulateScan()" id="btnSimulateScan" class="px-3 py-1.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-black text-xs shadow-sm hover:shadow active:scale-95 transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Tes Scan</span>
            </button>

            {{-- Fullscreen Toggle --}}
            <button onclick="toggleFullscreen()" class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-200 transition hidden sm:flex items-center shadow-sm" title="Layar Penuh">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
            </button>
        </div>
    </header>

    {{-- MAIN BUTTONS GRID (PUTIH BERSIH, 100% FIT NO SCROLL) --}}
    <main class="flex-1 p-2 sm:p-3 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 grid-rows-6 sm:grid-rows-4 lg:grid-rows-3 gap-2 sm:gap-2.5 overflow-hidden">
        @php
            $shortcuts = [
                'random' => 'R',
                'normal' => 'N',
                'success_hadir' => '1',
                'success_terlambat' => '2',
                'success_pulang' => '3',
                'warn_sudah_absen' => '4',
                'info_tidak_ada_jadwal' => '5',
                'error_salah_ruangan' => '6',
                'error_tidak_terdaftar' => '7',
                'error_belum_masuk_kelas' => '8',
                'error_hari_libur' => '9',
                'error_guru_izin' => '0',
            ];
        @endphp

        @foreach($modes as $code => $mode)
            @php
                $isActive = ($currentMode === $code);
                $shortcut = $shortcuts[$code] ?? '';
                $cat = $mode['category'];

                // Base Card Style (White modern theme)
                $cardBase = 'relative flex flex-col justify-between p-2.5 sm:p-3 rounded-2xl cursor-pointer border-2 transition-all duration-150 overflow-hidden shadow-sm hover:shadow-md text-left group ';

                // Theme mapping by category
                if ($cat === 'special') {
                    $borderActive = 'border-purple-500 bg-purple-50/90 ring-4 ring-purple-500/20 shadow-purple-200/60';
                    $borderNormal = 'border-gray-200 bg-white hover:border-purple-300 hover:bg-purple-50/30';
                    $badgeBeep = 'bg-purple-100 text-purple-800 border-purple-200';
                    $lcdBox = 'bg-purple-100/60 text-purple-900 border-purple-200';
                    $arrowColor = 'text-purple-600';
                } elseif ($cat === 'system') {
                    $borderActive = 'border-indigo-500 bg-indigo-50/90 ring-4 ring-indigo-500/20 shadow-indigo-200/60';
                    $borderNormal = 'border-gray-200 bg-white hover:border-indigo-300 hover:bg-indigo-50/30';
                    $badgeBeep = 'bg-indigo-100 text-indigo-800 border-indigo-200';
                    $lcdBox = 'bg-indigo-100/60 text-indigo-900 border-indigo-200';
                    $arrowColor = 'text-indigo-600';
                } elseif ($cat === 'success') {
                    if ($code === 'success_terlambat') {
                        $borderActive = 'border-amber-500 bg-amber-50/90 ring-4 ring-amber-500/20 shadow-amber-200/60';
                        $borderNormal = 'border-gray-200 bg-white hover:border-amber-300 hover:bg-amber-50/30';
                        $badgeBeep = 'bg-amber-100 text-amber-800 border-amber-200';
                        $lcdBox = 'bg-amber-100/60 text-amber-900 border-amber-200';
                        $arrowColor = 'text-amber-600';
                    } elseif ($code === 'success_pulang') {
                        $borderActive = 'border-blue-500 bg-blue-50/90 ring-4 ring-blue-500/20 shadow-blue-200/60';
                        $borderNormal = 'border-gray-200 bg-white hover:border-blue-300 hover:bg-blue-50/30';
                        $badgeBeep = 'bg-blue-100 text-blue-800 border-blue-200';
                        $lcdBox = 'bg-blue-100/60 text-blue-900 border-blue-200';
                        $arrowColor = 'text-blue-600';
                    } else {
                        $borderActive = 'border-emerald-500 bg-emerald-50/90 ring-4 ring-emerald-500/20 shadow-emerald-200/60';
                        $borderNormal = 'border-gray-200 bg-white hover:border-emerald-300 hover:bg-emerald-50/30';
                        $badgeBeep = 'bg-emerald-100 text-emerald-800 border-emerald-200';
                        $lcdBox = 'bg-emerald-100/60 text-emerald-900 border-emerald-200';
                        $arrowColor = 'text-emerald-600';
                    }
                } elseif ($cat === 'warning') {
                    $borderActive = 'border-orange-500 bg-orange-50/90 ring-4 ring-orange-500/20 shadow-orange-200/60';
                    $borderNormal = 'border-gray-200 bg-white hover:border-orange-300 hover:bg-orange-50/30';
                    $badgeBeep = 'bg-orange-100 text-orange-800 border-orange-200';
                    $lcdBox = 'bg-orange-100/60 text-orange-900 border-orange-200';
                    $arrowColor = 'text-orange-600';
                } elseif ($cat === 'info') {
                    $borderActive = 'border-sky-500 bg-sky-50/90 ring-4 ring-sky-500/20 shadow-sky-200/60';
                    $borderNormal = 'border-gray-200 bg-white hover:border-sky-300 hover:bg-sky-50/30';
                    $badgeBeep = 'bg-sky-100 text-sky-800 border-sky-200';
                    $lcdBox = 'bg-sky-100/60 text-sky-900 border-sky-200';
                    $arrowColor = 'text-sky-600';
                } else {
                    // Error
                    $borderActive = 'border-rose-500 bg-rose-50/90 ring-4 ring-rose-500/20 shadow-rose-200/60';
                    $borderNormal = 'border-gray-200 bg-white hover:border-rose-300 hover:bg-rose-50/30';
                    $badgeBeep = 'bg-rose-100 text-rose-800 border-rose-200';
                    $lcdBox = 'bg-rose-100/60 text-rose-900 border-rose-200';
                    $arrowColor = 'text-rose-600';
                }
            @endphp

            <button type="button" 
                    id="btn-mode-{{ $code }}"
                    data-mode="{{ $code }}"
                    data-title="{{ $mode['title'] }}"
                    data-beep="{{ $mode['beep'] }}"
                    data-lcd="{{ $mode['lcd_title'] }}"
                    data-category="{{ $cat }}"
                    onclick="selectResponseMode('{{ $code }}')"
                    class="mode-btn {{ $cardBase }} {{ $isActive ? $borderActive : $borderNormal }}">

                {{-- TOP ROW: SHORTCUT KEY + BEEP + AKTIF BADGE --}}
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center gap-1.5">
                        @if($shortcut)
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-gray-100 border border-gray-300 text-[10px] font-mono font-black text-gray-700 shadow-xs">
                            {{ $shortcut }}
                        </span>
                        @endif
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold tracking-wider uppercase border {{ $badgeBeep }}">
                            {{ $mode['beep'] }}
                        </span>
                    </div>

                    {{-- LIVE ACTIVE BADGE --}}
                    <div class="active-badge {{ $isActive ? 'flex' : 'hidden' }} items-center gap-1 px-2 py-0.5 rounded-full bg-slate-900 text-white text-[10px] font-black uppercase tracking-wider shadow animate-pulse">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>Aktif</span>
                    </div>
                </div>

                {{-- MIDDLE: TITLE & EMOJI --}}
                <div class="my-auto py-1">
                    <h3 class="text-xs sm:text-sm lg:text-base font-black text-gray-900 leading-snug group-hover:text-indigo-600 transition-colors line-clamp-2">
                        {{ $mode['title'] }}
                    </h3>
                </div>

                {{-- BOTTOM: LCD SIMULATION PREVIEW --}}
                <div class="w-full pt-1 border-t border-gray-100 flex items-center justify-between text-[10px] font-mono-lcd">
                    <span class="truncate max-w-[85%] px-1.5 py-0.5 rounded border {{ $lcdBox }} font-medium">
                        &gt; [{{ $mode['lcd_title'] }}]
                    </span>
                    <span class="text-xs opacity-60 group-hover:opacity-100 group-hover:translate-x-0.5 transition-all font-bold {{ $arrowColor }}">→</span>
                </div>
            </button>
        @endforeach
    </main>

    {{-- FOOTER COMPACT (PUTIH) --}}
    <footer class="h-9 shrink-0 px-4 border-t border-gray-200 bg-white/95 flex items-center justify-between text-[11px] text-gray-500 shadow-xs z-20">
        <div class="flex items-center gap-2">
            <span class="font-mono text-gray-400 hidden sm:inline">Shortcuts: [1-9, 0, R, N] ganti mode | [Spasi] tes scan</span>
            <span id="toastMessage" class="font-bold text-indigo-700 transition-opacity duration-300 opacity-0"></span>
        </div>
        <div class="flex items-center gap-3 font-mono text-[10px]">
            <span class="text-gray-400">FingerSync ESP32 Controller</span>
            <span class="text-gray-300">•</span>
            <span class="text-gray-600">Device: <strong class="text-indigo-600 font-bold">TKJ1</strong></span>
        </div>
    </footer>

    {{-- SCRIPTS --}}
    <script>
        const setModeUrl = "{{ route('admin.expo.set-mode') }}";
        const simulateScanUrl = "{{ route('admin.expo.simulate-scan') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        let isSoundEnabled = true;
        let audioCtx = null;

        // Initialize Web Audio Context for tactile feedback
        function initAudio() {
            if (!audioCtx) {
                const AudioContext = window.AudioContext || window.webkitAudioContext;
                if (AudioContext) {
                    audioCtx = new AudioContext();
                }
            }
        }

        // Play short synthetic tactile beep
        function playBeep(freq = 800, duration = 0.08, type = 'sine') {
            if (!isSoundEnabled) return;
            try {
                initAudio();
                if (!audioCtx) return;
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = type;
                osc.frequency.setValueAtTime(freq, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.12, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + duration);
            } catch (e) {
                // Ignore audio restriction errors
            }
        }

        function toggleSound() {
            isSoundEnabled = !isSoundEnabled;
            document.getElementById('soundIcon').innerText = isSoundEnabled ? '🔊' : '🔇';
            showToast(isSoundEnabled ? 'Suara feedback aktif' : 'Suara feedback dimatikan');
        }

        // Show Toast in footer
        let toastTimeout = null;
        function showToast(msg, isError = false) {
            const el = document.getElementById('toastMessage');
            if (!el) return;
            el.innerText = msg;
            el.className = isError 
                ? 'font-bold text-rose-600 transition-opacity duration-300 opacity-100'
                : 'font-bold text-indigo-700 transition-opacity duration-300 opacity-100';
            
            clearTimeout(toastTimeout);
            toastTimeout = setTimeout(() => {
                el.classList.replace('opacity-100', 'opacity-0');
            }, 2500);
        }

        // Select & Set Mode via Instant AJAX
        async function selectResponseMode(code) {
            const targetBtn = document.getElementById('btn-mode-' + code);
            if (!targetBtn) return;

            // Audio tactile feedback
            playBeep(950, 0.08, 'triangle');

            const title = targetBtn.dataset.title;
            const beep = targetBtn.dataset.beep;
            const lcd = targetBtn.dataset.lcd;
            const cat = targetBtn.dataset.category;

            // Update Top Header instantly
            document.getElementById('headerActiveTitle').innerText = title;
            document.getElementById('headerLcdText').innerText = 'LCD: ' + lcd;
            document.getElementById('headerBeepText').innerText = beep;

            // Update visual active state immediately
            document.querySelectorAll('.mode-btn').forEach(btn => {
                const badge = btn.querySelector('.active-badge');
                if (badge) badge.classList.replace('flex', 'hidden');

                // Remove active classes
                btn.className = 'mode-btn relative flex flex-col justify-between p-2.5 sm:p-3 rounded-2xl cursor-pointer border-2 transition-all duration-150 overflow-hidden shadow-sm hover:shadow-md text-left group border-gray-200 bg-white hover:bg-gray-50/50';
            });

            // Activate target button with its category active style
            const badge = targetBtn.querySelector('.active-badge');
            if (badge) badge.classList.replace('hidden', 'flex');

            let activeClass = 'border-indigo-500 bg-indigo-50/90 ring-4 ring-indigo-500/20 shadow-indigo-200/60';
            if (cat === 'special') activeClass = 'border-purple-500 bg-purple-50/90 ring-4 ring-purple-500/20 shadow-purple-200/60';
            else if (cat === 'system') activeClass = 'border-indigo-500 bg-indigo-50/90 ring-4 ring-indigo-500/20 shadow-indigo-200/60';
            else if (cat === 'success') {
                if (code === 'success_terlambat') activeClass = 'border-amber-500 bg-amber-50/90 ring-4 ring-amber-500/20 shadow-amber-200/60';
                else if (code === 'success_pulang') activeClass = 'border-blue-500 bg-blue-50/90 ring-4 ring-blue-500/20 shadow-blue-200/60';
                else activeClass = 'border-emerald-500 bg-emerald-50/90 ring-4 ring-emerald-500/20 shadow-emerald-200/60';
            } else if (cat === 'warning') activeClass = 'border-orange-500 bg-orange-50/90 ring-4 ring-orange-500/20 shadow-orange-200/60';
            else if (cat === 'info') activeClass = 'border-sky-500 bg-sky-50/90 ring-4 ring-sky-500/20 shadow-sky-200/60';
            else activeClass = 'border-rose-500 bg-rose-50/90 ring-4 ring-rose-500/20 shadow-rose-200/60';

            targetBtn.className = 'mode-btn relative flex flex-col justify-between p-2.5 sm:p-3 rounded-2xl cursor-pointer border-2 transition-all duration-150 overflow-hidden text-left group ' + activeClass;

            // Send non-blocking AJAX POST
            try {
                const response = await fetch(setModeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ mode: code })
                });

                const data = await response.json();
                if (data.success) {
                    showToast('✓ Respon diubah: ' + title);
                }
            } catch (err) {
                console.error(err);
                showToast('Gagal mengubah mode di server!', true);
            }
        }

        // Trigger Simulate Scan (Instant Test)
        async function triggerSimulateScan() {
            playBeep(1200, 0.12, 'square');
            const btn = document.getElementById('btnSimulateScan');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span>Scanning...</span>';
            btn.disabled = true;

            try {
                const response = await fetch(simulateScanUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        id_device: 'TKJ1',
                        id_siswa: 'auto'
                    })
                });

                const data = await response.json();
                if (data.success) {
                    showToast('⚡ Scan Berhasil: [' + data.response.status + '] ' + (data.response.nama || data.response.message));
                } else {
                    showToast('Scan simulasi gagal: ' + (data.message || 'Error'), true);
                }
            } catch (e) {
                showToast('Gagal memanggil simulasi scan', true);
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        // Toggle Fullscreen
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen().catch(() => {});
                }
            }
        }

        // Keyboard Shortcuts Handler
        const keyMap = {
            '1': 'success_hadir',
            '2': 'success_terlambat',
            '3': 'success_pulang',
            '4': 'warn_sudah_absen',
            '5': 'info_tidak_ada_jadwal',
            '6': 'error_salah_ruangan',
            '7': 'error_tidak_terdaftar',
            '8': 'error_belum_masuk_kelas',
            '9': 'error_hari_libur',
            '0': 'error_guru_izin',
            'r': 'random',
            'R': 'random',
            'n': 'normal',
            'N': 'normal'
        };

        window.addEventListener('keydown', (e) => {
            // Ignore if typing in input
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;

            if (e.code === 'Space') {
                e.preventDefault();
                triggerSimulateScan();
                return;
            }

            if (keyMap[e.key]) {
                e.preventDefault();
                selectResponseMode(keyMap[e.key]);
            }
        });
    </script>
</body>
</html>
