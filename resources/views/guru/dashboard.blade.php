<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Guru Dashboard - FingerSync Presence') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">Halo, {{ Auth::user()->nama }}!</h3>
                
                <div class="bg-gray-50 p-4 rounded-md border mb-6">
                    <h4 class="font-semibold text-gray-700">Profil Guru:</h4>
                    <ul class="mt-2 space-y-1 text-sm">
                        <li><strong>NIDN:</strong> {{ $guru->nidn }}</li>
                        <li><strong>Status:</strong> {{ $guru->status }}</li>
                        <li><strong>Wali Kelas:</strong> {{ $guru->is_bk ? 'Guru BK' : 'Guru Mapel' }}</li>
                    </ul>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-green-100 rounded-lg">
                        <h4 class="font-bold text-green-800">Cek Presensi</h4>
                        <p class="text-sm">Lihat kehadiran siswa hari ini secara real-time.</p>
                    </div>
                    </div>
            </div>
        </div>
    </div>
</x-app-layout>