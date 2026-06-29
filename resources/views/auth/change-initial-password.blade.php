<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-lg font-semibold text-gray-900">Ganti Password Awal</h1>
        <p class="mt-2 text-sm text-gray-600">
            Demi keamanan akun, buat password baru sebelum melanjutkan ke aplikasi. Password baru tidak boleh sama dengan NIM.
        </p>
    </div>

    <form method="POST" action="{{ route('password.initial.update') }}">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="current_password" value="Password Saat Ini (NIM)" />
            <x-text-input id="current_password" class="block mt-1 w-full" type="password"
                name="current_password" required autofocus autocomplete="current-password" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Password Baru" />
            <x-text-input id="password" class="block mt-1 w-full" type="password"
                name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Konfirmasi Password Baru" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>Simpan Password Baru</x-primary-button>
        </div>
    </form>
</x-guest-layout>
