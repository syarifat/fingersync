<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">Edit Hari Libur</h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.hari-libur.update', $hariLibur->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="bg-white shadow-2xl rounded-[2rem] overflow-hidden border border-gray-100 p-10">
                    
                    <div class="mb-8 border-b border-gray-100 pb-4">
                        <h3 class="text-lg font-black text-orange-600 uppercase tracking-wide">Edit Data Hari Libur</h3>
                        <p class="text-sm text-gray-500">Sesuaikan tanggal atau detail informasi hari libur.</p>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <x-input-label for="nama" value="Nama Hari Libur / Acara" class="font-bold text-gray-700" />
                            <x-text-input id="nama" name="nama" type="text" class="mt-2 block w-full focus:ring-orange-500 border-gray-200 rounded-xl" :value="old('nama', $hariLibur->nama)" required autofocus />
                            <x-input-error :messages="$errors->get('nama')" class="mt-1" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="tanggal_mulai" value="Tanggal Mulai" class="font-bold text-gray-700" />
                                <x-text-input id="tanggal_mulai" name="tanggal_mulai" type="date" class="mt-2 block w-full focus:ring-orange-500 border-gray-200 rounded-xl" :value="old('tanggal_mulai', $hariLibur->tanggal_mulai)" required />
                                <x-input-error :messages="$errors->get('tanggal_mulai')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="tanggal_selesai" value="Tanggal Selesai" class="font-bold text-gray-700" />
                                <x-text-input id="tanggal_selesai" name="tanggal_selesai" type="date" class="mt-2 block w-full focus:ring-orange-500 border-gray-200 rounded-xl" :value="old('tanggal_selesai', $hariLibur->tanggal_selesai)" required />
                                <x-input-error :messages="$errors->get('tanggal_selesai')" class="mt-1" />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="jenis" value="Jenis Libur" class="font-bold text-gray-700" />
                            <select name="jenis" id="jenis" class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm mt-2" required>
                                <option value="sekolah" {{ old('jenis', $hariLibur->jenis) == 'sekolah' ? 'selected' : '' }}>Libur Sekolah / Agenda Khusus</option>
                                <option value="nasional" {{ old('jenis', $hariLibur->jenis) == 'nasional' ? 'selected' : '' }}>Libur Nasional (PHBN/PHBI)</option>
                            </select>
                            <x-input-error :messages="$errors->get('jenis')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="keterangan" value="Keterangan / Deskripsi (Opsional)" class="font-bold text-gray-700" />
                            <textarea id="keterangan" name="keterangan" rows="3" class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm mt-2" placeholder="Tulis deskripsi atau alasan libur...">{{ old('keterangan', $hariLibur->keterangan) }}</textarea>
                            <x-input-error :messages="$errors->get('keterangan')" class="mt-1" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 border-t border-gray-100 pt-6 mt-8">
                        <a href="{{ route('admin.hari-libur.index') }}" class="px-6 py-3 text-gray-500 font-bold hover:text-gray-700 uppercase tracking-widest text-xs">BATAL</a>
                        <button type="submit" class="px-10 py-3 bg-orange-600 text-white font-black rounded-xl shadow-lg hover:bg-orange-700 uppercase tracking-widest text-xs">SIMPAN DATA</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
