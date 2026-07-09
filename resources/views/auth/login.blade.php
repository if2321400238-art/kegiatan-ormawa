<x-guest-layout>
    <x-auth-session-status class="mb-4 rounded-lg border border-info/20 bg-info-light px-4 py-3 text-[13px] font-medium text-blue-800" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <label for="login" class="mb-1.5 block text-[13px] font-semibold text-gray-700">Email atau NIM</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex w-11 items-center justify-center text-gray-400">
                    <i class="ti ti-user text-lg"></i>
                </span>
                <input id="login" type="text" name="login" value="{{ old('login') }}" required autofocus autocomplete="username"
                    class="w-full rounded-lg border-gray-300 py-2.5 pl-11 pr-3 text-[13px] shadow-sm transition focus:border-brand-accent focus:ring-brand-accent"
                    placeholder="email@unuja.ac.id atau NIM">
            </div>
            <x-input-error :messages="$errors->get('login')" class="mt-2" />
        </div>

        <div>
            <div class="mb-1.5 flex items-center justify-between gap-3">
                <label for="password" class="block text-[13px] font-semibold text-gray-700">Password</label>
                @if (Route::has('password.request'))
                    <a class="text-[12px] font-semibold text-brand-accent transition hover:text-brand" href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                @endif
            </div>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex w-11 items-center justify-center text-gray-400">
                    <i class="ti ti-lock text-lg"></i>
                </span>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="w-full rounded-lg border-gray-300 py-2.5 pl-11 pr-3 text-[13px] shadow-sm transition focus:border-brand-accent focus:ring-brand-accent"
                    placeholder="Masukkan password">
            </div>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between gap-3">
            <label for="remember_me" class="inline-flex items-center gap-2 text-[13px] font-medium text-gray-600">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-brand-accent shadow-sm focus:ring-brand-accent" name="remember">
                <span>Ingat saya</span>
            </label>
        </div>

        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand px-4 py-3 text-[13px] font-bold text-white shadow-sm transition hover:bg-brand-active focus:outline-none focus:ring-2 focus:ring-brand-accent focus:ring-offset-2">
            <i class="ti ti-login-2 text-lg"></i>
            <span>Masuk</span>
        </button>
    </form>
</x-guest-layout>
