<x-app-layout>
    <x-slot name="title">Edit Akun Dekan</x-slot>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <a href="{{ route('admin.dekan.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="ti ti-arrow-left"></i>
                </a>
                Edit Akun Dekan
            </h2>
            <p class="text-[12px] text-gray-500 mt-1">Perbarui profil akun dan penugasan fakultas</p>
        </div>
    </div>

    {{-- Card Form --}}
    <div class="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden max-w-3xl">
        <form action="{{ route('admin.dekan.update', $dekan->id) }}" method="POST">
            @csrf
            @method('PATCH')
            
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    {{-- Nama --}}
                    <div class="space-y-1.5">
                        <label for="nama" class="block text-[13px] font-medium text-gray-700">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" 
                            id="nama" 
                            name="nama" 
                            value="{{ old('nama', $dekan->nama) }}" 
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5 transition-colors"
                            required>
                        @error('nama')
                            <p class="text-danger text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Username --}}
                    <div class="space-y-1.5">
                        <label for="username" class="block text-[13px] font-medium text-gray-700">Username <span class="text-danger">*</span></label>
                        <input type="text" 
                            id="username" 
                            name="username" 
                            value="{{ old('username', $dekan->username) }}" 
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5 transition-colors"
                            required>
                        @error('username')
                            <p class="text-danger text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="space-y-1.5">
                        <label for="email" class="block text-[13px] font-medium text-gray-700">Email <span class="text-danger">*</span></label>
                        <input type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email', $dekan->email) }}" 
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5 transition-colors"
                            required>
                        @error('email')
                            <p class="text-danger text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="space-y-1.5">
                        <label for="password" class="block text-[13px] font-medium text-gray-700">Kata Sandi Baru</label>
                        <input type="password" 
                            id="password" 
                            name="password" 
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5 transition-colors"
                            placeholder="Biarkan kosong jika tidak diubah">
                        @error('password')
                            <p class="text-danger text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- No HP --}}
                    <div class="space-y-1.5">
                        <label for="no_hp" class="block text-[13px] font-medium text-gray-700">No. HP / WhatsApp</label>
                        <input type="text" 
                            id="no_hp" 
                            name="no_hp" 
                            value="{{ old('no_hp', $dekan->no_hp) }}" 
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5 transition-colors">
                        @error('no_hp')
                            <p class="text-danger text-[11px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <hr class="border-gray-100">

                {{-- Fakultas --}}
                <div class="space-y-1.5">
                    <label for="fakultas_id" class="block text-[13px] font-medium text-gray-700">Tugaskan ke Fakultas (Opsional)</label>
                    <select id="fakultas_id" 
                            name="fakultas_id" 
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5 transition-colors select2">
                        <option value="">-- Kosongkan jika belum terhubung --</option>
                        @foreach($fakultasList as $fakultas)
                            <option value="{{ $fakultas->id }}" {{ old('fakultas_id', $dekan->fakultas_id) == $fakultas->id ? 'selected' : '' }}>
                                {{ $fakultas->nama }} 
                                @if($fakultas->dekan && $fakultas->dekan->id !== $dekan->id)
                                    (Saat ini: {{ $fakultas->dekan->nama }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-500 mt-1">Mengubah pilihan ini akan otomatis memindahkan kepemimpinan fakultas ke akun ini.</p>
                    @error('fakultas_id')
                        <p class="text-danger text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status Aktif --}}
                <div class="flex items-center">
                    <input id="is_active" name="is_active" type="checkbox" value="1" {{ old('is_active', $dekan->is_active) ? 'checked' : '' }} class="w-4 h-4 text-brand bg-gray-100 border-gray-300 rounded focus:ring-brand focus:ring-2">
                    <label for="is_active" class="ml-2 text-[13px] font-medium text-gray-700">Akun Aktif</label>
                </div>
            </div>

            {{-- Footer Action --}}
            <div class="p-4 sm:p-6 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.dekan.index') }}" class="px-4 py-2 text-[13px] font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-sm">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 text-[13px] font-medium text-white bg-brand rounded-lg hover:bg-brand-active transition-colors shadow-sm flex items-center gap-2">
                    <i class="ti ti-device-floppy"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
