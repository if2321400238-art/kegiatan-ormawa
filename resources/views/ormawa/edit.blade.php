<x-app-layout>
    <x-slot name="title">Edit Ormawa</x-slot>

    {{-- Top Header Section --}}
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Edit Data Ormawa</h2>
        <p class="text-[12px] text-gray-500">Perbarui informasi organisasi mahasiswa</p>
    </div>

    {{-- Form Container Card --}}
    <div class="max-w-2xl bg-white rounded-xl border border-gray-100 shadow-sm overflow-visible">
        <x-ormawa.form 
            :ormawa="$ormawa"
            :submitRoute="$submitRoute" 
            :backRoute="$backRoute" 
            :searchMahasiswaRoute="$searchMahasiswaRoute"
            :fakultas="$fakultas" 
            :programStudi="$programStudi"
        />
    </div>

</x-app-layout>
