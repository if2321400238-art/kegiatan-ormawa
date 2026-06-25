<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Verifikasi Pengajuan Kegiatan
            </h2>

            <a href="{{ route('dosen.verifikasi.index') }}"
               class="text-blue-600 hover:text-blue-800">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">

                    {{-- Detail Pengajuan --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">
                                Informasi Kegiatan
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">
                                        Judul Kegiatan
                                    </label>
                                    <p class="text-gray-900">
                                        {{ $pengajuan->judul_kegiatan }}
                                    </p>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-500">
                                        Tujuan Kegiatan
                                    </label>
                                    <p class="text-gray-900">
                                        {{ $pengajuan->tujuan_kegiatan }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">
                                            Tanggal Kegiatan
                                        </label>
                                        <p class="text-gray-900">
                                            {{ $pengajuan->tanggal_mulai->format('d M Y') }}
                                            -
                                            {{ $pengajuan->tanggal_selesai->format('d M Y') }}
                                        </p>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-gray-500">
                                            Lokasi
                                        </label>
                                        <p class="text-gray-900">
                                            {{ $pengajuan->lokasi_kegiatan }}
                                        </p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">
                                            Ketua Pelaksana
                                        </label>
                                        <p class="text-gray-900">
                                            {{ $pengajuan->ketua_pelaksana }}
                                        </p>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-gray-500">
                                            Nama Pemohon
                                        </label>
                                        <p class="text-gray-900">
                                            {{ $pengajuan->nama_pemohon }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Dokumen --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">
                                Dokumen Pendukung
                            </h3>

                            <div class="space-y-3">

                                @if($pengajuan->proposal)
                                    <a href="{{ $pengajuan->proposal->file_url }}"
                                       target="_blank"
                                       class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100">

                                        <div class="flex items-center gap-3">
                                            <i class="ti ti-file-text text-red-500 text-2xl"></i>
                                            <span class="font-medium">
                                                Proposal Kegiatan
                                            </span>
                                        </div>

                                        <span class="text-blue-600">
                                            Lihat →
                                        </span>
                                    </a>
                                @endif

                                @if($pengajuan->rab)
                                    <a href="{{ $pengajuan->rab->file_url }}"
                                       target="_blank"
                                       class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100">

                                        <div class="flex items-center gap-3">
                                            <i class="ti ti-file-dollar text-green-500 text-2xl"></i>
                                            <span class="font-medium">
                                                RAB
                                            </span>
                                        </div>

                                        <span class="text-blue-600">
                                            Lihat →
                                        </span>
                                    </a>
                                @endif

                                @if($pengajuan->suratRekomendasi)
                                    <a href="{{ $pengajuan->suratRekomendasi->file_url }}"
                                       target="_blank"
                                       class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100">

                                        <div class="flex items-center gap-3">
                                            <i class="ti ti-file-description text-blue-500 text-2xl"></i>
                                            <span class="font-medium">
                                                Surat Rekomendasi
                                            </span>
                                        </div>

                                        <span class="text-blue-600">
                                            Lihat →
                                        </span>
                                    </a>
                                @endif

                            </div>
                        </div>
                    </div>

                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">

                    {{-- Form Verifikasi --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">
                                Verifikasi Dosen
                            </h3>

                            <form action="{{ route('dosen.verifikasi.verify', $pengajuan) }}"
                                  method="POST">
                                @csrf

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Keputusan
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <select name="status"
                                            required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                        <option value="">-- Pilih Keputusan --</option>
                                        <option value="disetujui">✓ Setujui</option>
                                        <option value="revisi">⚠ Perlu Revisi</option>
                                        <option value="ditolak">✗ Tolak</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Catatan
                                    </label>

                                    <textarea
                                        name="catatan"
                                        rows="4"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg"
                                        placeholder="Masukkan catatan verifikasi..."></textarea>
                                </div>

                                <button type="submit"
                                        class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                    Simpan Verifikasi
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Info Ormawa --}}
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="p-6">
                            <h3 class="text-sm font-semibold text-gray-500 mb-3">
                                ORGANISASI
                            </h3>

                            <p class="text-lg font-semibold text-gray-900">
                                {{ $pengajuan->ormawa->nama_ormawa }}
                            </p>

                            <p class="text-sm text-gray-600 mt-1">
                                Ketua: {{ $pengajuan->ormawa->ketua }}
                            </p>

                            <p class="text-sm text-gray-600">
                                Kategori:
                                {{ ucfirst($pengajuan->ormawa->kategori_organisasi) }}
                            </p>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>
