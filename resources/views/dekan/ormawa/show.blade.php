<x-app-layout>
    <x-slot name="title">Detail Ormawa Fakultas</x-slot>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <a href="{{ route('dekan.ormawa.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="ti ti-arrow-left"></i>
                </a>
                {{ $ormawa->nama_ormawa }}
            </h2>
            <p class="text-[12px] text-gray-500 mt-1">Profil dan riwayat pengajuan kegiatan</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kolom Kiri: Profil Ormawa --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white border border-gray-100 shadow-sm rounded-xl overflow-hidden p-6">
                <div class="w-16 h-16 bg-brand/10 text-brand rounded-xl flex items-center justify-center text-3xl mx-auto mb-4">
                    <i class="ti ti-users"></i>
                </div>
                <h3 class="text-center font-bold text-gray-900 mb-1">{{ $ormawa->nama_ormawa }}</h3>
                <div class="flex justify-center gap-2 mb-6">
                    <span class="badge badge-brand text-[10px]">Internal</span>
                    <span class="badge badge-info text-[10px]">Fakultas</span>
                </div>

                <div class="space-y-4 text-[13px]">
                    <div class="flex flex-col gap-1 border-b border-gray-50 pb-3">
                        <span class="text-gray-500 font-medium">Pembina</span>
                        <span class="text-gray-900 font-semibold flex items-center gap-2">
                            <i class="ti ti-star text-gray-400"></i> {{ $ormawa->pembina ?? '-' }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-1 border-b border-gray-50 pb-3">
                        <span class="text-gray-500 font-medium">Ketua</span>
                        <span class="text-gray-900 font-semibold flex items-center gap-2">
                            <i class="ti ti-user text-gray-400"></i> {{ $ormawa->ketua }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-1 border-b border-gray-50 pb-3">
                        <span class="text-gray-500 font-medium">Kontak</span>
                        <span class="text-gray-900 font-semibold flex items-center gap-2">
                            <i class="ti ti-phone text-gray-400"></i> {{ $ormawa->kontak ?? '-' }}
                        </span>
                    </div>
                    <div class="flex flex-col gap-1 border-b border-gray-50 pb-3">
                        <span class="text-gray-500 font-medium">Periode</span>
                        <span class="text-gray-900 font-semibold flex items-center gap-2">
                            <i class="ti ti-calendar text-gray-400"></i> {{ $ormawa->periode ?? '-' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Riwayat Pengajuan --}}
        <div class="lg:col-span-2">
            <div class="table-card h-full flex flex-col">
                <div class="p-4 sm:p-6 border-b border-gray-100">
                    <h3 class="text-[15px] font-bold text-gray-900">Riwayat Pengajuan Kegiatan</h3>
                </div>

                <div class="flex-1">
                    @if($pengajuan->count() > 0)
                        <div class="overflow-x-auto">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Judul Kegiatan</th>
                                        <th>Jadwal</th>
                                        <th>Status</th>
                                        <th style="width: 100px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pengajuan as $item)
                                        <tr>
                                            <td>
                                                <div class="font-semibold text-gray-900">{{ $item->judul_kegiatan }}</div>
                                                <div class="text-[11px] text-gray-500 mt-0.5">Dibuat: {{ $item->created_at->format('d M Y') }}</div>
                                            </td>
                                            <td>
                                                <div class="text-[12px] text-gray-700">
                                                    {{ $item->tanggal_mulai->format('d M Y') }}
                                                </div>
                                            </td>
                                            <td>
                                                @include('components.status-badge', ['status' => $item->status])
                                            </td>
                                            <td>
                                                <a href="{{ route('pengajuan.show', $item->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors text-[12px] font-medium">
                                                    Lihat
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($pengajuan, 'links'))
                            <div class="p-4 border-t border-gray-100">
                                {{ $pengajuan->links('pagination::tailwind') }}
                            </div>
                        @endif
                    @else
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 text-2xl mb-3">
                                <i class="ti ti-file-off"></i>
                            </div>
                            <h3 class="text-[14px] font-semibold text-gray-900 mb-1">Belum Ada Pengajuan</h3>
                            <p class="text-[12px] text-gray-500">Ormawa ini belum pernah membuat pengajuan kegiatan.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
