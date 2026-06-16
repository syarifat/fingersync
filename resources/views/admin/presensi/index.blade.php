<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Manajemen Data Presensi') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">

                    {{-- Header Section: Judul & Total Count --}}
                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-orange-600 uppercase tracking-tighter">Riwayat Kehadiran Siswa</h3>
                            <p class="text-sm text-gray-500">Total data masuk: {{ $dataPresensi->total() }} Baris</p>
                        </div>

                        {{-- Tombol (Export/Input Manual) - Opsional --}}
                        {{-- <a href="#" class="inline-flex items-center px-6 py-3 bg-orange-600 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-orange-700 transition-all shadow-lg shadow-orange-100">
                            + Input Manual
                        </a> --}}
                    </div>

                    {{-- FILTER SECTION --}}
                    <div class="mb-6 bg-gray-50 p-5 rounded-2xl border border-gray-100">
                        <form method="GET" action="{{ route('admin.presensi.index') }}" class="flex flex-col gap-3">

                            {{-- BARIS 1: Kelas + Mapel + Cari Siswa --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label for="kelas_id" class="block text-xs font-bold text-gray-500 uppercase mb-1">Kelas <span class="text-orange-500">*</span></label>
                                    <select name="kelas_id" id="kelas_id" required class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                                        @foreach($kelasList as $k)
                                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="mapel_id" class="block text-xs font-bold text-gray-500 uppercase mb-1">Mata Pelajaran</label>
                                    <select name="mapel_id" id="mapel_id" class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                                        <option value="">-- Semua Mata Pelajaran --</option>
                                        @foreach($mapelList as $m)
                                        <option value="{{ $m->id }}" {{ request('mapel_id') == $m->id ? 'selected' : '' }}>{{ $m->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="search" class="block text-xs font-bold text-gray-500 uppercase mb-1">Cari Siswa</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        </div>
                                        <input type="text" name="search" id="search" value="{{ request('search') }}" class="pl-10 block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm" placeholder="Nama atau NIS...">
                                    </div>
                                </div>
                            </div>

                            {{-- BARIS 2: Harian + Bulanan + Tombol --}}
                            <div class="flex flex-col md:flex-row gap-3 items-end">
                                <div class="flex-1">
                                    <label for="tanggal" class="block text-xs font-bold text-gray-500 uppercase mb-1">Filter Harian</label>
                                    <input type="date" name="tanggal" id="tanggal" value="{{ request('tanggal') }}" class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                                </div>
                                <div class="flex-1">
                                    <label for="bulan" class="block text-xs font-bold text-gray-500 uppercase mb-1">Filter Bulanan</label>
                                    <input type="month" name="bulan" id="bulan" value="{{ request('bulan') }}" class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="submit" class="px-6 py-2.5 bg-gray-800 text-white text-sm font-bold rounded-xl hover:bg-gray-900 transition-colors shadow-sm">Filter</button>
                                    <button type="button" onclick="bukaModalPdf()" class="px-4 py-2.5 bg-rose-50 border border-rose-200 text-rose-600 text-sm font-bold rounded-xl hover:bg-rose-100 transition-colors flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        Export PDF
                                    </button>
                                    @if(request()->hasAny(['mapel_id', 'tanggal', 'bulan', 'search']))
                                    <a href="{{ route('admin.presensi.index') }}" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-600 text-sm font-bold rounded-xl hover:bg-gray-50 transition-colors flex items-center" title="Reset">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </a>
                                    @endif
                                </div>
                            </div>

                        </form>
                    </div>

                    {{-- Alert Success --}}
                    @if (session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-700 text-sm font-bold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                    @endif

                    {{-- Alert Error --}}
                    @if (session('error'))
                    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-sm font-bold flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        {{ session('error') }}
                    </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 font-black px-4">Waktu</th>
                                    <th class="pb-4 font-black">Siswa</th>
                                    <th class="pb-4 font-black">Jadwal / Mapel</th>
                                    <th class="pb-4 font-black text-center">Status</th>
                                    <th class="pb-4 font-black px-4">Device</th>
                                    <th class="pb-4 font-black text-right px-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($dataPresensi as $row)
                                <tr class="group hover:bg-orange-50/30 transition-colors">

                                    {{-- Kolom Waktu --}}
                                    <td class="py-5 px-4">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900">
                                                {{ \Carbon\Carbon::parse($row->tanggal)->format('d M Y') }}
                                            </span>
                                            <span class="text-xs text-orange-500 font-bold tracking-wider">
                                                {{ $row->jam_scan }}
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Kolom Siswa --}}
                                    <td class="py-5">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-900">{{ $row->siswa->nama ?? 'Siswa dihapus' }}</span>
                                            <span class="text-xs text-gray-400 font-bold">{{ $row->siswa->nis ?? '-' }}</span>
                                        </div>
                                    </td>

                                    {{-- Kolom Jadwal --}}
                                    <td class="py-5">
                                        @if($row->tipe_scan === 'pulang')
                                        <span class="px-3 py-1 bg-teal-50 text-teal-600 rounded-lg text-xs font-black uppercase">
                                            Absen Pulang Sekolah
                                        </span>
                                        @elseif($row->rombelJadwalPelajaran)
                                        <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-lg text-xs font-black uppercase">
                                            {{ $row->rombelJadwalPelajaran->rombelMataPelajaran->mataPelajaran->nama ?? 'Mapel Tidak Ditemukan' }}
                                        </span>
                                        <div class="text-xs text-gray-400 mt-1 pl-1">
                                            {{ $row->rombelJadwalPelajaran->jam_mulai }} - {{ $row->rombelJadwalPelajaran->jam_selesai }}
                                        </div>
                                        @elseif($row->kegiatanSekolah)
                                        <span class="px-3 py-1 bg-orange-50 text-orange-600 rounded-lg text-xs font-black uppercase">
                                            Kegiatan: {{ $row->kegiatanSekolah->nama_kegiatan }}
                                        </span>
                                        <div class="text-xs text-gray-400 mt-1 pl-1">
                                            Tipe: {{ ucfirst($row->kegiatanSekolah->tipe) }} (Scan: {{ ucfirst($row->tipe_scan_kegiatan) }})
                                        </div>
                                        @else
                                        <span class="text-gray-300 italic text-xs font-bold">- Diluar Jadwal -</span>
                                        @endif
                                    </td>

                                    {{-- Kolom Status --}}
                                    <td class="py-5 text-center">
                                        @php
                                        if ($row->tipe_scan === 'pulang') {
                                            $badgeClass = 'bg-teal-50 text-teal-600';
                                            $statusText = 'PULANG';
                                        } else {
                                            $badgeClass = match($row->status) {
                                                'Hadir' => 'bg-emerald-50 text-emerald-600',
                                                'Terlambat' => 'bg-amber-50 text-amber-600',
                                                'Alpa' => 'bg-rose-50 text-rose-600',
                                                'Sakit' => 'bg-purple-50 text-purple-600',
                                                'Izin' => 'bg-indigo-50 text-indigo-600',
                                                default => 'bg-gray-50 text-gray-600'
                                            };
                                            $statusText = $row->status;
                                        }
                                        @endphp
                                        <span class="px-3 py-1 rounded-lg text-xs font-black uppercase {{ $badgeClass }}">
                                            {{ $statusText }}
                                        </span>
                                    </td>

                                    {{-- Kolom Device --}}
                                    <td class="py-5 px-4">
                                        <span class="text-xs font-mono text-gray-400 bg-gray-50 px-2 py-1 rounded font-bold">
                                            {{ $row->device->nama_device ?? $row->id_device }}
                                        </span>
                                    </td>
                                    
                                    {{-- Kolom Aksi --}}
                                    <td class="py-5 px-4 text-right">
                                        <a href="{{ route('admin.presensi.edit', $row->id) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-lg transition-colors">
                                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="py-10 text-center text-gray-400 italic font-bold">
                                        Belum ada data presensi yang terekam.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-8">
                        {{ $dataPresensi->links() }}
                    </div>

                </div>
            </div>
        </div>

{{-- MODAL EXPORT PDF --}}
<div id="modalPdf" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 mx-4">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-black text-gray-800">⚙️ Opsi Export PDF</h3>
            <button onclick="tutupModalPdf()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form id="formExportPdf" action="{{ route('admin.presensi.export_pdf') }}" method="GET" target="_blank">
            <input type="hidden" name="kelas_id" id="modal_kelas_id">

            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kelas yang Dipilih</label>
                <div id="modal_kelas_label" class="px-4 py-2 bg-orange-50 border border-orange-200 text-orange-700 font-bold rounded-xl text-sm">-</div>
            </div>

            <div class="mb-4">
                <label for="modal_mapel_id" class="block text-xs font-bold text-gray-500 uppercase mb-1">Mata Pelajaran</label>
                <select name="mapel_id" id="modal_mapel_id" class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                    <option value="">-- Semua Mata Pelajaran --</option>
                    @foreach($mapelList as $m)
                    <option value="{{ $m->id }}">{{ $m->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-6">
                <label for="modal_bulan" class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Bulan <span class="text-rose-500">*</span></label>
                <input type="month" name="bulan" id="modal_bulan" required
                    class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm"
                    value="{{ now()->format('Y-m') }}">
            </div>

            <div class="flex gap-3">
                <button type="submit" class="flex-1 px-6 py-3 bg-rose-600 text-white font-black rounded-xl hover:bg-rose-700 transition-colors text-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Download PDF
                </button>
                <button type="button" onclick="tutupModalPdf()" class="px-4 py-3 bg-gray-100 text-gray-600 font-bold rounded-xl hover:bg-gray-200 transition-colors text-sm">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const kelasList = @json($kelasList->map(fn($k) => ['id' => $k->id, 'nama' => $k->nama]));

function bukaModalPdf() {
    const kelasId = document.getElementById('kelas_id').value;
    const kelas = kelasList.find(k => k.id == kelasId);
    document.getElementById('modal_kelas_id').value = kelasId;
    document.getElementById('modal_kelas_label').textContent = kelas ? kelas.nama : 'Pilih kelas dulu di filter!';
    document.getElementById('modalPdf').classList.remove('hidden');
}
function tutupModalPdf() {
    document.getElementById('modalPdf').classList.add('hidden');
}
document.getElementById('modalPdf').addEventListener('click', function(e) {
    if (e.target === this) tutupModalPdf();
});
</script>
    </div>
</x-app-layout>