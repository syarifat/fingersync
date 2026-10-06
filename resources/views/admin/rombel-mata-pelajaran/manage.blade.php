<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.rombel-mata-pelajaran.index') }}" class="p-2 bg-white rounded-full shadow-sm border border-gray-200 hover:bg-gray-50">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">Plotting Mapel: {{ $kelas->nama }}</h2>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.rombel-mata-pelajaran.storeManage', $kelas->id) }}" method="POST">
                @csrf
                
                <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 p-8">
                    <div class="mb-6 flex justify-between items-center border-b border-gray-100 pb-4">
                        <div>
                            <h3 class="text-lg font-black text-orange-600 uppercase tracking-wide">Daftar Guru Pengajar</h3>
                            <p class="text-sm text-gray-500 font-medium">Tambahkan mata pelajaran dan pilih guru yang mengampu di kelas ini.</p>
                        </div>
                        <button type="button" id="addRowBtn" class="px-4 py-2 bg-gray-800 text-white rounded-xl text-sm font-bold hover:bg-gray-900 transition-colors shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah Baris
                        </button>
                    </div>

                    {{-- Header Kolom --}}
                    <div class="grid grid-cols-12 gap-4 mb-2 px-2 text-xs font-bold text-gray-400 uppercase tracking-widest hidden md:grid">
                        <div class="col-span-5">Mata Pelajaran</div>
                        <div class="col-span-6">Guru Pengampu</div>
                        <div class="col-span-1 text-center">Aksi</div>
                    </div>

                    {{-- Container Baris Dinamis --}}
                    <div id="dynamicContainer" class="space-y-3">
                        {{-- Akan diisi oleh JavaScript saat halaman dimuat (jika ada data existing) --}}
                    </div>

                    {{-- Pesan Jika Kosong --}}
                    <div id="emptyMessage" class="py-12 text-center border-2 border-dashed border-gray-200 rounded-2xl hidden">
                        <p class="text-gray-400 font-bold">Belum ada mata pelajaran yang diplot. Klik tombol "Tambah Baris".</p>
                    </div>

                    <div class="mt-8 flex justify-end gap-4 border-t border-gray-100 pt-6">
                        <a href="{{ route('admin.rombel-mata-pelajaran.index') }}" class="px-6 py-3 font-bold text-gray-500 hover:text-gray-800 uppercase text-sm tracking-widest">Batal</a>
                        <button type="submit" class="px-10 py-3 bg-orange-600 text-white font-black rounded-xl hover:bg-orange-700 shadow-lg uppercase text-sm tracking-widest">
                            Simpan Plotting
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TEMPLATE HIDDEN UNTUK JAVASCRIPT --}}
    <template id="rowTemplate">
        <div class="row-item grid grid-cols-1 md:grid-cols-12 gap-4 bg-gray-50 p-4 md:p-2 rounded-xl border border-gray-200 items-center">
            <div class="md:col-span-5">
                <select name="id_mata_pelajaran[]" class="mapel-select w-full border-gray-300 rounded-lg text-sm focus:ring-orange-500" required onchange="updateSelects()">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    @foreach($mapelList as $m)
                        <option value="{{ $m->id }}">{{ $m->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-6">
                <select name="id_guru[]" class="w-full border-gray-300 rounded-lg text-sm focus:ring-orange-500" required>
                    <option value="">-- Pilih Guru Pengajar --</option>
                    @foreach($guruList as $g)
                        <option value="{{ $g->id }}">{{ $g->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-1 flex justify-end md:justify-center">
                <button type="button" class="remove-btn p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition-colors" onclick="removeRow(this)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-3a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>
        </div>
    </template>

    {{-- SCRIPT JAVASCRIPT AJAIB --}}
    <script>
        const container = document.getElementById('dynamicContainer');
        const template = document.getElementById('rowTemplate');
        const emptyMessage = document.getElementById('emptyMessage');
        
        // Data dari database (jika sudah ada sebelumnya)
        const existingData = @json($plottingSaatIni);

        function checkEmpty() {
            if (container.children.length === 0) {
                emptyMessage.classList.remove('hidden');
            } else {
                emptyMessage.classList.add('hidden');
            }
        }

        function addRow(mapelId = '', guruId = '') {
            const clone = template.content.cloneNode(true);
            const rowDiv = clone.querySelector('.row-item');
            
            const mapelSelect = clone.querySelector('select[name="id_mata_pelajaran[]"]');
            const guruSelect = clone.querySelector('select[name="id_guru[]"]');
            
            // Set value jika ada data dari DB
            if (mapelId) mapelSelect.value = mapelId;
            if (guruId) guruSelect.value = guruId;

            container.appendChild(clone);
            checkEmpty();
            updateSelects(); // Langsung update disable option
        }

        function removeRow(button) {
            const row = button.closest('.row-item');
            row.remove();
            checkEmpty();
            updateSelects(); // Update lagi karena ada mapel yang "bebas"
        }

        // FUNGSI INTI: Menyembunyikan Mapel yang sudah dipilih di baris lain
        function updateSelects() {
            // 1. Kumpulkan semua value mapel yang sedang dipilih
            let selectedValues = [];
            document.querySelectorAll('.mapel-select').forEach(select => {
                if (select.value !== "") {
                    selectedValues.push(select.value);
                }
            });

            // 2. Loop setiap select mapel
            document.querySelectorAll('.mapel-select').forEach(select => {
                let currentValue = select.value;
                
                // Cek setiap option di dalam select tersebut
                Array.from(select.options).forEach(option => {
                    if (option.value === "") return; // Abaikan placeholder

                    // Jika option ada di daftar selected, TAPI BUKAN milik select ini sendiri
                    if (selectedValues.includes(option.value) && option.value !== currentValue) {
                        option.disabled = true;
                        option.style.display = 'none'; // Sembunyikan
                    } else {
                        option.disabled = false;
                        option.style.display = 'block'; // Tampilkan kembali
                    }
                });
            });
        }

        document.getElementById('addRowBtn').addEventListener('click', () => addRow());

        // Inisialisasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', () => {
            if (existingData.length > 0) {
                existingData.forEach(data => addRow(data.id_mata_pelajaran, data.id_guru));
            } else {
                addRow(); // Tambah 1 baris kosong secara default
            }
        });
    </script>
</x-app-layout>