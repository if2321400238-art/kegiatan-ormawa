<x-app-layout>
    <x-slot name="title">Daftar Akun Dekan</x-slot>

    {{-- Top Header Section --}}
    <div class="page-header">
        <div class="page-header-main">
            <div class="page-header-title">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Akun Dekan</h2>
                <p class="text-[12px] text-gray-500">Kelola informasi akun pengguna dengan peran Dekan</p>
            </div>

            <a href="{{ route('admin.dekan.create') }}" class="w-full sm:w-auto px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand-active transition text-[13px] font-medium flex items-center justify-center gap-2 shadow-sm">
                <i class="ti ti-plus"></i> Tambah Akun Dekan
            </a>
        </div>

        <div class="summary-stats">
            <div class="summary-stat">
                <div class="text-[20px] font-bold text-gray-900">{{ $dekanList->total() ?? $dekanList->count() }}</div>
                <div class="text-[11px] text-gray-500 font-medium">Total Akun</div>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="table-card">
        {{-- Search & Filters Section --}}
        <div class="p-4 sm:p-6 border-b border-gray-100">
            <form method="GET" action="{{ route('admin.dekan.index') }}" class="space-y-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ti ti-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" placeholder="Cari nama, email, atau username dekan..."
                            value="{{ request('search') }}"
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors">
                    </div>
                    <div class="flex gap-2 w-full sm:w-auto">
                        <button type="submit" class="flex-1 sm:flex-initial px-4 py-2 bg-gray-900 text-white rounded-lg text-[13px] font-medium hover:bg-gray-800 transition">
                            Cari
                        </button>
                        @if(request('search'))
                            <a href="{{ route('admin.dekan.index') }}" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-[13px] font-medium hover:bg-gray-200 transition flex items-center justify-center gap-1">
                                <i class="ti ti-refresh"></i> Reset
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        @if($dekanList->count() > 0)
            {{-- Desktop Table (hidden on mobile) --}}
            <div class="hidden md:block overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Info Akun</th>
                            <th>Fakultas</th>
                            <th>Status</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dekanList as $item)
                            <tr>
                                <td>
                                    <span class="text-[13px] text-gray-500 font-medium">{{ $dekanList->firstItem() + $loop->index }}</span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-brand/10 text-brand flex items-center justify-center font-bold text-xs uppercase overflow-hidden shrink-0">
                                            @if($item->avatar)
                                                <img src="{{ $item->avatar }}" alt="{{ $item->nama }}" class="w-full h-full object-cover">
                                            @else
                                                {{ substr($item->nama, 0, 2) }}
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ $item->nama }}</div>
                                            <div class="text-[11px] text-gray-500">{{ $item->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($item->fakultas)
                                        <div class="text-[13px] text-gray-700 flex items-center gap-1.5">
                                            <i class="ti ti-school text-gray-400"></i> {{ $item->fakultas->nama }}
                                        </div>
                                    @else
                                        <div class="text-[13px] text-gray-400 flex items-center gap-1.5 italic">
                                            <i class="ti ti-school-off text-gray-300"></i> Belum terhubung
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.dekan.edit', $item->id) }}" class="p-1.5 bg-warning-light text-warning rounded-md hover:bg-warning hover:text-white transition-colors" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>

                                        <form action="{{ route('admin.dekan.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus akun dekan ini? Tindakan ini akan mengosongkan relasi Fakultas yang terkait.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 bg-danger-light text-danger rounded-md hover:bg-danger hover:text-white transition-colors" title="Hapus">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
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
                    @foreach ($dekanList as $item)
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex justify-between items-start gap-2 mb-2">
                                <h3 class="text-[14px] font-semibold text-gray-900 leading-tight">{{ $item->nama }}</h3>
                                @if($item->is_active)
                                    <span class="badge badge-success text-[10px]">Aktif</span>
                                @else
                                    <span class="badge badge-danger text-[10px]">Nonaktif</span>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 gap-2 text-[12px] mb-4">
                                <div class="flex items-center gap-2 text-gray-700">
                                    <i class="ti ti-mail text-gray-400 w-4"></i>
                                    <span>{{ $item->email }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-gray-700">
                                    <i class="ti ti-school text-gray-400 w-4"></i>
                                    <span><span class="text-gray-400">Fakultas:</span> {{ $item->fakultas ? $item->fakultas->nama : 'Belum terhubung' }}</span>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <a href="{{ route('admin.dekan.edit', $item->id) }}" class="flex-1 text-center py-2 px-3 text-[12px] bg-warning-light text-warning font-medium rounded-lg hover:bg-warning hover:text-white transition-colors">
                                    Edit
                                </a>

                                <form action="{{ route('admin.dekan.destroy', $item->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Hapus akun dekan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full text-center py-2 px-3 text-[12px] bg-danger-light text-danger font-medium rounded-lg hover:bg-danger hover:text-white transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Pagination Footer --}}
            @if(method_exists($dekanList, 'links'))
                <div class="p-4 border-t border-gray-100">
                    <div class="mb-3 text-[12px] text-gray-500 text-center md:text-left">
                        Menampilkan <span class="font-medium text-gray-900">{{ $dekanList->firstItem() ?? 0 }}</span> - <span class="font-medium text-gray-900">{{ $dekanList->lastItem() ?? 0 }}</span> dari <span class="font-medium text-gray-900">{{ $dekanList->total() }}</span> Akun
                    </div>
                    {{ $dekanList->links('pagination::tailwind') }}
                </div>
            @endif
        @else
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center p-12 text-center">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 text-3xl mb-4">
                    <i class="ti ti-users"></i>
                </div>
                <h3 class="text-[15px] font-semibold text-gray-900 mb-1">Tidak ada data Akun Dekan</h3>
                <p class="text-[13px] text-gray-500 mb-6">Belum ada data akun pengguna yang terdaftar sebagai dekan.</p>
                <a href="{{ route('admin.dekan.create') }}" class="px-5 py-2.5 bg-brand text-white rounded-lg hover:bg-brand-active transition text-[13px] font-medium flex items-center gap-2 shadow-sm">
                    <i class="ti ti-plus"></i> Tambah Akun Dekan Pertama
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
