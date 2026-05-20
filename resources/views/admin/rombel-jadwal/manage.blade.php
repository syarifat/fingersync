<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.rombel-jadwal.index') }}" class="p-2 bg-white rounded-full shadow-sm border border-gray-200 hover:bg-gray-50">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">Kelola Jadwal: {{ $kelas->nama }}</h2>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('admin.rombel-jadwal.storeManage', $kelas->id) }}" method="POST" onsubmit="return validateManageJadwal(event)">
                @csrf
                
                <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 p-8 overflow-hidden">
                    {{-- TAMBAHKAN BLOK ALERT INI --}}
                    @if (session('error'))
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-xl font-bold text-sm shadow-sm flex items-start gap-3">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <div>
                            <span class="block uppercase tracking-wider text-xs font-black mb-1">Gagal Menyimpan</span>
                            {{ session('error') }}
                        </div>
                    </div>
                    @endif

                    @if (session('success'))
                    <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 rounded-r-xl font-bold text-sm shadow-sm flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        {{ session('success') }}
                    </div>
                    @endif
                    {{-- BATAS BLOK ALERT --}}
                    <div class="mb-6 flex justify-between items-center border-b border-gray-100 pb-4">
                        <div>
                            <h3 class="text-lg font-black text-blue-600 uppercase tracking-wide">Penyusunan Jadwal Kelas</h3>
                            <p class="text-sm text-gray-500 font-medium">Mapel yang muncul di sini adalah mapel yang sudah diplot di menu Plotting Guru.</p>
                        </div>
                        <button type="button" id="addRowBtn" class="px-4 py-2 bg-gray-800 text-white rounded-xl text-sm font-bold hover:bg-gray-900 transition-colors shadow-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah Baris
                        </button>
                    </div>

                    {{-- HEADER KOLOM (Grid 12) --}}
                    <div class="grid grid-cols-12 gap-3 mb-2 px-2 text-[10px] font-black text-gray-400 uppercase tracking-widest hidden lg:grid">
                        <div class="col-span-2">Hari</div>
                        <div class="col-span-3">Mata Pelajaran (Guru)</div>
                        <div class="col-span-2 text-center">Jam Mulai</div>
                        <div class="col-span-2 text-center">Jam Selesai</div>
                        <div class="col-span-2">Ruangan</div>
                        <div class="col-span-1 text-center">Aksi</div>
                    </div>

                    {{-- CONTAINER BARIS DINAMIS --}}
                    <div id="dynamicContainer" class="space-y-3">
                        {{-- Data di-inject via Javascript --}}
                    </div>

                    {{-- Pesan Jika Kosong --}}
                    <div id="emptyMessage" class="py-12 text-center border-2 border-dashed border-gray-200 rounded-2xl hidden mt-4">
                        <p class="text-gray-400 font-bold">Belum ada jadwal yang disusun. Klik tombol "Tambah Baris".</p>
                    </div>

                    <div class="mt-8 flex justify-end gap-4 border-t border-gray-100 pt-6">
                        <a href="{{ route('admin.rombel-jadwal.index') }}" class="px-6 py-3 font-bold text-gray-500 hover:text-gray-800 uppercase text-sm tracking-widest">Batal</a>
                        <button type="submit" class="px-10 py-3 bg-blue-600 text-white font-black rounded-xl hover:bg-blue-700 shadow-lg uppercase text-sm tracking-widest">
                            Simpan Jadwal
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TEMPLATE HIDDEN UNTUK JAVASCRIPT --}}
    <template id="rowTemplate">
        <div class="row-item grid grid-cols-1 lg:grid-cols-12 gap-3 bg-gray-50 p-4 lg:p-2 rounded-xl border border-gray-200 items-center">
            
            {{-- HARI --}}
            <div class="lg:col-span-2">
                <span class="block lg:hidden text-xs font-bold text-gray-500 mb-1">Hari</span>
                <select name="hari[]" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 font-bold" required>
                    <option value="">Pilih Hari</option>
                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                        <option value="{{ $hari }}">{{ $hari }}</option>
                    @endforeach
                </select>
            </div>

            {{-- MAPEL & GURU --}}
            <div class="lg:col-span-3">
                <span class="block lg:hidden text-xs font-bold text-gray-500 mb-1 mt-2">Mata Pelajaran</span>
                <select name="id_rombel_mata_pelajaran[]" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500" required>
                    <option value="">Pilih Mapel...</option>
                    @foreach($plottings as $plot)
                        <option value="{{ $plot->id }}">
                            {{ $plot->mataPelajaran->nama }} ({{ $plot->guru->nama }})
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- JAM MULAI --}}
            <div class="lg:col-span-2">
                <span class="block lg:hidden text-xs font-bold text-gray-500 mb-1 mt-2">Jam Mulai</span>
                <input type="time" name="jam_mulai[]" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 text-center font-mono" required>
            </div>

            {{-- JAM SELESAI --}}
            <div class="lg:col-span-2">
                <span class="block lg:hidden text-xs font-bold text-gray-500 mb-1 mt-2">Jam Selesai</span>
                <input type="time" name="jam_selesai[]" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500 text-center font-mono" required>
            </div>

            {{-- RUANGAN --}}
            <div class="lg:col-span-2">
                <span class="block lg:hidden text-xs font-bold text-gray-500 mb-1 mt-2">Ruangan</span>
                <select name="id_ruangan[]" class="w-full border-gray-300 rounded-lg text-sm focus:ring-blue-500" required>
                    <option value="">Pilih Ruangan...</option>
                    @foreach($ruangan as $r)
                        <option value="{{ $r->id }}">{{ $r->nama_ruangan }}</option>
                    @endforeach
                </select>
            </div>

            {{-- AKSI --}}
            <div class="lg:col-span-1 flex justify-end lg:justify-center mt-4 lg:mt-0">
                <button type="button" class="p-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-600 hover:text-white transition-colors" onclick="removeRow(this)" title="Hapus Baris">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-3a1 1 0 00-1 1v3M4 7h16"></path></svg>
                </button>
            </div>

        </div>
    </template>

    <script>
        const container = document.getElementById('dynamicContainer');
        const template = document.getElementById('rowTemplate');
        const emptyMessage = document.getElementById('emptyMessage');
        
        // Data dari database dengan Raw Echo yang aman
        const existingData = {!! $jadwalSaatIni->toJson() !!};

        function checkEmpty() {
            if (container.children.length === 0) {
                emptyMessage.classList.remove('hidden');
            } else {
                emptyMessage.classList.add('hidden');
            }
        }

        // Modifikasi fungsi AddRow untuk menampung 5 parameter data
        function addRow(data = null) {
            const clone = template.content.cloneNode(true);
            const rowDiv = clone.querySelector('.row-item');
            
            if(data) {
                clone.querySelector('select[name="hari[]"]').value = data.hari;
                clone.querySelector('select[name="id_rombel_mata_pelajaran[]"]').value = data.id_rombel_mata_pelajaran;
                
                // Potong detik dari database (contoh: "07:00:00" menjadi "07:00") agar pas di input HTML
                clone.querySelector('input[name="jam_mulai[]"]').value = data.jam_mulai.substring(0, 5);
                clone.querySelector('input[name="jam_selesai[]"]').value = data.jam_selesai.substring(0, 5);
                
                clone.querySelector('select[name="id_ruangan[]"]').value = data.id_ruangan;
            }

            container.appendChild(clone);
            checkEmpty();
        }

        function removeRow(button) {
            const row = button.closest('.row-item');
            row.remove();
            checkEmpty();
        }

        document.getElementById('addRowBtn').addEventListener('click', () => addRow(null));

        document.addEventListener('DOMContentLoaded', () => {
            if (existingData.length > 0) {
                existingData.forEach(data => addRow(data));
            } else {
                addRow(null); // Tambah 1 baris kosong jika belum ada data
            }
        });

        function validateManageJadwal(event) {
            const container = document.getElementById('dynamicContainer');
            // Jika container kosong (Sapu Bersih), beri peringatan foreign key
            if (container.children.length === 0) {
                confirmDelete(event, 'Aksi ini akan menghapus seluruh jadwal pelajaran di kelas ini beserta SEMUA riwayat presensi (absensi) yang sudah tercatat. Anda yakin ingin melanjutkan?');
            }
        }
    </script>
</x-app-layout>