<x-app-layout>
    <x-slot name="title">Dashboard Mahasiswa</x-slot>

    {{-- Welcome Section --}}
    <div class="mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl p-8 text-white shadow-lg">
            <h1 class="text-3xl font-bold mb-2">Halo, {{ auth()->user()->nama }}! 👋</h1>
            <p class="text-blue-100">Selamat datang di dashboard Anda</p>
        </div>
    </div>

    {{-- Active Ormawa Section --}}
    @if ($ormawas->isNotEmpty())
        <div class="mb-8 rounded-xl border border-blue-200 bg-blue-50 p-6">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Organisasi Aktif</h2>
                    <p class="mt-1 text-sm text-gray-600">
                        Anda sedang melihat pengajuan kegiatan untuk <span class="font-semibold text-blue-700">{{ $activeOrmawa?->nama_ormawa ?? 'Tidak ada' }}</span>.
                    </p>
                </div>

                @if ($ormawas->count() > 1)
                    <form action="{{ route('mahasiswa.setActiveOrmawa') }}" method="POST" class="flex items-center gap-2">
                        @csrf
                        <select name="ormawa_id" class="rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" onchange="this.form.submit()">
                            @foreach ($ormawas as $ormawaOption)
                                <option value="{{ $ormawaOption->id }}" {{ $activeOrmawa && $activeOrmawa->id == $ormawaOption->id ? 'selected' : '' }}>
                                    {{ $ormawaOption->nama_ormawa }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif
            </div>
        </div>
    @endif

    {{-- Section: Organisasi yang Anda Pimpin (Ketua) --}}
    @if ($ormawaDipimpin->isNotEmpty())
        <div class="mb-8">
            <div class="flex items-center gap-2 mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Organisasi yang Saya Pimpin</h2>
                <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-red-600 rounded-full">
                    {{ $ormawaDipimpin->count() }}
                </span>
            </div>

            <div class="grid gap-4">
                @foreach ($ormawaDipimpin as $ormawa)
                    <div class="bg-gradient-to-r from-red-50 to-pink-50 rounded-xl border-2 border-red-200 hover:shadow-md transition-all overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3 flex-1">
                                    <div class="w-12 h-12 rounded-lg bg-red-600 flex items-center justify-center text-white text-xl">
                                        <i class="ti ti-crown"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900">{{ $ormawa->nama_ormawa }}</h3>
                                        <p class="text-sm text-gray-600">Anda adalah Ketua Organisasi</p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 bg-red-600 text-white rounded-full text-xs font-semibold">KETUA</span>
                            </div>

                            <div class="grid grid-cols-2 gap-3 mb-4">
                                <div class="bg-white rounded-lg p-3 text-center">
                                    <p class="text-2xl font-bold text-red-600">{{ $ormawa->users()->count() }}</p>
                                    <p class="text-xs text-gray-600">Anggota</p>
                                </div>
                                <div class="bg-white rounded-lg p-3 text-center">
                                    <p class="text-2xl font-bold text-blue-600">{{ $ormawa->pengajuanKegiatan()->count() }}</p>
                                    <p class="text-xs text-gray-600">Kegiatan</p>
                                </div>
                            </div>

                            <div class="flex gap-2">
                                <a href="{{ route('dashboard') }}" class="flex-1 text-center px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition">
                                    <i class="ti ti-file-text mr-1"></i> Kelola Kegiatan
                                </a>
                                <a href="{{ route('ormawa.anggota.index', $ormawa) }}" class="flex-1 text-center px-4 py-2 bg-red-100 text-red-700 rounded-lg text-sm font-semibold hover:bg-red-200 transition">
                                    <i class="ti ti-users mr-1"></i> Kelola Anggota
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Section: Organisasi yang Anda Ikuti (Anggota) --}}
    @if ($memberOrganizations->isNotEmpty())
        <div class="mb-8">
            <div class="flex items-center gap-2 mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Organisasi yang Saya Ikuti</h2>
                <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-white bg-blue-600 rounded-full">
                    {{ $memberOrganizations->count() }}
                </span>
            </div>

            <div class="grid gap-4">
                @foreach ($memberOrganizations as $ormawa)
                    <div class="bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all overflow-hidden">
                        <div class="p-6 flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <i class="ti ti-building text-2xl text-blue-600"></i>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">{{ $ormawa->nama_ormawa }}</h3>
                                        <p class="text-[13px] text-gray-500">
                                            <strong>Ketua:</strong> {{ $ormawa->ketua()->first()?->nama ?? 'N/A' }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Jabatan Badge --}}
                                <div class="mt-3">
                                    @php
                                        $jabatan = $ormawa->memberData?->jabatan ?? 'anggota';
                                        $jabatanColors = [
                                            'ketua' => 'bg-red-100 text-red-700',
                                            'wakil_ketua' => 'bg-orange-100 text-orange-700',
                                            'sekretaris' => 'bg-green-100 text-green-700',
                                            'bendahara' => 'bg-yellow-100 text-yellow-700',
                                            'anggota' => 'bg-gray-100 text-gray-700'
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-[12px] font-medium {{ $jabatanColors[$jabatan] ?? 'bg-gray-100 text-gray-700' }}">
                                        <i class="ti ti-user-circle mr-1 text-xs"></i>
                                        {{ ucwords(str_replace('_', ' ', $jabatan)) }}
                                    </span>

                                    @if (!($ormawa->memberData?->status ?? true))
                                        <span class="inline-flex items-center ml-2 px-3 py-1 rounded-full text-[12px] font-medium bg-gray-100 text-gray-700">
                                            <i class="ti ti-circle-x mr-1 text-xs"></i> Tidak Aktif
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Empty State --}}
    @if ($ormawaDipimpin->isEmpty() && $memberOrganizations->isEmpty())
        <div class="bg-white rounded-xl border-2 border-dashed border-gray-300 p-12 text-center">
            <div class="mb-4">
                <i class="ti ti-building-off text-5xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Organisasi</h3>
            <p class="text-gray-600 mb-4">Anda belum menjadi anggota atau ketua dari organisasi manapun.</p>
            <p class="text-sm text-gray-500">Hubungi admin atau ketua organisasi untuk didaftarkan sebagai anggota.</p>
        </div>
    @endif
</x-app-layout>

