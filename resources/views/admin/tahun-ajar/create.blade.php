<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">Tambah Tahun Ajar</h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.tahun-ajar.store') }}" method="POST">
                @csrf
                <div class="bg-white shadow-2xl rounded-[2rem] overflow-hidden border border-gray-100 p-10">
                    
                    <div class="mb-8 border-b border-gray-100 pb-4">
                        <h3 class="text-lg font-black text-orange-600 uppercase tracking-wide">Periode Baru</h3>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <x-input-label for="tahun" value="Tahun Pelajaran" class="font-bold text-gray-700" />
                            <x-text-input id="tahun" name="tahun" type="text" class="mt-2 block w-full focus:ring-orange-500 border-gray-200 rounded-xl" placeholder="Contoh: 2025/2026" required autofocus />
                            <x-input-error :messages="$errors->get('tahun')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="semester" value="Semester" class="font-bold text-gray-700" />
                            <select name="semester" class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm mt-2" required>
                                <option value="Ganjil">Ganjil</option>
                                <option value="Genap">Genap</option>
                            </select>
                        </div>

                        <div class="p-4 bg-orange-50 rounded-xl border border-orange-100">
                            <label class="inline-flex items-center cursor-pointer w-full">
                                <input type="checkbox" name="status_aktif" value="1" class="rounded border-gray-300 text-orange-600 shadow-sm focus:ring-orange-500 h-5 w-5">
                                <span class="ml-3 text-sm font-bold text-gray-700">Set sebagai Tahun Ajar AKTIF</span>
                            </label>
                            <p class="ml-8 text-xs text-orange-600 mt-1">*Jika dicentang, tahun ajar lain akan otomatis dinonaktifkan.</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 border-t border-gray-100 pt-6 mt-8">
                        <a href="{{ route('admin.tahun-ajar.index') }}" class="px-6 py-3 text-gray-500 font-bold hover:text-gray-700 uppercase tracking-widest text-xs">BATAL</a>
                        <button type="submit" class="px-10 py-3 bg-orange-600 text-white font-black rounded-xl shadow-lg hover:bg-orange-700 uppercase tracking-widest text-xs">SIMPAN DATA</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>