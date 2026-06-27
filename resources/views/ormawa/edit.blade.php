<x-app-layout>
    <x-slot name="title">Edit Ormawa</x-slot>

    {{-- Top Header Section --}}
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Edit Data Ormawa</h2>
        <p class="text-[12px] text-gray-500">Perbarui informasi organisasi mahasiswa dan pilih dosen pembina</p>
    </div>

    {{-- Form Container Card --}}
    <div class="max-w-2xl bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        @php
            $updateRoute = auth()->user()->role === 'bauak'
                ? route('bauak.ormawa.update', $ormawa->id)
                : route('admin.ormawa.update', $ormawa->id);
        @endphp
        <form action="{{ $updateRoute }}" method="POST" class="p-6 space-y-4">
            @csrf
            @method('PATCH')

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
                        value="{{ old('nama_ormawa', $ormawa->nama_ormawa) }}" required>
                </div>
                @error('nama_ormawa')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Ketua Ormawa --}}
            <div>
                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Pilih Ketua Organisasi</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                        <i class="ti ti-user text-gray-400"></i>
                    </div>
                    <select name="user_id"
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors appearance-none"
                        required>
                        <option value="">-- Pilih Ketua Organisasi --</option>
                        @forelse($mahasiswaList as $mahasiswa)
                            <option value="{{ $mahasiswa->id }}" {{ old('user_id', $ormawa->user_id) == $mahasiswa->id ? 'selected' : '' }}>
                                {{ $mahasiswa->nama }} ({{ $mahasiswa->nim ?? $mahasiswa->email }})
                            </option>
                        @empty
                            <option disabled>Tidak ada mahasiswa yang terdaftar</option>
                        @endforelse
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                        <i class="ti ti-chevron-down"></i>
                    </div>
                </div>
            </div>

            {{-- Dosen Pembina - DROPDOWN --}}
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
                            <option value="{{ $dosen->id }}" {{ old('pembina_user_id', $ormawa->pembina_user_id) == $dosen->id ? 'selected' : '' }}>
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

            <div x-data="{ kategori: '{{ old('kategori_organisasi', $ormawa->kategori_organisasi) }}', tingkat: '{{ old('tingkat_organisasi', $ormawa->tingkat_organisasi) }}' }" x-init="$watch('kategori', value => {
                    if ($refs.tingkatField) {
                        $refs.tingkatField.classList.toggle('hidden', value !== 'internal');
                    }
                    if ($refs.fakultasField) {
                        $refs.fakultasField.classList.toggle('hidden', value !== 'internal' || this.tingkat !== 'fakultas');
                    }
                }); $watch('tingkat', value => {
                    if ($refs.fakultasField) {
                        $refs.fakultasField.classList.toggle('hidden', this.kategori !== 'internal' || value !== 'fakultas');
                    }
                })">
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
                            <option value="internal" {{ old('kategori_organisasi', $ormawa->kategori_organisasi) === 'internal' ? 'selected' : '' }}>Internal Kampus</option>
                            <option value="eksternal" {{ old('kategori_organisasi', $ormawa->kategori_organisasi) === 'eksternal' ? 'selected' : '' }}>Eksternal</option>
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
                <div>
                    <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Tingkat Organisasi</label>
                    <div id="tingkat-organisasi-field" x-ref="tingkatField" class="relative {{ old('kategori_organisasi', $ormawa->kategori_organisasi) !== 'internal' ? 'hidden' : '' }}">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none z-10">
                            <i class="ti ti-layer-plus text-gray-400"></i>
                        </div>
                        <select name="tingkat_organisasi"
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors appearance-none">
                            <option value="">-- Pilih Tingkat Organisasi --</option>
                            <option value="universitas" {{ old('tingkat_organisasi', $ormawa->tingkat_organisasi) === 'universitas' ? 'selected' : '' }}>Universitas</option>
                            <option value="fakultas" {{ old('tingkat_organisasi', $ormawa->tingkat_organisasi) === 'fakultas' ? 'selected' : '' }}>Fakultas</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-400">
                            <i class="ti ti-chevron-down"></i>
                        </div>
                    </div>
                    @error('tingkat_organisasi')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div id="fakultas-field" x-ref="fakultasField" class="relative mt-4 {{ old('tingkat_organisasi', $ormawa->tingkat_organisasi) === 'fakultas' ? '' : 'hidden' }}">
                    <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Fakultas</label>
                    <select name="fakultas_id"
                        class="w-full pl-4 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors appearance-none">
                        <option value="">-- Pilih Fakultas --</option>
                        @foreach($fakultas as $f)
                            <option value="{{ $f->id }}" {{ old('fakultas_id', $ormawa->fakultas_id) == $f->id ? 'selected' : '' }}>{{ $f->nama }}</option>
                        @endforeach
                    </select>
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
                        placeholder="Misal: 081234567xxx"
                        value="{{ old('kontak', $ormawa->kontak) }}">
                </div>
                @error('kontak')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div>
                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Deskripsi</label>
                <textarea name="deskripsi"
                    class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors"
                    placeholder="Deskripsi singkat tentang organisasi..." rows="3">{{ old('deskripsi', $ormawa->deskripsi) }}</textarea>
                @error('deskripsi')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-50">
                @php
                    $backRoute = auth()->user()->role === 'bauak'
                        ? route('bauak.ormawa.index')
                        : route('admin.ormawa.index');
                @endphp
                <a href="{{ $backRoute }}"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-[13px] font-medium hover:bg-gray-200 transition text-center">
                    Batal
                </a>

                <button type="submit"
                    class="px-4 py-2 bg-brand text-white rounded-lg text-[13px] font-medium hover:bg-brand-active transition shadow-sm flex items-center gap-1.5">
                    <i class="ti ti-device-floppy"></i> Perbarui Data
                </button>
            </div>

        </form>
    </div>

    {{-- Info Card --}}
    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-[13px] text-blue-900">
            <i class="ti ti-info-circle mr-2"></i>
            <strong>Catatan:</strong> Perubahan dosen pembina akan diterapkan untuk semua pengajuan kegiatan yang sedang menunggu verifikasi dosen.
        </p>
    </div>
</x-app-layout>
