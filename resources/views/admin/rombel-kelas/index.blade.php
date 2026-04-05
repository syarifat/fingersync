<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Manajemen Rombongan Belajar') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 p-8">
                
                <div class="mb-8 border-b border-gray-100 pb-6">
                    <h3 class="text-xl font-black text-orange-600 uppercase tracking-tighter">Daftar Kelas Aktif</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Pilih kelas di bawah ini untuk mengatur Wali Kelas, Guru BK, dan memasukkan siswa.
                        Tahun Ajar Aktif: <span class="font-bold text-black">{{ session('tahun_ajar_nama', 'Belum Dipilih') }}</span>
                    </p>
                </div>

                @if (session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl font-bold text-sm">
                    {{ session('success') }}
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($kelas as $k)
                    <div class="border border-gray-200 rounded-2xl p-6 hover:shadow-lg transition-all bg-white relative overflow-hidden group">
                        <div class="absolute top-0 left-0 w-full h-1 {{ $k->jumlah_siswa > 0 ? 'bg-emerald-500' : 'bg-red-500' }}"></div>
                        
                        <div class="flex justify-between items-center mb-4">
                            <h4 class="text-2xl font-black text-gray-800">{{ $k->nama }}</h4>
                            <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-bold">
                                {{ $k->jumlah_siswa }} Siswa
                            </span>
                        </div>

                        <div class="space-y-2 mb-6">
                            <div class="text-sm">
                                <span class="block text-xs font-bold text-gray-400 uppercase">Wali Kelas</span>
                                <span class="font-semibold text-gray-700">{{ $k->wali_kelas_nama }}</span>
                            </div>
                            <div class="text-sm">
                                <span class="block text-xs font-bold text-gray-400 uppercase">Guru BK</span>
                                <span class="font-semibold text-gray-700">{{ $k->guru_bk_nama }}</span>
                            </div>
                        </div>

                        <a href="{{ route('admin.rombel-kelas.manage', $k->id) }}" class="block w-full py-2.5 text-center bg-gray-800 text-white rounded-xl text-sm font-bold hover:bg-orange-600 transition-colors">
                            Kelola Rombel
                        </a>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</x-app-layout>