<x-app-layout>
    <x-slot name="title">Tambah Anggota</x-slot>

    @php
        $routePrefix = request()->routeIs('admin.*') ? 'admin.' : (request()->routeIs('bauak.*') ? 'bauak.' : '');
    @endphp

    {{-- Top Header Section --}}
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Tambah Anggota {{ $ormawa->nama_ormawa }}</h2>
        <p class="text-[12px] text-gray-500">Tambahkan mahasiswa ke organisasi ini</p>
    </div>

    {{-- Back Link --}}
    <div class="mb-4">
        <a href="{{ route($routePrefix . 'ormawa.anggota.index', $ormawa) }}"
            class="text-[12px] text-blue-600 hover:text-blue-700 flex items-center gap-1">
            <i class="ti ti-arrow-left"></i> Kembali ke Daftar Anggota
        </a>
    </div>

    {{-- Form Container Card --}}
    <div class="max-w-3xl bg-white rounded-xl border border-gray-100 shadow-sm overflow-visible">
        <form action="{{ route($routePrefix . 'ormawa.anggota.store', $ormawa) }}" method="POST" class="p-6 space-y-4">
            @csrf

            {{-- Pencarian mahasiswa dari API UNUJA --}}
            @php
                $selectedUser = old('nim') ? App\Models\User::where('nim', old('nim'))->first() : null;
                $initialSelected = $selectedUser ? [
                    'id' => $selectedUser->id,
                    'nama' => $selectedUser->nama,
                    'nim' => $selectedUser->nim,
                    'program_studi' => $selectedUser->program_studi,
                    'already_member' => false,
                ] : null;
            @endphp
            <div x-data="anggotaSearch(@js(route($routePrefix . 'ormawa.anggota.search', $ormawa)), @js($initialSelected))"
                @click.outside="showResults = false" class="space-y-3">
                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">
                    Pilih Mahasiswa <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="ti ti-search text-gray-400"></i>
                    </div>
                    <input type="text" x-model="query"
                        @input="onInput()" @focus="onFocus()" @keydown.escape="showResults = false"
                        placeholder="Ketik minimal 2 karakter nama atau NIM..."
                        class="w-full pl-10 pr-12 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors"
                        autocomplete="off">
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <i x-show="loading" class="ti ti-loader-2 animate-spin text-brand"></i>
                        <button x-show="selectedUser && !loading" type="button" @click="clearSelection()"
                            class="text-gray-400 hover:text-gray-700" title="Hapus pilihan">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>

                    <div x-show="showResults && !selectedUser"
                        x-transition.opacity class="absolute z-40 top-full left-0 right-0 mt-2 overflow-hidden bg-white border border-gray-200 rounded-xl shadow-xl"
                        style="display: none;">
                        <div x-show="loading" class="px-4 py-5 text-center text-[12px] text-gray-500">
                            <i class="ti ti-loader-2 animate-spin text-brand mr-1"></i> Mencari data mahasiswa...
                        </div>

                        <div x-show="!loading && results.length" class="max-h-72 overflow-y-auto divide-y divide-gray-100">
                            <template x-for="user in results" :key="user.nim">
                                <button type="button" @click="select(user)" :disabled="user.already_member"
                                    :class="user.already_member ? 'cursor-not-allowed bg-gray-50 opacity-60' : 'hover:bg-blue-50/60'"
                                    class="w-full flex items-center gap-3 px-4 py-3 text-left transition-colors">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center shrink-0"
                                        :class="user.already_member ? 'bg-gray-200 text-gray-500' : 'bg-brand/10 text-brand'">
                                        <i class="ti ti-user"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-medium text-[13px] text-gray-900 truncate" x-text="user.nama"></div>
                                        <div class="text-[11px] text-gray-500 truncate"
                                            x-text="['NIM: ' + user.nim, user.program_studi].filter(Boolean).join(' | ')"></div>
                                    </div>
                                    <span x-show="user.already_member" class="shrink-0 px-2 py-1 rounded-full bg-gray-200 text-gray-600 text-[10px] font-medium">
                                        Sudah terdaftar
                                    </span>
                                    <i x-show="!user.already_member" class="ti ti-chevron-right text-gray-300"></i>
                                </button>
                            </template>
                        </div>

                        <div x-show="!loading && searched && !results.length && !errorMessage"
                            class="px-4 py-6 text-center">
                            <i class="ti ti-user-search text-2xl text-gray-300"></i>
                            <div class="mt-1 text-[12px] font-medium text-gray-700">Mahasiswa tidak ditemukan</div>
                            <div class="text-[11px] text-gray-500">Periksa kembali nama atau NIM yang dicari.</div>
                        </div>

                        <div x-show="!loading && errorMessage" class="px-4 py-4 bg-red-50 text-red-700 text-[12px]">
                            <i class="ti ti-alert-circle mr-1"></i><span x-text="errorMessage"></span>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="nim" :value="selectedUser?.nim || ''">

                <div x-show="selectedUser" x-transition class="p-4 rounded-xl bg-blue-50 border border-blue-200" style="display: none;">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center shrink-0">
                            <i class="ti ti-user-check"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-[10px] font-semibold uppercase tracking-wider text-blue-600">Mahasiswa dipilih</div>
                            <div class="font-semibold text-[14px] text-blue-950" x-text="selectedUser?.nama"></div>
                            <div class="text-[12px] text-blue-700"
                                x-text="['NIM: ' + selectedUser?.nim, selectedUser?.program_studi].filter(Boolean).join(' | ')"></div>
                        </div>
                        <button type="button" @click="clearSelection()" class="p-1 text-blue-500 hover:text-blue-800" title="Ganti mahasiswa">
                            <i class="ti ti-x"></i>
                        </button>
                    </div>
                </div>

                @error('nim')
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
                    <input type="checkbox" name="status" value="1"
                        class="w-4 h-4 rounded border-gray-300 text-brand focus:ring-brand"
                        {{ old('status', true) ? 'checked' : '' }}>
                    <span class="text-[13px] font-medium text-gray-700">Anggota Aktif</span>
                </label>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-50">
                <a href="{{ route($routePrefix . 'ormawa.anggota.index', $ormawa) }}"
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
