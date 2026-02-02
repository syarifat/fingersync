<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">Edit Jadwal</h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">

            {{-- Alert Error Global --}}
            @if ($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Gagal Update!</strong>
                <span class="block sm:inline">Cek inputan di bawah, ada bentrok jadwal.</span>
            </div>
            @endif

            <form action="{{ route('admin.rombel-jadwal.update', $jadwal->id) }}" method="POST">
                @csrf @method('PUT')

                <div class="bg-white shadow-2xl rounded-[2rem] overflow-hidden border border-gray-100 p-10">

                    <div class="mb-8 border-b border-gray-100 pb-4">
                        <h3 class="text-lg font-black text-orange-600 uppercase tracking-wide">Edit Waktu & Tempat</h3>
                    </div>

                    <div class="space-y-6">
                        {{-- 1. PILIH KELAS & MAPEL --}}
                        <div>
                            <x-input-label for="id_rombel_mata_pelajaran" value="Pilih Kelas & Mata Pelajaran" class="font-bold text-gray-700" />
                            <select name="id_rombel_mata_pelajaran" class="w-full rounded-xl mt-2 {{ $errors->has('id_rombel_mata_pelajaran') ? 'border-red-500 text-red-900' : 'border-gray-200' }}" required>
                                @foreach($rombelMapel as $rm)
                                {{-- Gunakan old() dulu, jika tidak ada baru gunakan data database ($jadwal) --}}
                                <option value="{{ $rm->id }}" {{ old('id_rombel_mata_pelajaran', $jadwal->id_rombel_mata_pelajaran) == $rm->id ? 'selected' : '' }}>
                                    [{{ $rm->kelas->nama ?? '?' }}] {{ $rm->mataPelajaran->nama ?? '?' }} - {{ $rm->guru->nama ?? '?' }}
                                </option>
                                @endforeach
                            </select>
                            @error('id_rombel_mata_pelajaran')
                            <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- 2. HARI --}}
                        <div>
                            <x-input-label for="hari" value="Hari" class="font-bold text-gray-700" />
                            <select name="hari" class="w-full rounded-xl mt-2 {{ $errors->has('hari') ? 'border-red-500' : 'border-gray-200' }}" required>
                                @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $h)
                                <option value="{{ $h }}" {{ old('hari', $jadwal->hari) == $h ? 'selected' : '' }}>{{ $h }}</option>
                                @endforeach
                            </select>
                            @error('hari')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- 3. JAM MULAI & SELESAI --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="jam_mulai" value="Jam Mulai" class="font-bold text-gray-700" />
                                <x-text-input type="time" name="jam_mulai" class="mt-2 block w-full rounded-xl {{ $errors->has('jam_mulai') ? 'border-red-500' : 'border-gray-200' }}" :value="old('jam_mulai', $jadwal->jam_mulai)" required />
                                @error('jam_mulai')
                                <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <x-input-label for="jam_selesai" value="Jam Selesai" class="font-bold text-gray-700" />
                                <x-text-input type="time" name="jam_selesai" class="mt-2 block w-full rounded-xl {{ $errors->has('jam_selesai') ? 'border-red-500' : 'border-gray-200' }}" :value="old('jam_selesai', $jadwal->jam_selesai)" required />
                                @error('jam_selesai')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- 4. RUANGAN --}}
                        <div>
                            <x-input-label for="id_ruangan" value="Ruangan" class="font-bold text-gray-700" />
                            <select name="id_ruangan" class="w-full rounded-xl mt-2 {{ $errors->has('id_ruangan') ? 'border-red-500 text-red-900' : 'border-gray-200' }}" required>
                                @foreach($ruangan as $r)
                                <option value="{{ $r->id }}" {{ old('id_ruangan', $jadwal->id_ruangan) == $r->id ? 'selected' : '' }}>{{ $r->nama_ruangan }}</option>
                                @endforeach
                            </select>
                            @error('id_ruangan')
                            <p class="text-red-500 text-xs mt-1 font-bold">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 border-t border-gray-100 pt-6 mt-8">
                        <a href="{{ route('admin.rombel-jadwal.index') }}" class="px-6 py-3 text-gray-500 font-bold hover:text-gray-700 uppercase tracking-widest text-xs">BATAL</a>
                        <button type="submit" class="px-10 py-3 bg-orange-600 text-white font-black rounded-xl shadow-lg hover:bg-orange-700 uppercase tracking-widest text-xs">UPDATE JADWAL</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>