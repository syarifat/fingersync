<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Dashboard FingerSync') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- ALERT INBOX PENDING (Hanya muncul jika ada jari baru yang belum didaftarkan) --}}
            @if($inboxPending > 0)
            <div class="mb-8 bg-orange-100 border-l-4 border-orange-500 p-4 rounded-r-xl shadow-sm flex items-center justify-between">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-orange-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <p class="text-orange-800 font-semibold text-sm">
                        Perhatian: Ada <span class="font-black text-lg">{{ $inboxPending }}</span> antrian sidik jari baru dari alat yang menunggu kelengkapan data siswa!
                    </p>
                </div>
                <a href="{{ route('admin.siswa.inbox') }}" class="px-4 py-2 bg-orange-600 text-white text-xs font-bold rounded-lg hover:bg-orange-700 transition">
                    Cek Inbox Sekarang
                </a>
            </div>
            @endif

            {{-- GRID KARTU STATISTIK --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center">
                    <div class="p-4 bg-blue-50 rounded-xl mr-4">
                        <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Total Siswa</p>
                        <h3 class="text-3xl font-black text-gray-800">{{ $totalSiswa }}</h3>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center">
                    <div class="p-4 bg-emerald-50 rounded-xl mr-4">
                        <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Hadir (Terbaru)</p>
                        <h3 class="text-3xl font-black text-gray-800">{{ $hadirHariIni }}</h3>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center">
                    <div class="p-4 bg-red-50 rounded-xl mr-4">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Terlambat</p>
                        <h3 class="text-3xl font-black text-gray-800">{{ $terlambatHariIni }}</h3>
                    </div>
                </div>

                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center">
                    <div class="p-4 bg-purple-50 rounded-xl mr-4">
                        <svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">Alat ESP32</p>
                        <h3 class="text-3xl font-black text-gray-800">{{ $totalDevice }}</h3>
                    </div>
                </div>

            </div>

            {{-- SISWA TERLAMBAT HARI INI --}}
            @if($terlambatByKelas->count() > 0)
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100 p-8 mb-8">
                <div class="mb-6">
                    <h3 class="text-lg font-black text-gray-800 tracking-tight flex items-center gap-2">
                        <span class="p-1 bg-red-100 text-red-600 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </span>
                        Siswa Terlambat Kehadiran ({{ \Carbon\Carbon::parse($tanggalTerakhir)->format('d M Y') }})
                    </h3>
                    <p class="text-sm text-gray-500 font-medium">Daftar siswa terlambat yang dikelompokkan berdasarkan nama kelas.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($terlambatByKelas as $namaKelas => $listPresensi)
                        <div class="bg-rose-50/30 border border-rose-100 rounded-2xl p-5 hover:shadow-md transition-all duration-200">
                            <h4 class="font-black text-rose-700 text-sm mb-3 flex items-center justify-between border-b border-rose-100 pb-2">
                                <span>🏫 Kelas {{ $namaKelas }}</span>
                                <span class="bg-rose-200/50 text-rose-800 text-xs px-2.5 py-0.5 rounded-full font-black">{{ $listPresensi->count() }} Siswa</span>
                            </h4>
                            <ul class="space-y-2 text-xs">
                                @foreach($listPresensi as $p)
                                    <li class="flex justify-between items-center py-1.5 border-b border-rose-100/30 last:border-b-0">
                                        <div class="flex flex-col">
                                            <span class="font-bold text-gray-800 text-sm">{{ $p->siswa->nama }}</span>
                                            <span class="text-[10px] text-gray-400 font-mono">NIS: {{ $p->siswa->nis }}</span>
                                        </div>
                                        <span class="font-mono text-orange-600 font-black bg-white px-2 py-1 rounded-lg border border-orange-100 shadow-sm">{{ substr($p->jam_scan, 0, 5) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- TABEL PRESENSI TERBARU --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800 tracking-tight">Log Presensi Terbaru</h3>
                            <p class="text-sm text-gray-500">Menampilkan 5 siswa terakhir yang menempelkan jari di alat.</p>
                        </div>
                        <a href="#" class="text-sm font-bold text-orange-600 hover:text-orange-700">Lihat Semua Data &rarr;</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 font-semibold pl-4">Jam Scan</th>
                                    <th class="pb-4 font-semibold">Nama Siswa</th>
                                    <th class="pb-4 font-semibold">Mata Pelajaran</th>
                                    <th class="pb-4 font-semibold text-right pr-4">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($presensiTerbaru as $p)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-4 pl-4 font-mono text-sm text-gray-600 font-bold">
                                        {{ \Carbon\Carbon::parse($p->jam_scan)->format('H:i:s') }}
                                    </td>
                                    <td class="py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $p->siswa->nama ?? 'Siswa Tidak Ditemukan' }}</div>
                                        <div class="text-xs text-gray-400">NIS: {{ $p->siswa->nis ?? '-' }}</div>
                                    </td>
                                    <td class="py-4 text-sm text-gray-600">
                                        {{ $p->rombelJadwalPelajaran->rombelMataPelajaran->mataPelajaran->nama ?? 'Tidak Ada Jadwal' }}
                                    </td>
                                    <td class="py-4 text-right pr-4">
                                        @if($p->status == 'Hadir')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800">HADIR</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-red-100 text-red-800">TERLAMBAT</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-gray-400">
                                        Belum ada data presensi yang masuk.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>