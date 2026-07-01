<x-app-layout>
    <x-slot name="title">Detail Ormawa Prodi</x-slot>
    <div class="space-y-5">
        <div class="bg-white rounded-xl border p-6">
            <a href="{{ route('kaprodi.ormawa.index') }}" class="text-sm text-brand hover:underline">← Kembali ke daftar Ormawa</a>
            <h2 class="text-xl font-semibold mt-4">{{ $ormawa->nama_ormawa }}</h2>
            <div class="grid sm:grid-cols-3 gap-4 mt-5 text-sm">
                <div><div class="text-gray-500">Program Studi</div><div class="font-medium">{{ $ormawa->program_studi }}</div></div>
                <div><div class="text-gray-500">Ketua</div><div class="font-medium">{{ $ormawa->ketua }}</div></div>
                <div><div class="text-gray-500">Periode</div><div class="font-medium">{{ $ormawa->periode ?? '-' }}</div></div>
            </div>
        </div>
        <div class="bg-white rounded-xl border overflow-hidden">
            <div class="p-5 border-b font-semibold">Riwayat Pengajuan Ormawa</div>
            <div class="divide-y">
                @forelse($pengajuan as $item)
                    <a href="{{ route('kaprodi.persetujuan.show', $item) }}" class="flex justify-between gap-4 p-5 hover:bg-gray-50">
                        <div><div class="font-medium">{{ $item->judul_kegiatan }}</div><div class="text-xs text-gray-500">{{ $item->created_at->format('d M Y') }}</div></div>
                        <span class="badge badge-{{ $item->status_badge }}">{{ $item->status_label }}</span>
                    </a>
                @empty
                    <div class="p-8 text-center text-gray-500">Belum ada pengajuan kegiatan.</div>
                @endforelse
            </div>
            <div class="p-4">{{ $pengajuan->links() }}</div>
        </div>
    </div>
</x-app-layout>
