@php
    $mahasiswa = $mahasiswa ?? null;
@endphp

<div class="p-6 space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="space-y-1.5">
            <label for="nama" class="block text-[13px] font-medium text-gray-700">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" id="nama" name="nama" value="{{ old('nama', $mahasiswa?->nama) }}"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5 transition-colors"
                placeholder="Contoh: Rizky Pratama" required>
            @error('nama')
                <p class="text-danger text-[11px] mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-1.5">
            <label for="nim" class="block text-[13px] font-medium text-gray-700">NIM <span class="text-danger">*</span></label>
            <input type="text" id="nim" name="nim" value="{{ old('nim', $mahasiswa?->nim) }}"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5 transition-colors"
                placeholder="Contoh: 210001" required>
            @error('nim')
                <p class="text-danger text-[11px] mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-1.5">
            <label for="username" class="block text-[13px] font-medium text-gray-700">Username <span class="text-danger">*</span></label>
            <input type="text" id="username" name="username" value="{{ old('username', $mahasiswa?->username) }}"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5 transition-colors"
                placeholder="Contoh: mhs_210001" required>
            @error('username')
                <p class="text-danger text-[11px] mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-1.5">
            <label for="email" class="block text-[13px] font-medium text-gray-700">Email <span class="text-danger">*</span></label>
            <input type="email" id="email" name="email" value="{{ old('email', $mahasiswa?->email) }}"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5 transition-colors"
                placeholder="Contoh: 210001@student.unuja.ac.id" required>
            @error('email')
                <p class="text-danger text-[11px] mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-1.5">
            <label for="password" class="block text-[13px] font-medium text-gray-700">
                {{ $mahasiswa ? 'Kata Sandi Baru' : 'Kata Sandi' }}
                @unless($mahasiswa)<span class="text-danger">*</span>@endunless
            </label>
            <input type="password" id="password" name="password"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5 transition-colors"
                placeholder="{{ $mahasiswa ? 'Biarkan kosong jika tidak diubah' : '' }}" {{ $mahasiswa ? '' : 'required' }}>
            @error('password')
                <p class="text-danger text-[11px] mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="space-y-1.5">
            <label for="no_hp" class="block text-[13px] font-medium text-gray-700">No. HP / WhatsApp</label>
            <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $mahasiswa?->no_hp) }}"
                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5 transition-colors"
                placeholder="Contoh: 08123456789">
            @error('no_hp')
                <p class="text-danger text-[11px] mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="flex items-center">
        <input id="is_active" name="is_active" type="checkbox" value="1"
            {{ old('is_active', $mahasiswa?->is_active ?? true) ? 'checked' : '' }}
            class="w-4 h-4 text-brand bg-gray-100 border-gray-300 rounded focus:ring-brand focus:ring-2">
        <label for="is_active" class="ml-2 text-[13px] font-medium text-gray-700">Akun Aktif</label>
    </div>

    <hr class="border-gray-100">

    <div>
        <div class="flex items-center justify-between gap-3 mb-3">
            <div>
                <h3 class="text-[14px] font-semibold text-gray-900">Keanggotaan Organisasi</h3>
                <p class="text-[12px] text-gray-500">Pilih organisasi, jabatan, dan status aktif mahasiswa.</p>
            </div>
        </div>

        <div class="border border-gray-100 rounded-xl overflow-hidden">
            <div class="hidden md:grid grid-cols-[44px_1.3fr_180px_110px] gap-3 bg-gray-50 px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                <div>Pilih</div>
                <div>Ormawa</div>
                <div>Jabatan</div>
                <div>Status</div>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($ormawaList as $ormawa)
                    @php
                        $pivot = $mahasiswa?->ormawas?->firstWhere('id', $ormawa->id)?->pivot;
                        $selected = old("memberships.{$ormawa->id}.selected", $pivot ? '1' : null);
                        $jabatan = old("memberships.{$ormawa->id}.jabatan", $pivot?->jabatan ?? 'anggota');
                        $status = old("memberships.{$ormawa->id}.status", $pivot ? (string) (int) $pivot->status : '1');
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-[44px_1.3fr_180px_110px] gap-3 px-4 py-3 items-center">
                        <div>
                            <input type="checkbox" name="memberships[{{ $ormawa->id }}][selected]" value="1"
                                {{ $selected ? 'checked' : '' }}
                                class="w-4 h-4 text-brand bg-gray-100 border-gray-300 rounded focus:ring-brand focus:ring-2">
                        </div>
                        <div>
                            <div class="font-medium text-[13px] text-gray-900">{{ $ormawa->nama_ormawa }}</div>
                            <div class="text-[11px] text-gray-500">{{ ucfirst($ormawa->kategori_organisasi) }}{{ $ormawa->tingkat_organisasi ? ' - ' . ucfirst($ormawa->tingkat_organisasi) : '' }}</div>
                        </div>
                        <div>
                            <input type="text" name="memberships[{{ $ormawa->id }}][jabatan]" value="{{ $jabatan }}"
                                class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2 transition-colors"
                                placeholder="anggota">
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="memberships[{{ $ormawa->id }}][status]" value="1"
                                {{ $status ? 'checked' : '' }}
                                class="w-4 h-4 text-brand bg-gray-100 border-gray-300 rounded focus:ring-brand focus:ring-2">
                            <span class="text-[12px] text-gray-700">Aktif</span>
                        </div>
                    </div>
                @empty
                    <div class="px-4 py-6 text-center text-[13px] text-gray-500">Belum ada data Ormawa.</div>
                @endforelse
            </div>
        </div>
        @error('memberships')
            <p class="text-danger text-[11px] mt-1">{{ $message }}</p>
        @enderror
    </div>
</div>
