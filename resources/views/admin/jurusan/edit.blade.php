<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">Edit Jurusan</h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.jurusan.update', $jurusan->id) }}" method="POST">
                @csrf @method('PUT')
                
                <div class="bg-white shadow-2xl rounded-[2rem] overflow-hidden border border-gray-100 p-10">
                    
                    <div class="mb-8 border-b border-gray-100 pb-4">
                        <h3 class="text-lg font-black text-orange-600 uppercase tracking-wide">Edit: {{ $jurusan->nama }}</h3>
                    </div>

                    <div>
                        <x-input-label for="nama" value="Nama Jurusan" class="font-bold text-gray-700" />
                        <x-text-input id="nama" name="nama" type="text" class="mt-2 block w-full bg-gray-50" :value="old('nama', $jurusan->nama)" required />
                        <x-input-error :messages="$errors->get('nama')" class="mt-1" />
                    </div>

                    <div class="flex justify-end gap-4 border-t border-gray-100 pt-6 mt-8">
                        <a href="{{ route('admin.jurusan.index') }}" class="px-6 py-3 text-gray-500 font-bold hover:text-gray-700 uppercase tracking-widest text-xs">BATAL</a>
                        <button type="submit" class="px-10 py-3 bg-orange-600 text-white font-black rounded-xl shadow-lg hover:bg-orange-700 uppercase tracking-widest text-xs transform hover:-translate-y-0.5 transition-all">UPDATE JURUSAN</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>