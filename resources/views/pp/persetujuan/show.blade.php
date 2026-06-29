<x-app-layout>
    <x-slot name="title">Review Persetujuan Akhir</x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-6">
                <h1 class="text-xl font-bold text-gray-900">{{ $pengajuan->judul_kegiatan }}</h1>
                <p class="text-sm text-gray-500 mt-1">{{ $pengajuan->ormawa->nama_ormawa }}</p>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6 text-sm">
                    <div><dt class="text-gray-500">Tanggal</dt><dd>{{ $pengajuan->tanggal_mulai->format('d M Y') }} – {{ $pengajuan->tanggal_selesai->format('d M Y') }}</dd></div>
                    <div><dt class="text-gray-500">Lokasi</dt><dd>{{ $pengajuan->lokasi_kegiatan }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-gray-500">Tujuan</dt><dd>{{ $pengajuan->tujuan_kegiatan }}</dd></div>
                </dl>
            </div>
            <div class="card p-6">
                <h2 class="font-semibold mb-4">Dokumen</h2>
                <div class="flex flex-wrap gap-3">
                    @if($pengajuan->proposal)<a class="btn btn-secondary" target="_blank" href="{{ $pengajuan->proposal->file_url }}">Lihat Proposal</a>@endif
                    @if($pengajuan->rab)<a class="btn btn-secondary" target="_blank" href="{{ $pengajuan->rab->file_url }}">Lihat RAB</a>@endif
                </div>
            </div>
        </div>

        <div class="card p-6 h-fit">
            <h2 class="font-semibold mb-4">Keputusan Akhir</h2>
            <form method="POST" action="{{ route('pp.persetujuan.approve', $pengajuan) }}" class="space-y-3">
                @csrf
                <textarea name="catatan" rows="4" maxlength="1000" class="w-full rounded-lg border-gray-300" placeholder="Catatan persetujuan (opsional)"></textarea>
                <button class="w-full btn btn-primary">Setujui Pengajuan</button>
            </form>
            <form method="POST" action="{{ route('pp.persetujuan.reject', $pengajuan) }}" class="space-y-3 mt-6 pt-6 border-t">
                @csrf
                <textarea name="catatan" required rows="4" maxlength="1000" class="w-full rounded-lg border-gray-300" placeholder="Alasan penolakan (wajib)"></textarea>
                <button class="w-full px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Tolak Pengajuan</button>
            </form>
        </div>
    </div>
</x-app-layout>
