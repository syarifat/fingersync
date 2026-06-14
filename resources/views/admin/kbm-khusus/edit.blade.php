<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Edit Kondisi Khusus KBM Guru') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-orange-600 uppercase tracking-tighter">Edit Form Kondisi Khusus</h3>
                        <p class="text-sm text-gray-500">Ubah kondisi khusus guru.</p>
                    </div>

                    @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl text-red-700 text-sm font-bold shadow-sm">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('admin.kbm-khusus.update', $kbmKhusus->id) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label for="id_rombel_jadwal_pelajaran" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Pilih Jadwal Pelajaran</label>
                            <select name="id_rombel_jadwal_pelajaran" id="id_rombel_jadwal_pelajaran" required class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4">
                                @foreach($jadwals as $jadwal)
                                    <option value="{{ $jadwal->id }}" {{ old('id_rombel_jadwal_pelajaran', $kbmKhusus->id_rombel_jadwal_pelajaran) == $jadwal->id ? 'selected' : '' }}>
                                        [{{ $jadwal->hari }}] {{ $jadwal->rombelMapel->kelas->nama }} - {{ $jadwal->rombelMapel->mataPelajaran->nama }} (Guru: {{ $jadwal->rombelMapel->guru->nama }}) [{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="tanggal" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Tanggal Berhalangan</label>
                                <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', $kbmKhusus->tanggal) }}" required class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4">
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Status Guru</label>
                                <select name="status" id="status" required class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4" onchange="toggleGuruPengganti()">
                                    <option value="izin" {{ old('status', $kbmKhusus->status) == 'izin' ? 'selected' : '' }}>Izin (KBM Mandiri)</option>
                                    <option value="absen" {{ old('status', $kbmKhusus->status) == 'absen' ? 'selected' : '' }}>Absen/Mangkir (KBM Mandiri)</option>
                                    <option value="diganti" {{ old('status', $kbmKhusus->status) == 'diganti' ? 'selected' : '' }}>Digantikan (Terdapat Guru Pengganti)</option>
                                </select>
                            </div>
                        </div>

                        <div id="guru_pengganti_section" class="hidden">
                            <label for="id_guru_pengganti" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Guru Pengganti</label>
                            <select name="id_guru_pengganti" id="id_guru_pengganti" class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4">
                                <option value="">-- Pilih Guru Pengganti --</option>
                                @foreach($gurus as $guru)
                                    <option value="{{ $guru->id }}" {{ old('id_guru_pengganti', $kbmKhusus->id_guru_pengganti) == $guru->id ? 'selected' : '' }}>
                                        {{ $guru->nama }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="keterangan" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Tugas / Keterangan (Opsional)</label>
                            <textarea name="keterangan" id="keterangan" rows="3" placeholder="Contoh tugas: Mengerjakan LKS halaman 40" class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4">{{ old('keterangan', $kbmKhusus->keterangan) }}</textarea>
                        </div>

                        <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">
                            <a href="{{ route('admin.kbm-khusus.index') }}" class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl text-xs font-bold uppercase tracking-widest transition-all">
                                Batal
                            </a>
                            <button type="submit" class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-2xl font-bold text-xs uppercase tracking-widest transition-all shadow-lg shadow-orange-100">
                                Perbarui Kondisi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleGuruPengganti() {
            var status = document.getElementById('status').value;
            var section = document.getElementById('guru_pengganti_section');
            if (status === 'diganti') {
                section.classList.remove('hidden');
            } else {
                section.classList.add('hidden');
                document.getElementById('id_guru_pengganti').value = '';
            }
        }
        // Run on load
        document.addEventListener('DOMContentLoaded', toggleGuruPengganti);
    </script>
</x-app-layout>
