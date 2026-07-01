<x-app-layout>
    <x-slot name="title">Daftar Ormawa Prodi</x-slot>
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6"><div><h2 class="text-lg font-semibold text-gray-900">Daftar Ormawa Prodi</h2><p class="text-[12px] text-gray-500">Organisasi mahasiswa di bawah {{ auth()->user()->programStudiKaprodi?->nama }}</p></div><div><div class="text-[20px] font-bold">{{ $ormawa->total() }}</div><div class="text-[11px] text-gray-500">Total Ormawa</div></div></div>
    <div class="table-card">
        <div class="p-4 sm:p-6 border-b border-gray-100"><form method="GET" action="{{ route('kaprodi.ormawa.index') }}" class="flex flex-col sm:flex-row gap-3"><div class="flex-1 relative"><i class="ti ti-search absolute left-3 top-2.5 text-gray-400"></i><input name="search" value="{{ request('search') }}" class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px]" placeholder="Cari nama Ormawa..."></div><button class="px-4 py-2 bg-gray-900 text-white rounded-lg text-[13px] font-medium">Cari</button>@if(request('search'))<a href="{{ route('kaprodi.ormawa.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg text-[13px] text-center">Reset</a>@endif</form></div>
        @if($ormawa->count())
            <div class="overflow-x-auto"><table><thead><tr><th>No</th><th>Nama Ormawa</th><th>Ketua</th><th>Periode</th><th>Total Pengajuan</th><th>Aksi</th></tr></thead><tbody>
            @foreach($ormawa as $item)
                <tr><td class="text-gray-500">{{ $ormawa->firstItem()+$loop->index }}</td><td><div class="font-semibold">{{ $item->nama_ormawa }}</div><div class="text-[11px] text-gray-500">{{ $item->program_studi }}</div></td><td><div class="text-[13px]"><i class="ti ti-user text-gray-400"></i> {{ $item->ketua }}</div>@if($item->kontak)<div class="text-[11px] text-gray-500"><i class="ti ti-phone"></i> {{ $item->kontak }}</div>@endif</td><td>{{ $item->periode ?? '-' }}</td><td><span class="font-semibold">{{ $item->pengajuan_kegiatan_count }}</span> <span class="text-gray-400 text-[12px]">Kegiatan</span></td><td><a href="{{ route('kaprodi.ormawa.show',$item) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand/10 text-brand rounded-md hover:bg-brand hover:text-white text-[12px] font-medium"><i class="ti ti-eye"></i> Detail</a></td></tr>
            @endforeach
            </tbody></table></div><div class="p-4 border-t border-gray-100">{{ $ormawa->links() }}</div>
        @else
            <div class="p-12 text-center"><div class="w-16 h-16 mx-auto bg-gray-50 rounded-full flex items-center justify-center text-gray-400 text-3xl"><i class="ti ti-users"></i></div><h3 class="mt-4 text-[15px] font-semibold">Belum Ada Ormawa</h3><p class="text-[13px] text-gray-500">Belum ada Ormawa yang terhubung dengan prodi Anda.</p></div>
        @endif
    </div>
</x-app-layout>
