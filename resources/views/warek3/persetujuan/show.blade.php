<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Review & Persetujuan Pengajuan
            </h2>
            <a href="{{ route('warek3.persetujuan.index') }}" class="text-blue-600 hover:text-blue-800">
                ← Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Content --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Detail Pengajuan --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Informasi Kegiatan</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Judul Kegiatan</label>
                                    <p class="text-gray-900">{{ $pengajuan->judul_kegiatan }}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Tujuan</label>
                                    <p class="text-gray-900">{{ $pengajuan->tujuan_kegiatan }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Tanggal</label>
                                        <p class="text-gray-900">{{ $pengajuan->tanggal_mulai->format('d M Y') }} - {{ $pengajuan->tanggal_selesai->format('d M Y') }}</p>
                                    </div>
                                    <div>
                                        <label class="text-sm font-medium text-gray-500">Lokasi</label>
                                        <p class="text-gray-900">{{ $pengajuan->lokasi_kegiatan }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Dokumen --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Dokumen</h3>
                            <div class="space-y-3">
                                @if($pengajuan->proposal)
                                    <a href="{{ $pengajuan->proposal->file_url }}" target="_blank" class="flex items-center justify-between p-3 bg-gray-50 rounded hover:bg-gray-100">
                                        <div class="flex items-center">
                                            <svg class="w-8 h-8 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                                            <span class="ml-3 font-medium">Proposal</span>
                                        </div>
                                        <span class="text-blue-600">Lihat →</span>
                                    </a>
                                @endif
                                @if($pengajuan->rab)
                                    <a href="{{ $pengajuan->rab->file_url }}" target="_blank" class="flex items-center justify-between p-3 bg-gray-50 rounded hover:bg-gray-100">
                                        <div class="flex items-center">
                                            <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z"/></svg>
                                            <span class="ml-3 font-medium">RAB</span>
                                        </div>
                                        <span class="text-blue-600">Lihat →</span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Verifikasi BAUAK --}}
                    @if($pengajuan->latestVerifikasiBauak)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Hasil Verifikasi BAUAK</h3>
                            <div class="border-l-4 border-green-500 pl-4">
                                <p class="font-medium">{{ $pengajuan->latestVerifikasiBauak->user->nama }}</p>
                                <p class="text-sm text-gray-500">{{ $pengajuan->latestVerifikasiBauak->tanggal_verifikasi->format('d M Y H:i') }}</p>
                                <span class="inline-block mt-2 px-3 py-1 text-sm rounded-full bg-green-100 text-green-800">
                                    ✓ Disetujui
                                </span>
                                @if($pengajuan->latestVerifikasiBauak->catatan)
                                    <p class="mt-3 text-sm text-gray-700">{{ $pengajuan->latestVerifikasiBauak->catatan }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                {{-- Sidebar - Form Persetujuan --}}
                <div class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">Persetujuan</h3>

                            {{-- Approve Form --}}
                            <form action="{{ route('warek3.persetujuan.approve', $pengajuan) }}" method="POST" class="mb-3">
                                @csrf
                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (Opsional)</label>
                                    <textarea name="catatan" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Catatan persetujuan..."></textarea>
                                </div>
                                <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                                    ✓ Setujui & TTD
                                </button>
                            </form>

                            {{-- Reject Form --}}
                            <form action="{{ route('warek3.persetujuan.reject', $pengajuan) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span class="text-red-500">*</span></label>
                                    <textarea name="catatan" required rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Alasan penolakan..."></textarea>
                                </div>
                                <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                                    ✗ Tolak
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Info Ormawa --}}
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-semibold text-gray-500 mb-3">ORGANISASI</h3>
                            <p class="text-lg font-semibold text-gray-900">{{ $pengajuan->ormawa->nama_ormawa }}</p>
                            <p class="text-sm text-gray-600 mt-1">Ketua: {{ $pengajuan->ormawa->ketua }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
