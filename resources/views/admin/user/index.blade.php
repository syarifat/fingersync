<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic">
            {{ __('Log Data Pengguna (Users)') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-[2rem] border border-gray-100 overflow-hidden">
                <div class="p-8">

                    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                        <div>
                            <h3 class="text-lg font-bold text-orange-600 uppercase tracking-tighter">Daftar Akun Sistem</h3>
                            <p class="text-sm text-gray-500">Melihat seluruh data akun yang memiliki akses ke aplikasi (Read-Only).</p>
                        </div>
                        {{-- Badge Penanda Mode Read-Only --}}
                        <div class="px-4 py-2 bg-gray-100 text-gray-600 rounded-full font-bold text-xs uppercase tracking-widest flex items-center gap-2 border border-gray-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Mode Lihat Saja
                        </div>
                    </div>

                    {{-- FILTER SECTION --}}
                    <div class="mb-6 bg-gray-50 p-5 rounded-2xl border border-gray-100">
                        <form method="GET" action="{{ route('admin.user.index') }}" class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1">
                                <label for="search" class="block text-xs font-bold text-gray-500 uppercase mb-1">Cari Akun</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    </div>
                                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                                        class="pl-10 block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm"
                                        placeholder="Nama atau Username...">
                                </div>
                            </div>

                            <div class="md:w-1/4">
                                <label for="role" class="block text-xs font-bold text-gray-500 uppercase mb-1">Filter Hak Akses</label>
                                <select name="role" id="role" class="block w-full rounded-xl border-gray-200 bg-white text-sm focus:border-orange-500 focus:ring-orange-500 shadow-sm">
                                    <option value="">Semua Role</option>
                                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="guru" {{ request('role') == 'guru' ? 'selected' : '' }}>Guru</option>
                                </select>
                            </div>

                            <div class="flex items-end gap-2">
                                <button type="submit" class="px-6 py-2.5 bg-gray-800 text-white text-sm font-bold rounded-xl hover:bg-gray-900 transition-colors shadow-sm h-[42px]">
                                    Filter
                                </button>
                                @if(request()->hasAny(['search', 'role']))
                                <a href="{{ route('admin.user.index') }}" class="px-4 py-2.5 bg-white border border-gray-300 text-gray-700 text-sm font-bold rounded-xl hover:bg-gray-50 transition-colors flex items-center justify-center h-[42px]" title="Reset">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    {{-- TABEL DATA --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-gray-400 text-xs uppercase tracking-widest border-b border-gray-100">
                                    <th class="pb-4 pl-4 font-black w-16">No</th>
                                    <th class="pb-4 font-black">Informasi Akun</th>
                                    <th class="pb-4 font-black text-center">Hak Akses (Role)</th>
                                    <th class="pb-4 pr-4 font-black text-right">Tgl Terdaftar</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse ($users as $index => $u)
                                <tr class="group hover:bg-gray-50/50 transition-colors">
                                    <td class="py-5 pl-4 font-medium text-gray-500">
                                        {{ $users->firstItem() + $index }}
                                    </td>
                                    <td class="py-5">
                                        <div class="font-bold text-gray-900 text-base">{{ $u->nama }}</div>
                                        <div class="text-xs text-gray-500 font-mono mt-0.5">@ {{ $u->username }}</div>
                                    </td>
                                    <td class="py-5 text-center">
                                        @if($u->role == 'admin')
                                            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-bold shadow-sm uppercase tracking-wider">Admin</span>
                                        @elseif($u->role == 'guru')
                                            <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-xs font-bold shadow-sm uppercase tracking-wider">Guru</span>
                                        @else
                                            <span class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-xs font-bold shadow-sm uppercase tracking-wider">{{ $u->role }}</span>
                                        @endif
                                    </td>
                                    <td class="py-5 pr-4 text-right text-sm text-gray-500 font-medium">
                                        {{ $u->created_at->format('d M Y, H:i') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-gray-400 italic">Belum ada data pengguna.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-8">{{ $users->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>