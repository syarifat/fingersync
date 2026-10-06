<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Kondisi Khusus KBM Guru') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                        <div>
                            <h3 class="text-xl font-bold text-orange-600 uppercase tracking-tighter">Manajemen Kondisi Khusus Guru</h3>
                            <p class="text-sm text-gray-500">Atur kondisi ketika Guru mata pelajaran berhalangan hadir (Izin) pada tanggal dan jadwal tertentu.</p>
                        </div>
                        <a href="{{ route('admin.kbm-khusus.create') }}" class="w-full md:w-auto px-6 py-3 bg-emerald-600 text-white rounded-2xl font-bold text-xs uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-100 flex items-center justify-center gap-2 hover:-translate-y-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Tambah Kondisi Baru
                        </a>
                    </div>

                    @if (session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-center text-emerald-700 font-bold text-sm shadow-sm animate-fade-in">
                        {{ session('success') }}
                    </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 pl-4 font-black w-16">No</th>
                                    <th class="pb-4 font-black">Tanggal</th>
                                    <th class="pb-4 font-black">Jadwal & Mata Pelajaran</th>
                                    <th class="pb-4 font-black">Guru Asli</th>
                                    <th class="pb-4 font-black text-center">Status</th>
                                    <th class="pb-4 font-black">Guru Pengganti / Tugas</th>
                                    <th class="pb-4 pr-4 font-black text-right w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($kbmKhususList as $index => $khusus)
                                <tr class="group hover:bg-orange-50/10 transition-colors">
                                    <td class="py-5 pl-4 font-medium text-gray-500">{{ $index + 1 }}</td>
                                    <td class="py-5 text-gray-700 font-semibold">
                                        {{ Carbon\Carbon::parse($khusus->tanggal)->isoFormat('DD MMMM YYYY') }}
                                    </td>
                                    <td class="py-5">
                                        @if($khusus->rombelJadwalPelajaran)
                                            <div class="flex flex-col">
                                                <span class="font-bold text-gray-900 text-sm tracking-tight">{{ $khusus->rombelJadwalPelajaran->rombelMataPelajaran->mataPelajaran->nama ?? '-' }}</span>
                                                <span class="text-xs text-gray-400 font-semibold mt-0.5">Kelas: {{ $khusus->rombelJadwalPelajaran->rombelMataPelajaran->kelas->nama ?? '-' }} ({{ substr($khusus->rombelJadwalPelajaran->jam_mulai, 0, 5) }} - {{ substr($khusus->rombelJadwalPelajaran->jam_selesai, 0, 5) }})</span>
                                            </div>
                                        @else
                                            <span class="text-red-500 italic">Jadwal telah dihapus</span>
                                        @endif
                                    </td>
                                    <td class="py-5 text-gray-700 font-semibold text-sm">
                                        {{ $khusus->rombelJadwalPelajaran->rombelMataPelajaran->guru->nama ?? '-' }}
                                    </td>
                                    <td class="py-5 text-center">
                                        @if($khusus->status === 'izin_tugas')
                                            <span class="inline-flex items-center px-3 py-1 bg-emerald-50 text-emerald-700 rounded-full text-xs font-bold ring-1 ring-emerald-200 uppercase">Izin (Ada Tugas)</span>
                                        @elseif($khusus->status === 'izin_libur')
                                            <span class="inline-flex items-center px-3 py-1 bg-rose-50 text-rose-700 rounded-full text-xs font-bold ring-1 ring-rose-200 uppercase">Izin (Libur)</span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 bg-yellow-50 text-yellow-700 rounded-full text-xs font-bold ring-1 ring-yellow-200 uppercase">{{ $khusus->status }}</span>
                                        @endif
                                    </td>
                                    <td class="py-5 text-sm">
                                        @if($khusus->status === 'izin_tugas')
                                            <span class="text-emerald-700 font-semibold" title="{{ $khusus->keterangan }}">Tugas: {{ $khusus->keterangan ?? 'Mengerjakan Tugas yang Diberikan' }}</span>
                                        @elseif($khusus->status === 'izin_libur')
                                            <span class="text-gray-400 italic">Jadwal Libur (Tidak Ada Absensi)</span>
                                        @else
                                            <span class="text-gray-500 italic" title="{{ $khusus->keterangan }}">{{ $khusus->keterangan ?? '-' }}</span>
                                        @endif
                                    </td>
                                    <td class="py-5 pr-4 text-right">
                                        <div class="flex justify-end gap-3">
                                            <a href="{{ route('admin.kbm-khusus.edit', $khusus->id) }}" class="p-2 bg-orange-100 text-orange-600 rounded-lg hover:bg-orange-600 hover:text-white transition-all shadow-sm" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>

                                            <form action="{{ route('admin.kbm-khusus.destroy', $khusus->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data kondisi khusus guru ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-500 hover:text-white transition-all shadow-sm" title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-3a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-gray-400 italic">Belum ada data kondisi khusus KBM guru. Silakan tambahkan kondisi baru.</td>
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
