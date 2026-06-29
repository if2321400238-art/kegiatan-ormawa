<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        Masukkan email atau NIM. Tautan untuk membuat password baru akan dikirim ke email resmi yang tersimpan pada akun Anda.
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="login" value="Email atau NIM" />
            <x-text-input id="login" class="block mt-1 w-full" type="text" name="login" :value="old('login')" required autofocus />
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                Kirim Tautan Reset Password
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
