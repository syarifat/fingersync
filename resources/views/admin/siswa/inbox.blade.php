<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Inbox Registrasi Jari') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-2xl border border-gray-100">
                <div class="p-8">

                    <div class="mb-8">
                        <h3 class="text-lg font-bold text-orange-600 uppercase tracking-tighter">Antrian Sidik Jari Baru</h3>
                        <p class="text-sm text-gray-500">Daftar sidik jari yang baru di-scan di alat namun belum memiliki identitas nama siswa.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 pl-4 font-semibold">Waktu Scan</th>
                                    <th class="pb-4 font-semibold text-center">Asal Alat</th>
                                    <th class="pb-4 font-semibold text-center">Fingerprint ID</th>
                                    <th class="pb-4 font-semibold text-right pr-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($inbox as $item)
                                <tr class="group hover:bg-orange-50/30 transition-colors">
                                    <td class="py-5 pl-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $item->created_at->format('d M Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->created_at->format('H:i:s') }} WIB</div>
                                    </td>
                                    <td class="py-5 text-center">
                                        <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-bold font-mono border border-gray-200">
                                            {{ $item->id_device }}
                                        </span>
                                    </td>
                                    <td class="py-5 text-center">
                                        <span class="inline-flex items-center justify-center w-10 h-10 bg-orange-100 text-orange-600 rounded-full font-black text-lg border-2 border-orange-200">
                                            {{ $item->fingerprint_id }}
                                        </span>
                                    </td>
                                    <td class="py-5 text-right pr-4">
                                        <a href="{{ route('admin.siswa.create', ['finger_id' => $item->fingerprint_id, 'inbox_id' => $item->id]) }}" 
                                           class="inline-flex items-center px-4 py-2 bg-orange-600 text-white rounded-lg text-sm font-bold hover:bg-orange-700 transition shadow-sm">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                            Lengkapi Data
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-gray-400">
                                        Tidak ada antrian sidik jari baru dari alat.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6">{{ $inbox->links() }}</div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>