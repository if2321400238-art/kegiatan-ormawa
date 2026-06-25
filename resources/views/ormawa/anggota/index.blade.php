<x-app-layout>
    <x-slot name="title">Anggota {{ $ormawa->nama_ormawa }}</x-slot>

    {{-- Top Header Section --}}
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900">Anggota {{ $ormawa->nama_ormawa }}</h2>
                <p class="text-[12px] text-gray-500">Kelola anggota organisasi mahasiswa</p>
            </div>
            <a href="{{ route('admin.ormawa.anggota.create', $ormawa) }}"
                class="px-4 py-2 bg-brand text-white rounded-lg text-[13px] font-medium hover:bg-brand-active transition shadow-sm flex items-center gap-2">
                <i class="ti ti-plus"></i> Tambah Anggota
            </a>
        </div>
    </div>

    {{-- Back Link --}}
    <div class="mb-4">
        <a href="{{ route('admin.ormawa.index') }}"
            class="text-[12px] text-blue-600 hover:text-blue-700 flex items-center gap-1">
            <i class="ti ti-arrow-left"></i> Kembali ke Daftar Ormawa
        </a>
    </div>

    {{-- Alerts --}}
    @if ($errors->any())
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
            <p class="text-[13px] font-semibold text-red-900 mb-2">Terjadi kesalahan:</p>
            <ul class="text-[12px] text-red-800 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
            <p class="text-[13px] text-green-900 flex items-center gap-2">
                <i class="ti ti-check"></i> {{ session('success') }}
            </p>
        </div>
    @endif

    {{-- Anggota List --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        @forelse($anggota as $member)
            <div class="border-b border-gray-100 last:border-b-0 p-4 flex items-center justify-between hover:bg-gray-50 transition">
                <div class="flex-1">
                    <h3 class="text-[13px] font-semibold text-gray-900">{{ $member->nama }}</h3>
                    <p class="text-[12px] text-gray-500">{{ $member->email }}</p>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="text-[11px] px-2 py-1 bg-blue-100 text-blue-700 rounded">
                            {{ ucwords(str_replace('_', ' ', $member->pivot->jabatan)) }}
                        </span>
                        <span
                            class="text-[11px] px-2 py-1 {{ $member->pivot->aktif ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }} rounded">
                            {{ $member->pivot->aktif ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.ormawa.anggota.edit', [$ormawa, $member]) }}"
                        class="px-3 py-1.5 bg-gray-100 text-gray-700 rounded text-[12px] font-medium hover:bg-gray-200 transition">
                        <i class="ti ti-edit"></i> Edit
                    </a>
                    <form action="{{ route('admin.ormawa.anggota.destroy', [$ormawa, $member]) }}" method="POST"
                        class="inline"
                        onsubmit="return confirm('Yakin ingin menghapus anggota ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-3 py-1.5 bg-red-100 text-red-700 rounded text-[12px] font-medium hover:bg-red-200 transition">
                            <i class="ti ti-trash"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-8 text-center">
                <i class="ti ti-user-plus text-gray-300 text-4xl mb-3 block"></i>
                <p class="text-[13px] text-gray-500">Belum ada anggota.</p>
                <a href="{{ route('admin.ormawa.anggota.create', $ormawa) }}"
                    class="text-[12px] text-blue-600 hover:text-blue-700 font-medium mt-2">
                    Tambah anggota sekarang
                </a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($anggota->hasPages())
        <div class="mt-6">
            {{ $anggota->links() }}
        </div>
    @endif
</x-app-layout>
