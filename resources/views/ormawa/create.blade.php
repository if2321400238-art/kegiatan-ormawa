<x-app-layout>
    <x-slot name="title">Tambah Ormawa</x-slot>

    {{-- Top Header Section --}}
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Tambah Ormawa</h2>
        <p class="text-[12px] text-gray-500">Daftarkan organisasi mahasiswa baru ke dalam sistem</p>
    </div>

    {{-- Form Container Card --}}
    <div class="max-w-2xl bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <form action="{{ route('admin.ormawa.store') }}" method="POST" class="p-6 space-y-4">
            @csrf

            {{-- Nama Ormawa --}}
            <div>
                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama Ormawa</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ti ti-building text-gray-400"></i>
                    </div>
                    <input type="text" name="nama_ormawa"
                           class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors"
                           placeholder="Misal: Badan Eksekutif Mahasiswa"
                           required>
                </div>
            </div>

            {{-- Ketua Ormawa --}}
            <div>
                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama Ketua</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ti ti-user text-gray-400"></i>
                    </div>
                    <input type="text" name="ketua"
                           class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors"
                           placeholder="Misal: Ahmad Fulan"
                           required>
                </div>
            </div>

            {{-- Pembina Ormawa --}}
            <div>
                <label class="block text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama Pembina</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="ti ti-shield-check text-gray-400"></i>
                    </div>
                    <input type="text" name="pembina"
                           class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors"
                           placeholder="Misal: Dr. Indah Permata, M.Kom"
                           required>
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
                           required>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-50">
                <a href="{{ route('admin.ormawa.index') }}"
                   class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-[13px] font-medium hover:bg-gray-200 transition text-center">
                    Batal
                </a>

                <button type="submit"
                        class="px-4 py-2 bg-brand text-white rounded-lg text-[13px] font-medium hover:bg-brand-active transition shadow-sm flex items-center gap-1.5">
                    <i class="ti ti-device-floppy"></i> Simpan Data
                </button>
            </div>

        </form>
    </div>
</x-app-layout>