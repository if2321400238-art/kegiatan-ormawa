<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">
            Persetujuan Pengajuan Kegiatan
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Statistics Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 mb-6">
                <div class="bg-white p-3 sm:p-6 rounded-lg shadow">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                        <div class="p-2 sm:p-3 rounded-full bg-yellow-100 text-yellow-600 flex-shrink-0">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm text-gray-500">Menunggu</p>
                            <p class="text-lg sm:text-2xl font-semibold">{{ $stats['menunggu'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-3 sm:p-6 rounded-lg shadow">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                        <div class="p-2 sm:p-3 rounded-full bg-green-100 text-green-600 flex-shrink-0">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm text-gray-500">Disetujui</p>
                            <p class="text-lg sm:text-2xl font-semibold">{{ $stats['disetujui'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-3 sm:p-6 rounded-lg shadow">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                        <div class="p-2 sm:p-3 rounded-full bg-red-100 text-red-600 flex-shrink-0">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs sm:text-sm text-gray-500">Ditolak</p>
                            <p class="text-lg sm:text-2xl font-semibold">{{ $stats['ditolak'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter --}}
            <div class="mb-4">
                <select onchange="window.location.href='?status='+this.value" class="w-full sm:w-auto px-4 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="">Semua Status</option>
                    <option value="menunggu_warek3" {{ request('status') == 'menunggu_warek3' ? 'selected' : '' }}>Menunggu Approval</option>
                    <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Sudah Disetujui</option>
                    <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                </select>
            </div>

            {{-- List Pengajuan --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    @if($pengajuan->count() > 0)
                        {{-- Desktop Table --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ormawa</th>
                                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul Kegiatan</th>
                                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($pengajuan as $index => $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $pengajuan->firstItem() + $index }}
                                            </td>
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ $item->ormawa->nama_ormawa }}</div>
                                            </td>
                                            <td class="px-4 sm:px-6 py-4">
                                                <div class="text-sm text-gray-900">{{ $item->judul_kegiatan }}</div>
                                            </td>
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $item->tanggal_mulai->format('d M Y') }}
                                            </td>
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-{{ $item->status_badge }}-100 text-{{ $item->status_badge }}-800">
                                                    {{ $item->status_label }}
                                                </span>
                                            </td>
                                            <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('warek3.persetujuan.show', $item) }}" class="text-blue-600 hover:text-blue-900">
                                                    {{ $item->status == 'menunggu_warek3' ? 'Review' : 'Lihat' }} →
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile Cards --}}
                        <div class="md:hidden space-y-3">
                            @foreach($pengajuan as $item)
                                <div class="border rounded-lg p-3">
                                    <div class="flex justify-between items-start gap-2 mb-2">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 break-all">{{ $item->judul_kegiatan }}</p>
                                            <p class="text-xs text-gray-500">{{ $item->ormawa->nama_ormawa }}</p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full whitespace-nowrap bg-{{ $item->status_badge }}-100 text-{{ $item->status_badge }}-800">
                                            {{ $item->status_label }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mb-3">📅 {{ $item->tanggal_mulai->format('d M Y') }}</p>
                                    <a href="{{ route('warek3.persetujuan.show', $item) }}" class="text-center w-full text-sm bg-blue-100 text-blue-600 rounded px-3 py-2 hover:bg-blue-200">
                                        {{ $item->status == 'menunggu_warek3' ? 'Review' : 'Lihat' }} →
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4">
                            {{ $pengajuan->links() }}
                        </div>
                    @else
                        <div class="text-center py-8 sm:py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="mt-4 text-gray-500 text-sm">Tidak ada pengajuan</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
