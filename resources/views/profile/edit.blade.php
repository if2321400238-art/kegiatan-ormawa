<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Profile
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Update Profile Information --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Informasi Profile</h3>

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                                <input type="text" value="{{ $user->username }}" disabled class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" name="nama" value="{{ old('nama', $user->nama) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. HP</label>
                                <input type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>

                            @if($user->isOrmawa())
                                <hr class="my-6">
                                <h4 class="font-semibold mb-3">Informasi Ormawa</h4>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ormawa</label>
                                    <input type="text" name="nama_ormawa" value="{{ old('nama_ormawa', $user->ormawa->nama_ormawa ?? '') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Ketua</label>
                                    <input type="text" name="ketua" value="{{ old('ketua', $user->ormawa->ketua ?? '') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Kop Surat (PDF/Image)</label>
                                    @if($user->ormawa && $user->ormawa->kop_surat)
                                        <p class="text-sm text-green-600 mb-2">✓ Kop surat sudah diupload</p>
                                    @endif
                                    <input type="file" name="kop_surat" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                    <p class="text-xs text-gray-500 mt-1">Format: PDF, JPG, PNG. Max: 2MB</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                                    <textarea name="deskripsi" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md">{{ old('deskripsi', $user->ormawa->deskripsi ?? '') }}</textarea>
                                </div>
                            @endif

                            <div class="flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Telegram Connection --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold">Integrasi Telegram</h3>
                            <p class="text-sm text-gray-500 mt-1">Hubungkan akun untuk menerima notifikasi pengajuan melalui bot Telegram.</p>
                        </div>
                        @if($user->hasTelegram())
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-green-100 text-green-700 text-sm font-medium"><span class="w-2 h-2 rounded-full bg-green-500"></span>Terhubung</span>
                        @else
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-gray-100 text-gray-600 text-sm font-medium"><span class="w-2 h-2 rounded-full bg-gray-400"></span>Belum terhubung</span>
                        @endif
                    </div>

                    @if(session('telegram_success'))<div class="mt-4 p-3 rounded-lg bg-green-50 text-green-700 text-sm">{{ session('telegram_success') }}</div>@endif
                    @if(session('telegram_error'))<div class="mt-4 p-3 rounded-lg bg-red-50 text-red-700 text-sm">{{ session('telegram_error') }}</div>@endif

                    @if(session('telegram_otp'))
                        <div class="mt-5 p-5 rounded-xl border border-blue-200 bg-blue-50 text-center">
                            <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">Kode OTP Telegram</p>
                            <div class="text-3xl font-bold tracking-[0.35em] text-gray-900 mt-2 ml-[0.35em]">{{ session('telegram_otp') }}</div>
                            <p class="text-xs text-gray-500 mt-2">Berlaku 10 menit dan hanya dapat digunakan satu kali.</p>
                        </div>
                    @endif

                    @php($telegramUsername = ltrim(config('services.telegram.bot_username'), '@'))
                    <div class="mt-5 rounded-xl bg-gray-50 border border-gray-100 p-5">
                        <h4 class="font-semibold text-sm mb-3">Cara menyambungkan akun</h4>
                        <ol class="space-y-3 text-sm text-gray-700">
                            <li class="flex gap-3"><span class="flex-none w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">1</span><span>Buka Telegram dan cari bot <a href="https://t.me/{{ $telegramUsername }}" target="_blank" rel="noopener" class="font-semibold text-blue-600">{{ '@'.$telegramUsername }}</a>.</span></li>
                            <li class="flex gap-3"><span class="flex-none w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">2</span><span>Tekan <strong>Mulai</strong> pada chat bot.</span></li>
                            <li class="flex gap-3"><span class="flex-none w-6 h-6 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">3</span><span>Kirimkan kode OTP yang ditampilkan di atas kepada bot.</span></li>
                        </ol>
                    </div>

                    <div class="mt-5 flex justify-end">
                        @if($user->hasTelegram())
                            <form method="POST" action="{{ route('profile.telegram.disconnect') }}" onsubmit="return confirm('Putuskan koneksi akun Telegram?')">@csrf @method('DELETE')<button class="px-4 py-2 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 text-sm font-medium">Putuskan Telegram</button></form>
                        @else
                            <form method="POST" action="{{ route('profile.telegram.generate') }}">@csrf<button class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 text-sm font-medium"><i class="ti ti-brand-telegram mr-1"></i>{{ session('telegram_otp') ? 'Buat Ulang OTP' : 'Sambungkan dengan OTP' }}</button></form>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Update Password --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Ubah Password</h3>

                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password Lama</label>
                                <input type="password" name="current_password" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                                <input type="password" name="password" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                                <input type="password" name="password_confirmation" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                    Ubah Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
