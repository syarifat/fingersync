<x-guest-layout>
    <div class="mb-6 text-center">
        <h2 class="text-xl font-bold text-orange-600 uppercase tracking-widest">Aktivasi Akun Guru</h2>
        <p class="text-sm text-gray-500 mt-2">
            {{ __('Masukkan Username yang didaftarkan Admin, lalu buat password baru Anda.') }}
        </p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('aktivasi.store') }}">
        @csrf

        <div>
            <x-input-label for="username" :value="__('Username (NIP / NIDN)')" />
            <x-text-input id="username" class="block mt-1 w-full focus:ring-orange-500 border-gray-300" 
                          type="text" name="username" :value="old('username')" required autofocus 
                          placeholder="Masukkan username dari Admin" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Buat Password Baru')" />
            <x-text-input id="password" class="block mt-1 w-full focus:ring-orange-500 border-gray-300" 
                          type="password" name="password" required autocomplete="new-password" 
                          placeholder="Minimal 6 karakter" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Ulangi Password Baru')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full focus:ring-orange-500 border-gray-300" 
                          type="password" name="password_confirmation" required 
                          placeholder="Ketik ulang password tadi" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-8">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500" href="{{ route('login') }}">
                {{ __('Kembali ke Login') }}
            </a>

            <x-primary-button class="bg-orange-600 hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-900 ml-3">
                {{ __('Aktifkan Akun') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>