<x-app-layout>
    <x-slot name="title">Detail LPJ</x-slot>

    @php
        $statusClass = match ($lpj->status) {
            'diterima' => 'badge-success',
            'ditolak' => 'badge-danger',
            'draft' => 'badge-gray',
            default => 'badge-warning',
        };
        $totalRencana = $lpj->pengajuan->rab?->total_anggaran ?? $lpj->realisasiAnggaran->sum('anggaran_rencana');
    @endphp

    <div class="page-header">
        <div class="page-header-main">
            <div class="page-header-title">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <span class="badge {{ $statusClass }}">{{ $lpj->status_label }}</span>
                    <span class="text-xs font-medium text-gray-500">LPJ Kegiatan</span>
                </div>
                <h2 class="max-w-4xl text-xl font-semibold leading-snug text-gray-900 sm:text-2xl">
                    {{ $lpj->pengajuan->judul_kegiatan }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">{{ $lpj->pengajuan->ormawa->nama_ormawa }}</p>
            </div>

            <div class="page-header-actions">
                @if(in_array(auth()->user()->role, ['ormawa', 'mahasiswa']) && in_array($lpj->status, ['draft', 'revisi']))
                    <a href="{{ route('lpj.edit', $lpj) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-active">
                        <i class="ti ti-edit text-base"></i>
                        Perbarui LPJ
                    </a>
                @endif
                <a href="{{ route('lpj.index') }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    <i class="ti ti-arrow-left text-base"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_22rem]">
        <div class="min-w-0 space-y-6">
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pelaksanaan</p>
                        <p class="mt-2 text-sm font-semibold leading-relaxed text-gray-900">
                            {{ $lpj->tanggal_pelaksanaan_mulai->format('d M Y') }}<br>
                            <span class="text-gray-500">sampai</span> {{ $lpj->tanggal_pelaksanaan_selesai->format('d M Y') }}
                        </p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Peserta</p>
                        <p class="mt-2 text-2xl font-bold leading-none text-gray-900">{{ number_format($lpj->jumlah_peserta) }}</p>
                        <p class="mt-1 text-sm text-gray-500">orang</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Realisasi Anggaran</p>
                        <p class="mt-2 text-lg font-bold leading-snug text-gray-900">Rp {{ number_format($lpj->realisasi_anggaran, 0, ',', '.') }}</p>
                        <p class="mt-1 text-sm {{ $lpj->sisa_anggaran < 0 ? 'text-red-600' : 'text-gray-500' }}">
                            Sisa Rp {{ number_format($lpj->sisa_anggaran, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5 flex items-center gap-3 border-b border-gray-100 pb-4">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-info-light text-info">
                        <i class="ti ti-notes text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Ringkasan Pelaksanaan</h3>
                        <p class="text-sm text-gray-500">Narasi hasil kegiatan dan kendala yang dilaporkan.</p>
                    </div>
                </div>

                <div class="space-y-5 text-sm leading-6 text-gray-700">
                    <div>
                        <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">Ringkasan</p>
                        <p class="whitespace-pre-line break-words">{{ $lpj->ringkasan_pelaksanaan }}</p>
                    </div>
                    <div>
                        <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">Hasil Kegiatan</p>
                        <p class="whitespace-pre-line break-words">{{ $lpj->hasil_kegiatan }}</p>
                    </div>
                    @if($lpj->kendala)
                        <div>
                            <p class="mb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">Kendala</p>
                            <p class="whitespace-pre-line break-words">{{ $lpj->kendala }}</p>
                        </div>
                    @endif
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="flex flex-col gap-2 border-b border-gray-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Rencana dan Realisasi Anggaran</h3>
                        <p class="text-sm text-gray-500">Perbandingan anggaran yang diajukan dengan penggunaan aktual.</p>
                    </div>
                    <div class="text-sm font-semibold text-gray-700">
                        Total rencana: Rp {{ number_format($totalRencana, 0, ',', '.') }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-[760px] w-full text-sm">
                        <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="w-[42%] px-5 py-3 text-left font-semibold">Uraian</th>
                                <th class="px-5 py-3 text-right font-semibold">Rencana</th>
                                <th class="px-5 py-3 text-right font-semibold">Realisasi</th>
                                <th class="px-5 py-3 text-right font-semibold">Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($lpj->realisasiAnggaran as $item)
                                @php $selisih = $item->anggaran_rencana - $item->anggaran_realisasi; @endphp
                                <tr class="align-top">
                                    <td class="px-5 py-4">
                                        <div class="font-medium leading-5 text-gray-900 break-words">{{ $item->uraian }}</div>
                                        @if($item->keterangan)
                                            <div class="mt-1 text-xs leading-5 text-gray-500 break-words">{{ $item->keterangan }}</div>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right font-medium text-gray-700">Rp {{ number_format($item->anggaran_rencana, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right font-medium text-gray-900">Rp {{ number_format($item->anggaran_realisasi, 0, ',', '.') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right font-semibold {{ $selisih < 0 ? 'text-red-600' : 'text-gray-700' }}">Rp {{ number_format($selisih, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-gray-200 bg-gray-50 font-semibold text-gray-900">
                            <tr>
                                <td class="px-5 py-4">Total</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right">Rp {{ number_format($totalRencana, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right">Rp {{ number_format($lpj->realisasi_anggaran, 0, ',', '.') }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-right {{ $lpj->sisa_anggaran < 0 ? 'text-red-600' : 'text-gray-900' }}">Rp {{ number_format($lpj->sisa_anggaran, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            @if($lpj->pengajuan->rab?->items->isNotEmpty())
                <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="flex flex-col gap-2 border-b border-gray-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Rencana Anggaran Awal</h3>
                            <p class="text-sm text-gray-500">Otomatis dari pengajuan kegiatan.</p>
                        </div>
                        <a href="{{ route('pengajuan.rab.export', $lpj->pengajuan) }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-success px-3 py-2 text-sm font-semibold text-white hover:bg-success/90">
                            <i class="ti ti-file-spreadsheet"></i> Export Excel
                        </a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-[680px] w-full text-sm">
                            <thead class="bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                                <tr><th class="px-5 py-3 text-left">Uraian</th><th class="px-5 py-3 text-right">Rencana</th><th class="px-5 py-3 text-left">Keterangan</th></tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($lpj->pengajuan->rab->items as $item)
                                    <tr>
                                        <td class="px-5 py-4 font-medium text-gray-900">{{ $item->uraian }}</td>
                                        <td class="whitespace-nowrap px-5 py-4 text-right font-medium text-gray-700">Rp {{ number_format($item->anggaran_rencana, 0, ',', '.') }}</td>
                                        <td class="px-5 py-4 text-gray-500">{{ $item->keterangan }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endif
        </div>

        <aside class="space-y-6 xl:sticky xl:top-6 xl:self-start">
            <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                <h3 class="text-base font-semibold text-gray-900">Dokumen</h3>
                <div class="mt-4 space-y-3">
                    <a href="{{ $lpj->file_url }}" target="_blank" class="flex min-w-0 items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm hover:bg-white">
                        <i class="ti ti-file-text mt-0.5 flex-shrink-0 text-lg text-brand"></i>
                        <span class="min-w-0">
                            <span class="block font-semibold text-brand">Dokumen LPJ terbaru</span>
                            <span class="block text-xs text-gray-500">Buka di tab baru</span>
                        </span>
                    </a>

                    @foreach($lpj->lampiran as $file)
                        <a href="{{ $file->file_url }}" target="_blank" class="flex min-w-0 items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm hover:bg-white">
                            <i class="ti ti-paperclip mt-0.5 flex-shrink-0 text-lg text-brand"></i>
                            <span class="min-w-0">
                                <span class="block break-words font-semibold text-brand">{{ $file->nama_file }}</span>
                                <span class="block text-xs capitalize text-gray-500">{{ str_replace('_', ' ', $file->jenis) }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>

                @if($lpj->versiDokumen->count() > 1)
                    <div class="mt-5 border-t border-gray-100 pt-4">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Riwayat Versi</p>
                        <div class="space-y-2">
                            @foreach($lpj->versiDokumen as $versi)
                                <a href="{{ $versi->file_url }}" target="_blank" class="block rounded-lg px-3 py-2 text-xs text-brand hover:bg-gray-50">
                                    <span class="font-semibold">Versi {{ $versi->versi }}</span>
                                    <span class="block break-words text-gray-500">{{ $versi->nama_file }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>

            @if($lpj->catatan_verifikator)
                <section class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                    <div class="mb-2 flex items-center gap-2 text-amber-800">
                        <i class="ti ti-alert-circle text-lg"></i>
                        <h3 class="font-semibold">Catatan BAUAK</h3>
                    </div>
                    <p class="whitespace-pre-line break-words text-sm leading-6 text-amber-900">{{ $lpj->catatan_verifikator }}</p>
                </section>
            @endif

            @if(auth()->user()->isBauak() && $lpj->status === 'diajukan')
                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Keputusan BAUAK</h3>
                    <form method="POST" action="{{ route('bauak.lpj.decide', $lpj) }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <label for="catatan" class="mb-1 block text-sm font-medium text-gray-700">Catatan pemeriksaan</label>
                            <textarea id="catatan" name="catatan" rows="5" placeholder="Tuliskan poin revisi atau alasan keputusan..." class="w-full rounded-lg border-gray-300 text-sm leading-6 focus:border-brand focus:ring-brand"></textarea>
                        </div>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-3 xl:grid-cols-1">
                            <button name="status" value="revisi" class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-3 py-2 text-sm font-semibold text-white hover:bg-amber-600">
                                <i class="ti ti-edit-circle"></i> Revisi
                            </button>
                            <button name="status" value="ditolak" class="inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-700">
                                <i class="ti ti-circle-x"></i> Tolak
                            </button>
                            <button name="status" value="diterima" class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-700">
                                <i class="ti ti-circle-check"></i> Terima
                            </button>
                        </div>
                    </form>
                </section>
            @endif

            @if($lpj->riwayatVerifikasi->isNotEmpty())
                <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                    <h3 class="text-base font-semibold text-gray-900">Riwayat Verifikasi</h3>
                    <div class="mt-4 space-y-4">
                        @foreach($lpj->riwayatVerifikasi as $item)
                            <div class="relative border-l-2 border-gray-200 pl-4 text-sm">
                                <span class="absolute -left-[5px] top-1 h-2 w-2 rounded-full bg-brand"></span>
                                <p class="font-semibold capitalize text-gray-900">{{ $item->status }}</p>
                                <p class="mt-0.5 text-xs text-gray-500">{{ $item->user->nama }} · {{ $item->tanggal_verifikasi->format('d M Y H:i') }}</p>
                                @if($item->catatan)
                                    <p class="mt-2 whitespace-pre-line break-words leading-6 text-gray-700">{{ $item->catatan }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </aside>
    </div>
</x-app-layout>
