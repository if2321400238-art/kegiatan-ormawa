<x-app-layout>
    <x-slot name="title">Daftar Mahasiswa</x-slot>

    <div class="page-header">
        <div class="page-header-main">
            <div class="page-header-title">
                <h2 class="text-lg font-semibold text-gray-900">Daftar Mahasiswa</h2>
                <p class="text-[12px] text-gray-500">Kelola akun, NIM, jabatan, dan organisasi aktif mahasiswa</p>
            </div>
        </div>

        <div class="page-header-actions">
            <div class="summary-stats">
                <div class="summary-stat">
                    <div class="text-[20px] font-bold text-gray-900">{{ $mahasiswaList->total() }}</div>
                    <div class="text-[11px] text-gray-500 font-medium">Total Mahasiswa</div>
                </div>
            </div>

            <a href="{{ route('admin.mahasiswa.create') }}" class="w-full sm:w-auto px-4 py-2 bg-brand text-white rounded-lg hover:bg-brand-active transition text-[13px] font-medium flex items-center justify-center gap-2 shadow-sm">
                <i class="ti ti-plus"></i> Tambah Mahasiswa
            </a>
        </div>
    </div>

    <div class="table-card">
        <div class="p-4 sm:p-6 border-b border-gray-100">
            <form method="GET" action="{{ route('admin.mahasiswa.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 lg:grid-cols-[1fr_180px_240px_auto] gap-3">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="ti ti-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" placeholder="Cari nama, NIM, email, atau username..."
                            value="{{ request('search') }}"
                            class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors">
                    </div>

                    <select name="status" class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2 transition-colors">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Akun Aktif</option>
                        <option value="nonaktif" {{ request('status') === 'nonaktif' ? 'selected' : '' }}>Akun Nonaktif</option>
                    </select>

                    <select name="ormawa_id" class="w-full bg-gray-50 border border-gray-200 text-gray-900 text-[13px] rounded-lg focus:ring-brand focus:border-brand block p-2 transition-colors">
                        <option value="">Semua Ormawa</option>
                        @foreach($ormawaList as $ormawa)
                            <option value="{{ $ormawa->id }}" {{ (string) request('ormawa_id') === (string) $ormawa->id ? 'selected' : '' }}>
                                {{ $ormawa->nama_ormawa }}
                            </option>
                        @endforeach
                    </select>

                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-gray-900 text-white rounded-lg text-[13px] font-medium hover:bg-gray-800 transition">
                            Cari
                        </button>
                        @if(request()->hasAny(['search', 'status', 'ormawa_id']))
                            <a href="{{ route('admin.mahasiswa.index') }}" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-[13px] font-medium hover:bg-gray-200 transition flex items-center justify-center gap-1">
                                <i class="ti ti-refresh"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        @if($mahasiswaList->count() > 0)
            <div class="hidden md:block overflow-x-auto">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Mahasiswa</th>
                            <th>Kontak</th>
                            <th>Organisasi Aktif</th>
                            <th>Status Akun</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mahasiswaList as $item)
                            @php
                                $activeOrmawas = $item->ormawas->filter(fn ($ormawa) => (bool) $ormawa->pivot->status);
                            @endphp
                            <tr>
                                <td>
                                    <span class="text-[13px] text-gray-500 font-medium">{{ $mahasiswaList->firstItem() + $loop->index }}</span>
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-brand/10 text-brand flex items-center justify-center font-bold text-xs uppercase overflow-hidden shrink-0">
                                            {{ substr($item->nama, 0, 2) }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900">{{ $item->nama }}</div>
                                            <div class="text-[11px] text-gray-500">NIM: {{ $item->nim ?? '-' }} | {{ $item->username }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-[12px] text-gray-700">{{ $item->email }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $item->no_hp ?? '-' }}</div>
                                </td>
                                <td>
                                    @if($activeOrmawas->isNotEmpty())
                                        <div class="flex flex-col gap-1.5">
                                            @foreach($activeOrmawas->take(3) as $ormawa)
                                                <span class="inline-flex w-fit items-center gap-1.5 px-2 py-1 rounded-md bg-blue-50 text-blue-700 text-[11px] font-medium">
                                                    <i class="ti ti-building-community"></i>
                                                    {{ $ormawa->nama_ormawa }}
                                                    <span class="text-blue-500">({{ str_replace('_', ' ', $ormawa->pivot->jabatan) }})</span>
                                                </span>
                                            @endforeach
                                            @if($activeOrmawas->count() > 3)
                                                <span class="text-[11px] text-gray-500">+{{ $activeOrmawas->count() - 3 }} organisasi lain</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-[13px] text-gray-400 italic">Belum aktif di organisasi</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_active)
                                        <span class="badge badge-success">Aktif</span>
                                    @else
                                        <span class="badge badge-danger">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.mahasiswa.edit', $item->id) }}" class="p-1.5 bg-warning-light text-warning rounded-md hover:bg-warning hover:text-white transition-colors" title="Edit">
                                            <i class="ti ti-edit"></i>
                                        </a>

                                        <form action="{{ route('admin.mahasiswa.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus data mahasiswa ini? Keanggotaan organisasinya juga akan dilepas.');">
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

            <div class="md:hidden divide-y divide-gray-100">
                @foreach ($mahasiswaList as $item)
                    @php
                        $activeOrmawas = $item->ormawas->filter(fn ($ormawa) => (bool) $ormawa->pivot->status);
                    @endphp
                    <div class="p-4">
                        <div class="flex justify-between items-start gap-3 mb-3">
                            <div>
                                <h3 class="text-[14px] font-semibold text-gray-900 leading-tight">{{ $item->nama }}</h3>
                                <p class="text-[12px] text-gray-500">NIM: {{ $item->nim ?? '-' }}</p>
                            </div>
                            @if($item->is_active)
                                <span class="badge badge-success text-[10px]">Aktif</span>
                            @else
                                <span class="badge badge-danger text-[10px]">Nonaktif</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 gap-2 text-[12px] mb-4">
                            <div class="flex items-center gap-2 text-gray-700">
                                <i class="ti ti-mail text-gray-400 w-4"></i>
                                <span>{{ $item->email }}</span>
                            </div>
                            <div class="flex items-start gap-2 text-gray-700">
                                <i class="ti ti-building-community text-gray-400 w-4 mt-0.5"></i>
                                <div>
                                    @forelse($activeOrmawas as $ormawa)
                                        <div>{{ $ormawa->nama_ormawa }} <span class="text-gray-400">({{ str_replace('_', ' ', $ormawa->pivot->jabatan) }})</span></div>
                                    @empty
                                        <span class="text-gray-400">Belum aktif di organisasi</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('admin.mahasiswa.edit', $item->id) }}" class="flex-1 text-center py-2 px-3 text-[12px] bg-warning-light text-warning font-medium rounded-lg hover:bg-warning hover:text-white transition-colors">
                                Edit
                            </a>

                            <form action="{{ route('admin.mahasiswa.destroy', $item->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Hapus data mahasiswa ini?');">
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

            <div class="p-4 border-t border-gray-100">
                <div class="mb-3 text-[12px] text-gray-500 text-center md:text-left">
                    Menampilkan <span class="font-medium text-gray-900">{{ $mahasiswaList->firstItem() ?? 0 }}</span> - <span class="font-medium text-gray-900">{{ $mahasiswaList->lastItem() ?? 0 }}</span> dari <span class="font-medium text-gray-900">{{ $mahasiswaList->total() }}</span> Mahasiswa
                </div>
                {{ $mahasiswaList->links('pagination::tailwind') }}
            </div>
        @else
            <div class="flex flex-col items-center justify-center p-12 text-center">
                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 text-3xl mb-4">
                    <i class="ti ti-id-badge-2"></i>
                </div>
                <h3 class="text-[15px] font-semibold text-gray-900 mb-1">Tidak ada data mahasiswa</h3>
                <p class="text-[13px] text-gray-500 mb-6">Belum ada akun mahasiswa yang sesuai dengan filter Anda.</p>
                <a href="{{ route('admin.mahasiswa.create') }}" class="px-5 py-2.5 bg-brand text-white rounded-lg hover:bg-brand-active transition text-[13px] font-medium flex items-center gap-2 shadow-sm">
                    <i class="ti ti-plus"></i> Tambah Mahasiswa
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
