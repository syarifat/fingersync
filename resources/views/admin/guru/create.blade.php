<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">Registrasi Guru Baru</h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.guru.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white shadow-2xl rounded-[2rem] overflow-hidden border border-gray-100 p-10">
                    
                    <div class="mb-10">
                        <h3 class="text-lg font-black text-orange-600 mb-6 flex items-center tracking-tight">
                            <span class="bg-orange-100 p-2 rounded-lg mr-3 shadow-sm">🔐</span> AKUN LOGIN & KEAMANAN
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="username" value="Username Login" class="font-bold text-gray-700" />
                                <x-text-input name="username" class="mt-2 w-full bg-orange-50/30 border-orange-200 focus:ring-orange-500" required :value="old('username')" placeholder="NIP / NIDN (Unik)" />
                                <x-input-error :messages="$errors->get('username')" class="mt-1" />
                            </div>
                            
                            <div>
                                <x-input-label value="Password Akun" class="font-bold text-gray-400" />
                                <div class="mt-2 w-full bg-gray-50 border border-gray-200 rounded-xl p-3 text-sm text-gray-500 italic flex items-start gap-2 shadow-inner">
                                    <svg class="w-5 h-5 text-orange-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p>Password akan dibuat sendiri oleh Guru melalui menu <strong>"Aktivasi Akun"</strong> pada halaman login.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="h-px bg-gray-100 w-full mb-10 shadow-sm"></div>

                    <div class="mb-8">
                        <h3 class="text-lg font-black text-blue-600 mb-6 flex items-center tracking-tight">
                            <span class="bg-blue-100 p-2 rounded-lg mr-3 shadow-sm">👤</span> BIODATA LENGKAP
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="nidn" value="NIDN / NIP" class="font-bold text-gray-700" />
                                <x-text-input name="nidn" type="number" class="mt-2 w-full" required :value="old('nidn')" />
                                <x-input-error :messages="$errors->get('nidn')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="nama" value="Nama Lengkap & Gelar" class="font-bold text-gray-700" />
                                <x-text-input name="nama" type="text" class="mt-2 w-full" required :value="old('nama')" />
                                <x-input-error :messages="$errors->get('nama')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="gender" value="Jenis Kelamin" class="font-bold text-gray-700" />
                                <select name="gender" class="w-full border-gray-200 rounded-xl mt-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm">
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="nohp" value="Nomor HP (WhatsApp)" class="font-bold text-gray-700" />
                                <x-text-input name="nohp" type="number" class="mt-2 w-full" required :value="old('nohp')" placeholder="08..." />
                                <x-input-error :messages="$errors->get('nohp')" class="mt-1" />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="alamat" value="Alamat Domisili" class="font-bold text-gray-700" />
                                <textarea name="alamat" class="w-full border-gray-200 rounded-xl mt-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm" rows="2">{{ old('alamat') }}</textarea>
                                <x-input-error :messages="$errors->get('alamat')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="image" value="Foto Profil" class="font-bold text-gray-700" />
                                <input type="file" name="image" class="mt-2 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" />
                                <x-input-error :messages="$errors->get('image')" class="mt-1" />
                            </div>
                            <div class="flex items-center mt-8 p-4 bg-orange-50 rounded-xl border border-orange-100">
                                <label class="inline-flex items-center cursor-pointer w-full">
                                    <input type="checkbox" name="is_bk" value="1" class="rounded border-gray-300 text-orange-600 shadow-sm focus:ring-orange-500 h-5 w-5">
                                    <span class="ml-3 text-sm font-bold text-gray-700">Tandai sebagai Guru Bimbingan Konseling (BK)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 border-t border-gray-100 pt-6">
                        <a href="{{ route('admin.guru.index') }}" class="px-6 py-3 text-gray-500 font-bold hover:text-gray-700 uppercase tracking-widest text-xs flex items-center">BATAL</a>
                        <button type="submit" class="px-10 py-3 bg-orange-600 text-white font-black rounded-xl shadow-xl shadow-orange-200 hover:bg-orange-700 uppercase tracking-widest text-xs transform hover:-translate-y-0.5 transition-all">SIMPAN DATA</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>