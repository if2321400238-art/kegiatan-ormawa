<x-app-layout>
    <x-slot name="title">Persetujuan Kaprodi</x-slot>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div><h2 class="text-lg font-semibold text-gray-900">Persetujuan Kaprodi</h2><p class="text-[12px] text-gray-500">Tinjau pengajuan Ormawa {{ auth()->user()->programStudiKaprodi?->nama }}</p></div>
        <div class="summary-stat-card" style="--accent: #F59E0B"><div class="text-[20px] font-bold">{{ $pengajuanMenunggu->total() }}</div><div class="text-[11px] text-gray-500">Menunggu</div></div>
    </div>

    @if(session('success'))<div class="mb-4 p-4 bg-success-light text-success rounded-lg text-sm">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="mb-4 p-4 bg-danger-light text-danger rounded-lg text-sm">{{ session('error') }}</div>@endif

    <div x-data="{ tab: @js(request('tab', 'menunggu')) }" class="space-y-5">
        <div class="flex gap-5 border-b border-gray-200">
            <button @click="tab='menunggu'" :class="tab==='menunggu' ? 'border-brand text-brand' : 'border-transparent text-gray-500'" class="pb-3 border-b-2 text-[13px] font-medium">Menunggu Persetujuan ({{ $pengajuanMenunggu->total() }})</button>
            <button @click="tab='riwayat'" :class="tab==='riwayat' ? 'border-brand text-brand' : 'border-transparent text-gray-500'" class="pb-3 border-b-2 text-[13px] font-medium">Riwayat Persetujuan ({{ $riwayatPersetujuan->total() }})</button>
        </div>

        <div x-show="tab==='menunggu'" class="table-card">
            @if($pengajuanMenunggu->count())
                <div class="overflow-x-auto"><table><thead><tr><th>No</th><th>Ormawa & Kegiatan</th><th>Pelaksanaan</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
                @foreach($pengajuanMenunggu as $item)
                    <tr><td>{{ $pengajuanMenunggu->firstItem()+$loop->index }}</td><td><div class="font-semibold text-gray-900">{{ $item->judul_kegiatan }}</div><div class="text-[11px] text-gray-500">{{ $item->ormawa->nama_ormawa }} · {{ $item->lokasi_kegiatan }}</div></td><td><div class="text-[12px]">{{ $item->tanggal_mulai->format('d M Y') }}</div><div class="text-[11px] text-gray-400">{{ $item->created_at->diffForHumans() }}</div></td><td><span class="badge badge-{{ $item->status_badge }}">{{ $item->status_label }}</span></td><td><a href="{{ route('kaprodi.persetujuan.show',$item) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-brand/10 text-brand rounded-md text-[12px] font-medium hover:bg-brand hover:text-white"><i class="ti ti-eye"></i> Tinjau</a></td></tr>
                @endforeach
                </tbody></table></div><div class="p-4 border-t">{{ $pengajuanMenunggu->appends(['tab'=>'menunggu'])->links() }}</div>
            @else
                <div class="p-12 text-center"><div class="w-16 h-16 mx-auto bg-success-light text-success rounded-full flex items-center justify-center text-3xl"><i class="ti ti-check"></i></div><h3 class="mt-4 font-semibold">Semua pengajuan telah diproses</h3><p class="text-[13px] text-gray-500">Tidak ada pengajuan yang menunggu persetujuan.</p></div>
            @endif
        </div>

        <div x-show="tab==='riwayat'" style="display:none" class="table-card">
            @if($riwayatPersetujuan->count())
                <div class="overflow-x-auto"><table><thead><tr><th>Kegiatan</th><th>Ormawa</th><th>Keputusan</th><th>Tanggal</th><th>Aksi</th></tr></thead><tbody>
                @foreach($riwayatPersetujuan as $riwayat)
                    <tr><td class="font-medium">{{ $riwayat->pengajuanKegiatan->judul_kegiatan }}</td><td>{{ $riwayat->pengajuanKegiatan->ormawa->nama_ormawa }}</td><td><span class="badge {{ $riwayat->status==='disetujui'?'badge-success':($riwayat->status==='revisi'?'badge-warning':'badge-danger') }}">{{ ucfirst($riwayat->status) }}</span></td><td>{{ $riwayat->tanggal_acc->format('d M Y H:i') }}</td><td><a href="{{ route('kaprodi.persetujuan.show',$riwayat->pengajuanKegiatan) }}" class="text-brand text-[12px] font-medium hover:underline">Detail →</a></td></tr>
                @endforeach
                </tbody></table></div><div class="p-4 border-t">{{ $riwayatPersetujuan->appends(['tab'=>'riwayat'])->links() }}</div>
            @else
                <div class="p-12 text-center text-gray-500"><i class="ti ti-history text-4xl text-gray-300"></i><p class="mt-3 text-sm">Belum ada riwayat persetujuan.</p></div>
            @endif
        </div>
    </div>
</x-app-layout>
