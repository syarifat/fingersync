<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Edit Data Kelas: ') . $kelas->nama }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.kelas.update', $kelas->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="bg-white shadow-2xl rounded-[2rem] overflow-hidden border border-gray-100">
                    <div class="p-10">
                        <h3 class="text-xl font-black text-orange-600 mb-8 flex items-center tracking-tight uppercase">
                            <span class="bg-orange-600 text-white p-2 rounded-xl mr-4 shadow-lg shadow-orange-200">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </span> 
                            Perbarui Informasi Kelas
                        </h3>

                        <div class="space-y-6">
                            <div>
                                <x-input-label for="nama" value="Nama Kelas" class="font-bold text-gray-700" />
                                <x-text-input id="nama" name="nama" type="text" 
                                    class="mt-2 block w-full border-gray-200 focus:ring-orange-500 shadow-sm" 
                                    :value="old('nama', $kelas->nama)" required />
                                <x-input-error :messages="$errors->get('nama')" class="mt-1" />
                            </div>

                            <div>
                                <x-input-label for="id_jurusan" value="Program Keahlian (Jurusan)" class="font-bold text-gray-700" />
                                <select name="id_jurusan" required 
                                    class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm mt-2 transition-all">
                                    <option value="">-- Pilih Jurusan --</option>
                                    @foreach($jurusan as $j)
                                        <option value="{{ $j->id }}" 
                                            {{ old('id_jurusan', $kelas->id_jurusan) == $j->id ? 'selected' : '' }}>
                                            {{ $j->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('id_jurusan')" class="mt-1" />
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50/50 border-t border-gray-100 px-10 py-8 flex justify-end items-center gap-6">
                        <a href="{{ route('admin.kelas.index') }}" 
                           class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors uppercase tracking-widest">
                            Batal
                        </a>
                        <button type="submit" 
                            class="px-12 py-4 bg-orange-600 text-white font-black rounded-2xl shadow-xl shadow-orange-200 hover:bg-orange-700 hover:-translate-y-1 transition-all duration-200 uppercase tracking-widest text-xs">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>