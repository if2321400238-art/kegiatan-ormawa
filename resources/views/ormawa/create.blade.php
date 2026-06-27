<x-app-layout>
    <x-slot name="title">Tambah Ormawa</x-slot>

    {{-- Top Header Section --}}
    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Tambah Ormawa</h2>
        <p class="text-[12px] text-gray-500">Daftarkan organisasi mahasiswa baru ke dalam sistem</p>
    </div>

    {{-- Form Container Card --}}
    <div class="max-w-2xl bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <x-ormawa.form 
            :submitRoute="$submitRoute" 
            :backRoute="$backRoute" 
            :dosenList="$dosenList" 
            :fakultas="$fakultas" 
        />
    </div>
</x-app-layout>
