<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">
            Persetujuan Rektor
        </h2>
    </x-slot>

    <div class="py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Tab Navigation --}}
            <div class="mb-6 flex flex-wrap gap-4">
                <a href="?tab=menunggu" class="pb-2 border-b-2 {{ request('tab', 'menunggu') == 'menunggu' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-xs sm:text-sm font-medium">
                    Menunggu Persetujuan ({{ $pengajuanMenunggu->total() }})
                </a>
                <a href="?tab=riwayat" class="pb-2 border-b-2 {{ request('tab') == 'riwayat' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} text-xs sm:text-sm font-medium">
                    Riwayat Saya
                </a>
            </div>

            @if(request('tab', 'menunggu') == 'menunggu')
                {{-- Pengajuan Menunggu Persetujuan Rektor --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-semibold mb-4">Pengajuan Menunggu Persetujuan</h3>

                        @if($pengajuanMenunggu->count() > 0)
                            {{-- Desktop Table --}}
                            <div class="hidden md:block overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 text-sm">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ormawa</th>
                                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul Kegiatan</th>
                                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu Pengajuan</th>
                                            <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($pengajuanMenunggu as $index => $item)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $pengajuanMenunggu->firstItem() + $index }}
                                                </td>
                                                <td class="px-4 sm:px-6 py-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $item->ormawa->nama_ormawa }}</div>
                                                    <div class="text-xs text-gray-500">{{ $item->ormawa->ketua }}</div>
                                                </td>
                                                <td class="px-4 sm:px-6 py-4">
                                                    <div class="text-sm text-gray-900">{{ $item->judul_kegiatan }}</div>
                                                    <div class="text-xs text-gray-500">{{ $item->lokasi_kegiatan }}</div>
                                                </td>
                                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $item->tanggal_mulai->format('d M Y') }}
                                                </td>
                                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $item->created_at->diffForHumans() }}
                                                </td>
                                                <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <a href="{{ route('rektor.persetujuan.show', $item) }}" class="text-blue-600 hover:text-blue-900">
                                                        Review →
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Mobile Cards --}}
                            <div class="md:hidden space-y-3">
                                @foreach($pengajuanMenunggu as $item)
                                    <div class="border rounded-lg p-3">
                                        <div class="flex justify-between items-start gap-2 mb-2">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 break-all">{{ $item->judul_kegiatan }}</p>
                                                <p class="text-xs text-gray-500">{{ $item->ormawa->nama_ormawa }}</p>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                                            <div>
                                                <p class="text-gray-500">Tanggal</p>
                                                <p class="font-medium">{{ $item->tanggal_mulai->format('d M Y') }}</p>
                                            </div>
                                            <div>
                                                <p class="text-gray-500">Waktu Pengajuan</p>
                                                <p class="font-medium">{{ $item->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                        <a href="{{ route('rektor.persetujuan.show', $item) }}" class="text-center w-full text-sm bg-blue-100 text-blue-600 rounded px-3 py-2 hover:bg-blue-200">
                                            Review →
                                        </a>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4">
                                {{ $pengajuanMenunggu->appends(['tab' => 'menunggu'])->links() }}
                            </div>
                        @else
                            <div class="text-center py-8 sm:py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="mt-4 text-gray-500 text-sm">Tidak ada pengajuan yang menunggu persetujuan</p>
                            </div>
                        @endif
                    </div>
                </div>

            @else
                {{-- Riwayat Persetujuan Rektor --}}
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-6">
                        <h3 class="text-base sm:text-lg font-semibold mb-4">Riwayat Persetujuan Saya</h3>

                        @if($riwayatVerifikasi->count() > 0)
                            <div class="space-y-3">
                                @foreach($riwayatVerifikasi as $verifikasi)
                                    <div class="border rounded-lg p-3 sm:p-4 hover:bg-gray-50">
                                        <div class="flex flex-col sm:flex-row justify-between sm:items-start gap-3">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-medium text-gray-900 text-sm sm:text-base break-all">{{ $verifikasi->pengajuanKegiatan->judul_kegiatan }}</h4>
                                                <p class="text-xs sm:text-sm text-gray-500">{{ $verifikasi->pengajuanKegiatan->ormawa->nama_ormawa }}</p>

                                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-{{ $verifikasi->status_badge }}-100 text-{{ $verifikasi->status_badge }}-800">
                                                        {{ $verifikasi->status_label }}
                                                    </span>
                                                    <span class="text-xs text-gray-500">{{ $verifikasi->tanggal_acc->format('d M Y H:i') }}</span>
                                                </div>

                                                @if($verifikasi->catatan)
                                                    <p class="text-xs sm:text-sm text-gray-700 mt-2 bg-gray-50 p-2 rounded">{{ $verifikasi->catatan }}</p>
                                                @endif
                                            </div>
                                            <a href="{{ route('rektor.persetujuan.show', $verifikasi->pengajuanKegiatan) }}" class="w-full sm:w-auto text-center text-xs sm:text-sm text-blue-600 hover:text-blue-800 px-3 py-2 bg-blue-100 rounded hover:bg-blue-200">
                                                Lihat →
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4">
                                {{ $riwayatVerifikasi->appends(['tab' => 'riwayat'])->links() }}
                            </div>
                        @else
                            <p class="text-center text-gray-500 py-6 sm:py-8 text-sm">Belum ada riwayat persetujuan</p>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
