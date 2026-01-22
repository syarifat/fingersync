<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">Buat Jadwal Baru</h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.rombel-jadwal.store') }}" method="POST">
                @csrf
                <div class="bg-white shadow-2xl rounded-[2rem] overflow-hidden border border-gray-100 p-10">
                    
                    <div class="mb-8 border-b border-gray-100 pb-4 flex justify-between items-center">
                        <h3 class="text-lg font-black text-orange-600 uppercase tracking-wide">Set Waktu & Tempat</h3>
                        <span class="text-xs font-bold bg-orange-100 text-orange-700 px-3 py-1 rounded-full">
                            Tahun: {{ session('tahun_ajar_nama') }}
                        </span>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <x-input-label for="id_rombel_mata_pelajaran" value="Pilih Kelas & Mata Pelajaran" class="font-bold text-gray-700" />
                            <select name="id_rombel_mata_pelajaran" class="w-full border-gray-200 rounded-xl mt-2 focus:ring-orange-500" required>
                                <option value="">-- Pilih Plotting --</option>
                                @foreach($rombelMapel as $rm)
                                    <option value="{{ $rm->id }}">
                                        [{{ $rm->kelas->nama ?? '?' }}] {{ $rm->mataPelajaran->nama ?? '?' }} - {{ $rm->guru->nama ?? '?' }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-gray-400 mt-1">*Data diambil dari menu 'Plotting Guru'. Jika kosong, silakan plotting dulu.</p>
                        </div>

                        <div>
                            <x-input-label for="hari" value="Hari" class="font-bold text-gray-700" />
                            <select name="hari" class="w-full border-gray-200 rounded-xl mt-2 focus:ring-orange-500" required>
                                <option value="">-- Pilih Hari --</option>
                                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h)
                                    <option value="{{ $h }}">{{ $h }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="jam_mulai" value="Jam Mulai" class="font-bold text-gray-700" />
                                <x-text-input type="time" name="jam_mulai" class="mt-2 block w-full border-gray-200 rounded-xl" required />
                            </div>
                            <div>
                                <x-input-label for="jam_selesai" value="Jam Selesai" class="font-bold text-gray-700" />
                                <x-text-input type="time" name="jam_selesai" class="mt-2 block w-full border-gray-200 rounded-xl" required />
                            </div>
                        </div>

                        <div>
                            <x-input-label for="id_ruangan" value="Ruangan" class="font-bold text-gray-700" />
                            <select name="id_ruangan" class="w-full border-gray-200 rounded-xl mt-2 focus:ring-orange-500" required>
                                <option value="">-- Pilih Ruangan --</option>
                                @foreach($ruangan as $r)
                                    <option value="{{ $r->id }}">{{ $r->nama_ruangan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 border-t border-gray-100 pt-6 mt-8">
                        <a href="{{ route('admin.rombel-jadwal.index') }}" class="px-6 py-3 text-gray-500 font-bold hover:text-gray-700 uppercase tracking-widest text-xs">BATAL</a>
                        <button type="submit" class="px-10 py-3 bg-orange-600 text-white font-black rounded-xl shadow-lg hover:bg-orange-700 uppercase tracking-widest text-xs">SIMPAN JADWAL</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>