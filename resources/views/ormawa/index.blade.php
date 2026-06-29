<x-app-layout>
    <x-slot name="title">Daftar Ormawa</x-slot>

    {{-- Top Header Section --}}
    <div class="page-header">
        <div class="page-header-main">
            <div class="page-header-title">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Ormawa</h2>
                <p class="text-[12px] text-gray-500">Kelola dan pantau informasi organisasi mahasiswa</p>
            </div>

            @if(auth()->user()->role === 'admin')
                <div class="page-header-actions">
                    <a href="{{ route('admin.ormawa.create') }}" class="w-full sm:w-auto px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand-active transition text-[13px] font-medium flex items-center justify-center gap-2 shadow-sm">
                        <i class="ti ti-plus"></i> Tambah Ormawa Baru
                    </a>
                </div>
            @endif
        </div>

        <div class="summary-stats">
            <div class="summary-stat-card" style="--accent: #3B82F6">
                <div class="text-[20px] font-bold text-gray-900">{{ $ormawa->total() ?? $ormawa->count() }}</div>
                <div class="text-[11px] text-gray-500 font-medium">Total Ormawa</div>
            </div>
            <div class="summary-stat-card" style="--accent: #10B981">
                <div class="text-[20px] font-bold text-success">{{ $ormawa->total() ?? $ormawa->count() }}</div>
                <div class="text-[11px] text-gray-500 font-medium">Aktif</div>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="table-card">
        {{-- Search & Filters Section --}}
        <div class="p-4 sm:p-6 border-b border-gray-100">
            @php
                // Rute Form Pencarian Dinamis
                $indexRoute = auth()->user()->role === 'bauak' ? route('bauak.ormawa.index') : route('admin.ormawa.index');
            @endphp
            <form method="GET" action="{{ $indexRoute }}" class="space-y-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ti ti-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" placeholder="Cari nama ormawa, ketua, atau pembina..."
                            value="{{ request('search') }}"
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors">
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <button type="submit" class="flex-1 sm:flex-initial px-4 py-2 bg-gray-900 text-white rounded-lg text-[13px] font-medium hover:bg-gray-800 transition">
                            Cari
                        </button>
                        @if(request('search'))
                            <a href="{{ $indexRoute }}" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-[13px] font-medium hover:bg-gray-200 transition flex items-center justify-center gap-1">
                                <i class="ti ti-refresh"></i> Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        @if($ormawa->count() > 0)
            {{-- Desktop Table (hidden on mobile) --}}
            <div class="hidden md:block overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Ormawa</th>
                            <th>Ketua</th>
                            <th>Pembina</th>
                            <th>Kontak</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ormawa as $item)
                            <tr>
                                <td>
                                    <span class="text-[13px] text-gray-500 font-medium">{{ $loop->iteration }}</span>
                                </td>
                                <td>
                                    <div class="font-semibold text-gray-900">{{ $item->nama_ormawa }}</div>
                                </td>
                                <td>
                                    <div class="text-[13px] text-gray-700 flex items-center gap-1.5">
                                        <i class="ti ti-user text-gray-400"></i> {{ $item->ketua }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-[13px] text-gray-700 flex items-center gap-1.5">
                                        <i class="ti ti-shield-check text-gray-400"></i> {{ $item->pembina }}
                                    </div>
                                </td>
                                <td>
                                    <div class="text-[13px] text-gray-600 flex items-center gap-1.5">
                                        <i class="ti ti-phone text-gray-400"></i> {{ $item->kontak }}
                                    </div>
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        @php
                                            // Deteksi rute Edit & Destroy Dinamis untuk Desktop
                                            $isBauak = auth()->user()->role === 'bauak';
                                            $editRoute = $isBauak ? route('bauak.ormawa.edit', $item->id) : route('admin.ormawa.edit', $item->id);
                                            $destroyRoute = $isBauak ? route('bauak.ormawa.destroy', $item->id) : route('admin.ormawa.destroy', $item->id);
                                        @endphp

                                        <a href="{{ $editRoute }}" class="p-1.5 bg-warning-light text-warning rounded-md hover:bg-warning hover:text-white transition-colors" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>

                                        @if(auth()->user()->role === 'admin' || auth()->user()->role === 'bauak')
                                            <form action="{{ $destroyRoute }}" method="POST" class="inline" onsubmit="return confirm('Hapus ormawa ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 bg-danger-light text-danger rounded-md hover:bg-danger hover:text-white transition-colors" title="Hapus">
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </form>
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
                    @foreach ($ormawa as $item)
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex justify-between items-start gap-2 mb-2">
                                <h3 class="text-[14px] font-semibold text-gray-900 leading-tight">{{ $item->nama_ormawa }}</h3>
                                <span class="text-[11px] text-gray-400 font-bold uppercase tracking-wider">#{{ $loop->iteration }}</span>
                            </div>

                            <div class="grid grid-cols-1 gap-2 text-[12px] mb-4">
                                <div class="flex items-center gap-2 text-gray-700">
                                    <i class="ti ti-user text-gray-400 w-4"></i>
                                    <span><span class="text-gray-400">Ketua:</span> {{ $item->ketua }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-gray-700">
                                    <i class="ti ti-shield-check text-gray-400 w-4"></i>
                                    <span><span class="text-gray-400">Pembina:</span> {{ $item->pembina }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-gray-700">
                                    <i class="ti ti-phone text-gray-400 w-4"></i>
                                    <span><span class="text-gray-400">Kontak:</span> {{ $item->kontak }}</span>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                @php
                                    // Deteksi rute Edit & Destroy Dinamis untuk Mobile
                                    $isBauak = auth()->user()->role === 'bauak';
                                    $editRoute = $isBauak ? route('bauak.ormawa.edit', $item->id) : route('admin.ormawa.edit', $item->id);
                                    $destroyRoute = $isBauak ? route('bauak.ormawa.destroy', $item->id) : route('admin.ormawa.destroy', $item->id);
                                @endphp

                                <a href="{{ $editRoute }}" class="flex-1 text-center py-2 px-3 text-[12px] bg-warning-light text-warning font-medium rounded-lg hover:bg-warning hover:text-white transition-colors">
                                    Edit
                                </a>

                                @if(auth()->user()->role === 'admin' || auth()->user()->role === 'bauak')
                                    <form action="{{ $destroyRoute }}" method="POST" class="flex-1" onsubmit="return confirm('Hapus ormawa ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full text-center py-2 px-3 text-[12px] bg-danger-light text-danger font-medium rounded-lg hover:bg-danger hover:text-white transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Pagination Footer --}}
            @if(method_exists($ormawa, 'links'))
                <div class="p-4 border-t border-gray-100">
                    <div class="mb-3 text-[12px] text-gray-500 text-center md:text-left">
                        Menampilkan <span class="font-medium text-gray-900">{{ $ormawa->firstItem() ?? 0 }}</span> - <span class="font-medium text-gray-900">{{ $ormawa->lastItem() ?? 0 }}</span> dari <span class="font-medium text-gray-900">{{ $ormawa->total() }}</span> Ormawa
                    </div>
                    {{ $ormawa->links('pagination::tailwind') }}
                </div>
            @endif
        @else
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center p-12 text-center">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 text-3xl mb-4">
                    <i class="ti ti-folder-off"></i>
                </div>
                <h3 class="text-[15px] font-semibold text-gray-900 mb-1">Tidak ada data ormawa</h3>
                <p class="text-[13px] text-gray-500 mb-6">Belum ada data organisasi mahasiswa yang sesuai dengan pencarian Anda.</p>
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.ormawa.create') }}" class="px-5 py-2.5 bg-brand text-white rounded-lg hover:bg-brand-active transition text-[13px] font-medium flex items-center gap-2 shadow-sm">
                        <i class="ti ti-plus"></i> Tambah Ormawa Pertama
                    </a>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
