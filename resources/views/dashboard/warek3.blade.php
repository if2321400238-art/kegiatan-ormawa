<x-app-layout>
    <x-slot name="title">Dashboard Wakil Rektor III</x-slot>

    {{-- Key Statistics Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        {{-- Menunggu Wakil Rektor III --}}
        <div class="stat-card" style="--accent: #F59E0B">
            <div class="stat-icon bg-warning-light text-warning">
                <i class="ti ti-clock-down"></i>
            </div>
            <div>
                <span class="stat-label">Menunggu Wakil Rektor III</span>
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
                <span class="stat-label">Total Disetujui (Keseluruhan)</span>
                <span class="stat-value">{{ $stats['total_disetujui'] ?? 0 }}</span>
            </div>
        </div>

        {{-- Ditolak --}}
        <div class="stat-card" style="--accent: #EF4444">
            <div class="stat-icon bg-danger-light text-danger">
                <i class="ti ti-x"></i>
            </div>
            <div>
                <span class="stat-label">Pengajuan Ditolak</span>
                <span class="stat-value">{{ $stats['pengajuan_ditolak'] ?? 0 }}</span>
            </div>
        </div>

    </div>

    {{-- Lower Grid: Menunggu Persetujuan & Riwayat --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        {{-- Kolom Kiri: Menunggu Persetujuan --}}
        <div class="flex flex-col gap-6 min-h-0">
            <div class="table-card flex-1">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-semibold text-gray-900">Menunggu Persetujuan</h3>
                        <p class="text-[12px] text-gray-400">Pengajuan yang telah diverifikasi BAUAK dan menunggu persetujuan Wakil Rektor III</p>
                    </div>
                    <a href="{{ route('warek3.persetujuan.index') }}" class="badge badge-warning hover:bg-warning-light/80">Lihat Semua</a>
                </div>

                @if(count($pengajuanMenunggu ?? []) > 0)
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Ormawa & Kegiatan</th>
                                <th>Waktu Pengajuan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pengajuanMenunggu as $item)
                            <tr>
                                <td>
                                    <div class="font-medium text-gray-900">{{ $item->judul_kegiatan }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $item->ormawa->nama_ormawa }}</div>
                                </td>
                                <td>
                                    <div class="text-[12px]">{{ $item->created_at->diffForHumans() }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $item->created_at->format('d M Y, H:i') }}</div>
                                </td>
                                <td>
                                    <a href="{{ route('warek3.persetujuan.show', $item) }}" class="badge badge-info hover:underline text-xs">Tinjau</a>
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
            </div>
        </div>

        {{-- Kolom Kanan: Riwayat Persetujuan --}}
        <div class="flex flex-col gap-6 min-h-0">

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex-1 flex flex-col min-h-0 overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-semibold text-gray-900">Riwayat Persetujuan Anda</h3>
                        <p class="text-[12px] text-gray-400">Pengajuan yang telah Anda proses</p>
                    </div>
                </div>
                <div class="p-4 overflow-y-auto flex-1">
                    @forelse($riwayatPersetujuan ?? [] as $persetujuan)
                        <div class="mb-3 p-4 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between gap-3 mb-2">
                                <div>
                                    <h4 class="font-medium text-gray-900 text-[13px] leading-tight mb-1">{{ $persetujuan->pengajuanKegiatan->judul_kegiatan }}</h4>
                                    <p class="text-[11px] text-gray-500">{{ $persetujuan->pengajuanKegiatan->ormawa->nama_ormawa }}</p>
                                </div>
                                <span class="badge {{ $persetujuan->status_badge === 'success' ? 'badge-success' : 'badge-danger' }} flex-shrink-0">
                                    {{ $persetujuan->status_label ?? 'Disetujui' }}
                                </span>
                            </div>
                            @if($persetujuan->catatan)
                                <div class="text-[11px] text-gray-600 bg-white border border-gray-200 p-2 rounded-md mt-2">
                                    <span class="font-semibold text-gray-900">Catatan:</span> {{ $persetujuan->catatan }}
                                </div>
                            @endif
                            <div class="text-[10px] text-gray-400 mt-2 flex items-center gap-1">
                                @if($persetujuan->tanggal_persetujuan)
                                    {{ $persetujuan->tanggal_persetujuan->format('d M Y, H:i') }}
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="py-12 flex flex-col items-center justify-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 text-2xl">
                                <i class="ti ti-history"></i>
                            </div>
                            <div class="text-center text-gray-400 text-sm">Belum ada riwayat persetujuan</div>
                        </div>
                    @endforelse
                </div>
                @if(($riwayatPersetujuan ?? collect())->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $riwayatPersetujuan->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
