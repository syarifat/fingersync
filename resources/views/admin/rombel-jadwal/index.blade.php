<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Manajemen Jadwal Pelajaran') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 p-8">

                <div class="mb-8 border-b border-gray-100 pb-6">
                    <h3 class="text-xl font-black text-orange-600 uppercase tracking-tighter">Daftar Kelas Aktif</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Pilih kelas untuk mengatur jadwal mingguan (Jam, Mapel, dan Ruangan).
                        Tahun Ajar Aktif: <span class="font-bold text-black">{{ session('tahun_ajar_nama', 'Belum Dipilih') }}</span>
                    </p>
                </div>

                @if (session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl font-bold text-sm">
                    {{ session('success') }}
                </div>
                @endif
                
                @if (session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl font-bold text-sm">
                    {{ session('error') }}
                </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($kelas as $k)
                    <div class="border border-gray-200 rounded-2xl p-6 hover:shadow-lg transition-all bg-white relative overflow-hidden group">
                        <div class="absolute top-0 left-0 w-full h-1 {{ $k->jumlah_jadwal > 0 ? 'bg-blue-500' : 'bg-gray-300' }}"></div>
                        
                        <div class="flex justify-between items-center mb-6">
                            <h4 class="text-2xl font-black text-gray-800">{{ $k->nama }}</h4>
                            <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-bold border border-blue-100">
                                {{ $k->jumlah_jadwal }} Sesi
                            </span>
                        </div>

                        <a href="{{ route('admin.rombel-jadwal.manage', $k->id) }}" class="block w-full py-2.5 text-center bg-gray-800 text-white rounded-xl text-sm font-bold hover:bg-blue-600 transition-colors">
                            Atur Jadwal
                        </a>
                    </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
</x-app-layout>