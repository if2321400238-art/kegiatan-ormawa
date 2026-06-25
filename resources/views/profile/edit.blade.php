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
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Pembina</label>
                                    <select name="pembina" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                        <option value="">-- Pilih Dosen Pembina --</option>
                                        @foreach($dosen as $item)
                                            <option value="{{ $item->nama }}" {{ old('pembina', $user->ormawa->pembina ?? '') === $item->nama ? 'selected' : '' }}>{{ $item->nama }} ({{ $item->email }})</option>
                                        @endforeach
                                    </select>
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
