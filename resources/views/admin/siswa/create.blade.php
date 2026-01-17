<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Registrasi Siswa Baru') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.siswa.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white shadow-xl rounded-3xl overflow-hidden border border-gray-100">
                    <div class="p-8">
                        <div class="mb-10">
                            <h3 class="text-lg font-bold text-orange-600 mb-6 flex items-center">
                                <span class="bg-orange-100 p-2 rounded-lg mr-3">🎓</span> Data Akademik & Biometrik
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="nis" value="NIS" />
                                    <x-text-input id="nis" name="nis" type="text" class="mt-1 block w-full" :value="old('nis')" required />
                                    <x-input-error :messages="$errors->get('nis')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="nama" value="Nama Lengkap" />
                                    <x-text-input id="nama" name="nama" type="text" class="mt-1 block w-full" :value="old('nama')" required />
                                </div>
                                <div>
                                    <x-input-label for="fingerprint_id" value="Fingerprint ID" />
                                    <x-text-input id="fingerprint_id" name="fingerprint_id" type="number" class="mt-1 block w-full" :value="old('fingerprint_id')" required />
                                </div>
                                <div>
                                    <x-input-label for="id_jurusan" value="Jurusan" />
                                    <select name="id_jurusan" class="w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm mt-1">
                                        @foreach($jurusan as $j)
                                            <option value="{{ $j->id }}">{{ $j->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="status" value="Status" />
                                    <select name="status" class="w-full border-gray-300 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm mt-1">
                                        <option value="Aktif">Aktif</option>
                                        <option value="Non-Aktif">Non-Aktif</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="image" value="Foto Profil" />
                                    <input type="file" name="image" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100" />
                                </div>
                            </div>
                        </div>

                        <div class="mb-10">
                            <h3 class="text-lg font-bold text-blue-600 mb-6 flex items-center">
                                <span class="bg-blue-100 p-2 rounded-lg mr-3">👤</span> Informasi Pribadi
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="gender" value="Jenis Kelamin" />
                                    <select name="gender" class="w-full border-gray-300 rounded-xl shadow-sm mt-1">
                                        <option value="Laki-laki">Laki-laki</option>
                                        <option value="Perempuan">Perempuan</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="agama" value="Agama" />
                                    <x-text-input name="agama" type="text" class="mt-1 block w-full" :value="old('agama', 'Islam')" />
                                </div>
                                <div>
                                    <x-input-label for="email" value="Email" />
                                    <x-text-input name="email" type="email" class="mt-1 block w-full" :value="old('email')" />
                                </div>
                                <div class="md:col-span-3">
                                    <x-input-label for="alamat" value="Alamat Lengkap" />
                                    <textarea name="alamat" class="w-full border-gray-300 rounded-xl shadow-sm mt-1" rows="2">{{ old('alamat') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-lg font-bold text-emerald-600 mb-6 flex items-center">
                                <span class="bg-emerald-100 p-2 rounded-lg mr-3">👪</span> Data Keluarga
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="nama_ayah" value="Nama Ayah" />
                                    <x-text-input name="nama_ayah" type="text" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label for="nama_ibu" value="Nama Ibu" />
                                    <x-text-input name="nama_ibu" type="text" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label for="nohp_ortu" value="No. HP Orang Tua (WA)" />
                                    <x-text-input name="nohp_ortu" type="text" class="mt-1 block w-full border-orange-200 bg-orange-50/20" placeholder="628..." />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-8 py-6 flex justify-end gap-3">
                        <a href="{{ route('admin.siswa.index') }}" class="px-6 py-2 text-gray-600 font-bold hover:underline">Batal</a>
                        <button type="submit" class="px-10 py-2 bg-orange-600 text-white font-bold rounded-xl shadow-lg hover:bg-orange-700 transition-all">Simpan Siswa</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>