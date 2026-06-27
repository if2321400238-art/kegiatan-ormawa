<x-app-layout>
    <x-slot name="title">Daftar Fakultas</x-slot>

    {{-- Top Header Section --}}
    <div class="page-header">
        <div class="page-header-main">
            <div class="page-header-title">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Fakultas</h2>
                <p class="text-[12px] text-gray-500">Kelola informasi fakultas dan penugasan Dekan</p>
            </div>

            <a href="{{ route('admin.fakultas.create') }}" class="w-full sm:w-auto px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand-active transition text-[13px] font-medium flex items-center justify-center gap-2 shadow-sm">
                <i class="ti ti-plus"></i> Tambah Fakultas
            </a>
        </div>

        <div class="summary-stats">
            <div class="summary-stat">
                <div class="text-[20px] font-bold text-gray-900">{{ $fakultas->total() ?? $fakultas->count() }}</div>
                <div class="text-[11px] text-gray-500 font-medium">Total Fakultas</div>
            </div>
        </div>
    </div>

    {{-- Main Container Card --}}
    <div class="table-card">
        @if($fakultas->count() > 0)
            {{-- Desktop Table (hidden on mobile) --}}
            <div class="hidden md:block overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Nama Fakultas</th>
                            <th>Dekan</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fakultas as $item)
                            <tr>
                                <td>
                                    <span class="text-[13px] text-gray-500 font-medium">{{ $loop->iteration }}</span>
                                </td>
                                <td>
                                    <div class="font-semibold text-gray-900">{{ $item->nama }}</div>
                                </td>
                                <td>
                                    @if($item->dekan)
                                        <div class="text-[13px] text-gray-700 flex items-center gap-1.5">
                                            <i class="ti ti-user text-gray-400"></i> {{ $item->dekan->nama }}
                                        </div>
                                    @else
                                        <div class="text-[13px] text-gray-400 flex items-center gap-1.5 italic">
                                            <i class="ti ti-user-off text-gray-300"></i> Belum ada Dekan
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.fakultas.edit', $item->id) }}" class="p-1.5 bg-warning-light text-warning rounded-md hover:bg-warning hover:text-white transition-colors" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>

                                        <form action="{{ route('admin.fakultas.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus fakultas ini? Tindakan ini akan mengosongkan relasi Dekan yang terkait.');">
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
                    @foreach ($fakultas as $item)
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex justify-between items-start gap-2 mb-2">
                                <h3 class="text-[14px] font-semibold text-gray-900 leading-tight">{{ $item->nama }}</h3>
                                <span class="text-[11px] text-gray-400 font-bold uppercase tracking-wider">#{{ $loop->iteration }}</span>
                            </div>

                            <div class="grid grid-cols-1 gap-2 text-[12px] mb-4">
                                <div class="flex items-center gap-2 text-gray-700">
                                    <i class="ti ti-user text-gray-400 w-4"></i>
                                    <span><span class="text-gray-400">Dekan:</span> {{ $item->dekan ? $item->dekan->nama : 'Belum ditentukan' }}</span>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <a href="{{ route('admin.fakultas.edit', $item->id) }}" class="flex-1 text-center py-2 px-3 text-[12px] bg-warning-light text-warning font-medium rounded-lg hover:bg-warning hover:text-white transition-colors">
                                    Edit
                                </a>

                                <form action="{{ route('admin.fakultas.destroy', $item->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Hapus fakultas ini?');">
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
            @if(method_exists($fakultas, 'links'))
                <div class="p-4 border-t border-gray-100">
                    <div class="mb-3 text-[12px] text-gray-500 text-center md:text-left">
                        Menampilkan <span class="font-medium text-gray-900">{{ $fakultas->firstItem() ?? 0 }}</span> - <span class="font-medium text-gray-900">{{ $fakultas->lastItem() ?? 0 }}</span> dari <span class="font-medium text-gray-900">{{ $fakultas->total() }}</span> Fakultas
                    </div>
                    {{ $fakultas->links('pagination::tailwind') }}
                </div>
            @endif
        @else
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center p-12 text-center">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 text-3xl mb-4">
                    <i class="ti ti-folder-off"></i>
                </div>
                <h3 class="text-[15px] font-semibold text-gray-900 mb-1">Tidak ada data Fakultas</h3>
                <p class="text-[13px] text-gray-500 mb-6">Belum ada data fakultas yang terdaftar.</p>
                <a href="{{ route('admin.fakultas.create') }}" class="px-5 py-2.5 bg-brand text-white rounded-lg hover:bg-brand-active transition text-[13px] font-medium flex items-center gap-2 shadow-sm">
                    <i class="ti ti-plus"></i> Tambah Fakultas Pertama
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
