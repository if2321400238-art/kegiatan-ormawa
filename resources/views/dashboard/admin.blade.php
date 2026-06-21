<x-app-layout>
    <x-slot name="title">Dashboard Admin</x-slot>

    {{-- Key Statistics Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        {{-- Total Ormawa --}}
        <div class="stat-card" style="--accent: #3B82F6">
            <div class="stat-icon bg-info-light text-info">
                <i class="ti ti-building-community"></i>
            </div>
            <div>
                <span class="stat-label">Total Ormawa</span>
                <span class="stat-value">{{ $stats['total_ormawa'] ?? 0 }}</span>
            </div>
        </div>

        {{-- Total Pengajuan --}}
        <div class="stat-card" style="--accent: #10B981">
            <div class="stat-icon bg-success-light text-success">
                <i class="ti ti-file-description"></i>
            </div>
            <div>
                <span class="stat-label">Total Pengajuan</span>
                <span class="stat-value">{{ $stats['total_pengajuan'] ?? 0 }}</span>
            </div>
        </div>

        {{-- Menunggu Persetujuan --}}
        <div class="stat-card" style="--accent: #F59E0B">
            <div class="stat-icon bg-warning-light text-warning">
                <i class="ti ti-clock-down"></i>
            </div>
            <div>
                <span class="stat-label">Menunggu Persetujuan</span>
                <span class="stat-value">{{ $stats['pengajuan_pending'] ?? 0 }}</span>
            </div>
        </div>

        {{-- Perlu Revisi --}}
        <div class="stat-card" style="--accent: #F97316">
            <div class="stat-icon bg-orange-light text-orange">
                <i class="ti ti-edit"></i>
            </div>
            <div>
                <span class="stat-label">Perlu Revisi</span>
                <span class="stat-value">{{ $stats['pengajuan_revisi'] ?? 0 }}</span>
            </div>
        </div>

    </div>

    {{-- Lower Grid: Recent Pengajuan & Right Sidebar --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Kolom Kiri: Pengajuan Terbaru (2/3 lebar) --}}
        <div class="lg:col-span-2 flex flex-col gap-6 min-h-0">
            <div class="table-card flex-1">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-[15px] font-semibold text-gray-900">Pengajuan Terbaru</h3>
                        <p class="text-[12px] text-gray-400">Riwayat pengajuan kegiatan</p>
                    </div>
                    <span class="badge badge-gray">{{ count($pengajuanTerbaru ?? []) }} data</span>
                </div>
                
                @if(count($pengajuanTerbaru ?? []) > 0)
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Ormawa</th>
                                <th>Kegiatan</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pengajuanTerbaru as $item)
                            <tr>
                                <td>{{ $item->ormawa->nama_ormawa }}</td>
                                <td>{{ $item->judul_kegiatan }}</td>
                                <td>{{ $item->created_at->format('d M Y') }}</td>
                                <td>
                                    @php
                                        $statusClass = match($item->status) {
                                            'draft' => 'badge-gray',
                                            'diajukan' => 'badge-warning',
                                            'disetujui_bauak' => 'badge-info',
                                            'disetujui_warek3' => 'badge-success',
                                            'revisi_bauak' => 'badge-orange',
                                            'ditolak' => 'badge-danger',
                                            default => 'badge-gray'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ ucwords(str_replace('_', ' ', $item->status)) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="flex-1 flex flex-col items-center justify-center p-8 gap-3">
                    <div class="w-12 h-12 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 text-2xl">
                        <i class="ti ti-inbox"></i>
                    </div>
                    <p class="text-sm text-gray-400">Tidak ada pengajuan terbaru</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Kolom Kanan: Aksi Cepat & Ormawa Teraktif (1/3 lebar) --}}
        <div class="flex flex-col gap-6 min-h-0">
            
            {{-- Aksi Cepat --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm flex-shrink-0">
                <div class="p-4 border-b border-gray-100">
                    <h3 class="text-[15px] font-semibold text-gray-900">Aksi Cepat</h3>
                </div>
                <div class="p-4 grid grid-cols-2 gap-3">
                    <a href="{{ route('admin.ormawa.index') }}" class="quick-action-card p-4 !border-gray-100 hover:!border-brand-accent/30 block">
                        <div class="qa-icon bg-brand">
                            <i class="ti ti-building-community"></i>
                        </div>
                        <div class="qa-arrow"><i class="ti ti-arrow-right"></i></div>
                        <p class="qa-title text-[13px]">Ormawa</p>
                        <p class="qa-desc text-[11px]">Kelola ormawa</p>
                    </a>
                    
                    <a href="{{ route('pengajuan.index') }}" class="quick-action-card p-4 !border-gray-100 hover:!border-brand-accent/30 block">
                        <div class="qa-icon bg-success">
                            <i class="ti ti-file-plus"></i>
                        </div>
                        <div class="qa-arrow bg-success-light text-success"><i class="ti ti-arrow-right"></i></div>
                        <p class="qa-title text-[13px]">Pengajuan</p>
                        <p class="qa-desc text-[11px]">Lihat daftar</p>
                    </a>
                </div>
            </div>

            {{-- Ormawa Paling Aktif --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex-1 flex flex-col min-h-0 overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <h3 class="text-[15px] font-semibold text-gray-900">Ormawa Teraktif</h3>
                    <p class="text-[12px] text-gray-400">Berdasarkan total pengajuan</p>
                </div>
                <div class="p-4 overflow-y-auto flex-1">
                    @forelse($ormawaAktif ?? [] as $index => $item)
                        <div class="active-org-row">
                            <span class="rank-pill">{{ $index + 1 }}</span>
                            <span class="org-name truncate">{{ $item->nama_ormawa }}</span>
                            <span class="badge badge-info">{{ $item->pengajuan_kegiatan_count }} pengajuan</span>
                        </div>
                    @empty
                        <div class="py-6 text-center text-gray-400 text-sm">Tidak ada data ormawa</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
