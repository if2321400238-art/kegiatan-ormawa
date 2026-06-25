<x-app-layout>
    <x-slot name="title">Dashboard Dosen Pembina</x-slot>

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

        {{-- Ditolak --}}
        <div class="stat-card" style="--accent: #EF4444">
            <div class="stat-icon bg-red-light text-red">
                <i class="ti ti-x"></i>
            </div>
            <div>
                <span class="stat-label">Ditolak</span>
                <span class="stat-value">{{ $stats['ditolak'] ?? 0 }}</span>
            </div>
        </div>

        {{-- Perlu Revisi --}}
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

    {{-- Content Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Pengajuan Menunggu Persetujuan --}}
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header border-b">
                    <h5 class="card-title">📋 Pengajuan Menunggu Persetujuan</h5>
                </div>
                <div class="card-body p-0">
                    @forelse($pengajuanMenunggu as $pengajuan)
                        <div class="border-b last:border-b-0 hover:bg-gray-50 transition-colors">
                            <a href="{{ route('pengajuan.show', $pengajuan) }}" class="block p-4 text-decoration-none">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h6 class="font-semibold text-gray-900 mb-1">{{ $pengajuan->nama_kegiatan }}</h6>
                                        <p class="text-sm text-gray-600 mb-2">{{ $pengajuan->ormawa->nama_ormawa ?? 'N/A' }}</p>
                                    </div>
                                    <span class="badge badge-{{ $pengajuan->status_badge }}">{{ $pengajuan->status_label }}</span>
                                </div>
                                <div class="flex gap-3 text-xs text-gray-500">
                                    <span>📅 {{ $pengajuan->tanggal_mulai?->format('d M Y') ?? '-' }}</span>
                                    <span>📍 {{ $pengajuan->lokasi ?? '-' }}</span>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            <i class="ti ti-inbox text-4xl mb-2 block opacity-50"></i>
                            <p>Tidak ada pengajuan menunggu persetujuan</p>
                        </div>
                    @endforelse
                </div>
                @if($pengajuanMenunggu->hasPages())
                    <div class="card-footer border-t">
                        {{ $pengajuanMenunggu->links() }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Quick Info --}}
        <div>
            <div class="card">
                <div class="card-header border-b">
                    <h5 class="card-title">ℹ️ Informasi</h5>
                </div>
                <div class="card-body">
                    <div class="space-y-3">
                        <div class="p-3 bg-blue-50 rounded">
                            <p class="text-sm text-gray-700">
                                <strong>Peran Anda:</strong> Melihat dan memberikan persetujuan awal untuk pengajuan kegiatan Ormawa binaan.
                            </p>
                        </div>
                        <div class="p-3 bg-green-50 rounded">
                            <p class="text-sm text-gray-700">
                                <strong>Langkah Berikutnya:</strong> Pengajuan yang disetujui akan diteruskan ke tahap Dekan (untuk Ormawa fakultas) atau langsung ke BAUAK (untuk Ormawa universitas).
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
