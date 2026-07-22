<x-app-layout>
    <x-slot name="title">Dashboard Dekan</x-slot>

    {{-- Key Statistics Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        {{-- Menunggu Persetujuan --}}
        <div class="stat-card" style="--accent: #F59E0B">
            <div class="stat-icon bg-warning-light text-warning">
                <i class="ti ti-clock-down"></i>
            </div>
            <div>
                <span class="stat-label">Menunggu Persetujuan</span>
                <span class="stat-value">{{ $stats['menunggu_persetujuan'] ?? 0 }}</span>
            </div>
        </div>

        {{-- Disetujui Hari Ini --}}
        <div class="stat-card" style="--accent: #3B82F6">
            <div class="stat-icon bg-info-light text-info">
                <i class="ti ti-calendar-check"></i>
            </div>
            <div>
                <span class="stat-label">Disetujui Hari Ini</span>
                <span class="stat-value">{{ $stats['disetujui_hari_ini'] ?? 0 }}</span>
            </div>
        </div>

        {{-- Total Disetujui --}}
        <div class="stat-card" style="--accent: #10B981">
            <div class="stat-icon bg-success-light text-success">
                <i class="ti ti-check"></i>
            </div>
            <div>
                <span class="stat-label">Total Disetujui</span>
                <span class="stat-value">{{ $stats['total_disetujui'] ?? 0 }}</span>
            </div>
        </div>

        {{-- Perlu Revisi --}}
        <div class="stat-card" style="--accent: #F97316">
            <div class="stat-icon bg-orange-light text-orange">
                <i class="ti ti-edit"></i>
            </div>
            <div>
                <span class="stat-label">Perlu Revisi</span>
                <span class="stat-value">{{ $stats['perlu_revisi'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    {{-- Content Section --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Pengajuan Menunggu Persetujuan --}}
        <div class="xl:col-span-2 flex flex-col gap-6 min-h-0">
            <div class="table-card flex-1">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-semibold text-gray-900">Pengajuan Menunggu Persetujuan</h3>
                        <p class="text-[12px] text-gray-400">Pengajuan tingkat fakultas yang perlu ditinjau</p>
                    </div>
                    <a href="{{ route('dekan.persetujuan.index') }}" class="badge badge-warning hover:bg-warning-light/80">Lihat Semua</a>
                </div>

                @if(count($pengajuanMenunggu ?? []) > 0)
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Ormawa & Kegiatan</th>
                                <th>Waktu Pengajuan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pengajuanMenunggu as $pengajuan)
                            <tr>
                                <td>
                                    <div class="font-medium text-gray-900">{{ $pengajuan->nama_kegiatan }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $pengajuan->ormawa->nama_ormawa ?? 'N/A' }}</div>
                                </td>
                                <td>
                                    <div class="text-[12px]">{{ $pengajuan->created_at->diffForHumans() }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $pengajuan->created_at->format('d M Y, H:i') }}</div>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $pengajuan->status_badge }}">{{ $pengajuan->status_label }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('dekan.persetujuan.show', $pengajuan) }}" class="badge badge-info hover:underline text-xs">Persetujuan</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="flex-1 flex flex-col items-center justify-center p-8 gap-3">
                    <div class="w-12 h-12 rounded-full bg-success-light flex items-center justify-center text-success text-2xl">
                        <i class="ti ti-check"></i>
                    </div>
                    <p class="text-sm text-gray-400">Semua pengajuan telah disetujui</p>
                </div>
                @endif
                
                @if($pengajuanMenunggu->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $pengajuanMenunggu->links() }}
                </div>
                @endif
            </div>
        </div>

        {{-- Quick Info --}}
        <div class="flex flex-col gap-6 min-h-0">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex-1 flex flex-col min-h-0 overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-semibold text-gray-900">Informasi</h3>
                        <p class="text-[12px] text-gray-400">Panduan persetujuan Dekan</p>
                    </div>
                </div>
                <div class="p-4 flex flex-col gap-3">
                    <div class="p-3 bg-blue-50 rounded-lg border border-blue-100">
                        <p class="text-sm text-gray-700">
                            <strong>Peran Anda:</strong> Memberikan persetujuan untuk pengajuan kegiatan Ormawa tingkat fakultas.
                        </p>
                    </div>
                    <div class="p-3 bg-green-50 rounded-lg border border-green-100">
                        <p class="text-sm text-gray-700">
                            <strong>Langkah Berikutnya:</strong> Pengajuan yang disetujui akan diteruskan ke BAUAK untuk verifikasi administrasi.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
