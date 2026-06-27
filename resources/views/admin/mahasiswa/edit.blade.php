<x-app-layout>
    <x-slot name="title">Edit Mahasiswa</x-slot>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <a href="{{ route('admin.mahasiswa.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="ti ti-arrow-left"></i>
                </a>
                Edit Mahasiswa
            </h2>
            <p class="text-[12px] text-gray-500 mt-1">Perbarui identitas mahasiswa, jabatan, dan status organisasinya</p>
        </div>
    </div>

    <div class="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden max-w-5xl">
        <form action="{{ route('admin.mahasiswa.update', $mahasiswa->id) }}" method="POST">
            @csrf
            @method('PATCH')

            @include('admin.mahasiswa._form')

            <div class="p-4 sm:p-6 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                <a href="{{ route('admin.mahasiswa.index') }}" class="px-4 py-2 text-[13px] font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-sm">
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
