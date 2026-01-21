<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">Edit Data Guru</h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.guru.update', $guru->id) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                
                <div class="bg-white shadow-2xl rounded-[2rem] overflow-hidden border border-gray-100 p-10">
                    
                    <div class="flex gap-8 mb-10 items-center">
                        <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-orange-50 shrink-0 shadow-lg relative group">
                            @if($guru->image)
                                <img src="{{ asset('img/guru/'.$guru->image) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400 font-bold">No IMG</div>
                            @endif
                        </div>
                        <div>
                            <h3 class="text-3xl font-black text-gray-800 tracking-tight">{{ $guru->nama }}</h3>
                            <p class="text-orange-600 font-bold text-lg">{{ $guru->nidn }}</p>
                            <div class="mt-4">
                                <label class="cursor-pointer bg-orange-50 text-orange-600 px-5 py-2 rounded-xl text-xs font-bold hover:bg-orange-600 hover:text-white transition-all shadow-sm uppercase tracking-wide">
                                    Ganti Foto Profil
                                    <input type="file" name="image" class="hidden">
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        
                        <div class="space-y-6">
                            <div>
                                <h4 class="font-black text-gray-400 uppercase tracking-widest text-xs mb-4 border-b pb-2">Informasi Akun</h4>
                                <x-input-label value="Username Login" class="font-bold text-gray-700" />
                                <x-text-input name="username" class="w-full mt-2 bg-gray-50" :value="old('username', $guru->username)" required />
                            </div>
                            
                            <div>
                                <h4 class="font-black text-gray-400 uppercase tracking-widest text-xs mb-4 border-b pb-2 pt-4">Status & Jabatan</h4>
                                <x-input-label value="Status Keaktifan" class="font-bold text-gray-700" />
                                <select name="status" class="w-full border-gray-200 rounded-xl mt-2 bg-gray-50">
                                    <option value="Aktif" {{ $guru->status == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="Non-Aktif" {{ $guru->status == 'Non-Aktif' ? 'selected' : '' }}>Non-Aktif</option>
                                </select>
                            </div>

                             <div class="flex items-center p-4 bg-orange-50 rounded-xl border border-orange-100">
                                <label class="inline-flex items-center cursor-pointer w-full">
                                    <input type="checkbox" name="is_bk" value="1" {{ $guru->is_bk ? 'checked' : '' }} class="rounded border-gray-300 text-orange-600 shadow-sm focus:ring-orange-500 h-5 w-5">
                                    <span class="ml-3 text-sm font-bold text-gray-700">Guru Bimbingan Konseling (BK)</span>
                                </label>
                            </div>
                        </div>

                        <div class="space-y-6">
                             <h4 class="font-black text-gray-400 uppercase tracking-widest text-xs mb-4 border-b pb-2">Biodata Pribadi</h4>
                            
                            <div>
                                <x-input-label value="NIDN / NIP" class="font-bold text-gray-700" />
                                <x-text-input name="nidn" class="w-full mt-2" :value="old('nidn', $guru->nidn)" required />
                            </div>
                            <div>
                                <x-input-label value="Nama Lengkap" class="font-bold text-gray-700" />
                                <x-text-input name="nama" class="w-full mt-2" :value="old('nama', $guru->nama)" required />
                            </div>
                            <div>
                                <x-input-label value="No Handphone" class="font-bold text-gray-700" />
                                <x-text-input name="nohp" class="w-full mt-2" :value="old('nohp', $guru->nohp)" required />
                            </div>
                            <div>
                                <x-input-label value="Jenis Kelamin" class="font-bold text-gray-700" />
                                <select name="gender" class="w-full border-gray-200 rounded-xl mt-2">
                                    <option value="Laki-laki" {{ $guru->gender == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="Perempuan" {{ $guru->gender == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label value="Alamat Domisili" class="font-bold text-gray-700" />
                                <textarea name="alamat" class="w-full border-gray-200 rounded-xl mt-2" rows="2">{{ $guru->alamat }}</textarea>
                            </div>
                        </div>

                    </div>

                    <div class="flex justify-end gap-4 border-t border-gray-100 pt-6 mt-8">
                        <a href="{{ route('admin.guru.index') }}" class="px-6 py-3 text-gray-500 font-bold hover:text-gray-700 uppercase tracking-widest text-xs flex items-center">BATAL</a>
                        <button type="submit" class="px-10 py-3 bg-orange-600 text-white font-black rounded-xl shadow-xl shadow-orange-200 hover:bg-orange-700 uppercase tracking-widest text-xs transform hover:-translate-y-0.5 transition-all">SIMPAN PERUBAHAN</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>