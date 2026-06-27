<x-app-layout>
    <x-slot name="title">Tambah Anggota</x-slot>

    @php
        $routePrefix = request()->routeIs('admin.*') ? 'admin.' : '';
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
    <div class="max-w-2xl bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <form action="{{ route($routePrefix . 'ormawa.anggota.store', $ormawa) }}" method="POST" class="p-6 space-y-4">
            @csrf

            {{-- User Selection --}}
            @php
                $selectedUser = old('user_id') ? App\Models\User::find(old('user_id')) : null;
            @endphp
            <div x-data="anggotaSearch('{{ route($routePrefix . 'ormawa.anggota.search', $ormawa) }}', {{ $selectedUser ? json_encode($selectedUser->only(['id', 'nama', 'nim', 'email'])) : 'null' }})"
                x-init="init()"
                class="space-y-3">
                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">
                    Cari Mahasiswa
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="ti ti-search text-gray-400"></i>
                    </div>
                    <input type="text" x-model="query"
                        @input.debounce.300ms="fetchResults()"
                        placeholder="Cari NIM atau Nama"
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors"
                        autocomplete="off">
                </div>

                <input type="hidden" name="user_id" :value="selectedUser ? selectedUser.id : ''" required>

                <template x-if="selectedUser">
                    <div class="p-3 rounded-lg bg-green-50 border border-green-200 text-[13px] text-green-700">
                        <div class="font-semibold">Anggota dipilih:</div>
                        <div x-text="selectedUser.nama"></div>
                        <div class="text-xs text-gray-500" x-text="selectedUser.nim ? 'NIM: ' + selectedUser.nim : selectedUser.email"></div>
                    </div>
                </template>
                <template x-if="!selectedUser">
                    <p class="text-[12px] text-gray-500">Tuliskan NIM atau nama mahasiswa, lalu pilih dari hasil pencarian.</p>
                </template>

                <template x-if="results.length">
                    <div class="max-h-72 overflow-y-auto border border-gray-200 rounded-lg bg-white shadow-sm">
                        <template x-for="user in results" :key="user.id">
                            <button type="button"
                                @click="select(user)"
                                class="w-full text-left px-4 py-3 border-b border-gray-100 hover:bg-gray-50">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-medium text-gray-900" x-text="user.nama"></span>
                                    <span class="text-[11px] text-gray-500" x-text="user.nim"></span>
                                </div>
                                <div class="text-[12px] text-gray-500" x-text="user.email"></div>
                            </button>
                        </template>
                    </div>
                </template>

                <template x-if="query.length >= 2 && !results.length && !loading">
                    <div class="text-[12px] text-gray-500">Tidak ada mahasiswa yang cocok.</div>
                </template>

                <template x-if="loading">
                    <div class="text-[12px] text-gray-500">Mencari mahasiswa...</div>
                </template>

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
                    <input type="checkbox" name="status" value="1"
                        class="w-4 h-4 rounded border-gray-300 text-brand focus:ring-brand"
                        {{ old('status', true) ? 'checked' : '' }}>
                    <span class="text-[13px] font-medium text-gray-700">Anggota Aktif</span>
                </label>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-50">
                <a href="{{ route('ormawa.anggota.index', $ormawa) }}"
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

    <script>
        function anggotaSearch(searchUrl, initialSelected) {
            return {
                query: '',
                loading: false,
                results: [],
                selectedUser: initialSelected,

                init() {
                    if (this.selectedUser) {
                        this.query = this.selectedUser.nama;
                    }
                },

                async fetchResults() {
                    if (this.query.length < 2) {
                        this.results = [];
                        return;
                    }

                    this.loading = true;
                    try {
                        const url = new URL(searchUrl, window.location.origin);
                        url.searchParams.set('search', this.query);

                        const response = await fetch(url.toString(), {
                            headers: {
                                'Accept': 'application/json',
                            },
                        });

                        if (!response.ok) {
                            this.results = [];
                            return;
                        }

                        const payload = await response.json();
                        this.results = payload.data || [];
                    } catch (error) {
                        console.error(error);
                        this.results = [];
                    } finally {
                        this.loading = false;
                    }
                },

                select(user) {
                    this.selectedUser = user;
                    this.query = `${user.nama} (${user.nim ?? user.email})`;
                    this.results = [];
                },
            };
        }
    </script>
</x-app-layout>
