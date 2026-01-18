<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">Tambah Kelas Baru</h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.kelas.store') }}" method="POST">
                @csrf
                <div class="bg-white shadow-2xl rounded-[2rem] overflow-hidden">
                    <div class="p-10 space-y-6">
                        <div>
                            <x-input-label for="nama" value="Nama Kelas" class="font-bold" />
                            <x-text-input id="nama" name="nama" type="text" class="mt-2 block w-full" placeholder="Contoh: XII RPL 1" required />
                            <x-input-error :messages="$errors->get('nama')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="id_jurusan" value="Pilih Jurusan" class="font-bold" />
                            <select name="id_jurusan" required class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm mt-2">
                                <option value="">-- Pilih Jurusan --</option>
                                @foreach($jurusan as $j)
                                    <option value="{{ $j->id }}">{{ $j->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-10 py-6 flex justify-end gap-4">
                        <a href="{{ route('admin.kelas.index') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 flex items-center">BATAL</a>
                        <button type="submit" class="px-10 py-4 bg-orange-600 text-white font-black rounded-2xl shadow-xl shadow-orange-200 hover:bg-orange-700 transition-all uppercase tracking-widest text-xs">
                            Simpan Kelas
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>