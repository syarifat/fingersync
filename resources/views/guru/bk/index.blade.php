<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight">
                    {{ __('Rekap Presensi') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1 font-medium">
                    Panel Khusus Bimbingan Konseling (BK) &bull; Pengelolaan &amp; Koreksi Kehadiran Siswa Binaan
                </p>
            </div>
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-orange-100/70 border border-orange-200 text-orange-700 rounded-xl text-xs font-black">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                <span>Hak Akses: Guru BK (is_bk = 1)</span>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- ALERT NOTIFIKASI --}}
            @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl flex items-center justify-between shadow-sm animate-fade-in">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-sm font-bold">&times;</button>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-sm font-bold">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800 text-sm font-bold">&times;</button>
            </div>
            @endif

            {{-- HEADER INFO & KELAS BINAAN SELECTOR --}}
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 p-6 md:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-orange-500 to-amber-600 text-white rounded-2xl flex items-center justify-center font-black text-xl shadow-lg shadow-orange-200 shrink-0">
                            {{ strtoupper(substr($guru->nama, 0, 2)) }}
                        </div>
                        <div>
                            <span class="text-[11px] font-black uppercase tracking-widest text-orange-600 bg-orange-50 px-2.5 py-1 rounded-lg border border-orange-100">Guru Bimbingan Konseling</span>
                            <h3 class="text-xl font-black text-gray-900 mt-1">{{ $guru->nama }}</h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Membina <strong>{{ $kelasList->count() }} Kelas</strong>:
                                @foreach($kelasList as $k)
                                    <span class="inline-block px-2 py-0.5 bg-gray-100 text-gray-700 rounded-md font-semibold text-[11px] mr-1">{{ $k->nama }}</span>
                                @endforeach
                            </p>
                        </div>
                    </div>

                    {{-- Class Switcher Pills --}}
                    <div class="flex flex-wrap items-center gap-2 bg-gray-50 p-2 rounded-2xl border border-gray-100">
                        <a href="{{ route('guru.bk.presensi.index', array_merge(request()->query(), ['kelas_id' => 'all'])) }}"
                           class="px-4 py-2 rounded-xl text-xs font-black transition-all {{ $selectedKelasId === 'all' ? 'bg-orange-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200/60' }}">
                            Semua Kelas ({{ $kelasList->count() }})
                        </a>
                        @foreach($kelasList as $k)
                        <a href="{{ route('guru.bk.presensi.index', array_merge(request()->query(), ['kelas_id' => $k->id])) }}"
                           class="px-4 py-2 rounded-xl text-xs font-black transition-all {{ $selectedKelasId == $k->id ? 'bg-orange-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-200/60' }}">
                            {{ $k->nama }}
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- FILTER & PENCARIAN --}}
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 p-6">
                <form method="GET" action="{{ route('guru.bk.presensi.index') }}" id="filterForm" class="space-y-4">
                    <input type="hidden" name="kelas_id" value="{{ $selectedKelasId }}">

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- Filter Tanggal --}}
                        <div>
                            <label for="tanggal" class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Presensi</label>
                            <input type="date" name="tanggal" id="tanggal" value="{{ $selectedTanggal }}"
                                   onchange="document.getElementById('filterForm').submit()"
                                   class="block w-full rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-orange-500 focus:ring-orange-500 font-semibold shadow-sm">
                        </div>

                        {{-- Filter Status --}}
                        <div>
                            <label for="status" class="block text-xs font-bold text-gray-500 uppercase mb-1">Status Kehadiran</label>
                            <select name="status" id="status" onchange="document.getElementById('filterForm').submit()"
                                    class="block w-full rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-orange-500 focus:ring-orange-500 font-semibold shadow-sm">
                                <option value="">-- Semua Status --</option>
                                @foreach($statusList as $st)
                                <option value="{{ $st }}" {{ $selectedStatus == $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Cari Siswa (NIS / Nama) --}}
                        <div class="sm:col-span-2">
                            <label for="search" class="block text-xs font-bold text-gray-500 uppercase mb-1">Cari Siswa</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Ketik nama atau NIS siswa..."
                                       class="pl-10 block w-full rounded-xl border-gray-200 bg-gray-50 text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Quick Date Shortcuts & Reset --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-400 font-bold uppercase tracking-wider">Pintasan:</span>
                            @php
                                $kemarin = \Carbon\Carbon::yesterday()->format('Y-m-d');
                                $hariIni = \Carbon\Carbon::today()->format('Y-m-d');
                            @endphp
                            <button type="button" onclick="setTanggal('{{ $kemarin }}')" class="px-3 py-1 bg-gray-100 hover:bg-gray-200 rounded-lg text-xs font-bold text-gray-600 transition-colors">Kemarin</button>
                            <button type="button" onclick="setTanggal('{{ $hariIni }}')" class="px-3 py-1 bg-orange-50 hover:bg-orange-100 rounded-lg text-xs font-bold text-orange-700 transition-colors">Hari Ini</button>
                        </div>

                        <div class="flex items-center gap-2">
                            @if(request()->hasAny(['status', 'search']) || (request('tanggal') && request('tanggal') != $hariIni))
                            <a href="{{ route('guru.bk.presensi.index', ['kelas_id' => $selectedKelasId]) }}" class="px-3 py-1.5 text-xs text-gray-500 hover:text-gray-800 font-bold underline transition-colors">
                                Reset Filter
                            </a>
                            @endif
                            <button type="submit" class="px-5 py-1.5 bg-gray-800 hover:bg-gray-900 text-white rounded-xl text-xs font-bold shadow-sm transition-colors">
                                Terapkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- KARTU STATISTIK KEHADIRAN (KPI) --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-sm text-center">
                    <span class="block text-[11px] font-black text-gray-400 uppercase tracking-wider">Total Siswa</span>
                    <span class="text-2xl font-black text-gray-800 mt-1 block">{{ $stats['total'] }}</span>
                </div>
                <div class="bg-emerald-50/70 p-4 rounded-2xl border border-emerald-100 shadow-sm text-center">
                    <span class="block text-[11px] font-black text-emerald-600 uppercase tracking-wider">Hadir</span>
                    <span class="text-2xl font-black text-emerald-700 mt-1 block">{{ $stats['hadir'] }}</span>
                </div>
                <div class="bg-amber-50/70 p-4 rounded-2xl border border-amber-100 shadow-sm text-center">
                    <span class="block text-[11px] font-black text-amber-600 uppercase tracking-wider">Terlambat</span>
                    <span class="text-2xl font-black text-amber-700 mt-1 block">{{ $stats['terlambat'] }}</span>
                </div>
                <div class="bg-blue-50/70 p-4 rounded-2xl border border-blue-100 shadow-sm text-center">
                    <span class="block text-[11px] font-black text-blue-600 uppercase tracking-wider">Sakit</span>
                    <span class="text-2xl font-black text-blue-700 mt-1 block">{{ $stats['sakit'] }}</span>
                </div>
                <div class="bg-orange-50/70 p-4 rounded-2xl border border-orange-100 shadow-sm text-center">
                    <span class="block text-[11px] font-black text-orange-600 uppercase tracking-wider">Izin</span>
                    <span class="text-2xl font-black text-orange-700 mt-1 block">{{ $stats['izin'] }}</span>
                </div>
                <div class="bg-rose-50/70 p-4 rounded-2xl border border-rose-100 shadow-sm text-center">
                    <span class="block text-[11px] font-black text-rose-600 uppercase tracking-wider">Alpha / Belum</span>
                    <span class="text-2xl font-black text-rose-700 mt-1 block">{{ $stats['alpha'] + $stats['belum_absen'] }}</span>
                </div>
            </div>

            {{-- TOOLBAR BATCH ACTION & TABEL PRESENSI --}}
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-black text-gray-900">
                                Daftar Kehadiran Siswa
                                <span class="text-sm font-semibold text-gray-500 ml-2">
                                    ({{ \Carbon\Carbon::parse($selectedTanggal)->locale('id')->isoFormat('dddd, DD MMMM YYYY') }})
                                </span>
                            </h3>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Klik tombol <strong>Edit</strong> pada baris siswa untuk mengubah status perorangan, atau pilih kotak centang untuk edit massal.
                            </p>
                        </div>

                        {{-- Batch Action Control --}}
                        <div id="batchActionToolbar" class="flex items-center gap-2 bg-orange-50 p-2 rounded-xl border border-orange-200">
                            <span class="text-xs font-bold text-orange-800 pl-2">
                                <span id="selectedCount">0</span> Siswa Dipilih:
                            </span>
                            <select id="batchStatus" class="text-xs font-bold rounded-lg border-orange-200 bg-white focus:border-orange-500 focus:ring-orange-500 py-1.5">
                                <option value="Hadir">Hadir</option>
                                <option value="Izin">Izin</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Alpha">Alpha</option>
                            </select>
                            <button type="button" onclick="submitBatchUpdate()" class="px-3 py-1.5 bg-orange-600 hover:bg-orange-700 text-white rounded-lg text-xs font-black transition-colors shadow-sm">
                                Terapkan Massal
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-gray-400 text-xs uppercase tracking-widest bg-gray-50/70 border-b border-gray-100">
                                <th class="p-4 w-10 text-center">
                                    <input type="checkbox" id="checkAll" onclick="toggleCheckAll(this)" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500 h-4 w-4">
                                </th>
                                <th class="py-4 px-2 font-black text-center w-12">No</th>
                                <th class="p-4 font-black">Siswa</th>
                                <th class="p-4 font-black">Kelas</th>
                                <th class="p-4 font-black">Waktu Scan</th>
                                <th class="p-4 font-black text-center">Status Presensi</th>
                                <th class="p-4 font-black text-center">Riwayat AIS</th>
                                <th class="p-4 font-black text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($items as $index => $item)
                            @php
                                $badgeClass = match($item->status) {
                                    'Hadir' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'Terlambat' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'Sakit' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'Izin' => 'bg-orange-50 text-orange-700 border-orange-200',
                                    'Alpha' => 'bg-rose-50 text-rose-700 border-rose-200',
                                    default => 'bg-gray-100 text-gray-500 border-gray-200',
                                };
                            @endphp
                            <tr class="hover:bg-orange-50/20 transition-colors group">
                                <td class="p-4 text-center">
                                    <input type="checkbox" name="siswa_checkbox[]" value="{{ $item->siswa->id }}" onchange="updateSelectedCount()" class="student-checkbox rounded border-gray-300 text-orange-600 focus:ring-orange-500 h-4 w-4">
                                </td>
                                <td class="py-4 px-2 text-center text-xs font-bold text-gray-400">
                                    {{ $index + 1 }}
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-orange-100 text-orange-700 flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($item->siswa->nama, 0, 2)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-800 text-sm group-hover:text-orange-600 transition-colors">
                                                {{ $item->siswa->nama }}
                                            </div>
                                            <div class="text-[11px] text-gray-400 font-mono">
                                                NIS: {{ $item->siswa->nis }} &bull; {{ $item->siswa->gender }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span class="inline-block px-2.5 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-black">
                                        {{ $item->kelas_nama }}
                                    </span>
                                </td>
                                <td class="p-4">
                                    @if($item->jam_scan)
                                    <div class="text-xs font-mono font-bold text-gray-700">{{ substr($item->jam_scan, 0, 5) }} WIB</div>
                                    <div class="text-[10px] text-gray-400">{{ $item->ruangan }}</div>
                                    @else
                                    <span class="text-xs text-gray-300 italic font-semibold">- Belum Scan -</span>
                                    @endif
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-black uppercase border {{ $badgeClass }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <a href="{{ route('guru.bk.presensi.detail_ais', $item->siswa->id) }}"
                                       class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-black {{ $item->siswa->total_ais > 0 ? 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100' : 'bg-gray-100 text-gray-400' }} transition-colors"
                                       title="Klik untuk melihat rincian ketidakhadiran siswa ini">
                                        {{ $item->siswa->total_ais ?? 0 }} AIS
                                    </a>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button"
                                                onclick="openEditModal({{ json_encode([
                                                    'siswa_id' => $item->siswa->id,
                                                    'nama' => $item->siswa->nama,
                                                    'nis' => $item->siswa->nis,
                                                    'kelas' => $item->kelas_nama,
                                                    'status' => $item->status === 'Belum Absen' ? 'Alpha' : $item->status,
                                                    'jam_scan' => $item->jam_scan ? substr($item->jam_scan, 0, 5) : date('H:i'),
                                                    'presensi_id' => $item->presensi ? $item->presensi->id : null,
                                                ]) }})"
                                                class="inline-flex items-center px-3 py-1.5 bg-orange-50 hover:bg-orange-100 text-orange-700 rounded-xl text-xs font-bold border border-orange-200 transition-colors">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Edit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-gray-400 italic">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <p class="font-bold text-sm">Tidak ada siswa yang sesuai dengan filter yang dipilih.</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL EDIT PRESENSI INDIVIDUAL --}}
    <div id="modalEdit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 animate-fade-in">
        <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-lg overflow-hidden border border-gray-100">
            <div class="p-6 bg-gradient-to-r from-orange-600 to-amber-600 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black leading-tight">Edit Status Presensi Siswa</h3>
                    <p class="text-xs text-orange-100 font-medium">Bimbingan Konseling &bull; Tanggal: {{ \Carbon\Carbon::parse($selectedTanggal)->locale('id')->isoFormat('DD MMMM YYYY') }}</p>
                </div>
                <button type="button" onclick="closeEditModal()" class="w-8 h-8 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-colors font-bold">&times;</button>
            </div>

            <form action="{{ route('guru.bk.presensi.store') }}" method="POST" id="formEditSingle">
                @csrf
                <input type="hidden" name="siswa_id" id="edit_siswa_id">
                <input type="hidden" name="tanggal" value="{{ $selectedTanggal }}">

                <div class="p-6 space-y-4">
                    {{-- Student Badge Preview --}}
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100">
                        <span class="text-[10px] font-black uppercase tracking-wider text-gray-400">Data Siswa</span>
                        <div class="text-base font-black text-gray-800" id="edit_nama_siswa">Nama Siswa</div>
                        <div class="text-xs text-gray-500 mt-0.5">
                            NIS: <span id="edit_nis_siswa" class="font-mono font-bold">-</span> &bull;
                            Kelas: <span id="edit_kelas_siswa" class="font-bold">-</span>
                        </div>
                    </div>

                    <div>
                        <label for="edit_status" class="block text-xs font-bold text-gray-700 uppercase mb-1">Status Kehadiran <span class="text-orange-600">*</span></label>
                        <select name="status" id="edit_status" required class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 font-bold shadow-sm">
                            <option value="Hadir">Hadir</option>
                            <option value="Terlambat">Terlambat</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Izin">Izin</option>
                            <option value="Alpha">Alpha</option>
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1 italic">*Ubah status menjadi Sakit/Izin jika siswa membawa surat keterangan ke ruang BK.</p>
                    </div>

                    <div>
                        <label for="edit_jam_scan" class="block text-xs font-bold text-gray-700 uppercase mb-1">Jam Scan (WIB)</label>
                        <input type="time" name="jam_scan" id="edit_jam_scan" class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 font-bold shadow-sm">
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-gray-100">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-xs font-bold text-gray-500 hover:text-gray-800 uppercase tracking-wider transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-orange-600 hover:bg-orange-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md transition-all">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- FORM BATCH UPDATE TERSEMBUNYI --}}
    <form id="formBatchUpdate" action="{{ route('guru.bk.presensi.batch') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="tanggal" value="{{ $selectedTanggal }}">
        <input type="hidden" name="status" id="batch_status_input">
        <div id="batch_siswa_container"></div>
    </form>

    <script>
        function setTanggal(val) {
            document.getElementById('tanggal').value = val;
            document.getElementById('filterForm').submit();
        }

        function toggleCheckAll(source) {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => cb.checked = source.checked);
            updateSelectedCount();
        }

        function updateSelectedCount() {
            const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
            document.getElementById('selectedCount').innerText = checkedBoxes.length;
        }

        function openEditModal(data) {
            document.getElementById('edit_siswa_id').value = data.siswa_id;
            document.getElementById('edit_nama_siswa').innerText = data.nama;
            document.getElementById('edit_nis_siswa').innerText = data.nis;
            document.getElementById('edit_kelas_siswa').innerText = data.kelas;
            document.getElementById('edit_status').value = data.status;
            document.getElementById('edit_jam_scan').value = data.jam_scan;
            document.getElementById('modalEdit').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('modalEdit').classList.add('hidden');
        }

        function submitBatchUpdate() {
            const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
            if (checkedBoxes.length === 0) {
                alert('Silakan pilih minimal 1 siswa dengan mencentang kotak di kolom pertama tabel.');
                return;
            }

            const targetStatus = document.getElementById('batchStatus').value;
            if (!confirm(`Apakah Anda yakin ingin mengubah status presensi ${checkedBoxes.length} siswa terpilih menjadi "${targetStatus}" pada tanggal {{ $selectedTanggal }}?`)) {
                return;
            }

            const container = document.getElementById('batch_siswa_container');
            container.innerHTML = '';
            checkedBoxes.forEach(cb => {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'siswa_ids[]';
                hiddenInput.value = cb.value;
                container.appendChild(hiddenInput);
            });

            document.getElementById('batch_status_input').value = targetStatus;
            document.getElementById('formBatchUpdate').submit();
        }
    </script>
</x-app-layout>
