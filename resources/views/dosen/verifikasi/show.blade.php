<x-app-layout>
    <x-slot name="title">Verifikasi Pengajuan</x-slot>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-lg font-semibold">Verifikasi Pengajuan</h2>
            <p class="text-sm text-gray-500">Periksa detail lalu setujui atau minta revisi.</p>
        </div>
        <a href="{{ route('dosen.verifikasi.index') }}" class="px-3 py-2 bg-gray-100 rounded">Kembali</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-bold text-gray-900 mb-3">{{ $pengajuan->judul_kegiatan }}</h3>
        <p class="text-sm text-gray-700 mb-4">{{ $pengajuan->tujuan_kegiatan }}</p>

        <form method="POST" action="{{ route('dosen.verifikasi.verify', $pengajuan) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm text-gray-600">Catatan (opsional)</label>
                <textarea name="catatan" class="w-full border rounded p-2" rows="4"></textarea>
            </div>

            <div class="flex gap-2">
                <button name="action" value="approve" class="px-4 py-2 bg-green-600 text-white rounded">Setujui</button>
                <button name="action" value="revision" class="px-4 py-2 bg-orange-500 text-white rounded">Minta Revisi</button>
                <button name="action" value="reject" class="px-4 py-2 bg-red-600 text-white rounded">Tolak</button>
            </div>
        </form>
    </div>
</x-app-layout>
