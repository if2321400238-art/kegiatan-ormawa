<x-app-layout>
    <x-slot name="title">Detail Pengajuan Kegiatan</x-slot>

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Detail Pengajuan Kegiatan</h2>
            <p class="text-[12px] text-gray-500">Lihat detail dan pantau status pengajuan</p>
        </div>
        <div class="flex gap-2 w-full sm:w-auto">
        @if(in_array(auth()->user()->role, ['ormawa','mahasiswa']) && $pengajuan->status === 'disetujui' && !$pengajuan->lpj)
            <a href="{{ route('lpj.create',$pengajuan) }}" class="px-4 py-2 bg-brand text-white rounded-lg text-[13px] font-medium">Buat LPJ</a>
        @elseif($pengajuan->lpj)
            <a href="{{ route('lpj.show',$pengajuan->lpj) }}" class="px-4 py-2 bg-brand text-white rounded-lg text-[13px] font-medium">Lihat LPJ</a>
        @endif
        <a href="{{ route('pengajuan.index') }}"
            class="w-full sm:w-auto px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-[13px] font-medium flex items-center justify-center gap-2">
            <i class="ti ti-arrow-left"></i> Kembali
        </a>
        </div>
    </div>

    {{-- Status Timeline --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h3 class="text-[15px] font-bold text-gray-900 mb-6 flex items-center gap-2">
            <i class="ti ti-activity text-brand"></i> Status Pengajuan
        </h3>

        <div class="hidden md:flex items-center justify-between mb-2">
            {{-- Step 1: Menunggu Kaprodi (khusus Ormawa Prodi) --}}
            <div class="flex-1 text-center relative z-10">
                @php
                    $isStep1Active = in_array($pengajuan->status, [
                        'menunggu_kaprodi',
                        'menunggu_dekan',
                        'menunggu_bauak',
                        'menunggu_warek3',
                        'menunggu_rektor',
                        'disetujui',
                    ]);
                @endphp
                <div
                    class="w-10 h-10 mx-auto rounded-full flex items-center justify-center border-4 border-white shadow-sm {{ $isStep1Active ? 'bg-success text-white' : 'bg-gray-100 text-gray-400' }}">
                    <i class="ti {{ $isStep1Active ? 'ti-check' : 'ti-send' }} text-xl"></i>
                </div>
                <div class="mt-2 text-[12px] font-bold {{ $isStep1Active ? 'text-gray-900' : 'text-gray-400' }}">
                    Menunggu Kaprodi</div>
            </div>

            {{-- Connector --}}
            <div
                class="flex-1 h-1 -mx-8 z-0 {{ in_array($pengajuan->status, ['menunggu_warek3', 'menunggu_rektor', 'disetujui']) ? 'bg-success' : 'bg-gray-100' }}">
            </div>

            {{-- Step 2: Verifikasi BAUAK --}}
            <div class="flex-1 text-center relative z-10">
                @php
                    $isStep2Done = in_array($pengajuan->status, ['menunggu_warek3', 'menunggu_rektor', 'disetujui']);
                    $isStep2Active = $pengajuan->status == 'menunggu_bauak';
                    $isStep2Revisi = $pengajuan->status == 'revisi_bauak';
                    $isStep2Tolak = $pengajuan->status == 'ditolak_bauak';
                @endphp
                <div
                    class="w-10 h-10 mx-auto rounded-full flex items-center justify-center border-4 border-white shadow-sm
                    {{ $isStep2Done ? 'bg-success text-white' : ($isStep2Active ? 'bg-warning text-white' : ($isStep2Revisi ? 'bg-orange text-white' : ($isStep2Tolak ? 'bg-danger text-white' : 'bg-gray-100 text-gray-400'))) }}">
                    @if ($isStep2Done)
                        <i class="ti ti-check text-xl"></i>
                    @elseif($isStep2Revisi)
                        <i class="ti ti-refresh text-xl"></i>
                    @elseif($isStep2Tolak)
                        <i class="ti ti-x text-xl"></i>
                    @else
                        <span class="font-bold">2</span>
                    @endif
                </div>
                <div
                    class="mt-2 text-[12px] font-bold {{ $isStep2Done || $isStep2Active || $isStep2Revisi || $isStep2Tolak ? 'text-gray-900' : 'text-gray-400' }}">
                    Verifikasi BAUAK</div>
            </div>

            {{-- Connector --}}
            <div
                class="flex-1 h-1 -mx-8 z-0 {{ in_array($pengajuan->status, ['menunggu_rektor', 'disetujui']) ? 'bg-success' : 'bg-gray-100' }}">
            </div>

            {{-- Step 3: Warek III --}}
            <div class="flex-1 text-center relative z-10">
                @php
                    $isStep3Done = in_array($pengajuan->status, ['menunggu_rektor', 'disetujui']);
                    $isStep3Active = $pengajuan->status == 'menunggu_warek3';
                @endphp
                <div
                    class="w-10 h-10 mx-auto rounded-full flex items-center justify-center border-4 border-white shadow-sm
                    {{ $isStep3Done ? 'bg-success text-white' : ($isStep3Active ? 'bg-warning text-white' : 'bg-gray-100 text-gray-400') }}">
                    @if ($isStep3Done)
                        <i class="ti ti-check text-xl"></i>
                    @else
                        <span class="font-bold">3</span>
                    @endif
                </div>
                <div
                    class="mt-2 text-[12px] font-bold {{ $isStep3Done || $isStep3Active ? 'text-gray-900' : 'text-gray-400' }}">
                    Persetujuan Warek III</div>
            </div>
        </div>

        {{-- Mobile Timeline --}}
        <div class="md:hidden space-y-4">
            <div class="flex items-start gap-3">
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ in_array($pengajuan->status, ['menunggu_kaprodi', 'menunggu_dekan', 'menunggu_bauak', 'menunggu_warek3', 'menunggu_rektor', 'disetujui']) ? 'bg-success text-white' : 'bg-gray-100 text-gray-400' }}">
                    <i class="ti ti-check"></i>
                </div>
                <div>
                    <p class="text-[13px] font-bold text-gray-900">Persetujuan Kaprodi</p>
                    <p class="text-[11px] text-gray-500">Pengajuan telah dikirim</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                    {{ in_array($pengajuan->status, ['menunggu_warek3', 'menunggu_rektor', 'disetujui']) ? 'bg-success text-white' : ($pengajuan->status == 'menunggu_kaprodi' ? 'bg-warning text-white' : ($pengajuan->status == 'revisi_bauak' ? 'bg-orange text-white' : ($pengajuan->status == 'ditolak_bauak' ? 'bg-danger text-white' : 'bg-gray-100 text-gray-400'))) }}">
                    @if (in_array($pengajuan->status, ['menunggu_warek3', 'menunggu_rektor', 'disetujui']))
                        <i class="ti ti-check"></i>
                    @elseif($pengajuan->status == 'revisi_bauak')
                        <i class="ti ti-refresh"></i>
                    @elseif($pengajuan->status == 'ditolak_bauak')
                        <i class="ti ti-x"></i>
                    @else
                        <span class="text-[12px] font-bold">2</span>
                    @endif
                </div>
                <div>
                    <p class="text-[13px] font-bold text-gray-900">Verifikasi BAUAK</p>
                    <p class="text-[11px] text-gray-500">Menunggu verifikasi BAUAK</p>
                </div>
            </div>

            <div class="flex items-start gap-3">
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0
                    {{ in_array($pengajuan->status, ['menunggu_rektor', 'disetujui']) ? 'bg-success text-white' : ($pengajuan->status == 'menunggu_warek3' ? 'bg-warning text-white' : 'bg-gray-100 text-gray-400') }}">
                    @if (in_array($pengajuan->status, ['menunggu_rektor', 'disetujui']))
                        <i class="ti ti-check"></i>
                    @else
                        <span class="text-[12px] font-bold">3</span>
                    @endif
                </div>
                <div>
                    <p class="text-[13px] font-bold text-gray-900">Persetujuan Warek III</p>
                    <p class="text-[11px] text-gray-500">Menunggu persetujuan final</p>
                </div>
            </div>
        </div>

        <div class="mt-6 text-center border-t border-gray-100 pt-4">
            @php
                $statusClass = match ($pengajuan->status) {
                    'draft' => 'badge-gray',
                    'menunggu_kaprodi' => 'badge-warning',
                    'menunggu_bauak' => 'badge-warning',
                    'menunggu_warek3' => 'badge-warning',
                    'menunggu_rektor' => 'badge-warning',
                    'menunggu_pp' => 'badge-warning',
                    'disetujui' => 'badge-success',
                    'revisi_bauak' => 'badge-orange',
                    'ditolak_kaprodi',
                    'ditolak_dekan',
                    'ditolak_bauak',
                    'ditolak_warek3',
                    'ditolak_rektor' => 'badge-danger',
                    'ditolak_pp' => 'badge-danger',
                    default => 'badge-gray',
                };
            @endphp
            <span class="text-[12px] text-gray-500 mr-2">Status Saat Ini:</span>
            <span class="badge {{ $statusClass }} text-[12px] px-3 py-1">
                {{ $pengajuan->status_label }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Informasi Kegiatan --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6">
                    <h3
                        class="text-[15px] font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                        <i class="ti ti-file-description text-brand"></i> Informasi Kegiatan
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Judul Kegiatan
                            </p>
                            <p class="text-[14px] font-semibold text-gray-900">{{ $pengajuan->judul_kegiatan }}</p>
                        </div>

                        <div>
                            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tujuan Kegiatan
                            </p>
                            <p class="text-[13px] text-gray-700 bg-gray-50 p-3 rounded-lg border border-gray-100">
                                {{ $pengajuan->tujuan_kegiatan }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Lokasi</p>
                                <p class="text-[13px] text-gray-900 flex items-center gap-2"><i
                                        class="ti ti-map-pin text-gray-400"></i> {{ $pengajuan->lokasi_kegiatan }}</p>
                            </div>
                            @if ($pengajuan->tempat_pesantren)
                                <div>
                                    <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tempat
                                        Pesantren</p>
                                    <p class="text-[13px] text-gray-900 flex items-center gap-2"><i
                                            class="ti ti-building text-gray-400"></i>
                                        {{ $pengajuan->tempat_pesantren }}</p>
                                </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tanggal
                                    Mulai</p>
                                <p class="text-[13px] text-gray-900 flex items-center gap-2"><i
                                        class="ti ti-calendar text-gray-400"></i>
                                    {{ $pengajuan->tanggal_mulai->format('d F Y') }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Tanggal
                                    Selesai</p>
                                <p class="text-[13px] text-gray-900 flex items-center gap-2"><i
                                        class="ti ti-calendar text-gray-400"></i>
                                    {{ $pengajuan->tanggal_selesai->format('d F Y') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Ketua
                                    Pelaksana</p>
                                <p class="text-[13px] text-gray-900 flex items-center gap-2"><i
                                        class="ti ti-user text-gray-400"></i> {{ $pengajuan->ketua_pelaksana }}</p>
                            </div>
                            <div>
                                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-1">Pemohon</p>
                                <p class="text-[13px] text-gray-900 flex items-center gap-2"><i
                                        class="ti ti-user-circle text-gray-400"></i> {{ $pengajuan->nama_pemohon }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dokumen --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6">
                    <h3
                        class="text-[15px] font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                        <i class="ti ti-files text-brand"></i> Dokumen Terlampir
                    </h3>

                    <div class="space-y-3">
                        @if ($pengajuan->proposal)
                            <div
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-danger-light text-danger flex items-center justify-center text-xl">
                                        <i class="ti ti-file-type-pdf"></i>
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-bold text-gray-900 leading-tight">Proposal Kegiatan
                                        </p>
                                        <p class="text-[11px] text-gray-500">
                                            {{ $pengajuan->proposal->file_size ?? 'PDF Document' }}</p>
                                    </div>
                                </div>
                                <a href="{{ $pengajuan->proposal->file_url }}" target="_blank"
                                    class="px-3 py-1.5 bg-white border border-gray-200 text-gray-700 rounded-lg text-[12px] font-medium hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                                    <i class="ti ti-download"></i> Unduh
                                </a>
                            </div>
                        @endif

                        @if ($pengajuan->rab)
                            <div
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-success-light text-success flex items-center justify-center text-xl">
                                        <i class="ti ti-file-spreadsheet"></i>
                                    </div>
                                    <div>
                                        <p class="text-[13px] font-bold text-gray-900 leading-tight">Rencana Anggaran
                                            Biaya (RAB)</p>
                                        <p class="text-[11px] text-gray-500">
                                            {{ $pengajuan->rab->file_size ?? 'PDF Document' }}</p>
                                    </div>
                                </div>
                                <a href="{{ $pengajuan->rab->file_url }}" target="_blank"
                                    class="px-3 py-1.5 bg-white border border-gray-200 text-gray-700 rounded-lg text-[12px] font-medium hover:bg-gray-50 transition shadow-sm flex items-center gap-2">
                                    <i class="ti ti-download"></i> Unduh
                                </a>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Ormawa Info --}}
            <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center gap-4">
                    <div
                        class="w-12 h-12 rounded-xl bg-brand-light text-brand flex items-center justify-center text-2xl font-bold">
                        {{ substr($pengajuan->ormawa->nama_ormawa, 0, 1) }}
                    </div>
                    <div class="p-5">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Organisasi</p>
                        <p class="text-[14px] font-bold text-gray-900 leading-tight mb-1">
                            {{ $pengajuan->ormawa->nama_ormawa }}</p>
                        <p class="text-[12px] text-gray-500 flex items-center gap-2"><i
                                class="ti ti-user-shield text-gray-400"></i> {{ $pengajuan->ormawa->ketua }}</p>
                    </div>
                </div>
            </div>
            {{-- Riwayat Verifikasi Gabungan Premium Timeline Layout --}}
            @php
                // 1. Ambil semua koleksi riwayat verifikasi
                $kaprodiLog = $pengajuan->persetujuanKaprodi ?? collect();
                $bauakLog = $pengajuan->verifikasiBauak ?? collect();
                $warek3Log = $pengajuan->persetujuanWarek3 ?? collect();
                $rektorLog = $pengajuan->persetujuanRektor ?? collect();

                // 2. Gabungkan dan urutkan maju dari tahapan TERAWAL ke TERAKHIR
                // Gunakan 'created_at' jika 'tanggal_verifikasi' pada beberapa log bernilai null
                $semuaRiwayat = collect()
                    ->merge($kaprodiLog)
                    ->merge($bauakLog)
                    ->merge($warek3Log)
                    ->merge($rektorLog)
                    ->sortBy('created_at');
            @endphp

            @if ($semuaRiwayat->count() > 0)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-5 sm:p-6">
                        <h3
                            class="text-[14px] font-bold text-gray-900 mb-6 pb-3 border-b border-gray-100 flex items-center gap-2">
                            <i class="ti ti-history text-brand text-lg"></i> Progres & Riwayat Verifikasi
                        </h3>

                        {{-- Timeline Container --}}
                        <div class="relative border-l-2 border-gray-100 pl-6 ml-3 space-y-6">
                            @foreach ($semuaRiwayat as $verifikasi)
                                @php
                                    $statusClean = strtolower($verifikasi->status ?? '');
                                    $roleClean = strtolower($verifikasi->user->role ?? '');

                                    // Konfigurasi Warna Tema Status
                                    [$badgeClass, $bgCircle, $iconCheck] = match ($statusClean) {
                                        'disetujui', 'setuju' => [
                                            'bg-green-50 text-green-700 border-green-200',
                                            'bg-green-500 text-white',
                                            'ti-check',
                                        ],
                                        'revisi' => [
                                            'bg-amber-50 text-amber-700 border-amber-200',
                                            'bg-amber-500 text-white',
                                            'ti-refresh',
                                        ],
                                        'ditolak', 'tolak' => [
                                            'bg-red-50 text-red-700 border-red-200',
                                            'bg-red-500 text-white',
                                            'ti-x',
                                        ],
                                        default => [
                                            'bg-gray-50 text-gray-600 border-gray-200',
                                            'bg-gray-400 text-white',
                                            'ti-minus',
                                        ],
                                    };

                                    // Ikon Dinamis Berdasarkan Jabatan/Role
                                    $roleIcon = match ($roleClean) {
                                        'dosen' => 'ti-school',
                                        'bauak' => 'ti-briefcase',
                                        'warek3' => 'ti-user-check',
                                        'rektor' => 'ti-id',
                                        default => 'ti-user',
                                    };

                                    // Label Cantik Jabatan
                                    $roleLabel = match ($roleClean) {
                                        'kaprodi' => 'Kepala Program Studi',
                                        'bauak' => 'Staff BAUAK',
                                        'warek3' => 'Wakil Rektor III',
                                        'rektor' => 'Rektor',
                                        default => ucfirst($verifikasi->user->role ?? 'Petugas'),
                                    };
                                @endphp

                                {{-- Timeline Item --}}
                                <div class="relative">
                                    {{-- Bulatan Penanda di Garis Timeline --}}
                                    <span
                                        class="absolute -left-[35px] top-0.5 w-6 h-6 rounded-full {{ $bgCircle }} flex items-center justify-center shadow-sm border-4 border-white z-10 text-[11px]">
                                        <i class="ti {{ $iconCheck }}"></i>
                                    </span>

                                    {{-- Kotak Konten --}}
                                    <div
                                        class="bg-gray-50/60 border border-gray-100 rounded-xl p-4 hover:bg-gray-50 transition duration-200">
                                        {{-- Baris Header Info --}}
                                        <div
                                            class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-2">
                                            <div>
                                                <h4 class="text-[13px] font-bold text-gray-900 leading-tight">
                                                    {{ $verifikasi->user->nama ?? 'Sistem' }}
                                                </h4>
                                                <p
                                                    class="text-[10px] text-gray-500 font-semibold flex items-center gap-1 mt-0.5">
                                                    <i class="ti {{ $roleIcon }} text-gray-400"></i>
                                                    {{ $roleLabel }}
                                                </p>
                                            </div>

                                            {{-- Waktu / Tanggal --}}
                                            <span
                                                class="text-[10px] text-gray-400 bg-white px-2 py-0.5 rounded-md border border-gray-100 self-start sm:self-center">
                                                <i class="ti ti-clock"></i>
                                                {{ $verifikasi->created_at?->format('d M Y, H:i') ?? ($verifikasi->tanggal_verifikasi?->format('d M Y, H:i') ?? 'Baru saja') }}
                                            </span>
                                        </div>

                                        {{-- Status Badge --}}
                                        <div class="mb-2.5">
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[10px] font-bold rounded-full border {{ $badgeClass }}">
                                                <span class="w-1 h-1 rounded-full bg-current"></span>
                                                {{ $verifikasi->status_label ?? ucfirst($statusClean) }}
                                            </span>
                                        </div>

                                        {{-- Catatan Review --}}
                                        @if ($verifikasi->catatan)
                                            <div
                                                class="text-[11px] text-gray-600 bg-white p-2.5 rounded-lg border border-gray-200/60 italic shadow-2xs">
                                                "{!! nl2br(e($verifikasi->catatan)) !!}"
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
