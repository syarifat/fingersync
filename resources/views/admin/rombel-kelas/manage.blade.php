<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.rombel-kelas.index') }}" class="p-2 bg-white rounded-full shadow-sm border border-gray-200 hover:bg-gray-50">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">Kelola Kelas: {{ $kelas->nama }}</h2>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <form id="rombelForm" action="{{ route('admin.rombel-kelas.storeManage', $kelas->id) }}" method="POST">
                @csrf
                
                {{-- SECTION 1: GURU --}}
                <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 p-8 mb-6">
                    <h3 class="text-lg font-black text-orange-600 uppercase tracking-wide mb-6 border-b border-gray-100 pb-4">Tentukan Pengampu</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Wali Kelas <span class="text-red-500">*</span></label>
                            <select name="id_guru_wali_kelas" class="w-full border-gray-300 rounded-xl focus:ring-orange-500" required>
                                <option value="">-- Pilih Wali Kelas --</option>
                                {{-- PERBAIKAN: Menggunakan $guruWaliList --}}
                                @foreach($guruWaliList as $g)
                                    <option value="{{ $g->id }}" {{ ($rombelSaatIni->id_guru_wali_kelas ?? '') == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-gray-400 mt-1 italic">* Guru yang sudah menjadi wali di kelas lain tidak akan muncul di sini.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Guru BK <span class="text-red-500">*</span></label>
                            <select name="id_guru_bk" class="w-full border-gray-300 rounded-xl focus:ring-orange-500" required>
                                <option value="">-- Pilih Guru BK --</option>
                                {{-- PERBAIKAN: Menggunakan $guruBkList --}}
                                @foreach($guruBkList as $g)
                                    <option value="{{ $g->id }}" {{ ($rombelSaatIni->id_guru_bk ?? '') == $g->id ? 'selected' : '' }}>{{ $g->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                {{-- SECTION 2: PLOTTING SISWA (2 DIV KIRI KANAN) --}}
                <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 p-8">
                    <div class="mb-6 flex justify-between items-center border-b border-gray-100 pb-4">
                        <div>
                            <h3 class="text-lg font-black text-orange-600 uppercase tracking-wide">Pilih Siswa</h3>
                            <p class="text-sm text-gray-500 font-medium">Geser siswa ke kotak kanan untuk memasukkan ke kelas ini.</p>
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row gap-4 items-stretch">
                        
                        {{-- KOTAK KIRI: Siswa Nganggur --}}
                        <div class="flex-1 bg-gray-50 border border-gray-200 rounded-2xl p-4 flex flex-col">
                            <h4 class="text-sm font-bold text-gray-700 text-center mb-3 uppercase tracking-tighter">Siswa Belum Punya Kelas</h4>
                            <select id="sourceBox" multiple class="flex-1 w-full min-h-[350px] border-gray-300 rounded-xl text-sm p-2 shadow-inner focus:ring-orange-500">
                                @foreach($siswaNoClass as $s)
                                    <option value="{{ $s->id }}" class="p-2 border-b border-gray-100 hover:bg-orange-100">{{ $s->nama }} ({{ $s->nis }})</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-gray-400 mt-2 text-center">* Tahan <strong>CTRL</strong> untuk memilih banyak siswa</p>
                        </div>

                        {{-- TOMBOL PINDAH --}}
                        <div class="flex md:flex-col justify-center gap-3 py-4 md:py-0">
                            <button type="button" onclick="moveOptions('sourceBox', 'destBox')" class="p-4 bg-orange-100 text-orange-600 rounded-2xl hover:bg-orange-600 hover:text-white transition-all shadow-sm group" title="Pindah ke Kanan">
                                <svg class="w-6 h-6 rotate-90 md:rotate-0 transform group-active:scale-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path></svg>
                            </button>
                            <button type="button" onclick="moveOptions('destBox', 'sourceBox')" class="p-4 bg-gray-200 text-gray-600 rounded-2xl hover:bg-gray-800 hover:text-white transition-all shadow-sm group" title="Kembalikan ke Kiri">
                                <svg class="w-6 h-6 rotate-90 md:rotate-0 transform group-active:scale-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                            </button>
                        </div>

                        {{-- KOTAK KANAN: Siswa Kelas Ini --}}
                        <div class="flex-1 bg-blue-50 border border-blue-200 rounded-2xl p-4 flex flex-col">
                            <h4 class="text-sm font-bold text-blue-800 text-center mb-3 uppercase tracking-tighter">Siswa di Kelas Ini ({{ $siswaInClass->count() }})</h4>
                            {{-- PENTING: name="id_siswa[]" ada di select kanan ini --}}
                            <select id="destBox" name="id_siswa[]" multiple class="flex-1 w-full min-h-[350px] border-blue-300 bg-white rounded-xl text-sm p-2 shadow-inner focus:ring-blue-500">
                                @foreach($siswaInClass as $s)
                                    <option value="{{ $s->id }}" class="p-2 border-b border-gray-100 hover:bg-blue-100">{{ $s->nama }} ({{ $s->nis }})</option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-blue-400 mt-2 text-center italic">Data di dalam kotak ini yang akan tersimpan ke database.</p>
                        </div>

                    </div>

                    <div class="mt-8 flex justify-end gap-4 border-t border-gray-100 pt-6">
                        <a href="{{ route('admin.rombel-kelas.index') }}" class="px-6 py-3 font-bold text-gray-500 hover:text-gray-800 uppercase text-xs tracking-widest transition-colors">Batal</a>
                        <button type="submit" class="px-10 py-3 bg-orange-600 text-white font-black rounded-xl hover:bg-orange-700 shadow-lg hover:shadow-orange-200 uppercase text-xs tracking-widest transition-all">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- Script JavaScript untuk Dual Listbox --}}
    <script>
        // Fungsi untuk memindah option antar kotak
        function moveOptions(sourceId, targetId) {
            const source = document.getElementById(sourceId);
            const target = document.getElementById(targetId);
            const selectedOptions = Array.from(source.selectedOptions);
            
            // Urutkan alfabetis setelah dipindah (Opsional agar rapi)
            selectedOptions.forEach(option => {
                target.appendChild(option);
            });
            
            // Sort otomatis biar tetap rapi berdasarkan Nama
            sortSelect(target);
        }

        function sortSelect(selElem) {
            const tmpAry = new Array();
            for (let i=0; i<selElem.options.length; i++) {
                tmpAry[i] = new Array();
                tmpAry[i][0] = selElem.options[i].text;
                tmpAry[i][1] = selElem.options[i].value;
            }
            tmpAry.sort();
            while (selElem.options.length > 0) {
                selElem.options[0] = null;
            }
            for (let i=0; i<tmpAry.length; i++) {
                const op = new Option(tmpAry[i][0], tmpAry[i][1]);
                selElem.options[i] = op;
                selElem.options[i].classList.add('p-2', 'border-b', 'border-gray-100', 'hover:bg-blue-100');
            }
        }

        // SANGAT PENTING: Saat form disubmit, kita harus menyeleksi (select) 
        // semua data di kotak kanan agar datanya terkirim ke Laravel (Controller).
        document.getElementById('rombelForm').addEventListener('submit', function() {
            const destBox = document.getElementById('destBox');
            Array.from(destBox.options).forEach(option => {
                option.selected = true;
            });
        });
    </script>
</x-app-layout>