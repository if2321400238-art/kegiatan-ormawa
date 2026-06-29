<x-app-layout>
    <x-slot name="title">Detail Ormawa</x-slot>

    {{-- Top Header Section --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">{{ $ormawa->nama_ormawa }}</h2>
            <p class="text-[12px] text-gray-500">Detail informasi organisasi mahasiswa</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->role === 'admin' || auth()->user()->id === $ormawa->user_id)
                <a href="{{ route('ormawa.anggota.index', $ormawa->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-[13px] font-medium flex items-center gap-2">
                    <i class="ti ti-users"></i> Kelola Anggota
                </a>
            @endif
            @php
                $editRoute = auth()->user()->role === 'bauak' ? route('bauak.ormawa.edit', $ormawa->id) : route('admin.ormawa.edit', $ormawa->id);
            @endphp
            <a href="{{ $editRoute }}" class="px-4 py-2 bg-warning text-white rounded-lg hover:bg-orange-600 transition text-[13px] font-medium flex items-center gap-2">
                <i class="ti ti-edit"></i> Edit Data
            </a>
        </div>
    </div>

    {{-- Detail Cards Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Main Info Card --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Informasi Dasar</h3>

                {{-- Nama Ormawa --}}
                <div>
                    <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Nama Ormawa</p>
                    <p class="text-[13px] text-gray-900 flex items-center gap-2">
                        <i class="ti ti-building text-brand"></i> {{ $ormawa->nama_ormawa }}
                    </p>
                </div>

                {{-- Ketua --}}
                <div>
                    <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Ketua</p>
                    <p class="text-[13px] text-gray-900 flex items-center gap-2">
                        <i class="ti ti-user text-brand"></i> {{ $ormawa->ketua }}
                    </p>
                </div>

                {{-- Pembina --}}
                <div>
                    <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Dosen Pembina</p>
                    <p class="text-[13px] text-gray-900 flex items-center gap-2">
                        <i class="ti ti-shield-check text-brand"></i> {{ $ormawa->pembina }}
                    </p>
                </div>

                {{-- Kategori & Tingkat Organisasi --}}
                <div>
                    <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Kategori Organisasi</p>
                    <div class="flex items-center gap-2">
                        @if($ormawa->kategori_organisasi === 'internal')
                            <span class="badge badge-info">Internal Kampus</span>
                        @else
                            <span class="badge badge-warning">Eksternal</span>
                        @endif
                    </div>
                </div>

                @if($ormawa->kategori_organisasi === 'internal')
                    <div>
                        <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Tingkat Organisasi</p>
                        <div class="flex items-center gap-2">
                            @if($ormawa->tingkat_organisasi === 'universitas')
                                <span class="badge badge-info">Universitas</span>
                            @else
                                <span class="badge badge-primary">Fakultas</span>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Kontak --}}
                <div>
                    <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Kontak</p>
                    <p class="text-[13px] text-gray-900 flex items-center gap-2">
                        <i class="ti ti-phone text-brand"></i> {{ $ormawa->kontak ?? '-' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Activity Card --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Statistik</h3>

                {{-- Total Pengajuan --}}
                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                    <div>
                        <p class="text-[11px] text-gray-500 uppercase font-semibold">Total Pengajuan</p>
                        <p class="text-lg font-bold text-gray-900">{{ $ormawa->pengajuanKegiatan->count() }}</p>
                    </div>
                    <i class="ti ti-file-text text-2xl text-blue-500 opacity-20"></i>
                </div>

                {{-- Disetujui --}}
                <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                    <div>
                        <p class="text-[11px] text-gray-500 uppercase font-semibold">Disetujui</p>
                        <p class="text-lg font-bold text-gray-900">{{ $ormawa->pengajuanKegiatan->where('status', 'disetujui')->count() }}</p>
                    </div>
                    <i class="ti ti-check text-2xl text-green-500 opacity-20"></i>
                </div>

                {{-- Menunggu Verifikasi --}}
                <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                    <div>
                        <p class="text-[11px] text-gray-500 uppercase font-semibold">Menunggu Verifikasi</p>
                        <p class="text-lg font-bold text-gray-900">
                            {{ $ormawa->pengajuanKegiatan->whereIn('status', ['menunggu_dosen', 'menunggu_dekan', 'menunggu_bauak', 'menunggu_warek3', 'menunggu_rektor', 'menunggu_pp'])->count() }}
                        </p>
                    </div>
                    <i class="ti ti-clock-down text-2xl text-yellow-500 opacity-20"></i>
                </div>

                {{-- Ditolak --}}
                <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                    <div>
                        <p class="text-[11px] text-gray-500 uppercase font-semibold">Ditolak</p>
                        <p class="text-lg font-bold text-gray-900">{{ $ormawa->pengajuanKegiatan->whereIn('status', \App\Models\PengajuanKegiatan::REJECTED_STATUSES)->count() }}</p>
                    </div>
                    <i class="ti ti-x text-2xl text-red-500 opacity-20"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Deskripsi Card --}}
    @if($ormawa->deskripsi)
        <div class="mt-6 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-3">Deskripsi</h3>
            <p class="text-[13px] text-gray-700 leading-relaxed">{{ $ormawa->deskripsi }}</p>
        </div>
    @endif

    {{-- Back Button --}}
    @php
        $backRoute = auth()->user()->role === 'bauak' ? route('bauak.ormawa.index') : route('admin.ormawa.index');
    @endphp
    <div class="mt-6">
        <a href="{{ $backRoute }}" class="inline-flex items-center gap-2 text-[13px] font-medium text-brand hover:text-brand-active">
            <i class="ti ti-arrow-left"></i> Kembali ke Daftar Ormawa
        </a>
    </div>
</x-app-layout>
