<x-app-layout>
    <x-slot name="title">Ormawa Binaan</x-slot>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Daftar Ormawa Binaan</h2>
            <p class="text-[12px] text-gray-500">Daftar organisasi mahasiswa di bawah bimbingan Anda</p>
        </div>

        <div class="flex gap-4">
            <div>
                <div class="text-[20px] font-bold text-gray-900">{{ $ormawaBinaan->total() }}</div>
                <div class="text-[11px] text-gray-500 font-medium">Total Ormawa</div>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="table-card">
        {{-- Search Section --}}
        <div class="p-4 sm:p-6 border-b border-gray-100">
            <form method="GET" action="{{ route('dosen.ormawa.index') }}" class="space-y-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ti ti-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" placeholder="Cari nama ormawa..."
                            value="{{ request('search') }}"
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors">
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <button type="submit" class="flex-1 sm:flex-initial px-4 py-2 bg-gray-900 text-white rounded-lg text-[13px] font-medium hover:bg-gray-800 transition">
                            Cari
                        </button>
                        @if(request('search'))
                            <a href="{{ route('dosen.ormawa.index') }}" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-[13px] font-medium hover:bg-gray-200 transition flex items-center justify-center gap-1">
                                <i class="ti ti-refresh"></i> Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        @if($ormawaBinaan->count() > 0)
            <div class="overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Ormawa</th>
                            <th>Kategori</th>
                            <th>Ketua</th>
                            <th>Total Pengajuan</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ormawaBinaan as $item)
                            <tr>
                                <td>
                                    <span class="text-[13px] text-gray-500 font-medium">{{ $ormawaBinaan->firstItem() + $loop->index }}</span>
                                </td>
                                <td>
                                    <div class="font-semibold text-gray-900">{{ $item->nama_ormawa }}</div>
                                    <div class="text-[11px] text-gray-500 mt-0.5">Periode: {{ $item->periode ?? '-' }}</div>
                                </td>
                                <td>
                                    @if($item->isInternal())
                                        <span class="badge badge-brand">Internal</span>
                                    @else
                                        <span class="badge badge-warning">Eksternal</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-[13px] text-gray-700 flex items-center gap-1.5">
                                        <i class="ti ti-user text-gray-400"></i> {{ $item->ketua }}
                                    </div>
                                    @if($item->kontak)
                                        <div class="text-[11px] text-gray-500 flex items-center gap-1.5 mt-0.5">
                                            <i class="ti ti-phone text-gray-300"></i> {{ $item->kontak }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-[13px] font-semibold text-gray-900">
                                        {{ $item->pengajuan_kegiatan_count }} <span class="text-gray-400 font-normal">Kegiatan</span>
                                    </div>
                                </td>
                                <td>
                                    <a href="{{ route('dosen.ormawa.show', $item->id) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand/10 text-brand rounded-md hover:bg-brand hover:text-white transition-colors text-[12px] font-medium">
                                        <i class="ti ti-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if(method_exists($ormawaBinaan, 'links'))
                <div class="p-4 border-t border-gray-100">
                    {{ $ormawaBinaan->links('pagination::tailwind') }}
                </div>
            @endif
        @else
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center p-12 text-center">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 text-3xl mb-4">
                    <i class="ti ti-users"></i>
                </div>
                <h3 class="text-[15px] font-semibold text-gray-900 mb-1">Belum Ada Ormawa Binaan</h3>
                <p class="text-[13px] text-gray-500 mb-6">Anda belum ditugaskan sebagai pembina untuk Ormawa manapun.</p>
            </div>
        @endif
    </div>
</x-app-layout>
