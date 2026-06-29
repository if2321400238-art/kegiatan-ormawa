<x-app-layout>
    <x-slot name="title">Persetujuan Akhir Kepala/Wakil PP</x-slot>

    <div class="card">
        <div class="card-header border-b flex items-center justify-between gap-4">
            <div>
                <h1 class="card-title">Persetujuan Akhir Kepala/Wakil PP</h1>
                <p class="text-sm text-gray-500">Pengajuan yang telah disetujui Rektor</p>
            </div>
            <form method="GET" class="flex gap-2">
                <input name="search" value="{{ request('search') }}" placeholder="Cari pengajuan..." class="rounded-lg border-gray-300 text-sm">
                <button class="btn btn-primary">Cari</button>
            </form>
        </div>
        <div class="card-body p-0">
            @forelse($pengajuanMenunggu as $pengajuan)
                <a href="{{ route('pp.persetujuan.show', $pengajuan) }}" class="flex items-center justify-between gap-4 p-4 border-b hover:bg-gray-50">
                    <div>
                        <p class="font-semibold text-gray-900">{{ $pengajuan->judul_kegiatan }}</p>
                        <p class="text-sm text-gray-500">{{ $pengajuan->ormawa->nama_ormawa }} · {{ $pengajuan->tanggal_mulai->format('d M Y') }}</p>
                    </div>
                    <span class="badge badge-warning">Review →</span>
                </a>
            @empty
                <div class="p-10 text-center text-gray-500">Tidak ada pengajuan yang menunggu persetujuan akhir.</div>
            @endforelse
        </div>
        @if($pengajuanMenunggu->hasPages())
            <div class="p-4">{{ $pengajuanMenunggu->links() }}</div>
        @endif
    </div>
</x-app-layout>
