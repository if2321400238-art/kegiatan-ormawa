<x-app-layout>
    <x-slot name="title">Dashboard Kaprodi</x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card" style="--accent: #F59E0B"><div class="stat-icon bg-warning-light text-warning"><i class="ti ti-clock-down"></i></div><div><span class="stat-label">Menunggu Persetujuan</span><span class="stat-value">{{ $stats['menunggu_persetujuan'] }}</span></div></div>
        <div class="stat-card" style="--accent: #3B82F6"><div class="stat-icon bg-info-light text-info"><i class="ti ti-calendar-check"></i></div><div><span class="stat-label">Disetujui Hari Ini</span><span class="stat-value">{{ $stats['disetujui_hari_ini'] }}</span></div></div>
        <div class="stat-card" style="--accent: #10B981"><div class="stat-icon bg-success-light text-success"><i class="ti ti-check"></i></div><div><span class="stat-label">Total Disetujui</span><span class="stat-value">{{ $stats['total_disetujui'] }}</span></div></div>
        <div class="stat-card" style="--accent: #8B5CF6"><div class="stat-icon bg-purple-100 text-purple-600"><i class="ti ti-users"></i></div><div><span class="stat-label">Ormawa Prodi</span><span class="stat-value">{{ $stats['total_ormawa'] }}</span></div></div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="table-card">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between"><div><h3 class="text-[15px] font-semibold">Menunggu Persetujuan</h3><p class="text-[12px] text-gray-400">Pengajuan Ormawa yang perlu ditinjau</p></div><a href="{{ route('kaprodi.persetujuan.index') }}" class="badge badge-warning">Lihat Semua</a></div>
            @forelse($pengajuanMenunggu as $item)
                <a href="{{ route('kaprodi.persetujuan.show', $item) }}" class="flex items-center justify-between gap-4 p-4 border-b border-gray-100 hover:bg-gray-50"><div><div class="text-[13px] font-medium text-gray-900">{{ $item->judul_kegiatan }}</div><div class="text-[11px] text-gray-500">{{ $item->ormawa->nama_ormawa }} · {{ $item->created_at->diffForHumans() }}</div></div><span class="badge badge-warning">Tinjau</span></a>
            @empty
                <div class="py-12 text-center text-gray-400"><i class="ti ti-circle-check text-3xl text-success"></i><p class="mt-2 text-sm">Semua pengajuan telah diproses</p></div>
            @endforelse
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100"><h3 class="text-[15px] font-semibold">Riwayat Persetujuan Anda</h3><p class="text-[12px] text-gray-400">Keputusan terbaru yang telah diberikan</p></div>
            <div class="p-4 space-y-3">
                @forelse($riwayatPersetujuan as $riwayat)
                    <a href="{{ route('kaprodi.persetujuan.show', $riwayat->pengajuanKegiatan) }}" class="block p-4 rounded-xl border border-gray-100 bg-gray-50/50 hover:bg-gray-50"><div class="flex justify-between gap-3"><div><div class="text-[13px] font-medium">{{ $riwayat->pengajuanKegiatan->judul_kegiatan }}</div><div class="text-[11px] text-gray-500">{{ $riwayat->pengajuanKegiatan->ormawa->nama_ormawa }}</div></div><span class="badge {{ $riwayat->status === 'disetujui' ? 'badge-success' : ($riwayat->status === 'revisi' ? 'badge-warning' : 'badge-danger') }}">{{ ucfirst($riwayat->status) }}</span></div><div class="text-[10px] text-gray-400 mt-2"><i class="ti ti-clock"></i> {{ $riwayat->tanggal_acc->format('d M Y, H:i') }}</div></a>
                @empty
                    <div class="py-10 text-center text-sm text-gray-400">Belum ada riwayat persetujuan</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
