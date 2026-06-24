<x-app-layout>
    <x-slot name="title">Verifikasi Dosen Pembina</x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold">Verifikasi Dosen Pembina</h2>
            <p class="text-sm text-gray-500">Daftar pengajuan yang menunggu verifikasi Anda</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        @if($pengajuan->count())
            <table class="w-full">
                <thead>
                    <tr class="text-left text-xs text-gray-500">
                        <th>Judul</th>
                        <th>Ormawa</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuan as $item)
                        <tr class="border-t">
                            <td class="py-3">{{ $item->judul_kegiatan }}</td>
                            <td>{{ $item->ormawa->nama_ormawa }}</td>
                            <td>{{ $item->tanggal_mulai->format('d M Y') }}</td>
                            <td>
                                <a href="{{ route('dosen.verifikasi.show', $item) }}" class="px-3 py-1 bg-gray-100 rounded">Lihat</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-gray-500">Tidak ada pengajuan untuk diverifikasi.</p>
        @endif
    </div>
</x-app-layout>
