<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Tambah Kondisi Khusus KBM Guru') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <div class="mb-8">
                        <h3 class="text-xl font-bold text-orange-600 uppercase tracking-tighter">Form Kondisi Khusus</h3>
                        <p class="text-sm text-gray-500">Pilih tipe izin (Satu Jadwal atau Banyak Jadwal/Rentang Tanggal) KBM reguler guru yang terganggu dan tentukan status tugas/liburnya.</p>
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

                    <form action="{{ route('admin.kbm-khusus.store') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        {{-- Tipe Input --}}
                        <div>
                            <label for="input_type" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Tipe Izin</label>
                            <select name="input_type" id="input_type" onchange="toggleInputType()" class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4">
                                <option value="single" {{ old('input_type') == 'single' ? 'selected' : '' }}>Izin Singkat (Satu Jadwal & Satu Tanggal)</option>
                                <option value="bulk" {{ old('input_type') == 'bulk' ? 'selected' : '' }}>Izin Panjang / Banyak (Satu Guru & Rentang Tanggal)</option>
                            </select>
                        </div>

                        {{-- SINGLE MODE FIELDS --}}
                        <div id="single_schedule_container" class="space-y-6">
                            <div>
                                <label for="id_rombel_jadwal_pelajaran" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Pilih Jadwal Pelajaran</label>
                                <select name="id_rombel_jadwal_pelajaran" id="id_rombel_jadwal_pelajaran" required class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4">
                                    <option value="">-- Pilih Jadwal KBM Terpengaruh --</option>
                                    @foreach($jadwals as $jadwal)
                                        <option value="{{ $jadwal->id }}" data-hari="{{ $jadwal->hari }}" {{ old('id_rombel_jadwal_pelajaran') == $jadwal->id ? 'selected' : '' }}>
                                            [{{ $jadwal->hari }}] {{ $jadwal->rombelMapel->kelas->nama }} - {{ $jadwal->rombelMapel->mataPelajaran->nama }} (Guru: {{ $jadwal->rombelMapel->guru->nama }}) [{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}]
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div id="single_date_container">
                            <label for="tanggal" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Tanggal Berhalangan</label>
                            <input type="date" name="tanggal" id="tanggal" value="{{ old('tanggal') }}" required class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4">
                        </div>

                        {{-- BULK MODE FIELDS --}}
                        <div id="bulk_guru_container" class="hidden">
                            <label for="id_guru" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Pilih Guru</label>
                            <select name="id_guru" id="id_guru" onchange="filterSchedulesByGuru()" class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4">
                                <option value="">-- Pilih Guru Terlebih Dahulu --</option>
                                @foreach($gurus as $g)
                                    <option value="{{ $g->id }}">{{ $g->nama }} (NIDN: {{ $g->nidn }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="bulk_schedule_container" class="hidden">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2 font-bold text-orange-600">Pilih Jadwal yang Diliburkan (Centang Semua yang Sesuai)</label>
                            <div id="schedule_checkboxes" class="space-y-2 max-h-56 overflow-y-auto bg-gray-50 p-4 rounded-xl border border-gray-200">
                                <p class="text-xs text-gray-400 italic">Pilih guru terlebih dahulu untuk memuat daftar jadwal pelajaran.</p>
                            </div>
                        </div>

                        <div id="bulk_date_container" class="hidden grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="tanggal_mulai" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Tanggal Mulai Izin</label>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4">
                            </div>
                            <div>
                                <label for="tanggal_selesai" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Tanggal Selesai Izin</label>
                                <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4">
                            </div>
                        </div>

                        {{-- STATUS / TUGAS --}}
                        <div class="bg-gray-50 p-5 rounded-2xl border border-gray-200">
                            <div class="flex items-start gap-3">
                                <div class="flex items-center h-5 mt-1">
                                    <input type="checkbox" name="berikan_tugas" id="berikan_tugas" value="1" 
                                        {{ old('berikan_tugas', '1') == '1' ? 'checked' : '' }}
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
                        
                        <input type="hidden" name="status" id="status" value="izin_tugas">

                        <div id="keterangan_div">
                            <label for="keterangan" class="block text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Tugas / Keterangan (Opsional)</label>
                            <textarea name="keterangan" id="keterangan" rows="3" placeholder="Contoh tugas: Mengerjakan LKS halaman 40" class="w-full border-gray-200 focus:border-orange-500 focus:ring-orange-500 rounded-xl shadow-sm text-sm py-2.5 px-4">{{ old('keterangan') }}</textarea>
                        </div>

                        <div class="flex justify-end gap-3 border-t border-gray-100 pt-6">
                            <a href="{{ route('admin.kbm-khusus.index') }}" class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl text-xs font-bold uppercase tracking-widest transition-all">
                                Batal
                            </a>
                            <button type="submit" class="px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white rounded-2xl font-bold text-xs uppercase tracking-widest transition-all shadow-lg shadow-orange-100">
                                Simpan Kondisi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const allSchedules = @json($jadwals->map(fn($j) => [
            'id' => $j->id,
            'hari' => $j->hari,
            'id_guru' => $j->rombelMapel->guru->id ?? null,
            'nama_guru' => $j->rombelMapel->guru->nama ?? '',
            'nama_kelas' => $j->rombelMapel->kelas->nama ?? '',
            'nama_mapel' => $j->rombelMapel->mataPelajaran->nama ?? '',
            'jam_mulai' => substr($j->jam_mulai, 0, 5),
            'jam_selesai' => substr($j->jam_selesai, 0, 5)
        ]));

        function toggleInputType() {
            const type = document.getElementById('input_type').value;
            const singleScheduleCont = document.getElementById('single_schedule_container');
            const singleDateCont = document.getElementById('single_date_container');
            const bulkGuruCont = document.getElementById('bulk_guru_container');
            const bulkScheduleCont = document.getElementById('bulk_schedule_container');
            const bulkDateCont = document.getElementById('bulk_date_container');
            
            // Reset required attributes
            const scheduleSelect = document.getElementById('id_rombel_jadwal_pelajaran');
            const tanggalInput = document.getElementById('tanggal');
            const tanggalMulaiInput = document.getElementById('tanggal_mulai');
            const tanggalSelesaiInput = document.getElementById('tanggal_selesai');

            if (type === 'bulk') {
                singleScheduleCont.classList.add('hidden');
                singleDateCont.classList.add('hidden');
                scheduleSelect.removeAttribute('required');
                tanggalInput.removeAttribute('required');
                
                bulkGuruCont.classList.remove('hidden');
                bulkScheduleCont.classList.remove('hidden');
                bulkDateCont.classList.remove('hidden');
                tanggalMulaiInput.setAttribute('required', 'required');
                tanggalSelesaiInput.setAttribute('required', 'required');
            } else {
                singleScheduleCont.classList.remove('hidden');
                singleDateCont.classList.remove('hidden');
                scheduleSelect.setAttribute('required', 'required');
                tanggalInput.setAttribute('required', 'required');
                
                bulkGuruCont.classList.add('hidden');
                bulkScheduleCont.classList.add('hidden');
                bulkDateCont.classList.add('hidden');
                tanggalMulaiInput.removeAttribute('required');
                tanggalSelesaiInput.removeAttribute('required');
            }
        }

        function filterSchedulesByGuru() {
            const guruId = document.getElementById('id_guru').value;
            const container = document.getElementById('schedule_checkboxes');
            container.innerHTML = '';
            
            if (!guruId) {
                container.innerHTML = '<p class="text-xs text-gray-400 italic">Pilih guru terlebih dahulu untuk memuat daftar jadwal pelajaran.</p>';
                return;
            }
            
            const filtered = allSchedules.filter(j => j.id_guru == guruId);
            
            if (filtered.length === 0) {
                container.innerHTML = '<p class="text-xs text-rose-500 font-bold italic">Guru ini tidak memiliki jadwal pelajaran aktif.</p>';
                return;
            }
            
            filtered.forEach(j => {
                const div = document.createElement('div');
                div.className = 'flex items-center gap-3 py-2 border-b border-gray-100 last:border-0 hover:bg-gray-100/50 px-2 rounded-lg transition-colors';
                div.innerHTML = `
                    <input type="checkbox" name="schedules[]" value="${j.id}" id="sched_${j.id}" checked class="w-4 h-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500 cursor-pointer">
                    <label for="sched_${j.id}" class="text-sm text-gray-700 cursor-pointer flex-1">
                        <span class="inline-block bg-orange-100 text-orange-800 text-[10px] font-bold px-2 py-0.5 rounded-full mr-2">${j.hari}</span>
                        <strong>${j.nama_kelas}</strong> - ${j.nama_mapel} <span class="text-xs text-gray-400">(${j.jam_mulai} - ${j.jam_selesai})</span>
                    </label>
                `;
                container.appendChild(div);
            });
        }

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
            toggleInputType();
            toggleTugasSection();

            const scheduleSelect = document.getElementById('id_rombel_jadwal_pelajaran');
            const dateInput = document.getElementById('tanggal');

            const hariToNum = {
                'Minggu': 0, 'Senin': 1, 'Selasa': 2, 'Rabu': 3, 'Kamis': 4, 'Jumat': 5, 'Sabtu': 6
            };

            function validateDate() {
                if (document.getElementById('input_type').value !== 'single') return;
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
