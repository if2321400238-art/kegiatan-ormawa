<x-app-layout>
    <x-slot name="title">Permintaan Bergabung Ormawa</x-slot>

    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Permintaan Bergabung {{ $ormawa->nama_ormawa }}</h1>
            <p class="text-sm text-gray-600">Kelola permintaan bergabung mahasiswa sebagai ketua organisasi.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-green-200 bg-green-50 p-4 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="mb-4 rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-yellow-800">
            {{ session('warning') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Mahasiswa</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Email / NIM</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Keterangan</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse ($requests as $requestItem)
                        <tr>
                            <td class="px-4 py-4 text-sm text-gray-900">{{ $requestItem->user->nama }}</td>
                            <td class="px-4 py-4 text-sm text-gray-700">{{ $requestItem->user->email }} / {{ $requestItem->user->nim }}</td>
                            <td class="px-4 py-4 text-sm">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $requestItem->isPending() ? 'bg-yellow-100 text-yellow-700' : ($requestItem->isApproved() ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700') }}">
                                    {{ ucfirst($requestItem->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-sm text-gray-700">
                                @if ($requestItem->rejection_reason)
                                    <span class="text-red-600">{{ $requestItem->rejection_reason }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-4 text-right text-sm font-medium space-x-2">
                                @if ($requestItem->isPending())
                                    <form method="POST" action="{{ route('ormawa.requests.approve', [$ormawa, $requestItem]) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition">
                                            Setujui
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('ormawa.requests.reject', [$ormawa, $requestItem]) }}" class="inline">
                                        @csrf
                                        <button type="submit" name="reason" value="Ditolak oleh ketua" class="rounded-lg bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700 transition">
                                            Tolak
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-500">Tidak ada aksi</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">
                                Belum ada permintaan bergabung.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-gray-200 p-4">
            {{ $requests->links('pagination::tailwind') }}
        </div>
    </div>
</x-app-layout>
