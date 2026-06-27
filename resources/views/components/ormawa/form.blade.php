@props([
    'submitRoute',
    'backRoute',
    'ormawa' => null,
    'dosenList' => [],
    'fakultas' => []
])

@php
    $isEdit = !is_null($ormawa) && $ormawa->exists;
    $kategori = old('kategori_organisasi', $ormawa?->kategori_organisasi ?? '');
    $tingkat = old('tingkat_organisasi', $ormawa?->tingkat_organisasi ?? '');
    
    // Prepare initial data for search component
    $initialKetuaId = old('user_id', $ormawa?->user_id ?? '');
    $initialKetuaName = old('nama_ketua_text', $ormawa?->user?->nama ?? '');
    $initialKetuaNim = $ormawa?->user?->nim ?? '';
    $initialKetuaEmail = $ormawa?->user?->email ?? '';
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
            '{{ $initialKetuaId }}', 
            '{{ $initialKetuaName }}',
            '{{ $initialKetuaNim }}',
            '{{ $initialKetuaEmail }}',
            '{{ route('bauak.ormawa.search-mahasiswa') }}'
        )" 
        @click.outside="showResults = false"
    >
        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Pilih Ketua Organisasi</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                <i class="ti ti-user text-gray-400"></i>
            </div>
            <input type="text" x-model="searchInput" @input="search()" @focus="onFocus()"
                placeholder="Cari nama atau NIM ketua..."
                class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors"
                autocomplete="off">
            <input type="hidden" name="user_id" :value="selectedId" required>
            
            {{-- Search Results Dropdown --}}
            <div x-show="showResults && results.length > 0"
                class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-30 max-h-64 overflow-y-auto"
                style="display: none;"
                x-transition
            >
                <template x-for="mahasiswa in results" :key="mahasiswa.id">
                    <button type="button" @click="selectMahasiswa(mahasiswa)"
                        class="w-full text-left px-4 py-2.5 hover:bg-gray-50 border-b border-gray-100 last:border-0 transition text-[13px] block">
                        <div class="font-medium text-gray-900" x-text="mahasiswa.nama"></div>
                        <div class="text-xs text-gray-500"
                            x-text="'NIM: ' + (mahasiswa.nim ? mahasiswa.nim : '-') + ' | ' + mahasiswa.email"></div>
                    </button>
                </template>
            </div>
        </div>

        {{-- Error Backend & Frontend Display --}}
        <p x-show="errorMessage" x-text="errorMessage" class="text-red-500 text-xs mt-1" style="display: none;"></p>
        @error('user_id')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror

        {{-- Selected Item Display --}}
        <div x-show="selectedMahasiswa" class="mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-[13px]" style="display: none;">
            <div class="flex justify-between items-start">
                <div>
                    <div class="font-medium text-blue-900" x-text="selectedMahasiswa?.nama"></div>
                    <div class="text-xs text-blue-700"
                        x-text="'NIM: ' + (selectedMahasiswa?.nim ? selectedMahasiswa?.nim : '-') + ' | ' + selectedMahasiswa?.email"></div>
                </div>
                <button type="button" @click="clearSelection()" class="text-blue-500 hover:text-blue-700">
                    <i class="ti ti-x text-sm"></i>
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

    {{-- Dosen Pembina --}}
    <div>
        <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">
            <i class="ti ti-shield-check text-gray-400"></i> Dosen Pembina
        </label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                <i class="ti ti-shield-check text-gray-400"></i>
            </div>
            <select name="pembina_user_id"
                class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors appearance-none"
                required>
                <option value="">-- Pilih Dosen Pembina --</option>
                @forelse($dosenList as $dosen)
                    <option value="{{ $dosen->id }}"
                        {{ old('pembina_user_id', $ormawa?->pembina_user_id ?? '') == $dosen->id ? 'selected' : '' }}>
                        {{ $dosen->nama }} ({{ $dosen->email }})
                    </option>
                @empty
                    <option disabled>Tidak ada dosen pembina yang terdaftar</option>
                @endforelse
            </select>
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                <i class="ti ti-chevron-down"></i>
            </div>
        </div>
        @error('pembina_user_id')
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
        <div x-show="kategori === 'internal' && tingkat === 'fakultas'" style="display: none;" x-transition>
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
