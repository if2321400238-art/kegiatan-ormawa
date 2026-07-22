<x-app-layout>
    <x-slot name="title">Tambah Fakultas</x-slot>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <a href="{{ route('admin.fakultas.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="ti ti-arrow-left"></i>
                </a>
                Tambah Fakultas
            </h2>
            <p class="text-[12px] text-gray-500 mt-1">Tambahkan fakultas baru dan tetapkan dekan</p>
        </div>
    </div>

    {{-- Card Form --}}
    <div class="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden max-w-3xl">
        <form action="{{ route('admin.fakultas.store') }}" method="POST">
            @csrf
            
            <div class="p-6 space-y-6">
                {{-- Nama Fakultas --}}
                <div class="space-y-1.5">
                    <label for="nama" class="block text-[13px] font-medium text-gray-700">Nama Fakultas <span class="text-danger">*</span></label>
                    <input type="text" 
                        id="nama" 
                        name="nama" 
                        value="{{ old('nama') }}" 
                        class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5 transition-colors"
                        placeholder="Contoh: Fakultas Teknik"
                        required>
                    @error('nama')
                        <p class="text-danger text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Dekan --}}
                <div class="space-y-1.5">
                    <label for="dekan_user_id" class="block text-[13px] font-medium text-gray-700">Pilih Dekan (Opsional)</label>
                    <select id="dekan_user_id" 
                            name="dekan_user_id" 
                            class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2.5 transition-colors select2">
                        <option value="">-- Kosongkan jika belum ada --</option>
                        @foreach($dekanList as $dekan)
                            <option value="{{ $dekan->id }}" {{ old('dekan_user_id') == $dekan->id ? 'selected' : '' }}>
                                {{ $dekan->nama }} ({{ $dekan->email }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-500 mt-1">Hanya user dengan role Dekan yang muncul di daftar ini.</p>
                    @error('dekan_user_id')
                        <p class="text-danger text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Footer Action --}}
            <div class="p-4 sm:p-6 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.fakultas.index') }}" class="px-4 py-2 text-[13px] font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-sm">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 text-[13px] font-medium text-white bg-brand rounded-lg hover:bg-brand-active transition-colors shadow-sm flex items-center gap-2">
                    <i class="ti ti-device-floppy"></i>
                    Simpan Fakultas
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
