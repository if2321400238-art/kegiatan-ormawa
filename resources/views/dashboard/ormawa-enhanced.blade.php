<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">
            Dashboard - {{ auth()->user()->ormawa->nama_ormawa }}
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Alert Messages --}}
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if (session('warning'))
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative">
                    <span class="block sm:inline">{{ session('warning') }}</span>
                </div>
            @endif

            {{-- Key Statistics Cards --}}
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4 mb-6">
                {{-- Total Pengajuan --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-blue-500">
                    <div class="p-3 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <dt class="text-xs sm:text-sm font-medium text-gray-500">Total Pengajuan</dt>
                                <dd class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $stats['total_pengajuan'] }}</dd>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Pending --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-yellow-500">
                    <div class="p-3 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <dt class="text-xs sm:text-sm font-medium text-gray-500">Menunggu Approval</dt>
                                <dd class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $stats['menunggu_verifikasi'] }}</dd>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Approved --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-green-500">
                    <div class="p-3 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <dt class="text-xs sm:text-sm font-medium text-gray-500">Disetujui</dt>
                                <dd class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $stats['disetujui'] }}</dd>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Revision --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-orange-500">
                    <div class="p-3 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-orange-100 rounded-md p-3">
                                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4v.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <dt class="text-xs sm:text-sm font-medium text-gray-500">Perlu Revisi</dt>
                                <dd class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $stats['revisi'] }}</dd>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

            {{-- Additional Quick Stats --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
                <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
                    <div class="text-xs text-gray-600 font-medium">Draft</div>
                    <div class="text-2xl font-bold text-gray-900">{{ $stats['draft'] }}</div>
                </div>
                <div class="bg-red-50 rounded-lg p-3 sm:p-4 border-l-4 border-red-500">
                    <div class="text-xs text-gray-600 font-medium">Ditolak</div>
                    <div class="text-2xl font-bold text-red-700">{{ $stats['ditolak'] }}</div>
                </div>

                <div class="bg-blue-50 rounded-lg p-3 sm:p-4 border-l-4 border-blue-500">
                    <div class="text-xs text-gray-600 font-medium">Kegiatan Mendatang</div>
                    <div class="text-2xl font-bold text-blue-700">{{ $upcomingEvents->count() }}</div>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold mb-4">Aksi Cepat</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                        <a href="{{ route('pengajuan.create') }}" class="flex flex-col sm:flex-row sm:items-start p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition gap-3 border border-blue-200">
                            <svg class="h-6 w-6 sm:h-8 sm:w-8 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Ajukan Kegiatan Baru</p>
                                <p class="text-xs text-gray-500">Buat pengajuan kegiatan</p>
                            </div>
                        </a>



                        <a href="{{ route('pengajuan.index') }}" class="flex flex-col sm:flex-row sm:items-start p-4 bg-green-50 rounded-lg hover:bg-green-100 transition gap-3 border border-green-200">
                            <svg class="h-6 w-6 sm:h-8 sm:w-8 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Lihat Riwayat</p>
                                <p class="text-xs text-gray-500">Semua pengajuan</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Recent Pengajuan --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-4 gap-2">
                        <h3 class="text-base sm:text-lg font-semibold">Pengajuan Terbaru</h3>
                        <a href="{{ route('pengajuan.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                            Lihat Semua →
                        </a>
                    </div>

                    @if($recentPengajuan->count() > 0)
                        {{-- Desktop Table --}}
                        <div class="hidden md:block overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Judul Kegiatan
                                        </th>
                                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentPengajuan as $pengajuan)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 sm:px-6 py-3 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $pengajuan->judul_kegiatan }}
                                                </div>
                                            </td>
                                            <td class="px-4 sm:px-6 py-3 whitespace-nowrap text-sm text-gray-500">
                                                {{ $pengajuan->tanggal_mulai->format('d M Y') }}
                                            </td>
                                            <td class="px-4 sm:px-6 py-3 whitespace-nowrap">
                                                @php
                                                    $statusColors = [
                                                        'draft' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Draft'],
                                                        'menunggu_dosen' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Pending'],
                                                        'menunggu_warek3' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Disetujui BAUAK'],
                                                        'disetujui' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Disetujui'],
                                                        'revisi_bauak' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Revisi'],
                                                        'ditolak' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Ditolak'],
                                                    ];
                                                    $color = $statusColors[$pengajuan->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => $pengajuan->status];
                                                @endphp
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color['bg'] }} {{ $color['text'] }}">
                                                    {{ $color['label'] }}
                                                </span>
                                            </td>
                                            <td class="px-4 sm:px-6 py-3 whitespace-nowrap text-sm font-medium">
                                                <a href="{{ route('pengajuan.show', $pengajuan) }}" class="text-blue-600 hover:text-blue-900">
                                                    Detail
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Mobile Cards --}}
                        <div class="md:hidden space-y-3">
                            @foreach($recentPengajuan as $pengajuan)
                                @php
                                    $statusColors = [
                                        'draft' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Draft'],
                                        'menunggu_dosen' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Pending'],
                                        'menunggu_warek3' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Disetujui BAUAK'],
                                        'disetujui' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Disetujui'],
                                        'revisi_bauak' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Revisi'],
                                        'ditolak' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Ditolak'],
                                    ];
                                    $color = $statusColors[$pengajuan->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => $pengajuan->status];
                                @endphp
                                <div class="border rounded-lg p-3">
                                    <div class="flex justify-between items-start mb-2 gap-2">
                                        <h4 class="font-medium text-gray-900 text-sm break-all">{{ $pengajuan->judul_kegiatan }}</h4>
                                        <span class="px-2 py-1 text-xs rounded-full whitespace-nowrap {{ $color['bg'] }} {{ $color['text'] }}">
                                            {{ $color['label'] }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mb-2">📅 {{ $pengajuan->tanggal_mulai->format('d M Y') }}</p>
                                    <a href="{{ route('pengajuan.show', $pengajuan) }}" class="text-center w-full text-sm bg-blue-100 text-blue-600 rounded px-3 py-2 hover:bg-blue-200">
                                        Detail →
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-6 sm:py-8 text-sm">Belum ada pengajuan</p>
                    @endif
                </div>
            </div>

            {{-- Upcoming Events --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold mb-4">Kegiatan Mendatang</h3>

                    @if($upcomingEvents->count() > 0)
                        <div class="space-y-3">
                            @foreach($upcomingEvents as $event)
                                <div class="border-l-4 border-green-500 pl-3 sm:pl-4 py-2 bg-green-50 p-3 rounded">
                                    <h4 class="font-medium text-gray-900 text-sm sm:text-base">{{ $event->judul_kegiatan }}</h4>
                                    <p class="text-xs sm:text-sm text-gray-600">📍 {{ $event->lokasi_kegiatan }}</p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        📅 {{ $event->tanggal_mulai->format('d M Y') }} - {{ $event->tanggal_selesai->format('d M Y') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-6 sm:py-8 text-sm">Tidak ada kegiatan mendatang</p>
                    @endif
                </div>
            </div>
        </div>
    </div>


</x-app-layout>
