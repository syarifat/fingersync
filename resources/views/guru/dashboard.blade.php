<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Dashboard Tenaga Pendidik') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- KARTU UCAPAN SELAMAT DATANG --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-gray-100 p-8 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-6">
                    <div class="h-20 w-20 rounded-full overflow-hidden border-4 border-orange-100 bg-gray-100 flex items-center justify-center shrink-0 shadow-md">
                        @if($guru->image)
                            <img src="{{ asset('img/guru/'.$guru->image) }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-orange-600 font-bold text-3xl">{{ substr($guru->nama, 0, 1) }}</span>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-2xl font-black text-gray-800">Selamat datang, {{ $guru->nama }}!</h3>
                        <p class="text-gray-500 font-medium mt-1">NIY: {{ $guru->nidn }}</p>
                    </div>
                </div>
                
                <div class="flex gap-4">
                    <div class="bg-orange-50 px-6 py-3 rounded-2xl border border-orange-100 text-center">
                        <span class="block text-2xl font-black text-orange-600">{{ $totalJadwalSeminggu }}</span>
                        <span class="block text-xs font-bold text-orange-800 uppercase tracking-widest mt-1">Total Kelas</span>
                    </div>
                    @if($isWaliKelas)
                    <div class="bg-blue-50 px-6 py-3 rounded-2xl border border-blue-100 text-center">
                        <span class="block text-2xl font-black text-blue-600">{{ $isWaliKelas->kelas->nama }}</span>
                        <span class="block text-xs font-bold text-blue-800 uppercase tracking-widest mt-1">Wali Kelas</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- TABEL PRESENSI TERBARU --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-gray-100 p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4 border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="text-lg font-black text-orange-600 uppercase tracking-wide">Log Presensi Terbaru</h3>
                        <p class="text-sm text-gray-500 font-medium mt-1">Menampilkan 5 siswa terakhir dari kelas Anda yang menempelkan jari.</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <form method="GET" action="{{ route('guru.dashboard') }}" id="filterForm" class="flex items-center">
                            <select name="kelas_id" onchange="this.form.submit()" class="rounded-xl border-gray-200 bg-white text-xs font-bold text-gray-600 focus:border-orange-500 focus:ring-orange-500 shadow-sm py-1.5 pl-3 pr-8">
                                <option value="">-- Semua Kelas --</option>
                                @foreach($kelasList as $k)
                                    <option value="{{ $k->id }}" {{ $filterKelasId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                                @endforeach
                            </select>
                        </form>
                        <a href="{{ route('guru.riwayat-absensi.index', ['kelas_id' => $filterKelasId]) }}" class="text-sm font-bold text-orange-600 hover:text-orange-700 whitespace-nowrap">Lihat Semua Data &rarr;</a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                <th class="pb-4 font-semibold pl-4">Jam Scan</th>
                                <th class="pb-4 font-semibold">Nama Siswa</th>
                                <th class="pb-4 font-semibold">Kelas</th>
                                <th class="pb-4 font-semibold">Mata Pelajaran / Kegiatan</th>
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
                                <td class="py-4 text-sm text-gray-600 font-medium">
                                    {{ $p->siswa->rombelKelas->kelas->nama ?? '-' }}
                                </td>
                                <td class="py-4 text-sm text-gray-600">
                                    @if($p->tipe_scan === 'pulang')
                                        <span class="text-teal-600 font-bold">Absen Pulang Sekolah</span>
                                    @elseif($p->rombelJadwalPelajaran)
                                        {{ $p->rombelJadwalPelajaran->rombelMataPelajaran->mataPelajaran->nama ?? 'Tidak Ada Jadwal' }}
                                    @elseif($p->kegiatanSekolah)
                                        <span class="text-orange-600 font-bold">Kegiatan: {{ $p->kegiatanSekolah->nama_kegiatan }}</span>
                                    @else
                                        <span class="text-gray-400 italic">Diluar Jadwal</span>
                                    @endif
                                </td>
                                <td class="py-4 text-right pr-4">
                                    @if($p->tipe_scan === 'pulang')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-teal-100 text-teal-800">PULANG</span>
                                    @elseif($p->status == 'Hadir')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-emerald-100 text-emerald-800">HADIR</span>
                                    @elseif($p->status == 'Terlambat')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-red-100 text-red-800">TERLAMBAT</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold bg-gray-100 text-gray-800">{{ strtoupper($p->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-gray-400">
                                    Belum ada data presensi yang masuk.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- JADWAL HARI INI --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-gray-100 p-8">
                <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="text-lg font-black text-orange-600 uppercase tracking-wide">Jadwal Mengajar Hari Ini</h3>
                        <p class="text-sm text-gray-500 font-medium">{{ $hariIni }}, {{ \Carbon\Carbon::now()->format('d M Y') }}</p>
                    </div>
                </div>

                @if($jadwalHariIni->isEmpty())
                    <div class="py-12 text-center bg-gray-50 rounded-2xl border border-dashed border-gray-200">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-gray-500 font-bold">Alhamdulillah, Anda tidak ada jadwal mengajar hari ini.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($jadwalHariIni as $jadwal)
                        @php
                            $khusus = $kbmKhususUtama[$jadwal->id] ?? null;
                        @endphp
                        <div class="group bg-white border border-gray-200 rounded-2xl p-5 hover:border-orange-500 hover:shadow-lg transition-all duration-300 relative overflow-hidden">
                            <div class="absolute top-0 left-0 w-1 h-full bg-orange-500"></div>
                            
                            <div class="flex justify-between items-start mb-4">
                                <div class="bg-orange-100 text-orange-700 font-black text-sm px-3 py-1 rounded-lg">
                                    {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                </div>
                                <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded">
                                    {{ $jadwal->ruangan->nama_ruangan }}
                                </span>
                            </div>
                            
                            {{-- Nama Mata Pelajaran --}}
                            <h4 class="font-black text-xl text-gray-800 mb-1">{{ $jadwal->rombelMataPelajaran->mataPelajaran->nama }}</h4>
                            
                            {{-- Nama Kelas --}}
                            <p class="text-gray-500 font-medium text-sm flex items-center gap-2">
                                <svg class="w-4 h-4 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                Kelas: {{ $jadwal->rombelMataPelajaran->kelas->nama }}
                            </p>

                            @if($khusus)
                                <div class="mt-3 p-3 bg-red-50 rounded-xl border border-red-100">
                                    @if(in_array($khusus->status, ['izin', 'absen']))
                                        <span class="text-red-700 font-black text-xs uppercase block">Status KBM: Anda {{ ucfirst($khusus->status) }}</span>
                                        @if($khusus->keterangan)
                                            <p class="text-xs text-red-600 font-medium mt-1">Tugas: {{ $khusus->keterangan }}</p>
                                        @endif
                                    @elseif($khusus->status === 'diganti')
                                        <span class="text-red-700 font-black text-xs uppercase block">Status KBM: Digantikan</span>
                                        <p class="text-xs text-red-600 font-medium mt-1">Diganti oleh: {{ $khusus->guruPengganti->nama ?? 'Guru Lain' }}</p>
                                    @endif
                                </div>
                            @endif

                            {{-- TAMBAHKAN KODE TOMBOL INI DI SINI --}}
                            <div class="mt-5 pt-4 border-t border-gray-100 flex justify-end">
                                @if($khusus && $khusus->status === 'diganti')
                                    <span class="text-gray-400 text-xs italic font-bold">KBM dialihkan ke guru pengganti</span>
                                @else
                                    <a href="{{ route('guru.presensi.show', $jadwal->id) }}" class="px-4 py-2 bg-orange-50 text-orange-700 rounded-xl text-sm font-bold hover:bg-orange-600 hover:text-white transition-colors shadow-sm flex items-center gap-2">
                                        Lihat Presensi
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                @endif
                            </div>
                            {{-- BATAS KODE TOMBOL --}}

                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if($jadwalHariIniPengganti->isNotEmpty())
            {{-- JADWAL PENGGANTI HARI INI --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-gray-100 p-8 mt-8">
                <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                    <div>
                        <h3 class="text-lg font-black text-blue-600 uppercase tracking-wide">Jadwal Mengajar Tambahan (Guru Pengganti)</h3>
                        <p class="text-sm text-gray-500 font-medium">Jadwal di mana Anda ditunjuk sebagai guru pengganti hari ini.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($jadwalHariIniPengganti as $jadwal)
                    <div class="group bg-white border border-blue-200 rounded-2xl p-5 hover:border-blue-500 hover:shadow-lg transition-all duration-300 relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-1 h-full bg-blue-500"></div>
                        
                        <div class="flex justify-between items-start mb-4">
                            <div class="bg-blue-100 text-blue-700 font-black text-sm px-3 py-1 rounded-lg">
                                {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                            </div>
                            <span class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-1 rounded">
                                {{ $jadwal->ruangan->nama_ruangan }}
                            </span>
                        </div>
                        
                        <h4 class="font-black text-xl text-gray-800 mb-1">{{ $jadwal->rombelMataPelajaran->mataPelajaran->nama }}</h4>
                        
                        <p class="text-gray-500 font-medium text-sm flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            Kelas: {{ $jadwal->rombelMataPelajaran->kelas->nama }}
                        </p>

                        <div class="mt-5 pt-4 border-t border-gray-100 flex justify-between items-center">
                            <span class="bg-blue-100 text-blue-800 text-[10px] px-2 py-0.5 rounded-full font-bold uppercase">Guru Pengganti</span>
                            <a href="{{ route('guru.presensi.show', $jadwal->id) }}" class="px-4 py-2 bg-blue-50 text-blue-700 rounded-xl text-sm font-bold hover:bg-blue-600 hover:text-white transition-colors shadow-sm flex items-center gap-2">
                                Lihat Presensi
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            </div>

        </div>
    </div>
</x-app-layout>