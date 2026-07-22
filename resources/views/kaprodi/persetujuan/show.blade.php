<x-app-layout>
    <x-slot name="title">Detail Persetujuan Kaprodi</x-slot>

    <div class="mb-6 flex items-center gap-3"><a href="{{ route('kaprodi.persetujuan.index') }}" class="p-2 rounded-lg bg-white border text-gray-500 hover:text-brand"><i class="ti ti-arrow-left"></i></a><div><h2 class="text-lg font-semibold text-gray-900">Detail Persetujuan</h2><p class="text-[12px] text-gray-500">Periksa informasi dan dokumen sebelum memberikan keputusan</p></div></div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b flex justify-between gap-4"><div><h3 class="text-lg font-semibold">{{ $pengajuan->judul_kegiatan }}</h3><p class="text-[12px] text-gray-500 mt-1">{{ $pengajuan->ormawa->nama_ormawa }}</p></div><span class="badge badge-{{ $pengajuan->status_badge }} h-fit">{{ $pengajuan->status_label }}</span></div>
                <div class="p-5 grid sm:grid-cols-2 gap-5 text-[13px]">
                    <div><div class="text-gray-500 mb-1">Ketua Pelaksana</div><div class="font-medium">{{ $pengajuan->ketua_pelaksana }}</div></div>
                    <div><div class="text-gray-500 mb-1">Lokasi</div><div class="font-medium">{{ $pengajuan->lokasi_kegiatan }}</div></div>
                    <div><div class="text-gray-500 mb-1">Tanggal Pelaksanaan</div><div class="font-medium">{{ $pengajuan->tanggal_mulai->format('d M Y') }} – {{ $pengajuan->tanggal_selesai->format('d M Y') }}</div></div>
                    <div><div class="text-gray-500 mb-1">Program Studi</div><div class="font-medium">{{ $pengajuan->ormawa->program_studi }}</div></div>
                    <div class="sm:col-span-2"><div class="text-gray-500 mb-1">Tujuan Kegiatan</div><div class="leading-relaxed">{{ $pengajuan->tujuan_kegiatan }}</div></div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-4 border-b"><h3 class="text-[15px] font-semibold">Dokumen Pengajuan</h3></div>
                <div class="p-5 grid sm:grid-cols-2 gap-3">
                    @if($pengajuan->proposal)<a href="{{ $pengajuan->proposal->file_url }}" target="_blank" class="flex items-center justify-between p-4 bg-gray-50 border rounded-lg hover:border-brand"><div class="flex items-center gap-3"><i class="ti ti-file-type-pdf text-2xl text-danger"></i><div><div class="text-[13px] font-medium">Proposal Kegiatan</div><div class="text-[11px] text-gray-400">Buka dokumen</div></div></div><i class="ti ti-external-link"></i></a>@endif
                    @if($pengajuan->rab)<a href="{{ $pengajuan->rab->file_url }}" target="_blank" class="flex items-center justify-between p-4 bg-gray-50 border rounded-lg hover:border-brand"><div class="flex items-center gap-3"><i class="ti ti-report-money text-2xl text-success"></i><div><div class="text-[13px] font-medium">Rencana Anggaran Biaya</div><div class="text-[11px] text-gray-400">Buka dokumen</div></div></div><i class="ti ti-external-link"></i></a>@endif
                    @if(!$pengajuan->proposal && !$pengajuan->rab)<div class="sm:col-span-2 py-8 text-center text-gray-400 text-sm">Tidak ada dokumen terlampir.</div>@endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            @if(in_array($pengajuan->status,['menunggu_kaprodi','revisi_kaprodi']))
                <form method="POST" action="{{ route('kaprodi.persetujuan.decide',$pengajuan) }}" class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">@csrf
                    <div><h3 class="text-[15px] font-semibold">Keputusan Kaprodi</h3><p class="text-[11px] text-gray-400">Pengajuan disetujui akan diteruskan ke Dekan.</p></div>
                    <div><label class="block text-[12px] font-medium text-gray-700 mb-1">Catatan</label><textarea name="catatan" rows="5" class="w-full rounded-lg border-gray-200 text-[13px]" placeholder="Wajib untuk revisi atau penolakan">{{ old('catatan') }}</textarea>@error('catatan')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror</div>
                    <button name="status" value="disetujui" class="w-full py-2.5 bg-success text-white rounded-lg text-[13px] font-medium hover:opacity-90"><i class="ti ti-check mr-1"></i> Setujui & Teruskan</button>
                    <button name="status" value="revisi" class="w-full py-2.5 bg-warning text-white rounded-lg text-[13px] font-medium hover:opacity-90"><i class="ti ti-edit mr-1"></i> Minta Revisi</button>
                    <button name="status" value="ditolak" class="w-full py-2.5 bg-danger text-white rounded-lg text-[13px] font-medium hover:opacity-90"><i class="ti ti-x mr-1"></i> Tolak Pengajuan</button>
                </form>
            @else
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5"><div class="w-11 h-11 bg-info-light text-info rounded-full flex items-center justify-center text-xl"><i class="ti ti-info-circle"></i></div><h3 class="font-semibold mt-3">Sudah Diproses</h3><p class="text-[12px] text-gray-500 mt-1">Status saat ini: {{ $pengajuan->status_label }}.</p></div>
            @endif

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5"><div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Organisasi</div><div class="font-semibold mt-2">{{ $pengajuan->ormawa->nama_ormawa }}</div><div class="text-[12px] text-gray-500 mt-1">Ketua: {{ $pengajuan->ormawa->ketua }}</div><a href="{{ route('kaprodi.ormawa.show',$pengajuan->ormawa) }}" class="inline-flex items-center gap-1 text-brand text-[12px] font-medium mt-3">Lihat Ormawa <i class="ti ti-arrow-right"></i></a></div>
        </div>
    </div>
</x-app-layout>
