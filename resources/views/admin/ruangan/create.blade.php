<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            Tambah Ruangan Baru
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.ruangan.store') }}" method="POST">
                @csrf
                <div class="bg-white shadow-2xl rounded-[2rem] overflow-hidden border border-gray-100">
                    <div class="p-10 space-y-6">
                        
                        <div class="border-b border-gray-100 pb-4 mb-4">
                            <h3 class="text-lg font-black text-orange-600 uppercase tracking-wide flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                Informasi Ruangan
                            </h3>
                        </div>

                        <div>
                            <x-input-label for="nama_ruangan" value="Nama Ruangan" class="font-bold text-gray-700" />
                            <x-text-input id="nama_ruangan" name="nama_ruangan" type="text" class="mt-2 block w-full border-gray-200 focus:ring-orange-500 rounded-xl" placeholder="Contoh: Laboratorium RPL 1" required autofocus />
                            <x-input-error :messages="$errors->get('nama_ruangan')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="keterangan" value="Keterangan / Lokasi (Opsional)" class="font-bold text-gray-700" />
                            <textarea name="keterangan" id="keterangan" rows="3" class="mt-2 block w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm" placeholder="Contoh: Gedung A, Lantai 2, Sebelah Kantor Guru"></textarea>
                            <x-input-error :messages="$errors->get('keterangan')" class="mt-1" />
                        </div>
                    </div>

                    <div class="bg-gray-50 px-10 py-6 flex justify-end items-center gap-4">
                        <a href="{{ route('admin.ruangan.index') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors uppercase tracking-widest">
                            Batal
                        </a>
                        <button type="submit" class="px-8 py-3 bg-orange-600 text-white font-black rounded-xl shadow-lg hover:bg-orange-700 transition-all uppercase tracking-widest text-xs transform hover:-translate-y-0.5">
                            Simpan Ruangan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>