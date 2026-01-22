<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">Edit Penempatan Siswa</h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.rombel-kelas.update', $rombel->id) }}" method="POST">
                @csrf @method('PUT')
                
                <div class="bg-white shadow-2xl rounded-[2rem] overflow-hidden border border-gray-100 p-10">
                    
                    <div class="mb-8 border-b border-gray-100 pb-4">
                        <h3 class="text-lg font-black text-orange-600 uppercase tracking-wide">Edit: {{ $rombel->siswa->nama ?? 'Siswa' }}</h3>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <x-input-label value="Nama Siswa" class="font-bold text-gray-700" />
                            <x-text-input type="text" class="mt-2 block w-full bg-gray-100 border-gray-200 rounded-xl" :value="$rombel->siswa->nama ?? '-'" disabled />
                        </div>

                        <div>
                            <x-input-label for="id_kelas" value="Pindah ke Kelas" class="font-bold text-gray-700" />
                            <select name="id_kelas" class="w-full border-gray-200 rounded-xl mt-2 focus:ring-orange-500" required>
                                @foreach($kelas as $k)
                                    <option value="{{ $k->id }}" {{ $rombel->id_kelas == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="id_guru_wali_kelas" value="Wali Kelas" class="font-bold text-gray-700" />
                            <select name="id_guru_wali_kelas" class="w-full border-gray-200 rounded-xl mt-2 focus:ring-orange-500" required>
                                @foreach($guru as $g)
                                    <option value="{{ $g->id }}" {{ $rombel->id_guru_wali_kelas == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-input-label for="id_guru_bk" value="Guru BK" class="font-bold text-gray-700" />
                            <select name="id_guru_bk" class="w-full border-gray-200 rounded-xl mt-2 focus:ring-orange-500" required>
                                @foreach($guru as $g)
                                    <option value="{{ $g->id }}" {{ $rombel->id_guru_bk == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 border-t border-gray-100 pt-6 mt-8">
                        <a href="{{ route('admin.rombel-kelas.index') }}" class="px-6 py-3 text-gray-500 font-bold hover:text-gray-700 uppercase tracking-widest text-xs">BATAL</a>
                        <button type="submit" class="px-10 py-3 bg-orange-600 text-white font-black rounded-xl shadow-lg hover:bg-orange-700 uppercase tracking-widest text-xs">UPDATE</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>