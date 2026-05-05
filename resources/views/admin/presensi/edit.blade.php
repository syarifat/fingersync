<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            Edit Data Presensi
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.presensi.update', $presensi->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="bg-white shadow-2xl rounded-[2rem] overflow-hidden border border-gray-100">
                    <div class="p-10 space-y-6">
                        
                        <div class="border-b border-gray-100 pb-4 mb-4 flex justify-between items-center">
                            <h3 class="text-lg font-black text-orange-600 uppercase tracking-wide flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Ubah Status Kehadiran
                            </h3>
                            <span class="text-sm font-bold text-gray-400">{{ \Carbon\Carbon::parse($presensi->tanggal)->format('d M Y') }} - {{ $presensi->jam_scan }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-6 bg-gray-50 p-6 rounded-2xl border border-gray-100">
                            <div>
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Siswa</span>
                                <span class="font-black text-gray-800">{{ $presensi->siswa->nama ?? 'Siswa Tidak Ditemukan' }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">NIS</span>
                                <span class="font-black text-gray-800">{{ $presensi->siswa->nis ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Mata Pelajaran</span>
                                <span class="font-black text-gray-800">{{ $presensi->rombelJadwalPelajaran->rombelMataPelajaran->mataPelajaran->nama ?? 'Mapel Tidak Ditemukan' }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Waktu Pelajaran</span>
                                <span class="font-black text-gray-800">{{ $presensi->rombelJadwalPelajaran->jam_mulai ?? '-' }} - {{ $presensi->rombelJadwalPelajaran->jam_selesai ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="pt-4">
                            <x-input-label for="status" value="Status Kehadiran" class="font-bold text-gray-700" />
                            <select name="status" id="status" class="mt-2 block w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm">
                                <option value="Hadir" {{ $presensi->status == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="Izin" {{ $presensi->status == 'Izin' ? 'selected' : '' }}>Izin</option>
                                <option value="Sakit" {{ $presensi->status == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="Terlambat" {{ $presensi->status == 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                                <option value="Alpa" {{ $presensi->status == 'Alpa' ? 'selected' : '' }}>Alpa</option>
                            </select>
                            <x-input-error :messages="$errors->get('status')" class="mt-1" />
                            <p class="text-xs text-gray-500 mt-2 italic">*Mengubah status absensi ini akan langsung tercermin di laporan nilai dan dashboard guru.</p>
                        </div>

                    </div>

                    <div class="bg-gray-50 px-10 py-6 flex justify-end items-center gap-4 border-t border-gray-100">
                        <a href="{{ route('admin.presensi.index') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors uppercase tracking-widest">
                            Batal
                        </a>
                        <button type="submit" class="px-8 py-3 bg-orange-600 text-white font-black rounded-xl shadow-lg hover:bg-orange-700 transition-all uppercase tracking-widest text-xs transform hover:-translate-y-0.5">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
