<x-app-layout>
    <x-slot name="title">Laporan Pertanggungjawaban</x-slot>
    <div class="flex justify-between items-start mb-6">
        <div><h2 class="text-lg font-semibold">Laporan Pertanggungjawaban</h2><p class="text-xs text-gray-500">Pantau LPJ dan penyelesaian kegiatan</p></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
        <form class="flex flex-col sm:flex-row gap-3">
            <input name="search" value="{{ request('search') }}" placeholder="Cari kegiatan{{ $ownerMode ? '' : ' atau Ormawa' }}..." class="flex-1 rounded-lg border-gray-300 text-sm">
            <select name="status" class="rounded-lg border-gray-300 text-sm">
                <option value="">Semua status</option>
                @if($ownerMode)<option value="belum_lpj" @selected(request('status') === 'belum_lpj')>Belum LPJ</option>@endif
                @foreach(['draft'=>'Draft','diajukan'=>'Menunggu BAUAK','revisi'=>'Revisi','diterima'=>'Diterima','ditolak'=>'Ditolak'] as $value=>$label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 bg-brand text-white rounded-lg text-sm">Filter</button>
        </form>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto"><table class="w-full text-sm">
            <thead class="bg-gray-50 text-xs uppercase text-gray-500"><tr><th class="text-left p-4">Kegiatan</th><th class="text-left p-4">Ormawa</th><th class="text-left p-4">Realisasi</th><th class="text-left p-4">Status</th><th class="p-4"></th></tr></thead>
            <tbody class="divide-y divide-gray-100">
            @if($ownerMode)
            @forelse($kegiatan as $item)
                <tr>
                    <td class="p-4"><div class="font-medium text-gray-900">{{ $item->judul_kegiatan }}</div><div class="text-xs text-gray-500 mt-1">{{ $item->tanggal_mulai->format('d M Y') }} – {{ $item->tanggal_selesai->format('d M Y') }}</div></td>
                    <td class="p-4">{{ $item->ormawa->nama_ormawa }}</td>
                    <td class="p-4">{{ $item->lpj ? 'Rp '.number_format($item->lpj->realisasi_anggaran,0,',','.') : '—' }}</td>
                    <td class="p-4">
                        @if(!$item->lpj)<span class="badge badge-gray">Belum LPJ</span>
                        @else<span class="badge {{ $item->lpj->status === 'diterima' ? 'badge-success' : ($item->lpj->status === 'ditolak' ? 'badge-danger' : 'badge-warning') }}">{{ $item->lpj->status_label }}</span>@endif
                    </td>
                    <td class="p-4 text-right whitespace-nowrap">
                        @if(!$item->lpj)<a href="{{ route('lpj.create',$item) }}" class="inline-flex items-center gap-1 px-3 py-2 bg-brand text-white rounded-lg text-xs font-medium"><i class="ti ti-plus"></i> Tambah LPJ</a>
                        @else<a class="text-brand font-medium" href="{{ route('lpj.show',$item->lpj) }}">Lihat LPJ</a>@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-10 text-center text-gray-500">
                    @if(!$hasOrganizationContext)
                        Belum ada organisasi yang terhubung atau dipilih untuk akun ini.
                    @else
                        Belum ada kegiatan yang disetujui.
                    @endif
                </td></tr>
            @endforelse
            @else
            @forelse($lpjs as $lpj)
                <tr><td class="p-4 font-medium">{{ $lpj->pengajuan->judul_kegiatan }}</td><td class="p-4">{{ $lpj->pengajuan->ormawa->nama_ormawa }}</td>
                    <td class="p-4">Rp {{ number_format($lpj->realisasi_anggaran,0,',','.') }}</td>
                    <td class="p-4"><span class="badge {{ $lpj->status === 'diterima' ? 'badge-success' : ($lpj->status === 'ditolak' ? 'badge-danger' : 'badge-warning') }}">{{ $lpj->status_label }}</span></td>
                    <td class="p-4 text-right"><a class="text-brand font-medium" href="{{ route('lpj.show',$lpj) }}">Lihat</a></td></tr>
            @empty
                <tr><td colspan="5" class="p-10 text-center text-gray-500">Belum ada LPJ.</td></tr>
            @endforelse
            @endif
            </tbody>
        </table></div>
        <div class="p-4">{{ ($ownerMode ? $kegiatan : $lpjs)->links() }}</div>
    </div>
</x-app-layout>
