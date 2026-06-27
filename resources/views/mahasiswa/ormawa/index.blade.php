<x-app-layout>
    <x-slot name="title">Cari Organisasi</x-slot>

    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Cari Organisasi</h1>
            <p class="text-sm text-gray-600">Ajukan permintaan bergabung ke organisasi yang ingin Anda ikuti.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('mahasiswa.ormawa.requests') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition">
                Lihat Permintaan Saya
            </a>
            <a href="{{ route('mahasiswa.dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    <div class="mb-6 bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
        <form method="GET" action="{{ route('mahasiswa.ormawa.index') }}" class="grid gap-3 md:grid-cols-[1fr_auto]">
            <label class="sr-only" for="search">Cari organisasi</label>
            <input id="search" name="search" type="text" placeholder="Cari nama ormawa, ketua, atau pembina..."
                value="{{ request('search') }}"
                class="w-full rounded-lg border border-gray-200 px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100" />
            <button type="submit" class="px-4 py-3 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700 transition">Cari</button>
        </form>
    </div>

    @if ($ormawas->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <div class="mb-4 text-gray-400 text-5xl">
                <i class="ti ti-building-off"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum ada organisasi yang cocok</h3>
            <p class="text-sm text-gray-500 mb-4">Coba gunakan kata kunci berbeda atau kembali lagi nanti.</p>
            <a href="{{ route('mahasiswa.dashboard') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-5 py-3 text-sm font-medium text-white hover:bg-blue-700 transition">
                Kembali ke Dashboard
            </a>
        </div>
    @else
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Ormawa</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Ketua</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Pembina</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach ($ormawas as $ormawa)
                            <tr>
                                <td class="px-4 py-4 text-sm text-gray-900">{{ $ormawa->nama_ormawa }}</td>
                                <td class="px-4 py-4 text-sm text-gray-700">{{ $ormawa->ketua }}</td>
                                <td class="px-4 py-4 text-sm text-gray-700">{{ $ormawa->pembina }}</td>
                                <td class="px-4 py-4 text-sm">
                                    @if (in_array($ormawa->id, $memberOrmawaIds))
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Sudah Anggota</span>
                                    @elseif (in_array($ormawa->id, $pendingOrmawaIds))
                                        <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-xs font-semibold text-yellow-700">Menunggu Persetujuan</span>
                                    @elseif (in_array($ormawa->id, $rejectedOrmawaIds))
                                        <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Ditolak</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">Belum Bergabung</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-right text-sm font-medium">
                                    @if (in_array($ormawa->id, $memberOrmawaIds))
                                        <a href="{{ route('mahasiswa.dashboard') }}" class="inline-flex items-center justify-center rounded-lg bg-gray-100 px-4 py-2 text-xs text-gray-700 hover:bg-gray-200 transition">Dashboard</a>
                                    @elseif (in_array($ormawa->id, $pendingOrmawaIds))
                                        <a href="{{ route('mahasiswa.ormawa.requests') }}" class="inline-flex items-center justify-center rounded-lg bg-yellow-100 px-4 py-2 text-xs font-semibold text-yellow-700 hover:bg-yellow-200 transition">Lihat Permintaan</a>
                                    @else
                                        <form method="POST" action="{{ route('mahasiswa.ormawa.join', $ormawa) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white hover:bg-blue-700 transition">
                                                Ajukan Bergabung
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 p-4">
                {{ $ormawas->links('pagination::tailwind') }}
            </div>
        </div>
    @endif
</x-app-layout>
