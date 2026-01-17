<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            Edit: {{ $siswa->nama }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.siswa.update', $siswa->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="bg-white shadow-xl rounded-3xl overflow-hidden">
                    <div class="p-8">
                        <div class="flex flex-col md:flex-row gap-10">
                            <div class="w-full md:w-1/4 flex flex-col items-center">
                                <div class="w-48 h-48 rounded-2xl overflow-hidden border-4 border-orange-100 shadow-inner mb-4">
                                    @if($siswa->image)
                                        <img src="{{ asset('img/siswa/'.$siswa->image) }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400">No Photo</div>
                                    @endif
                                </div>
                                <label class="cursor-pointer bg-gray-100 px-4 py-2 rounded-lg text-xs font-bold text-gray-600 hover:bg-orange-100">
                                    Ganti Foto
                                    <input type="file" name="image" class="hidden">
                                </label>
                            </div>

                            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label value="NIS" />
                                    <x-text-input name="nis" :value="old('nis', $siswa->nis)" class="w-full mt-1" />
                                </div>
                                <div>
                                    <x-input-label value="Nama Lengkap" />
                                    <x-text-input name="nama" :value="old('nama', $siswa->nama)" class="w-full mt-1" />
                                </div>
                                <div>
                                    <x-input-label value="Jurusan" />
                                    <select name="id_jurusan" class="w-full border-gray-300 rounded-xl mt-1">
                                        @foreach($jurusan as $j)
                                            <option value="{{ $j->id }}" {{ $siswa->id_jurusan == $j->id ? 'selected' : '' }}>{{ $j->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label value="Fingerprint ID" />
                                    <x-text-input name="fingerprint_id" :value="old('fingerprint_id', $siswa->fingerprint_id)" class="w-full mt-1" />
                                </div>
                                <div>
                                    <x-input-label value="No. HP Ortu (WhatsApp)" />
                                    <x-text-input name="nohp_ortu" :value="old('nohp_ortu', $siswa->nohp_ortu)" class="w-full mt-1" />
                                </div>
                                <div>
                                    <x-input-label value="Status" />
                                    <select name="status" class="w-full border-gray-300 rounded-xl mt-1">
                                        <option value="Aktif" {{ $siswa->status == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                        <option value="Non-Aktif" {{ $siswa->status == 'Non-Aktif' ? 'selected' : '' }}>Non-Aktif</option>
                                    </select>
                                </div>
                                </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-8 py-6 flex justify-end gap-3">
                        <button type="submit" class="px-10 py-2 bg-orange-600 text-white font-bold rounded-xl shadow-lg hover:bg-orange-700">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>