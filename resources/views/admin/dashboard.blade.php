<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard - FingerSync Presence') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">Selamat Datang, {{ Auth::user()->nama }}!</h3>
                <p class="text-gray-600 mb-6">Anda masuk sebagai <strong>Administrator</strong>. Anda memiliki akses penuh ke manajemen perangkat dan data master.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="p-4 bg-blue-100 rounded-lg">
                        <h4 class="font-bold">Kelola Perangkat</h4>
                        <p class="text-sm">Pantau status ESP32 di berbagai lokasi.</p>
                    </div>
                    </div>
            </div>
        </div>
    </div>
</x-app-layout>