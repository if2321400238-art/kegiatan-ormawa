<x-app-layout>
    <x-slot name="title">Edit Ormawa</x-slot>

    {{-- Top Header Section --}}
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Edit Data Ormawa</h2>
        <p class="text-[12px] text-gray-500">Perbarui informasi organisasi mahasiswa dan pilih dosen pembina</p>
    </div>

    {{-- Form Container Card --}}
    <div class="max-w-2xl bg-white rounded-xl border border-gray-100 shadow-sm overflow-visible">
        <x-ormawa.form 
            :ormawa="$ormawa"
            :submitRoute="$submitRoute" 
            :backRoute="$backRoute" 
            :searchMahasiswaRoute="$searchMahasiswaRoute"
            :dosenList="$dosenList" 
            :fakultas="$fakultas" 
        />
    </div>

    {{-- Info Card --}}
    <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <p class="text-[13px] text-blue-900">
            <i class="ti ti-info-circle mr-2"></i>
            <strong>Catatan:</strong> Perubahan dosen pembina akan diterapkan untuk semua pengajuan kegiatan yang sedang
            menunggu verifikasi dosen.
        </p>
    </div>
</x-app-layout>
