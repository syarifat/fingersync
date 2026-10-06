<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Edit Kegiatan Sekolah') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-orange-600 uppercase tracking-tighter">Edit Form Kegiatan</h3>
                        <p class="text-sm text-gray-500">Ubah detail kegiatan sekolah serentak.</p>
                    </div>

                    @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-sm font-bold shadow-sm">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('admin.kegiatan-sekolah.update', $kegiatan->id) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label for="nama_kegiatan" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Nama Kegiatan</label>
                            <input type="text" name="nama_kegiatan" id="nama_kegiatan" value="{{ old('nama_kegiatan', $kegiatan->nama_kegiatan) }}" required class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="tanggal" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Tanggal Pelaksanaan</label>
                                <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', $kegiatan->tanggal) }}" required class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4">
                            </div>

                            <div>
                                <label for="tipe" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Tipe Kegiatan</label>
                                <select name="tipe" id="tipe" required class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4">
                                    <option value="serentak" {{ old('tipe', $kegiatan->tipe) == 'serentak' ? 'selected' : '' }}>Serentak (Bypass KBM Reguler)</option>
                                    <option value="non-serentak" {{ old('tipe', $kegiatan->tipe) == 'non-serentak' ? 'selected' : '' }}>Non-Serentak (KBM Reguler Tetap Jalan)</option>
                                </select>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-6">
                            <h4 class="font-bold text-gray-800 text-sm mb-4">Pengaturan Waktu Absensi Datang</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="jam_mulai_datang" class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Jam Mulai Scan Datang</label>
                                    <input type="time" name="jam_mulai_datang" id="jam_mulai_datang" value="{{ old('jam_mulai_datang', substr($kegiatan->jam_mulai_datang, 0, 5)) }}" required class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4 font-mono">
                                </div>
                                <div>
                                    <label for="jam_selesai_datang" class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Jam Selesai Scan Datang</label>
                                    <input type="time" name="jam_selesai_datang" id="jam_selesai_datang" value="{{ old('jam_selesai_datang', substr($kegiatan->jam_selesai_datang, 0, 5)) }}" required class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4 font-mono">
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 pt-6">
                            <h4 class="font-bold text-gray-800 text-sm mb-4">Pengaturan Waktu Absensi Pulang</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="jam_mulai_pulang" class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Jam Mulai Scan Pulang</label>
                                    <input type="time" name="jam_mulai_pulang" id="jam_mulai_pulang" value="{{ old('jam_mulai_pulang', substr($kegiatan->jam_mulai_pulang, 0, 5)) }}" required class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4 font-mono">
                                </div>
                                <div>
                                    <label for="jam_selesai_pulang" class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Jam Selesai Scan Pulang</label>
                                    <input type="time" name="jam_selesai_pulang" id="jam_selesai_pulang" value="{{ old('jam_selesai_pulang', substr($kegiatan->jam_selesai_pulang, 0, 5)) }}" required class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4 font-mono">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="keterangan" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Keterangan / Catatan Tambahan (Opsional)</label>
                            <textarea name="keterangan" id="keterangan" rows="3" class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4">{{ old('keterangan', $kegiatan->keterangan) }}</textarea>
                        </div>

                        <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">
                            <a href="{{ route('admin.kegiatan-sekolah.index') }}" class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl text-xs font-bold uppercase tracking-widest transition-all">
                                Batal
                            </a>
                            <button type="submit" class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-2xl font-bold text-xs uppercase tracking-widest transition-all shadow-lg shadow-orange-100">
                                Perbarui Kegiatan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
