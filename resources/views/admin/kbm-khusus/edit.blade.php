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
                        <p class="text-sm text-gray-500">Ubah kondisi khusus guru dan tentukan status tugas/liburnya.</p>
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
                                    <option value="{{ $jadwal->id }}" data-hari="{{ $jadwal->hari }}" {{ old('id_rombel_jadwal_pelajaran', $kbmKhusus->id_rombel_jadwal_pelajaran) == $jadwal->id ? 'selected' : '' }}>
                                        [{{ $jadwal->hari }}] {{ $jadwal->rombelMapel->kelas->nama }} - {{ $jadwal->rombelMapel->mataPelajaran->nama }} (Guru: {{ $jadwal->rombelMapel->guru->nama }}) [{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}]
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="tanggal" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Tanggal Berhalangan</label>
                                <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal', $kbmKhusus->tanggal) }}" required class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4">
                            </div>
                        </div>

                        <div class="bg-gray-50 p-5 rounded-2xl border border-gray-200">
                            <div class="flex items-start gap-3">
                                <div class="flex items-center h-5 mt-1">
                                    <input type="checkbox" name="berikan_tugas" id="berikan_tugas" value="1" 
                                        {{ in_array(old('status', $kbmKhusus->status), ['izin_tugas', 'izin', 'absen', 'diganti']) ? 'checked' : '' }}
                                        class="w-5 h-5 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                        onchange="toggleTugasSection()">
                                </div>
                                <div>
                                    <label for="berikan_tugas" class="block text-sm font-bold text-gray-800">Berikan Tugas (Siswa Tetap Absen)</label>
                                    <span class="block text-xs text-gray-500 mt-1 leading-relaxed">
                                        <strong>Aktif:</strong> Siswa wajib melakukan absen dengan respon mesin "Mengerjakan Tugas yang Diberikan".<br>
                                        <strong>Nonaktif:</strong> Kelas diliburkan (tidak usah absen). Siswa yang absen akan direspon "Jadwal Libur, Guru Sedang Izin".
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <input type="hidden" name="status" id="status" value="{{ old('status', $kbmKhusus->status) }}">

                        <div id="keterangan_div">
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
        function toggleTugasSection() {
            var isChecked = document.getElementById('berikan_tugas').checked;
            var statusInput = document.getElementById('status');
            var keteranganDiv = document.getElementById('keterangan_div');
            
            if (isChecked) {
                statusInput.value = 'izin_tugas';
                keteranganDiv.classList.remove('hidden');
            } else {
                statusInput.value = 'izin_libur';
                keteranganDiv.classList.add('hidden');
                document.getElementById('keterangan').value = '';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            toggleTugasSection();

            const scheduleSelect = document.getElementById('id_rombel_jadwal_pelajaran');
            const dateInput = document.getElementById('tanggal');

            const hariToNum = {
                'Minggu': 0, 'Senin': 1, 'Selasa': 2, 'Rabu': 3, 'Kamis': 4, 'Jumat': 5, 'Sabtu': 6
            };

            function validateDate() {
                if (!scheduleSelect.value || !dateInput.value) return;
                
                const selectedOption = scheduleSelect.options[scheduleSelect.selectedIndex];
                const targetHari = selectedOption.getAttribute('data-hari');
                
                if (!targetHari) return;
                
                const parts = dateInput.value.split('-');
                const dateVal = new Date(parts[0], parts[1] - 1, parts[2]);
                const dayOfWeek = dateVal.getDay();
                
                const targetNum = hariToNum[targetHari];
                if (dayOfWeek !== targetNum) {
                    alert('Tanggal yang dipilih harus bertepatan dengan hari ' + targetHari + '!');
                    dateInput.value = '';
                }
            }

            scheduleSelect.addEventListener('change', validateDate);
            dateInput.addEventListener('change', validateDate);
        });
    </script>
</x-app-layout>
