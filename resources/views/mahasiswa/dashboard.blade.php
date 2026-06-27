<x-app-layout>
    <x-slot name="title">Dashboard Mahasiswa</x-slot>

    {{-- Welcome Section --}}
    <div class="mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <h1 class="text-[22px] font-bold text-gray-900 mb-1">Halo, {{ auth()->user()->nama }}! 👋</h1>
            <p class="text-[13px] text-gray-500">Selamat datang di portal Sistem Ormawa Universitas Nurul Jadid</p>
        </div>
    </div>

    @if ($activeOrmawa)
        {{-- HERO SECTION: Organisasi Aktif --}}
        <div class="mb-8">
            <div class="flex items-center gap-2 mb-4">
                <h2 class="text-[16px] font-bold text-gray-900">Organisasi Aktif</h2>
            </div>
            
            <div class="bg-white rounded-xl border border-[#6C63FF]/30 p-6 shadow-sm flex flex-col md:flex-row md:items-center gap-6 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-[#EEF2FF] rounded-bl-full -z-10 opacity-50"></div>
                
                <div class="w-16 h-16 rounded-xl bg-[#1E3A6E] text-white flex items-center justify-center flex-shrink-0 text-3xl shadow-md">
                    <i class="ti ti-building-community"></i>
                </div>
                
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-1">
                        <h3 class="text-xl font-bold text-gray-900">{{ $activeOrmawa->nama_ormawa }}</h3>
                        @php
                            $activeMemberData = $activeOrmawa->anggota()->where('user_id', auth()->id())->first();
                            $activeJabatan = $activeMemberData?->jabatan ?? 'anggota';
                            $jabatanColors = [
                                'ketua' => 'badge-success',
                                'wakil_ketua' => 'badge-orange',
                                'sekretaris' => 'badge-info',
                                'bendahara' => 'badge-warning',
                                'anggota' => 'badge-gray'
                            ];
                            $badgeClass = $jabatanColors[$activeJabatan] ?? 'badge-gray';
                        @endphp
                        <span class="badge {{ $badgeClass }} text-[12px] px-3 py-1">{{ ucwords(str_replace('_', ' ', $activeJabatan)) }}</span>
                    </div>
                    
                    @if($activeOrmawa->periode)
                        <p class="text-[13px] text-gray-500 mb-3"><i class="ti ti-calendar-time mr-1"></i> Periode Kepengurusan: <span class="font-semibold text-gray-700">{{ $activeOrmawa->periode }}</span></p>
                    @else
                        <p class="text-[13px] text-gray-500 mb-3"><i class="ti ti-user mr-1"></i> Ketua: <span class="font-semibold text-gray-700">{{ $activeOrmawa->ketua }}</span></p>
                    @endif
                    
                    <div class="flex flex-wrap gap-4 text-[13px]">
                        <div class="flex items-center gap-1.5 text-gray-600">
                            <div class="w-6 h-6 rounded bg-gray-100 flex items-center justify-center text-gray-500"><i class="ti ti-users text-sm"></i></div>
                            <span class="font-medium text-gray-900">{{ $activeOrmawa->users()->count() }}</span> Anggota
                        </div>
                        <div class="flex items-center gap-1.5 text-gray-600">
                            <div class="w-6 h-6 rounded bg-gray-100 flex items-center justify-center text-gray-500"><i class="ti ti-file-text text-sm"></i></div>
                            <span class="font-medium text-gray-900">{{ $activeOrmawa->pengajuanKegiatan()->count() }}</span> Kegiatan
                        </div>
                    </div>
                </div>
                
                @if($activeJabatan === 'ketua')
                    <div class="flex flex-col sm:flex-row gap-2 md:ml-auto w-full md:w-auto mt-4 md:mt-0">
                        <a href="{{ route('pengajuan.index') }}" class="px-5 py-2.5 bg-[#1E3A6E] text-white rounded-lg text-[13px] font-medium hover:bg-[#2D5196] transition-colors shadow-sm flex items-center justify-center gap-2">
                            <i class="ti ti-file-text"></i> Kelola Kegiatan
                        </a>
                        <a href="{{ route('ormawa.anggota.index', $activeOrmawa) }}" class="px-5 py-2.5 bg-[#EEF2FF] text-[#1E3A6E] rounded-lg text-[13px] font-medium hover:bg-[#E0E7FF] transition-colors flex items-center justify-center gap-2">
                            <i class="ti ti-users"></i> Kelola Anggota
                        </a>
                    </div>
                @else
                    <div class="flex flex-col sm:flex-row gap-2 md:ml-auto w-full md:w-auto mt-4 md:mt-0">
                        <a href="{{ route('pengajuan.index') }}" class="px-5 py-2.5 bg-[#1E3A6E] text-white rounded-lg text-[13px] font-medium hover:bg-[#2D5196] transition-colors shadow-sm flex items-center justify-center gap-2">
                            <i class="ti ti-list"></i> Lihat Kegiatan
                        </a>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Section: Organisasi Saya (List Organisasi Lainnya) --}}
    @if ($ormawas->count() > 1 || (!$activeOrmawa && $ormawas->isNotEmpty()))
        <div>
            <div class="flex items-center gap-2 mb-4">
                <h2 class="text-[16px] font-bold text-gray-900">Organisasi Saya</h2>
                <span class="inline-flex items-center justify-center w-5 h-5 text-[10px] font-bold text-white bg-[#1E3A6E] rounded-full">
                    {{ $ormawas->count() }}
                </span>
            </div>

            <div class="table-card">
                <div class="p-4 sm:p-5">
                    <div class="flex flex-col gap-0">
                        @foreach ($ormawas as $ormawa)
                            @php
                                $isActive = $activeOrmawa && $activeOrmawa->id === $ormawa->id;
                                $memberData = $ormawa->anggota()->where('user_id', auth()->id())->first();
                                $jabatan = $memberData?->jabatan ?? 'anggota';
                                $jabatanColors = [
                                    'ketua' => 'badge-success',
                                    'wakil_ketua' => 'badge-orange',
                                    'sekretaris' => 'badge-info',
                                    'bendahara' => 'badge-warning',
                                    'anggota' => 'badge-gray'
                                ];
                                $badgeClass = $jabatanColors[$jabatan] ?? 'badge-gray';
                            @endphp
                            <div class="active-org-row {{ $loop->last ? 'border-b-0 pb-0' : '' }} {{ $loop->first ? 'pt-0' : '' }}">
                                <div class="w-10 h-10 rounded {{ $isActive ? 'bg-[#1E3A6E] text-white' : 'bg-[#DBEAFE] text-[#3B82F6]' }} flex items-center justify-center flex-shrink-0 text-xl font-bold">
                                    <i class="ti ti-building-community text-lg"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-[14px] font-semibold {{ $isActive ? 'text-[#1E3A6E]' : 'text-gray-900' }} mb-0.5 truncate flex items-center gap-2">
                                        {{ $ormawa->nama_ormawa }}
                                        @if($isActive)
                                            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-[#EEF2FF] text-[#6C63FF]">AKTIF</span>
                                        @endif
                                    </h3>
                                    <p class="text-[11px] text-gray-500 flex items-center gap-3">
                                        @if($ormawa->periode)
                                            <span><i class="ti ti-calendar-time"></i> {{ $ormawa->periode }}</span>
                                        @endif
                                        <span><i class="ti ti-user"></i> Ketua: {{ $ormawa->ketua()->first()?->nama ?? 'N/A' }}</span>
                                    </p>
                                </div>
                                <div class="flex flex-col sm:flex-row items-end sm:items-center gap-3 flex-shrink-0">
                                    <div class="flex flex-col items-end gap-1">
                                        <span class="badge {{ $badgeClass }}">
                                            {{ ucwords(str_replace('_', ' ', $jabatan)) }}
                                        </span>
                                    </div>
                                    @if(!$isActive)
                                        <form action="{{ route('mahasiswa.setActiveOrmawa') }}" method="POST" class="m-0">
                                            @csrf
                                            <input type="hidden" name="ormawa_id" value="{{ $ormawa->id }}">
                                            <button type="submit" class="text-[12px] font-medium text-[#6C63FF] hover:text-[#5046e5] px-3 py-1.5 bg-gray-50 hover:bg-gray-100 rounded border border-gray-200 transition-colors">
                                                Jadikan Aktif
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @elseif (!$activeOrmawa && $ormawas->isEmpty())
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center flex flex-col items-center justify-center">
            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center text-gray-400 text-3xl mb-4">
                <i class="ti ti-users-minus"></i>
            </div>
            <h3 class="text-[15px] font-medium text-gray-900 mb-1">Belum Menjadi Anggota</h3>
            <p class="text-[13px] text-gray-500 max-w-[300px]">Anda belum terdaftar sebagai anggota di organisasi kemahasiswaan manapun.</p>
        </div>
    @endif
</x-app-layout>
