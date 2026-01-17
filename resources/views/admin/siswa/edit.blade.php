<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Edit Profil Siswa: ') . $siswa->nama }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.siswa.update', $siswa->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="bg-white shadow-2xl rounded-[2rem] overflow-hidden border border-gray-100">
                    <div class="p-10">
                        
                        <div class="flex flex-col md:flex-row gap-12 mb-12">
                            <div class="w-full md:w-1/4 flex flex-col items-center">
                                <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4 text-center">Foto Profil Saat Ini</p>
                                <div class="w-56 h-56 rounded-[2.5rem] overflow-hidden border-8 border-orange-50 shadow-2xl mb-6 relative group">
                                    @if($siswa->image)
                                        <img src="{{ asset('img/siswa/'.$siswa->image) }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                    @else
                                        <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400 font-bold">No Photo</div>
                                    @endif
                                </div>
                                <label class="w-full flex flex-col items-center px-4 py-3 bg-white text-orange-600 rounded-2xl shadow-lg border border-orange-100 cursor-pointer hover:bg-orange-600 hover:text-white transition-all duration-300">
                                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    <span class="text-xs font-bold uppercase tracking-tighter">Ganti Foto Baru</span>
                                    <input type="file" name="image" class="hidden" />
                                </label>
                                <x-input-error :messages="$errors->get('image')" class="mt-2" />
                            </div>

                            <div class="flex-1">
                                <h3 class="text-xl font-black text-orange-600 mb-8 flex items-center tracking-tight">
                                    <span class="bg-orange-600 text-white p-2 rounded-xl mr-4 shadow-lg shadow-orange-200">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path></svg>
                                    </span> 
                                    I. IDENTITAS AKADEMIK
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                    <div>
                                        <x-input-label for="nis" value="Nomor Induk Siswa (NIS)" class="font-bold" />
                                        <x-text-input id="nis" name="nis" type="text" class="mt-2 block w-full bg-gray-50" :value="old('nis', $siswa->nis)" required />
                                        <x-input-error :messages="$errors->get('nis')" class="mt-1" />
                                    </div>
                                    <div>
                                        <x-input-label for="nama" value="Nama Lengkap" class="font-bold" />
                                        <x-text-input id="nama" name="nama" type="text" class="mt-2 block w-full" :value="old('nama', $siswa->nama)" required />
                                        <x-input-error :messages="$errors->get('nama')" class="mt-1" />
                                    </div>
                                    <div>
                                        <x-input-label for="id_jurusan" value="Program Keahlian (Jurusan)" class="font-bold" />
                                        <select name="id_jurusan" required class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm mt-2">
                                            @foreach($jurusan as $j)
                                                <option value="{{ $j->id }}" {{ old('id_jurusan', $siswa->id_jurusan) == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="fingerprint_id" value="Biometric Fingerprint ID" class="font-bold text-orange-700" />
                                        <x-text-input id="fingerprint_id" name="fingerprint_id" type="number" class="mt-2 block w-full border-orange-200 bg-orange-50/20" :value="old('fingerprint_id', $siswa->fingerprint_id)" required />
                                        <x-input-error :messages="$errors->get('fingerprint_id')" class="mt-1" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="h-px bg-gray-100 w-full mb-12"></div>

                        <div class="mb-12">
                            <h3 class="text-xl font-black text-blue-600 mb-8 flex items-center tracking-tight">
                                <span class="bg-blue-600 text-white p-2 rounded-xl mr-4 shadow-lg shadow-blue-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                </span> 
                                II. INFORMASI PRIBADI & KONTAK
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                                <div>
                                    <x-input-label for="gender" value="Jenis Kelamin" class="font-bold" />
                                    <select name="gender" required class="w-full border-gray-200 rounded-xl shadow-sm mt-2">
                                        <option value="Laki-laki" {{ old('gender', $siswa->gender) == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="Perempuan" {{ old('gender', $siswa->gender) == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="agama" value="Agama" class="font-bold" />
                                    <select name="agama" required class="w-full border-gray-200 rounded-xl shadow-sm mt-2">
                                        @foreach(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Lainnya'] as $agm)
                                            <option value="{{ $agm }}" {{ old('agama', $siswa->agama) == $agm ? 'selected' : '' }}>{{ $agm }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="email" value="Alamat Email" class="font-bold" />
                                    <x-text-input name="email" type="email" class="mt-2 block w-full" :value="old('email', $siswa->email)" required />
                                </div>
                                <div>
                                    <x-input-label for="nohp_siswa" value="No. WhatsApp Siswa" class="font-bold" />
                                    <x-text-input name="nohp_siswa" type="text" class="mt-2 block w-full" :value="old('nohp_siswa', $siswa->nohp_siswa)" required />
                                </div>
                                <div class="md:col-span-3">
                                    <x-input-label for="alamat" value="Alamat Domisili Lengkap" class="font-bold" />
                                    <textarea name="alamat" required class="w-full border-gray-200 rounded-2xl shadow-sm mt-2" rows="2">{{ old('alamat', $siswa->alamat) }}</textarea>
                                </div>
                                <div>
                                    <x-input-label for="status" value="Status Keaktifan" class="font-bold text-orange-700" />
                                    <select name="status" required class="w-full border-orange-200 bg-orange-50/20 rounded-xl shadow-sm mt-2">
                                        <option value="Aktif" {{ old('status', $siswa->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Non-Aktif" {{ old('status', $siswa->status) == 'Non-Aktif' ? 'selected' : '' }}>Non-Aktif</option>
                                        <option value="Lulus" {{ old('status', $siswa->status) == 'Lulus' ? 'selected' : '' }}>Lulus</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="h-px bg-gray-100 w-full mb-12"></div>

                        <div class="mb-5">
                            <h3 class="text-xl font-black text-emerald-600 mb-8 flex items-center tracking-tight">
                                <span class="bg-emerald-600 text-white p-2 rounded-xl mr-4 shadow-lg shadow-emerald-200">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7"></path></svg>
                                </span> 
                                III. DATA ORANG TUA / WALI
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                <div>
                                    <x-input-label for="nama_ayah" value="Nama Ayah" class="font-bold" />
                                    <x-text-input name="nama_ayah" type="text" class="mt-2 block w-full" :value="old('nama_ayah', $siswa->nama_ayah)" required />
                                </div>
                                <div>
                                    <x-input-label for="nama_ibu" value="Nama Ibu" class="font-bold" />
                                    <x-text-input name="nama_ibu" type="text" class="mt-2 block w-full" :value="old('nama_ibu', $siswa->nama_ibu)" required />
                                </div>
                                <div>
                                    <x-input-label for="nohp_ortu" value="WhatsApp Ortu (Notifikasi)" class="font-bold text-orange-700" />
                                    <x-text-input name="nohp_ortu" type="text" class="mt-2 block w-full border-orange-200 bg-orange-50/20" :value="old('nohp_ortu', $siswa->nohp_ortu)" required />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50/50 border-t border-gray-100 px-10 py-8 flex justify-end items-center gap-6">
                        <a href="{{ route('admin.siswa.index') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors uppercase tracking-widest">
                            Batal
                        </a>
                        <button type="submit" class="px-12 py-4 bg-orange-600 text-white font-black rounded-2xl shadow-xl shadow-orange-200 hover:bg-orange-700 hover:-translate-y-1 transition-all duration-200 uppercase tracking-widest text-xs">
                            Update Data Siswa
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>