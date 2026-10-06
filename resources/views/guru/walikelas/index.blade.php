<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Panel Wali Kelas') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- HEADER KELAS & FILTER BULAN --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-gray-100 p-8 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-orange-50 text-orange-600 rounded-2xl">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Wali Kelas Dari</h3>
                        <p class="text-3xl font-black text-gray-900">{{ $infoKelas->kelas->nama }}</p>
                    </div>
                </div>

                <form method="GET" action="{{ route('guru.walikelas.index') }}" class="flex items-end gap-3 bg-gray-50 p-3 rounded-2xl border border-gray-200">
                    <div>
                        <label for="bulan" class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Bulan</label>
                        <input type="month" name="bulan" id="bulan" value="{{ $bulanFilter }}" class="rounded-xl border-gray-300 text-sm focus:ring-orange-500 focus:border-orange-500 shadow-sm" onchange="this.form.submit()">
                    </div>
                </form>
            </div>

            {{-- TABEL REKAP ABSENSI --}}
            <div class="bg-white overflow-hidden shadow-sm rounded-[2rem] border border-gray-100 p-8">
                <div class="mb-6 flex justify-between items-center">
                    <h3 class="text-lg font-black text-gray-800">Rekapitulasi Kehadiran Siswa</h3>
                    <a href="{{ route('guru.walikelas.export_pdf', ['bulan' => $bulanFilter]) }}" class="px-4 py-2 bg-orange-600 text-white rounded-xl text-sm font-bold hover:bg-orange-700 transition-colors shadow-sm flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2-2v4h10z"></path></svg>
                        Cetak Laporan (PDF)
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-gray-500 text-xs uppercase tracking-widest bg-gray-50">
                                <th class="p-4 font-black rounded-tl-xl w-16">No</th>
                                <th class="p-4 font-black">NIS / Nama Siswa</th>
                                <th class="p-4 font-black text-center text-emerald-600">Hadir</th>
                                <th class="p-4 font-black text-center text-blue-600">Sakit</th>
                                <th class="p-4 font-black text-center text-orange-600">Izin</th>
                                <th class="p-4 font-black text-center text-red-600">Alpha</th>
                                <th class="p-4 font-black text-center text-rose-700 rounded-tr-xl">Jumlah AIS</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($siswaKelas as $index => $rs)
                            <tr class="hover:bg-orange-50/20 transition-colors">
                                <td class="p-4 font-medium text-gray-500">
                                    {{ $index + 1 }}
                                </td>
                                <td class="p-4">
                                    <div class="text-xs text-gray-400 font-mono mb-0.5">{{ $rs->siswa->nis }}</div>
                                    <div class="font-bold text-gray-800">{{ $rs->siswa->nama }}</div>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $rs->total_hadir > 0 ? 'bg-emerald-100 text-emerald-700 font-bold' : 'text-gray-300' }}">
                                        {{ $rs->total_hadir }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $rs->total_sakit > 0 ? 'bg-blue-100 text-blue-700 font-bold' : 'text-gray-300' }}">
                                        {{ $rs->total_sakit }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $rs->total_izin > 0 ? 'bg-orange-100 text-orange-700 font-bold' : 'text-gray-300' }}">
                                        {{ $rs->total_izin }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full {{ $rs->total_alpha > 0 ? 'bg-red-100 text-red-700 font-bold' : 'text-gray-300' }}">
                                        {{ $rs->total_alpha }}
                                    </span>
                                </td>
                                <td class="p-4 text-center">
                                    <span class="inline-flex items-center justify-center w-10 h-8 rounded-full {{ ($rs->total_alpha + $rs->total_izin + $rs->total_sakit) > 0 ? 'bg-rose-100 text-rose-700 font-black' : 'text-gray-300' }}">
                                        {{ $rs->total_alpha + $rs->total_izin + $rs->total_sakit }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>