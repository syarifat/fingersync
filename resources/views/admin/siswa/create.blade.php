<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Registrasi Siswa Baru - FingerSync') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.siswa.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white shadow-2xl rounded-[2rem] overflow-hidden border border-gray-100">
                    <div class="p-10">
                        
                        <div class="mb-12">
                            <h3 class="text-xl font-black text-orange-600 mb-8 flex items-center tracking-tight">
                                <span class="bg-orange-600 text-white p-2 rounded-xl mr-4 shadow-lg shadow-orange-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                                </span> 
                                I. DATA AKADEMIK & BIOMETRIK
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                <div>
                                    <x-input-label for="nis" value="Nomor Induk Siswa (NIS)" class="font-bold text-gray-700" />
                                    <x-text-input id="nis" name="nis" type="text" class="mt-2 block w-full border-gray-200 focus:ring-orange-500" :value="old('nis')" required placeholder="Wajib diisi" />
                                    <x-input-error :messages="$errors->get('nis')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="nama" value="Nama Lengkap Siswa" class="font-bold text-gray-700" />
                                    <x-text-input id="nama" name="nama" type="text" class="mt-2 block w-full border-gray-200" :value="old('nama')" required placeholder="Sesuai Akta Kelahiran" />
                                    <x-input-error :messages="$errors->get('nama')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="fingerprint_id" value="Fingerprint ID (Alat)" class="font-bold text-gray-700" />
                                    <x-text-input id="fingerprint_id" name="fingerprint_id" type="number" class="mt-2 block w-full border-gray-200" :value="old('fingerprint_id')" required placeholder="ID Sensor" />
                                    <x-input-error :messages="$errors->get('fingerprint_id')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="id_jurusan" value="Program Keahlian (Jurusan)" class="font-bold text-gray-700" />
                                    <select name="id_jurusan" required class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm mt-2">
                                        <option value="">-- Pilih Jurusan --</option>
                                        @foreach($jurusan as $j)
                                            <option value="{{ $j->id }}" {{ old('id_jurusan') == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('id_jurusan')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="status" value="Status Keanggotaan" class="font-bold text-gray-700" />
                                    <select name="status" required class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm mt-2">
                                        <option value="Aktif" {{ old('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Non-Aktif" {{ old('status') == 'Non-Aktif' ? 'selected' : '' }}>Non-Aktif</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('status')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="image" value="Pas Foto Siswa" class="font-bold text-gray-700" />
                                    <input type="file" name="image" required class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100" />
                                    <x-input-error :messages="$errors->get('image')" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <div class="h-px bg-gray-100 w-full mb-12 shadow-inner"></div>

                        <div class="mb-12">
                            <h3 class="text-xl font-black text-blue-600 mb-8 flex items-center tracking-tight">
                                <span class="bg-blue-600 text-white p-2 rounded-xl mr-4 shadow-lg shadow-blue-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                </span> 
                                II. INFORMASI PRIBADI & KONTAK
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                                <div>
                                    <x-input-label for="gender" value="Jenis Kelamin" class="font-bold text-gray-700" />
                                    <select name="gender" required class="w-full border-gray-200 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm mt-2">
                                        <option value="Laki-laki" {{ old('gender') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="Perempuan" {{ old('gender') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="agama" value="Agama" class="font-bold text-gray-700" />
                                    <select name="agama" required class="w-full border-gray-200 focus:border-blue-500 focus:ring-blue-500 rounded-xl shadow-sm mt-2">
                                        <option value="Islam" {{ old('agama') == 'Islam' ? 'selected' : '' }}>Islam</option>
                                        <option value="Kristen" {{ old('agama') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                        <option value="Katolik" {{ old('agama') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                        <option value="Hindu" {{ old('agama') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                        <option value="Budha" {{ old('agama') == 'Budha' ? 'selected' : '' }}>Budha</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="email" value="Alamat Email" class="font-bold text-gray-700" />
                                    <x-text-input name="email" type="email" class="mt-2 block w-full border-gray-200" :value="old('email')" required placeholder="Wajib diisi" />
                                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="nohp_siswa" value="No. WhatsApp Siswa" class="font-bold text-gray-700" />
                                    <x-text-input name="nohp_siswa" type="text" class="mt-2 block w-full border-gray-200" :value="old('nohp_siswa')" required placeholder="628..." />
                                    <x-input-error :messages="$errors->get('nohp_siswa')" class="mt-1" />
                                </div>
                                <div class="md:col-span-4">
                                    <x-input-label for="alamat" value="Alamat Domisili Lengkap" class="font-bold text-gray-700" />
                                    <textarea name="alamat" required class="w-full border-gray-200 focus:border-blue-500 focus:ring-blue-500 rounded-2xl shadow-sm mt-2" rows="3" placeholder="Jl. Nama Jalan, No, RT/RW, Desa, Kecamatan">{{ old('alamat') }}</textarea>
                                    <x-input-error :messages="$errors->get('alamat')" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <div class="h-px bg-gray-100 w-full mb-12 shadow-inner"></div>

                        <div class="mb-5">
                            <h3 class="text-xl font-black text-emerald-600 mb-8 flex items-center tracking-tight">
                                <span class="bg-emerald-600 text-white p-2 rounded-xl mr-4 shadow-lg shadow-emerald-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </span> 
                                III. DATA ORANG TUA / WALI
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                <div>
                                    <x-input-label for="nama_ayah" value="Nama Lengkap Ayah" class="font-bold text-gray-700" />
                                    <x-text-input name="nama_ayah" type="text" class="mt-2 block w-full border-gray-200" :value="old('nama_ayah')" required />
                                    <x-input-error :messages="$errors->get('nama_ayah')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="nama_ibu" value="Nama Lengkap Ibu" class="font-bold text-gray-700" />
                                    <x-text-input name="nama_ibu" type="text" class="mt-2 block w-full border-gray-200" :value="old('nama_ibu')" required />
                                    <x-input-error :messages="$errors->get('nama_ibu')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="nohp_ortu" value="WhatsApp Orang Tua (Gateway)" class="font-bold text-orange-700" />
                                    <x-text-input name="nohp_ortu" type="text" class="mt-2 block w-full border-orange-200 bg-orange-50/20" :value="old('nohp_ortu')" required placeholder="628..." />
                                    <p class="text-[10px] text-orange-600 mt-2 font-semibold">*Nomor ini akan menerima log presensi ananda.</p>
                                    <x-input-error :messages="$errors->get('nohp_ortu')" class="mt-1" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50/50 border-t border-gray-100 px-10 py-8 flex justify-end items-center gap-6">
                        <a href="{{ route('admin.siswa.index') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors">
                            BATALKAN PENDAFTARAN
                        </a>
                        <button type="submit" class="px-12 py-4 bg-orange-600 text-white font-black rounded-2xl shadow-xl shadow-orange-200 hover:bg-orange-700 hover:-translate-y-1 transition-all duration-200 uppercase tracking-widest text-xs">
                            Simpan & Daftarkan Siswa
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>