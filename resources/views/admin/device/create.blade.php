<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">Registrasi Device Baru</h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.device.store') }}" method="POST">
                @csrf
                <div class="bg-white shadow-2xl rounded-[2rem] overflow-hidden border border-gray-100 p-10">
                    
                    <div class="mb-8 border-b border-gray-100 pb-4">
                        <h3 class="text-lg font-black text-orange-600 uppercase tracking-wide">Konfigurasi Hardware</h3>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <x-input-label for="id_device" value="Serial Number (ID)" class="font-bold text-gray-700" />
                            <x-text-input id="id_device" name="id_device" type="text" class="mt-2 block w-full focus:ring-orange-500 border-gray-200 rounded-xl font-mono uppercase" placeholder="Contoh: ESP-RPL-01" required autofocus />
                            <p class="text-xs text-gray-400 mt-1">*Masukkan ID yang sama persis dengan kode pada chip ESP32.</p>
                            <x-input-error :messages="$errors->get('id_device')" class="mt-1" />
                        </div>

                        <div>
                            <x-input-label for="id_ruangan" value="Lokasi Pemasangan" class="font-bold text-gray-700" />
                            <select name="id_ruangan" class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm mt-2" required>
                                <option value="">-- Pilih Ruangan --</option>
                                @foreach($ruangan as $r)
                                    <option value="{{ $r->id }}">{{ $r->nama_ruangan }} ({{ $r->keterangan }})</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('id_ruangan')" class="mt-1" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 border-t border-gray-100 pt-6 mt-8">
                        <a href="{{ route('admin.device.index') }}" class="px-6 py-3 text-gray-500 font-bold hover:text-gray-700 uppercase tracking-widest text-xs">BATAL</a>
                        <button type="submit" class="px-10 py-3 bg-orange-600 text-white font-black rounded-xl shadow-lg hover:bg-orange-700 uppercase tracking-widest text-xs">SIMPAN DEVICE</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>