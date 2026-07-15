@php
    $editing = isset($lpj);
    $items = old('uraian')
        ? collect(old('uraian'))->map(fn ($v, $i) => (object) [
            'uraian' => $v,
            'anggaran_rencana' => old('anggaran_rencana')[$i] ?? 0,
            'anggaran_realisasi' => old('anggaran_realisasi')[$i] ?? 0,
            'keterangan' => old('keterangan')[$i] ?? '',
        ])
        : ($editing ? $lpj->realisasiAnggaran : collect([(object) [
            'uraian' => '',
            'anggaran_rencana' => 0,
            'anggaran_realisasi' => 0,
            'keterangan' => '',
        ]]));

    $fieldClass = 'mt-1.5 w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-brand-accent focus:outline-none focus:ring-2 focus:ring-brand-accent/20';
    $labelClass = 'block text-sm font-semibold text-gray-700';
@endphp

<x-app-layout>
    <x-slot name="title">{{ $editing ? 'Perbarui' : 'Buat' }} LPJ</x-slot>

    <form id="lpj-form" method="POST" enctype="multipart/form-data" action="{{ $editing ? route('lpj.update', $lpj) : route('lpj.store', $pengajuan) }}" class="space-y-6">
        @csrf
        @if($editing)
            @method('PATCH')
        @endif

        <div class="page-header">
            <div class="page-header-main">
                <div class="page-header-title">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $editing ? 'Perbarui Laporan Pertanggungjawaban' : 'Buat Laporan Pertanggungjawaban' }}</p>
                    <h2 class="mt-1 max-w-4xl text-xl font-semibold leading-snug text-gray-900 sm:text-2xl">{{ $pengajuan->judul_kegiatan }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ $pengajuan->ormawa->nama_ormawa }}</p>
                </div>
                <div class="page-header-actions">
                    <a href="{{ $editing ? route('lpj.show', $lpj) : route('pengajuan.show', $pengajuan) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                        <i class="ti ti-arrow-left text-base"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 shadow-sm">
                <div class="mb-2 flex items-center gap-2 font-semibold">
                    <i class="ti ti-alert-circle text-lg"></i>
                    Ada isian yang perlu diperbaiki
                </div>
                <ul class="ml-5 list-disc space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($editing && $lpj->status === 'revisi')
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900 shadow-sm">
                <div class="mb-1 flex items-center gap-2 font-semibold text-amber-800">
                    <i class="ti ti-alert-triangle text-lg"></i>
                    Catatan BAUAK
                </div>
                <p class="whitespace-pre-line break-words leading-6">{{ $lpj->catatan_verifikator }}</p>
            </div>
        @endif

        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex items-center gap-3 border-b border-gray-100 pb-4">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-info-light text-info">
                    <i class="ti ti-calendar-event text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Pelaksanaan Kegiatan</h3>
                    <p class="text-sm text-gray-500">Isi tanggal aktual, narasi pelaksanaan, hasil, peserta, dan kendala.</p>
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="tanggal_pelaksanaan_mulai" class="{{ $labelClass }}">Tanggal mulai aktual</label>
                    <input id="tanggal_pelaksanaan_mulai" type="date" name="tanggal_pelaksanaan_mulai" required value="{{ old('tanggal_pelaksanaan_mulai', $editing ? $lpj->tanggal_pelaksanaan_mulai->format('Y-m-d') : $pengajuan->tanggal_mulai->format('Y-m-d')) }}" class="{{ $fieldClass }}">
                </div>
                <div>
                    <label for="tanggal_pelaksanaan_selesai" class="{{ $labelClass }}">Tanggal selesai aktual</label>
                    <input id="tanggal_pelaksanaan_selesai" type="date" name="tanggal_pelaksanaan_selesai" required value="{{ old('tanggal_pelaksanaan_selesai', $editing ? $lpj->tanggal_pelaksanaan_selesai->format('Y-m-d') : $pengajuan->tanggal_selesai->format('Y-m-d')) }}" class="{{ $fieldClass }}">
                </div>
            </div>

            <div class="mt-4 grid gap-4">
                <div>
                    <label for="ringkasan_pelaksanaan" class="{{ $labelClass }}">Ringkasan pelaksanaan</label>
                    <textarea id="ringkasan_pelaksanaan" name="ringkasan_pelaksanaan" required rows="5" placeholder="Ketik ringkasan pelaksanaan kegiatan di sini..." class="{{ $fieldClass }} leading-6">{{ old('ringkasan_pelaksanaan', $lpj->ringkasan_pelaksanaan ?? '') }}</textarea>
                </div>
                <div>
                    <label for="hasil_kegiatan" class="{{ $labelClass }}">Hasil kegiatan</label>
                    <textarea id="hasil_kegiatan" name="hasil_kegiatan" required rows="5" placeholder="Tuliskan capaian, luaran, atau dampak kegiatan..." class="{{ $fieldClass }} leading-6">{{ old('hasil_kegiatan', $lpj->hasil_kegiatan ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="jumlah_peserta" class="{{ $labelClass }}">Jumlah peserta</label>
                    <input id="jumlah_peserta" type="number" min="0" name="jumlah_peserta" required value="{{ old('jumlah_peserta', $lpj->jumlah_peserta ?? 0) }}" placeholder="Contoh: 120" class="{{ $fieldClass }}">
                </div>
                <div>
                    <label for="kendala" class="{{ $labelClass }}">Kendala <span class="font-medium text-gray-400">(opsional)</span></label>
                    <textarea id="kendala" name="kendala" rows="3" placeholder="Tuliskan kendala jika ada..." class="{{ $fieldClass }} leading-6">{{ old('kendala', $lpj->kendala ?? '') }}</textarea>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-gray-100 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-success-light text-success">
                        <i class="ti ti-cash-banknote text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Realisasi Anggaran</h3>
                        <p class="text-sm text-gray-500">Total RAB: {{ $pengajuan->rab?->total_anggaran_formatted ?? 'Belum tercatat' }}</p>
                    </div>
                </div>
                <button type="button" onclick="addItem()" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    <i class="ti ti-plus text-base"></i>
                    Tambah item
                </button>
            </div>

            <div class="overflow-x-auto">
                <div class="min-w-[920px]">
                    <div class="grid grid-cols-12 gap-3 border-b border-gray-200 bg-gray-50 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500">
                        <div class="col-span-3">Uraian</div>
                        <div class="col-span-2">Rencana (Rp)</div>
                        <div class="col-span-2">Realisasi (Rp)</div>
                        <div class="col-span-4">Keterangan</div>
                        <div class="col-span-1 text-center">Aksi</div>
                    </div>
                    <div id="items" class="divide-y divide-gray-100">
                        @foreach($items as $item)
                            <div class="item grid grid-cols-12 gap-3 px-5 py-4 align-top">
                                <div class="col-span-3">
                                    <label class="sr-only">Uraian</label>
                                    <input name="uraian[]" required value="{{ $item->uraian }}" placeholder="Contoh: Konsumsi peserta" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-accent focus:outline-none focus:ring-2 focus:ring-brand-accent/20">
                                </div>
                                <div class="col-span-2">
                                    <label class="sr-only">Anggaran rencana</label>
                                    <input type="number" min="0" step="0.01" name="anggaran_rencana[]" required value="{{ $item->anggaran_rencana }}" placeholder="Contoh: 500000" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-accent focus:outline-none focus:ring-2 focus:ring-brand-accent/20">
                                </div>
                                <div class="col-span-2">
                                    <label class="sr-only">Anggaran realisasi</label>
                                    <input type="number" min="0" step="0.01" name="anggaran_realisasi[]" required value="{{ $item->anggaran_realisasi }}" placeholder="Contoh: 500000" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-accent focus:outline-none focus:ring-2 focus:ring-brand-accent/20">
                                </div>
                                <div class="col-span-4">
                                    <label class="sr-only">Keterangan</label>
                                    <input name="keterangan[]" value="{{ $item->keterangan }}" placeholder="Catatan penggunaan anggaran" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-accent focus:outline-none focus:ring-2 focus:ring-brand-accent/20">
                                </div>
                                <div class="col-span-1 flex justify-center">
                                    <button type="button" onclick="removeItem(this)" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 hover:bg-red-100" aria-label="Hapus item anggaran">
                                        <i class="ti ti-trash text-lg"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
            <div class="mb-5 flex items-center gap-3 border-b border-gray-100 pb-4">
                <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-brand-surface text-brand-accent">
                    <i class="ti ti-file-upload text-xl"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Dokumen dan Bukti</h3>
                    <p class="text-sm text-gray-500">Unggah dokumen LPJ, dokumentasi, bukti transaksi, dan lampiran pendukung.</p>
                </div>
            </div>

            <div class="grid gap-4 lg:grid-cols-2">
                <div class="lg:col-span-2">
                    <label class="{{ $labelClass }}">Dokumen LPJ <span class="font-medium text-gray-400">PDF/DOC/DOCX, maks. 10 MB</span></label>
                    <div id="file-upload-alert" class="mt-2 hidden rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"></div>
                    <label data-drop-zone="file_laporan" class="mt-1.5 flex cursor-pointer items-center gap-3 rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-4 text-sm transition hover:border-brand-accent hover:bg-brand-surface/60">
                        <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-white text-brand shadow-sm">
                            <i class="ti ti-file-text text-xl"></i>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-semibold text-gray-900">Pilih dokumen LPJ</span>
                            <span data-file-label="file_laporan" class="block truncate text-xs text-gray-500">Belum ada file dipilih</span>
                        </span>
                        <span class="rounded-lg bg-brand px-3 py-2 text-xs font-semibold text-white">Pilih File</span>
                        <input type="file" name="file_laporan" @required(!$editing) accept=".pdf,.doc,.docx" class="sr-only" data-file-input="file_laporan" data-default-label="Belum ada file dipilih" data-max-mb="10">
                    </label>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Dokumentasi</label>
                    <label data-drop-zone="dokumentasi" class="mt-1.5 flex cursor-pointer items-center gap-3 rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-4 text-sm transition hover:border-brand-accent hover:bg-brand-surface/60">
                        <i class="ti ti-photo text-2xl text-brand"></i>
                        <span class="min-w-0 flex-1">
                            <span class="block font-semibold text-gray-900">Unggah dokumentasi</span>
                            <span data-file-label="dokumentasi" class="block truncate text-xs text-gray-500">JPG, PNG, atau PDF</span>
                        </span>
                        <input type="file" multiple name="dokumentasi[]" accept=".jpg,.jpeg,.png,.pdf" class="sr-only" data-file-input="dokumentasi" data-default-label="JPG, PNG, atau PDF" data-max-mb="5">
                    </label>
                </div>

                <div>
                    <label class="{{ $labelClass }}">Bukti transaksi</label>
                    <label data-drop-zone="bukti_transaksi" class="mt-1.5 flex cursor-pointer items-center gap-3 rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-4 text-sm transition hover:border-brand-accent hover:bg-brand-surface/60">
                        <i class="ti ti-receipt text-2xl text-brand"></i>
                        <span class="min-w-0 flex-1">
                            <span class="block font-semibold text-gray-900">Unggah bukti transaksi</span>
                            <span data-file-label="bukti_transaksi" class="block truncate text-xs text-gray-500">JPG, PNG, atau PDF</span>
                        </span>
                        <input type="file" multiple name="bukti_transaksi[]" accept=".jpg,.jpeg,.png,.pdf" class="sr-only" data-file-input="bukti_transaksi" data-default-label="JPG, PNG, atau PDF" data-max-mb="5">
                    </label>
                </div>

                <div class="lg:col-span-2">
                    <label class="{{ $labelClass }}">Lampiran lainnya</label>
                    <label data-drop-zone="lampiran_lainnya" class="mt-1.5 flex cursor-pointer items-center gap-3 rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-4 text-sm transition hover:border-brand-accent hover:bg-brand-surface/60">
                        <i class="ti ti-paperclip text-2xl text-brand"></i>
                        <span class="min-w-0 flex-1">
                            <span class="block font-semibold text-gray-900">Unggah lampiran tambahan</span>
                            <span data-file-label="lampiran_lainnya" class="block truncate text-xs text-gray-500">PDF, gambar, DOC, atau DOCX</span>
                        </span>
                        <input type="file" multiple name="lampiran_lainnya[]" class="sr-only" data-file-input="lampiran_lainnya" data-default-label="PDF, gambar, DOC, atau DOCX" data-max-mb="5">
                    </label>
                </div>
            </div>

            @if($editing && $lpj->lampiran->isNotEmpty())
                <div class="mt-5 border-t border-gray-100 pt-4">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Lampiran tersimpan</p>
                    <div class="space-y-2">
                        @foreach($lpj->lampiran as $file)
                            <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm">
                                <a target="_blank" class="min-w-0 flex-1 truncate font-semibold text-brand" href="{{ $file->file_url }}">{{ $file->nama_file }}</a>
                                <button type="submit" form="delete-file-{{ $file->id }}" class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">
                                    <i class="ti ti-trash"></i>
                                    Hapus
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button type="submit" name="aksi" value="draft" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                <i class="ti ti-device-floppy text-base"></i>
                Simpan Draft
            </button>
            <button type="button" onclick="openSubmitModal()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-active">
                <i class="ti ti-send text-base"></i>
                Ajukan ke BAUAK
            </button>
        </div>

        <div id="submit-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-950/50 p-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <div class="mb-4 flex items-start gap-3">
                    <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-warning-light text-warning">
                        <i class="ti ti-alert-triangle text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Ajukan LPJ ke BAUAK?</h3>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Pastikan data pelaksanaan, realisasi anggaran, dan dokumen pendukung sudah benar. Setelah diajukan, LPJ akan masuk proses pemeriksaan.</p>
                    </div>
                </div>
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <button type="button" onclick="closeSubmitModal()" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
                    <button type="submit" name="aksi" value="ajukan" class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white hover:bg-brand-active">Ya, Ajukan</button>
                </div>
            </div>
        </div>
    </form>

    @if($editing)
        @foreach($lpj->lampiran as $file)
            <form id="delete-file-{{ $file->id }}" method="POST" action="{{ route('lpj.lampiran.destroy', [$lpj, $file]) }}">
                @csrf
                @method('DELETE')
            </form>
        @endforeach
    @endif

    <template id="budget-item-template">
        <div class="item grid grid-cols-12 gap-3 px-5 py-4 align-top">
            <div class="col-span-3">
                <label class="sr-only">Uraian</label>
                <input name="uraian[]" required placeholder="Contoh: Konsumsi peserta" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-accent focus:outline-none focus:ring-2 focus:ring-brand-accent/20">
            </div>
            <div class="col-span-2">
                <label class="sr-only">Anggaran rencana</label>
                <input type="number" min="0" step="0.01" name="anggaran_rencana[]" required placeholder="Contoh: 500000" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-accent focus:outline-none focus:ring-2 focus:ring-brand-accent/20">
            </div>
            <div class="col-span-2">
                <label class="sr-only">Anggaran realisasi</label>
                <input type="number" min="0" step="0.01" name="anggaran_realisasi[]" required placeholder="Contoh: 500000" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-accent focus:outline-none focus:ring-2 focus:ring-brand-accent/20">
            </div>
            <div class="col-span-4">
                <label class="sr-only">Keterangan</label>
                <input name="keterangan[]" placeholder="Catatan penggunaan anggaran" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm placeholder:text-gray-400 focus:border-brand-accent focus:outline-none focus:ring-2 focus:ring-brand-accent/20">
            </div>
            <div class="col-span-1 flex justify-center">
                <button type="button" onclick="removeItem(this)" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 hover:bg-red-100" aria-label="Hapus item anggaran">
                    <i class="ti ti-trash text-lg"></i>
                </button>
            </div>
        </div>
    </template>

    <script>
        function addItem() {
            const template = document.getElementById('budget-item-template');
            document.getElementById('items').appendChild(template.content.cloneNode(true));
        }

        function removeItem(button) {
            const items = document.querySelectorAll('#items .item');
            if (items.length > 1) {
                button.closest('.item').remove();
            }
        }

        function openSubmitModal() {
            document.getElementById('submit-modal').classList.remove('hidden');
            document.getElementById('submit-modal').classList.add('flex');
        }

        function closeSubmitModal() {
            document.getElementById('submit-modal').classList.add('hidden');
            document.getElementById('submit-modal').classList.remove('flex');
        }

        const uploadAlert = document.getElementById('file-upload-alert');
        const maxTotalUploadBytes = 32 * 1024 * 1024;

        function showUploadAlert(message) {
            if (!uploadAlert) return;
            uploadAlert.textContent = message;
            uploadAlert.classList.remove('hidden');
        }

        function clearUploadAlert() {
            if (!uploadAlert) return;
            uploadAlert.textContent = '';
            uploadAlert.classList.add('hidden');
        }

        function resetFileInput(input) {
            input.value = '';
            const label = document.querySelector(`[data-file-label="${input.dataset.fileInput}"]`);
            if (label) label.textContent = input.dataset.defaultLabel || 'Belum ada file dipilih';
        }

        function selectedUploadBytes() {
            return Array.from(document.querySelectorAll('[data-file-input]'))
                .flatMap((input) => Array.from(input.files || []))
                .reduce((total, file) => total + file.size, 0);
        }

        function updateFileLabel(input) {
            const label = document.querySelector(`[data-file-label="${input.dataset.fileInput}"]`);
            if (!label) return;
            if (input.files.length === 0) {
                label.textContent = input.dataset.defaultLabel || 'Belum ada file dipilih';
                return;
            }
            label.textContent = input.files.length === 1 ? input.files[0].name : `${input.files.length} file dipilih`;
        }

        function validateFileInput(input) {
            clearUploadAlert();
            const files = Array.from(input.files || []);
            const maxMb = Number(input.dataset.maxMb || 0);
            const maxBytes = maxMb * 1024 * 1024;
            const tooLarge = files.find((file) => maxBytes && file.size > maxBytes);

            if (tooLarge) {
                resetFileInput(input);
                showUploadAlert(`File "${tooLarge.name}" terlalu besar. Maksimal ${maxMb} MB per file untuk bagian ini.`);
                return false;
            }

            if (selectedUploadBytes() > maxTotalUploadBytes) {
                resetFileInput(input);
                showUploadAlert('Total file yang dipilih terlalu besar. Maksimal 32 MB dalam satu kali pengajuan. Kurangi jumlah atau ukuran file.');
                return false;
            }

            updateFileLabel(input);
            return true;
        }

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((eventName) => {
            window.addEventListener(eventName, (event) => {
                event.preventDefault();
                event.stopPropagation();
            });
        });

        document.querySelectorAll('[data-file-input]').forEach((input) => {
            input.addEventListener('change', () => validateFileInput(input));
        });

        document.querySelectorAll('[data-drop-zone]').forEach((zone) => {
            const input = document.querySelector(`[data-file-input="${zone.dataset.dropZone}"]`);
            if (!input) return;

            ['dragenter', 'dragover'].forEach((eventName) => {
                zone.addEventListener(eventName, () => {
                    zone.classList.add('border-brand-accent', 'bg-brand-surface');
                });
            });

            ['dragleave', 'drop'].forEach((eventName) => {
                zone.addEventListener(eventName, () => {
                    zone.classList.remove('border-brand-accent', 'bg-brand-surface');
                });
            });

            zone.addEventListener('drop', (event) => {
                const droppedFiles = Array.from(event.dataTransfer?.files || []);
                if (droppedFiles.length === 0) return;

                const transfer = new DataTransfer();
                const files = input.multiple ? droppedFiles : [droppedFiles[0]];
                files.forEach((file) => transfer.items.add(file));
                input.files = transfer.files;
                validateFileInput(input);
            });
        });
    </script>
</x-app-layout>
