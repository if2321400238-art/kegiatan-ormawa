<x-app-layout>
    <x-slot name="title">Dashboard - {{ auth()->user()->ormawa->nama_ormawa }}</x-slot>

    {{-- Key Statistics Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        
        {{-- Total Pengajuan --}}
        <div class="stat-card" style="--accent: #3B82F6">
            <div class="stat-icon bg-info-light text-info">
                <i class="ti ti-file-description"></i>
            </div>
            <div>
                <span class="stat-label">Total Pengajuan</span>
                <span class="stat-value">{{ $stats['total_pengajuan'] ?? 0 }}</span>
            </div>
        </div>

        {{-- Menunggu Verifikasi --}}
        <div class="stat-card" style="--accent: #F59E0B">
            <div class="stat-icon bg-warning-light text-warning">
                <i class="ti ti-clock-down"></i>
            </div>
            <div>
                <span class="stat-label">Menunggu Approval</span>
                <span class="stat-value">{{ $stats['menunggu_verifikasi'] ?? 0 }}</span>
            </div>
        </div>

        {{-- Disetujui --}}
        <div class="stat-card" style="--accent: #10B981">
            <div class="stat-icon bg-success-light text-success">
                <i class="ti ti-check"></i>
            </div>
            <div>
                <span class="stat-label">Disetujui</span>
                <span class="stat-value">{{ $stats['disetujui'] ?? 0 }}</span>
            </div>
        </div>

        {{-- Revisi --}}
        <div class="stat-card" style="--accent: #F97316">
            <div class="stat-icon bg-orange-light text-orange">
                <i class="ti ti-edit"></i>
            </div>
            <div>
                <span class="stat-label">Perlu Revisi</span>
                <span class="stat-value">{{ $stats['revisi'] ?? 0 }}</span>
            </div>
        </div>

    </div>

    {{-- Additional Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
        <div class="stat-card !p-4" style="--accent: #9CA3AF">
            <div>
                <span class="stat-label">Draft</span>
                <span class="stat-value text-xl">{{ $stats['draft'] ?? 0 }}</span>
            </div>
        </div>
        <div class="stat-card !p-4" style="--accent: #EF4444">
            <div>
                <span class="stat-label">Ditolak</span>
                <span class="stat-value text-xl">{{ $stats['ditolak'] ?? 0 }}</span>
            </div>
        </div>
        <div class="stat-card !p-4 sm:col-span-1 col-span-2" style="--accent: #3B82F6">
            <div>
                <span class="stat-label">Kegiatan Mendatang</span>
                <span class="stat-value text-xl">{{ $upcomingEvents->count() ?? 0 }}</span>
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
                        <p class="text-[12px] text-gray-400">Riwayat pengajuan kegiatan Anda</p>
                    </div>
                    <a href="{{ route('pengajuan.index') }}" class="badge badge-gray hover:bg-gray-200">Lihat Semua</a>
                </div>
                
                @if(count($recentPengajuan ?? []) > 0)
                <div class="overflow-x-auto">
                    <table>
                        <thead>
                            <tr>
                                <th>Judul Kegiatan</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPengajuan as $pengajuan)
                            <tr>
                                <td>
                                    <div class="font-medium">{{ $pengajuan->judul_kegiatan }}</div>
                                </td>
                                <td>{{ $pengajuan->tanggal_mulai->format('d M Y') }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'badge-gray',
                                            'diajukan' => 'badge-warning',
                                            'disetujui_bauak' => 'badge-info',
                                            'disetujui_warek3' => 'badge-success',
                                            'revisi_bauak' => 'badge-orange',
                                            'ditolak' => 'badge-danger',
                                        ];
                                        $statusClass = $statusColors[$pengajuan->status] ?? 'badge-gray';
                                        $statusLabel = match($pengajuan->status) {
                                            'diajukan' => 'Pending',
                                            'disetujui_bauak' => 'Disetujui BAUAK',
                                            'disetujui_warek3' => 'Disetujui',
                                            'revisi_bauak' => 'Revisi',
                                            default => ucwords($pengajuan->status)
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('pengajuan.show', $pengajuan) }}" class="text-brand-accent hover:underline text-xs font-medium">Detail</a>
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
                    <p class="text-sm text-gray-400">Belum ada pengajuan</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Kolom Kanan: Aksi Cepat & Kegiatan Mendatang (1/3 lebar) --}}
        <div class="flex flex-col gap-6 min-h-0">
            
            {{-- Aksi Cepat --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-sm flex-shrink-0">
                <div class="p-4 border-b border-gray-100">
                    <h3 class="text-[15px] font-semibold text-gray-900">Aksi Cepat</h3>
                </div>
                <div class="p-4 grid grid-cols-2 gap-3">
                    <a href="{{ route('pengajuan.create') }}" class="quick-action-card p-4 !border-gray-100 hover:!border-brand-accent/30 block">
                        <div class="qa-icon bg-success">
                            <i class="ti ti-plus"></i>
                        </div>
                        <div class="qa-arrow bg-success-light text-success"><i class="ti ti-arrow-right"></i></div>
                        <p class="qa-title text-[13px]">Buat Baru</p>
                        <p class="qa-desc text-[11px]">Ajukan kegiatan</p>
                    </a>
                    
                    <a href="{{ route('pengajuan.index') }}" class="quick-action-card p-4 !border-gray-100 hover:!border-brand-accent/30 block">
                        <div class="qa-icon bg-brand">
                            <i class="ti ti-list"></i>
                        </div>
                        <div class="qa-arrow bg-info-light text-info"><i class="ti ti-arrow-right"></i></div>
                        <p class="qa-title text-[13px]">Riwayat</p>
                        <p class="qa-desc text-[11px]">Semua pengajuan</p>
                    </a>
                </div>
            </div>

            {{-- Kegiatan Mendatang --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex-1 flex flex-col min-h-0 overflow-hidden">
                <div class="p-4 border-b border-gray-100">
                    <h3 class="text-[15px] font-semibold text-gray-900">Kegiatan Mendatang</h3>
                </div>
                <div class="p-4 overflow-y-auto flex-1">
                    @forelse($upcomingEvents ?? [] as $event)
                        <div class="mb-3 p-3 rounded-lg border border-success-light bg-success-light/20 relative overflow-hidden">
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-success"></div>
                            <h4 class="font-medium text-gray-900 text-[13px] mb-1">{{ $event->judul_kegiatan }}</h4>
                            <p class="text-[11px] text-gray-500 mb-1 flex items-center gap-1">
                                <i class="ti ti-map-pin"></i> {{ $event->lokasi_kegiatan }}
                            </p>
                            <p class="text-[11px] text-gray-500 flex items-center gap-1">
                                <i class="ti ti-calendar"></i> {{ $event->tanggal_mulai->format('d M Y') }} - {{ $event->tanggal_selesai->format('d M Y') }}
                            </p>
                        </div>
                    @empty
                        <div class="py-6 text-center text-gray-400 text-sm">Tidak ada kegiatan mendatang</div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

</x-app-layout>
