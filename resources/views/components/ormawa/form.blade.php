@props([
    'submitRoute',
    'backRoute',
    'searchMahasiswaRoute',
    'ormawa' => null,
    'fakultas' => [],
    'programStudi' => []
])

@php
    $isEdit = !is_null($ormawa) && $ormawa->exists;
    $kategori = old('kategori_organisasi', $ormawa?->kategori_organisasi ?? '');
    $tingkat = old('tingkat_organisasi', $ormawa?->tingkat_organisasi ?? '');
    $selectedProdiId = old('prodi_id', $ormawa?->prodi_id ?? '');
    $selectedProdi = collect($programStudi)->firstWhere('id', (int) $selectedProdiId);

    // Prepare initial data for search component
    $initialKetuaId = old('user_id', $ormawa?->user_id ?? '');
    $initialKetuaName = old('nama_ketua_text', $ormawa?->user?->nama ?? '');
    $initialKetuaNim = $ormawa?->user?->nim ?? '';
    $initialKetuaProgram = $ormawa?->user?->program_studi ?? '';
@endphp

<form action="{{ $submitRoute }}" method="POST" class="p-6 space-y-4">
    @csrf
    @if($isEdit)
        @method('PATCH')
    @endif

    {{-- Nama Ormawa --}}
    <div>
        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama Ormawa</label>
        <div class="relative flex items-center">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <i class="ti ti-building text-gray-400 text-base"></i>
            </div>
            <input type="text" name="nama_ormawa"
                class="w-full !pl-11 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors"
                placeholder="Misal: Badan Eksekutif Mahasiswa"
                value="{{ old('nama_ormawa', $ormawa?->nama_ormawa ?? '') }}" required>
        </div>
        @error('nama_ormawa')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Ketua Ormawa - Interactive Search via Extracted AlpineJS Component --}}
    <div x-data="mahasiswaSearch(
            @js((string) $initialKetuaId),
            @js($initialKetuaName),
            @js($initialKetuaNim),
            @js($initialKetuaProgram),
            @js($searchMahasiswaRoute)
        )"
        @click.outside="showResults = false"
    >
        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Pilih Ketua Organisasi</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                <i class="ti ti-user text-gray-400"></i>
            </div>
            <input type="text" x-model="searchInput" @input="onInput()" @focus="onFocus()" @keydown.escape="showResults = false"
                placeholder="Ketik minimal 2 karakter nama atau NIM..."
                class="w-full pl-10 pr-12 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors"
                autocomplete="off">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                <i x-show="loading" class="ti ti-loader-2 animate-spin text-brand"></i>
                <button x-show="selectedMahasiswa && !loading" type="button" @click="clearSelection()"
                    class="text-gray-400 hover:text-gray-700" title="Hapus pilihan">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <input type="hidden" name="ketua_nim" :value="selectedMahasiswa?.nim || ''">
            <input type="hidden" name="user_id" :value="selectedId">

            {{-- Search Results Dropdown --}}
            <div x-show="showResults && !selectedMahasiswa"
                class="absolute top-full left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-xl z-40 overflow-hidden"
                style="display: none;"
                x-transition.opacity
            >
                <div x-show="loading" class="px-4 py-5 text-center text-[12px] text-gray-500">
                    <i class="ti ti-loader-2 animate-spin text-brand mr-1"></i> Mencari data mahasiswa...
                </div>

                <div x-show="!loading && results.length" class="max-h-72 overflow-y-auto divide-y divide-gray-100">
                    <template x-for="mahasiswa in results" :key="mahasiswa.nim">
                        <button type="button" @click="selectMahasiswa(mahasiswa)"
                            class="w-full flex items-center gap-3 px-4 py-3 hover:bg-blue-50/60 transition-colors text-left">
                            <div class="w-9 h-9 rounded-full bg-brand/10 text-brand flex items-center justify-center shrink-0">
                                <i class="ti ti-user"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-[13px] text-gray-900 truncate" x-text="mahasiswa.nama"></div>
                                <div class="text-[11px] text-gray-500 truncate"
                                    x-text="['NIM: ' + (mahasiswa.nim || '-'), mahasiswa.program_studi].filter(Boolean).join(' | ')"></div>
                            </div>
                            <i class="ti ti-chevron-right text-gray-300"></i>
                        </button>
                    </template>
                </div>

                <div x-show="!loading && searched && !results.length && !errorMessage" class="px-4 py-6 text-center">
                    <i class="ti ti-user-search text-2xl text-gray-300"></i>
                    <div class="mt-1 text-[12px] font-medium text-gray-700">Mahasiswa tidak ditemukan</div>
                    <div class="text-[11px] text-gray-500">Periksa kembali nama atau NIM yang dicari.</div>
                </div>

                <div x-show="!loading && errorMessage" class="px-4 py-4 bg-red-50 text-red-700 text-[12px]">
                    <i class="ti ti-alert-circle mr-1"></i><span x-text="errorMessage"></span>
                </div>
            </div>
        </div>

        {{-- Error Backend & Frontend Display --}}
        @error('user_id')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
        @error('ketua_nim')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror

        {{-- Selected Item Display --}}
        <div x-show="selectedMahasiswa" x-transition class="mt-3 p-4 bg-blue-50 border border-blue-200 rounded-xl" style="display: none;">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-600 text-white flex items-center justify-center shrink-0">
                    <i class="ti ti-user-check"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-[10px] font-semibold uppercase tracking-wider text-blue-600">Ketua dipilih</div>
                    <div class="font-semibold text-[14px] text-blue-950" x-text="selectedMahasiswa?.nama"></div>
                    <div class="text-[12px] text-blue-700"
                        x-text="[
                            'NIM: ' + (selectedMahasiswa?.nim || '-'),
                            selectedMahasiswa?.program_studi,
                        ].filter(Boolean).join(' | ')"></div>
                </div>
                <button type="button" @click="clearSelection()" class="p-1 text-blue-500 hover:text-blue-800" title="Ganti ketua">
                    <i class="ti ti-x"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Periode Kepengurusan --}}
    <div>
        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Periode Kepengurusan</label>
        <div class="relative flex items-center">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <i class="ti ti-calendar-time text-gray-400 text-base"></i>
            </div>
            <input type="text" name="periode"
                class="w-full pl-11 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors"
                placeholder="Misal: 2024-2025"
                value="{{ old('periode', $ormawa?->periode ?? '') }}">
        </div>
        <p class="text-[11px] text-gray-500 mt-1">Kosongkan jika tidak ada batasan periode.</p>
        @error('periode')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Kategori & Tingkat & Fakultas --}}
    <div x-data="{
            kategori: '{{ $kategori }}',
            tingkat: '{{ $tingkat }}'
        }"
        class="space-y-4"
    >
        {{-- Kategori Organisasi --}}
        <div>
            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Kategori Organisasi</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                    <i class="ti ti-building-factory text-gray-400"></i>
                </div>
                <select name="kategori_organisasi" x-model="kategori"
                    class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors appearance-none"
                    required>
                    <option value="">-- Pilih Kategori Organisasi --</option>
                    <option value="internal">Internal Kampus</option>
                    <option value="eksternal">Eksternal</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                    <i class="ti ti-chevron-down"></i>
                </div>
            </div>
            @error('kategori_organisasi')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tingkat Organisasi --}}
        <div x-show="kategori === 'internal'" style="display: none;" x-transition>
            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Tingkat Organisasi</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                    <i class="ti ti-layer-plus text-gray-400"></i>
                </div>
                <select name="tingkat_organisasi" x-model="tingkat"
                    class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors appearance-none">
                    <option value="">-- Pilih Tingkat Organisasi --</option>
                    <option value="universitas">Universitas</option>
                    <option value="fakultas">Fakultas</option>
                    <option value="prodi">Program Studi</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                    <i class="ti ti-chevron-down"></i>
                </div>
            </div>
            @error('tingkat_organisasi')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Fakultas --}}
        <div x-show="kategori === 'internal' && ['fakultas', 'prodi'].includes(tingkat)" style="display: none;" x-transition>
            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Fakultas</label>
            <div class="relative">
                <select name="fakultas_id"
                    class="w-full pl-4 pr-10 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors appearance-none">
                    <option value="">-- Pilih Fakultas --</option>
                    @foreach ($fakultas as $f)
                        <option value="{{ $f->id }}" {{ old('fakultas_id', $ormawa?->fakultas_id ?? '') == $f->id ? 'selected' : '' }}>
                            {{ $f->nama }}
                        </option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                    <i class="ti ti-chevron-down"></i>
                </div>
            </div>
            @error('fakultas_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div x-show="kategori === 'internal' && tingkat === 'prodi'" style="display: none;" x-transition
            x-data="{ prodiId: @js((string) $selectedProdiId), lainnyaId: @js((string) (collect($programStudi)->firstWhere('is_lainnya', true)?->id ?? '')) }">
            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Program Studi</label>
            <select name="prodi_id" x-model="prodiId" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand">
                <option value="">-- Pilih Program Studi --</option>
                @foreach($programStudi->groupBy(fn ($prodi) => $prodi->fakultas?->nama ?? 'Lainnya') as $namaFakultas => $daftarProdi)
                    <optgroup label="{{ $namaFakultas }}">
                        @foreach($daftarProdi as $prodi)
                            <option value="{{ $prodi->id }}">{{ $prodi->nama }} ({{ $prodi->kode }})</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
            @error('prodi_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            <div x-show="prodiId === lainnyaId" class="mt-3">
                <input type="text" name="program_studi_lainnya" value="{{ old('program_studi_lainnya', $selectedProdi?->is_lainnya ? $ormawa?->program_studi : '') }}"
                    class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand"
                    placeholder="Tuliskan nama program studi">
                @error('program_studi_lainnya')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    {{-- Kontak --}}
    <div>
        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Kontak / No. WhatsApp</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="ti ti-phone text-gray-400"></i>
            </div>
            <input type="text" name="kontak"
                class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors"
                placeholder="Misal: 081234567xxx" value="{{ old('kontak', $ormawa?->kontak ?? '') }}" required>
        </div>
        @error('kontak')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- Deskripsi (only on edit) --}}
    @if($isEdit)
        <div>
            <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Deskripsi</label>
            <textarea name="deskripsi"
                class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors"
                placeholder="Deskripsi singkat tentang organisasi..." rows="3">{{ old('deskripsi', $ormawa?->deskripsi ?? '') }}</textarea>
            @error('deskripsi')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    @endif

    {{-- Action Buttons --}}
    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-50">
        <a href="{{ $backRoute }}"
            class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-[13px] font-medium hover:bg-gray-200 transition text-center">
            Batal
        </a>

        <button type="submit"
            class="px-4 py-2 bg-brand text-white rounded-lg text-[13px] font-medium hover:bg-brand-active transition shadow-sm flex items-center gap-1.5">
            <i class="ti ti-device-floppy"></i> {{ $isEdit ? 'Perbarui Data' : 'Simpan Data' }}
        </button>
    </div>
</form>
