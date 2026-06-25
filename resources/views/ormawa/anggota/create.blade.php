<x-app-layout>
    <x-slot name="title">Tambah Anggota</x-slot>

    {{-- Top Header Section --}}
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Tambah Anggota {{ $ormawa->nama_ormawa }}</h2>
        <p class="text-[12px] text-gray-500">Tambahkan mahasiswa ke organisasi ini</p>
    </div>

    {{-- Back Link --}}
    <div class="mb-4">
        <a href="{{ route('admin.ormawa.anggota.index', $ormawa) }}"
            class="text-[12px] text-blue-600 hover:text-blue-700 flex items-center gap-1">
            <i class="ti ti-arrow-left"></i> Kembali ke Daftar Anggota
        </a>
    </div>

    {{-- Form Container Card --}}
    <div class="max-w-2xl bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <form action="{{ route('admin.ormawa.anggota.store', $ormawa) }}" method="POST" class="p-6 space-y-4">
            @csrf

            {{-- User Selection --}}
            <div>
                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">
                    Pilih User
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="ti ti-user text-gray-400"></i>
                    </div>
                    <select name="user_id"
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors appearance-none"
                        required>
                        <option value="">-- Pilih User --</option>
                        @forelse($availableUsers as $user)
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->nama }} ({{ $user->email }})
                                {{ $user->nim ? '- ' . $user->nim : '' }}
                            </option>
                        @empty
                            <option disabled>Semua user sudah menjadi anggota atau tidak tersedia</option>
                        @endforelse
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <i class="ti ti-chevron-down"></i>
                    </div>
                </div>
                @error('user_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Jabatan --}}
            <div>
                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">
                    Jabatan
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="ti ti-briefcase text-gray-400"></i>
                    </div>
                    <select name="jabatan"
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors appearance-none"
                        required>
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($jabatanOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('jabatan') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <i class="ti ti-chevron-down"></i>
                    </div>
                </div>
                @error('jabatan')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status Aktif --}}
            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="aktif" value="1"
                        class="w-4 h-4 rounded border-gray-300 text-brand focus:ring-brand"
                        {{ old('aktif', true) ? 'checked' : '' }}>
                    <span class="text-[13px] font-medium text-gray-700">Anggota Aktif</span>
                </label>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-50">
                <a href="{{ route('admin.ormawa.anggota.index', $ormawa) }}"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-[13px] font-medium hover:bg-gray-200 transition text-center">
                    Batal
                </a>

                <button type="submit"
                    class="px-4 py-2 bg-brand text-white rounded-lg text-[13px] font-medium hover:bg-brand-active transition shadow-sm flex items-center gap-1.5">
                    <i class="ti ti-device-floppy"></i> Simpan Anggota
                </button>
            </div>

        </form>
    </div>
</x-app-layout>
