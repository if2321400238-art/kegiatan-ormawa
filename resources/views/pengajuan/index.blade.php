<x-app-layout>
    <x-slot name="title">Daftar Pengajuan Kegiatan</x-slot>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Pengajuan Kegiatan</h2>
            <p class="text-[12px] text-gray-500">Kelola dan pantau status pengajuan kegiatan</p>
        </div>
        @if(auth()->user()->role === 'ormawa')
        <a href="{{ route('pengajuan.create') }}" class="w-full sm:w-auto px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand-active transition text-[13px] font-medium flex items-center justify-center gap-2 shadow-sm">
            <i class="ti ti-plus"></i> Ajukan Kegiatan Baru
        </a>
        @endif
    </div>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <div class="stat-card !p-4" style="--accent: #3B82F6">
            <div class="text-[20px] font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</div>
            <div class="text-[11px] text-gray-500 font-medium">Total</div>
        </div>
        <div class="stat-card !p-4" style="--accent: #9CA3AF">
            <div class="text-[20px] font-bold text-gray-700">{{ $stats['draft'] ?? 0 }}</div>
            <div class="text-[11px] text-gray-500 font-medium">Draft</div>
        </div>
        <div class="stat-card !p-4" style="--accent: #F59E0B">
            <div class="text-[20px] font-bold text-warning">{{ $stats['pending'] ?? 0 }}</div>
            <div class="text-[11px] text-gray-500 font-medium">Pending</div>
        </div>
        <div class="stat-card !p-4" style="--accent: #10B981">
            <div class="text-[20px] font-bold text-success">{{ $stats['approved'] ?? 0 }}</div>
            <div class="text-[11px] text-gray-500 font-medium">Disetujui</div>
        </div>
        <div class="stat-card !p-4" style="--accent: #EF4444">
            <div class="text-[20px] font-bold text-danger">{{ $stats['rejected'] ?? 0 }}</div>
            <div class="text-[11px] text-gray-500 font-medium">Ditolak</div>
        </div>
        <div class="stat-card !p-4" style="--accent: #F97316">
            <div class="text-[20px] font-bold text-orange">{{ $stats['revision'] ?? 0 }}</div>
            <div class="text-[11px] text-gray-500 font-medium">Revisi</div>
        </div>
    </div>

    <div class="table-card">
        <div class="p-4 sm:p-6 border-b border-gray-100">
            {{-- Search & Filters Section --}}
            <form method="GET" action="{{ route('pengajuan.index') }}" class="space-y-4">
                {{-- Search Box --}}
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ti ti-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" placeholder="Cari pengajuan atau ormawa..."
                            value="{{ request('search') }}"
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg text-[13px] font-medium hover:bg-gray-800 transition">
                        Cari
                    </button>
                </div>

                {{-- Filter Dropdowns & Date Range --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    {{-- Status Filter --}}
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 uppercase tracking-wider mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:bg-white transition-colors appearance-none">
                            <option value="">Semua Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="diajukan" {{ request('status') == 'diajukan' ? 'selected' : '' }}>Pending</option>
                            <option value="disetujui_bauak" {{ request('status') == 'disetujui_bauak' ? 'selected' : '' }}>Disetujui BAUAK</option>
                            <option value="disetujui_warek3" {{ request('status') == 'disetujui_warek3' ? 'selected' : '' }}>Disetujui Warek III</option>
                            <option value="revisi_bauak" {{ request('status') == 'revisi_bauak' ? 'selected' : '' }}>Revisi</option>
                            <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        </select>
                    </div>

                    {{-- Date From --}}
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 uppercase tracking-wider mb-1">Dari Tanggal</label>
                        <input type="date" name="tanggal_dari" value="{{ request('tanggal_dari') }}"
                            class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:bg-white transition-colors">
                    </div>

                    {{-- Date To --}}
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                        <input type="date" name="tanggal_sampai" value="{{ request('tanggal_sampai') }}"
                            class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:bg-white transition-colors">
                    </div>

                    {{-- Per Page --}}
                    <div>
                        <label class="block text-[11px] font-medium text-gray-500 uppercase tracking-wider mb-1">Tampil</label>
                        <select name="per_page" onchange="this.form.submit()" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:bg-white transition-colors appearance-none">
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 baris</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 baris</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 baris</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 baris</option>
                        </select>
                    </div>

                    {{-- Reset Button --}}
                    <div class="flex items-end">
                        <a href="{{ route('pengajuan.index') }}" class="w-full px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-[13px] font-medium hover:bg-gray-200 transition text-center flex items-center justify-center gap-1">
                            <i class="ti ti-refresh"></i> Reset
                        </a>
                    </div>
                </div>

                <button type="submit" class="w-full px-4 py-2 bg-brand text-white rounded-lg text-[13px] font-medium hover:bg-brand-active transition lg:hidden">
                    Terapkan Filter
                </button>
            </form>

            {{-- Export Buttons --}}
            <div class="mt-4 flex flex-wrap gap-2">
                <a href="{{ route('pengajuan.exportCSV', array_merge(request()->query())) }}" class="px-3 py-2 bg-success text-white rounded-lg text-[12px] font-medium hover:bg-success/90 transition flex items-center gap-2">
                    <i class="ti ti-file-spreadsheet"></i> Export Excel
                </a>
                <a href="{{ route('pengajuan.printView', array_merge(request()->query())) }}" target="_blank" class="px-3 py-2 bg-gray-800 text-white rounded-lg text-[12px] font-medium hover:bg-gray-900 transition flex items-center gap-2">
                    <i class="ti ti-printer"></i> Cetak / PDF
                </a>
            </div>

            {{-- Active Filters Display --}}
            @if(request('search') || request('status') || request('tanggal_dari') || request('tanggal_sampai'))
                <div class="mt-4 flex flex-wrap items-center gap-2">
                    <span class="text-[11px] text-gray-500 font-medium">Filter Aktif:</span>
                    @if(request('search'))
                        <span class="badge badge-info">Pencarian: {{ request('search') }}</span>
                    @endif
                    @if(request('status'))
                        <span class="badge badge-info">Status: {{ request('status') }}</span>
                    @endif
                    @if(request('tanggal_dari') || request('tanggal_sampai'))
                        <span class="badge badge-info">{{ request('tanggal_dari') }} s/d {{ request('tanggal_sampai') }}</span>
                    @endif
                </div>
            @endif
        </div>

        {{-- Desktop Table (hidden on mobile) --}}
        @if($pengajuan->count() > 0)
            <div class="hidden md:block overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th>Judul Kegiatan</th>
                            <th>Nama Ormawa</th>
                            <th>Lokasi</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pengajuan as $item)
                            <tr>
                                <td>
                                    <div class="font-semibold text-gray-900 mb-0.5">{{ $item->judul_kegiatan }}</div>
                                    <div class="text-[11px] text-gray-500 flex items-center gap-1">
                                        <i class="ti ti-user text-gray-400"></i> Ketua: {{ $item->ketua_pelaksana }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-[13px] text-gray-700">{{ $item->ormawa->nama_ormawa }}</div>
                                </td>
                                <td>
                                    <div class="text-[13px] text-gray-700 flex items-center gap-1">
                                        <i class="ti ti-map-pin text-gray-400"></i> {{ $item->lokasi_kegiatan }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-[13px] text-gray-700">
                                        {{ $item->tanggal_mulai->format('d M Y') }}
                                    </div>
                                    <div class="text-[11px] text-gray-500">
                                        s/d {{ $item->tanggal_selesai->format('d M Y') }}
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($item->status) {
                                            'draft' => 'badge-gray',
                                            'diajukan' => 'badge-warning',
                                            'disetujui_bauak' => 'badge-info',
                                            'disetujui_warek3' => 'badge-success',
                                            'revisi_bauak' => 'badge-orange',
                                            'ditolak' => 'badge-danger',
                                            default => 'badge-gray'
                                        };
                                        $statusLabel = match($item->status) {
                                            'diajukan' => 'Pending',
                                            'disetujui_bauak' => 'Disetujui BAUAK',
                                            'disetujui_warek3' => 'Disetujui Warek III',
                                            'revisi_bauak' => 'Revisi',
                                            default => ucwords($item->status)
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('pengajuan.show', $item) }}" class="p-1.5 bg-info-light text-info rounded-md hover:bg-info hover:text-white transition-colors" title="Detail">
                                            <i class="ti ti-eye"></i>
                                        </a>
                                        @if($item->canBeEditedBy(auth()->user()))
                                            <a href="{{ route('pengajuan.edit', $item) }}" class="p-1.5 bg-warning-light text-warning rounded-md hover:bg-warning hover:text-white transition-colors" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile Cards (visible on mobile only) --}}
            <div class="md:hidden">
                <div class="divide-y divide-gray-100">
                    @foreach($pengajuan as $item)
                        @php
                            $statusClass = match($item->status) {
                                'draft' => 'badge-gray',
                                'diajukan' => 'badge-warning',
                                'disetujui_bauak' => 'badge-info',
                                'disetujui_warek3' => 'badge-success',
                                'revisi_bauak' => 'badge-orange',
                                'ditolak' => 'badge-danger',
                                default => 'badge-gray'
                            };
                            $statusLabel = match($item->status) {
                                'diajukan' => 'Pending',
                                'disetujui_bauak' => 'Disetujui BAUAK',
                                'disetujui_warek3' => 'Disetujui Warek III',
                                'revisi_bauak' => 'Revisi',
                                default => ucwords($item->status)
                            };
                        @endphp
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex justify-between items-start gap-2 mb-2">
                                <h3 class="text-[14px] font-semibold text-gray-900 leading-tight">{{ $item->judul_kegiatan }}</h3>
                                <span class="badge {{ $statusClass }} whitespace-nowrap">{{ $statusLabel }}</span>
                            </div>
                            <div class="text-[12px] text-gray-500 mb-3">
                                {{ $item->ormawa->nama_ormawa }} &bull; Ketua: {{ $item->ketua_pelaksana }}
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-[12px] mb-4">
                                <div class="bg-gray-50 p-2 rounded-lg border border-gray-100">
                                    <div class="text-gray-400 mb-0.5"><i class="ti ti-map-pin"></i> Lokasi</div>
                                    <div class="font-medium text-gray-900">{{ $item->lokasi_kegiatan }}</div>
                                </div>
                                <div class="bg-gray-50 p-2 rounded-lg border border-gray-100">
                                    <div class="text-gray-400 mb-0.5"><i class="ti ti-calendar"></i> Tanggal</div>
                                    <div class="font-medium text-gray-900">{{ $item->tanggal_mulai->format('d M Y') }}</div>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <a href="{{ route('pengajuan.show', $item) }}" class="flex-1 text-center py-2 px-3 text-[12px] bg-info-light text-info font-medium rounded-lg hover:bg-info hover:text-white transition-colors">
                                    Detail
                                </a>
                                @if($item->canBeEditedBy(auth()->user()))
                                    <a href="{{ route('pengajuan.edit', $item) }}" class="flex-1 text-center py-2 px-3 text-[12px] bg-warning-light text-warning font-medium rounded-lg hover:bg-warning hover:text-white transition-colors">
                                        Edit
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Pagination --}}
            <div class="p-4 border-t border-gray-100">
                <div class="mb-3 text-[12px] text-gray-500 text-center md:text-left">
                    Menampilkan <span class="font-medium text-gray-900">{{ $pengajuan->firstItem() ?? 0 }}</span> - <span class="font-medium text-gray-900">{{ $pengajuan->lastItem() ?? 0 }}</span> dari <span class="font-medium text-gray-900">{{ $pengajuan->total() }}</span> pengajuan
                </div>
                {{ $pengajuan->links('pagination::tailwind') }}
            </div>
        @else
            <div class="flex flex-col items-center justify-center p-12 text-center">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 text-3xl mb-4">
                    <i class="ti ti-file-off"></i>
                </div>
                <h3 class="text-[15px] font-semibold text-gray-900 mb-1">Tidak ada data pengajuan</h3>
                <p class="text-[13px] text-gray-500 mb-6">Belum ada pengajuan kegiatan yang sesuai dengan filter pencarian Anda.</p>
                @if(auth()->user()->role === 'ormawa')
                <a href="{{ route('pengajuan.create') }}" class="px-5 py-2.5 bg-brand text-white rounded-lg hover:bg-brand-active transition text-[13px] font-medium flex items-center gap-2 shadow-sm">
                    <i class="ti ti-plus"></i> Ajukan Kegiatan Pertama
                </a>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
