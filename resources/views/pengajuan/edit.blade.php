<x-app-layout>
    <x-slot name="title">Edit Pengajuan Kegiatan</x-slot>

    @php
        $rabItems = old('rab_uraian')
            ? collect(old('rab_uraian'))->map(fn ($v, $i) => (object) ['uraian' => $v, 'anggaran_rencana' => old('rab_anggaran_rencana')[$i] ?? 0, 'keterangan' => old('rab_keterangan')[$i] ?? ''])
            : ($pengajuan->rab?->items->isNotEmpty() ? $pengajuan->rab->items : collect([(object) ['uraian' => '', 'anggaran_rencana' => 0, 'keterangan' => '']]));
    @endphp

    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Edit Pengajuan Kegiatan</h2>
        <p class="text-[12px] text-gray-500">Perbarui informasi pengajuan kegiatan Anda di bawah ini</p>
    </div>

    <div class="max-w-4xl">
        {{-- Alert Catatan Revisi --}}
        @if($pengajuan->catatan)
            <div class="mb-6 bg-warning-light border border-warning/20 rounded-xl p-4 flex gap-3 shadow-sm">
                <i class="ti ti-alert-triangle-filled text-warning text-xl"></i>
                <div>
                    <h4 class="text-[13px] font-bold text-amber-900 mb-1">Catatan Revisi:</h4>
                    <p class="text-[12px] text-amber-800">{{ $pengajuan->catatan }}</p>
                </div>
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6">
                @if ($errors->any())
                    <div class="mb-6 bg-danger-light border border-danger/20 text-red-800 px-4 py-3 rounded-xl flex items-start gap-3">
                        <i class="ti ti-alert-triangle-filled text-danger text-xl"></i>
                        <div>
                            <span class="block text-[13px] font-semibold pt-0.5 mb-1">Ada beberapa masalah dengan input Anda:</span>
                            <ul class="text-[12px] list-disc list-inside space-y-0.5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('pengajuan.update', $pengajuan) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    @method('PATCH')

                    {{-- Informasi Kegiatan --}}
                    <div>
                        <h3 class="text-[15px] font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                            <i class="ti ti-file-info text-brand"></i> Informasi Kegiatan
                        </h3>

                        <div class="space-y-4">
                            <div class="mb-4">
                                <label for="judul_kegiatan" class="block text-[13px] font-medium text-gray-700 mb-1">
                                    Judul Kegiatan <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="judul_kegiatan" id="judul_kegiatan" required
                                    value="{{ old('judul_kegiatan', $pengajuan->judul_kegiatan) }}"
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors">
                            </div>

                            <div class="mb-4">
                                <label for="tujuan_kegiatan" class="block text-[13px] font-medium text-gray-700 mb-1">
                                    Tujuan Kegiatan <span class="text-danger">*</span>
                                </label>
                                <textarea name="tujuan_kegiatan" id="tujuan_kegiatan" required rows="3"
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors">{{ old('tujuan_kegiatan', $pengajuan->tujuan_kegiatan) }}</textarea>
                            </div>

                            <div class="mb-4">
                                <label for="lokasi_kegiatan" class="block text-[13px] font-medium text-gray-700 mb-1">
                                    Lokasi Kegiatan <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="lokasi_kegiatan" id="lokasi_kegiatan" required
                                    value="{{ old('lokasi_kegiatan', $pengajuan->lokasi_kegiatan) }}"
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors">
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="tanggal_mulai" class="block text-[13px] font-medium text-gray-700 mb-1">
                                        Tanggal Mulai <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" required
                                        value="{{ old('tanggal_mulai', $pengajuan->tanggal_mulai->format('Y-m-d')) }}"
                                        class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors">
                                </div>

                                <div>
                                    <label for="tanggal_selesai" class="block text-[13px] font-medium text-gray-700 mb-1">
                                        Tanggal Selesai <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" required
                                        value="{{ old('tanggal_selesai', $pengajuan->tanggal_selesai->format('Y-m-d')) }}"
                                        class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Penanggung Jawab --}}
                    <div>
                        <h3 class="text-[15px] font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                            <i class="ti ti-users text-brand"></i> Penanggung Jawab
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="ketua_pelaksana" class="block text-[13px] font-medium text-gray-700 mb-1">
                                    Ketua Pelaksana <span class="text-danger">*</span>
                                </label>
                                <select name="ketua_pelaksana" id="ketua_pelaksana" required
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors">
                                    <option value="">Pilih anggota ormawa</option>
                                    @foreach($anggotaPelaksana as $anggota)
                                        <option value="{{ $anggota->nama }}" @selected(old('ketua_pelaksana', $pengajuan->ketua_pelaksana) === $anggota->nama)>
                                            {{ $anggota->nama }}{{ $anggota->nim ? ' - '.$anggota->nim : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="nama_pemohon" class="block text-[13px] font-medium text-gray-700 mb-1">
                                    Nama Pemohon <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nama_pemohon" id="nama_pemohon" required
                                    value="{{ old('nama_pemohon', $pengajuan->nama_pemohon) }}" readonly
                                    class="w-full px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg text-[13px] text-gray-500 cursor-not-allowed">
                            </div>
                        </div>
                    </div>

                    {{-- Rencana Anggaran --}}
                    <div>
                        <h3 class="text-[15px] font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                            <i class="ti ti-cash-banknote text-brand"></i> Rencana Anggaran
                        </h3>

                        <div class="overflow-x-auto rounded-xl border border-gray-100">
                            <div class="min-w-[760px]">
                                <div class="grid grid-cols-12 gap-3 bg-gray-50 px-4 py-3 text-[11px] font-semibold uppercase tracking-wide text-gray-500">
                                    <div class="col-span-4">Uraian</div>
                                    <div class="col-span-3">Rencana (Rp)</div>
                                    <div class="col-span-4">Keterangan</div>
                                    <div class="col-span-1 text-center">Aksi</div>
                                </div>
                                <div id="rab-items" class="divide-y divide-gray-100">
                                    @foreach($rabItems as $item)
                                        <div class="rab-item grid grid-cols-12 gap-3 px-4 py-3">
                                            <input name="rab_uraian[]" required value="{{ $item->uraian }}" placeholder="Contoh: Konsumsi peserta" class="col-span-4 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand">
                                            <input type="number" min="0" step="0.01" name="rab_anggaran_rencana[]" required value="{{ $item->anggaran_rencana }}" placeholder="500000" class="col-span-3 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand">
                                            <input name="rab_keterangan[]" value="{{ $item->keterangan }}" placeholder="Opsional" class="col-span-4 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand">
                                            <button type="button" onclick="removeRabItem(this)" class="col-span-1 rounded-lg border border-red-200 bg-red-50 text-red-600 hover:bg-red-100"><i class="ti ti-trash"></i></button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="addRabItem()" class="mt-3 px-3 py-2 bg-gray-100 text-gray-700 rounded-lg text-[13px] font-medium hover:bg-gray-200"><i class="ti ti-plus"></i> Tambah Item</button>
                    </div>

                    {{-- Dokumen --}}
                    <div>
                        <h3 class="text-[15px] font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                            <i class="ti ti-upload text-brand"></i> Update Dokumen <span class="text-gray-400 font-normal">(Opsional)</span>
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label for="file_proposal" class="block text-[13px] font-medium text-gray-700 mb-1">
                                    File Proposal Baru (PDF)
                                </label>
                                @if($pengajuan->proposal)
                                    <div class="mb-2 text-[12px] flex items-center gap-2">
                                        <span class="text-gray-500">File saat ini:</span>
                                        <a href="{{ $pengajuan->proposal->file_url }}" target="_blank" class="text-brand hover:text-brand-active hover:underline flex items-center gap-1 font-medium">
                                            <i class="ti ti-external-link"></i> Lihat Proposal
                                        </a>
                                    </div>
                                @endif
                                <input type="file" name="file_proposal" id="file_proposal" accept=".pdf"
                                    class="block w-full text-[13px] text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-[13px] file:font-semibold file:bg-brand/10 file:text-brand hover:file:bg-brand/20 transition-colors border border-gray-200 rounded-lg bg-gray-50">
                                <p class="text-[11px] text-gray-500 mt-1"><i class="ti ti-info-circle"></i> Kosongkan jika tidak ingin mengubah. Format: PDF, Max: 5MB</p>
                            </div>

                            <div>
                                <label for="file_rab" class="block text-[13px] font-medium text-gray-700 mb-1">
                                    File RAB Baru (PDF)
                                </label>
                                @if($pengajuan->rab)
                                    <div class="mb-2 text-[12px] flex items-center gap-2">
                                        <span class="text-gray-500">File saat ini:</span>
                                        <a href="{{ $pengajuan->rab->file_url }}" target="_blank" class="text-brand hover:text-brand-active hover:underline flex items-center gap-1 font-medium">
                                            <i class="ti ti-external-link"></i> Lihat RAB
                                        </a>
                                    </div>
                                @endif
                                <input type="file" name="file_rab" id="file_rab" accept=".pdf"
                                    class="block w-full text-[13px] text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-[13px] file:font-semibold file:bg-brand/10 file:text-brand hover:file:bg-brand/20 transition-colors border border-gray-200 rounded-lg bg-gray-50">
                                <p class="text-[11px] text-gray-500 mt-1"><i class="ti ti-info-circle"></i> Kosongkan jika tidak ingin mengubah. Format: PDF, Max: 5MB</p>
                            </div>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('pengajuan.show', $pengajuan) }}"
                            class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-lg text-[13px] font-medium hover:bg-gray-200 transition">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-5 py-2.5 bg-brand text-white rounded-lg text-[13px] font-medium hover:bg-brand-active transition flex items-center gap-2 shadow-sm">
                            <i class="ti ti-device-floppy"></i> Simpan & Ajukan Ulang
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        function addRabItem() {
            document.getElementById('rab-items').insertAdjacentHTML('beforeend', `
                <div class="rab-item grid grid-cols-12 gap-3 px-4 py-3">
                    <input name="rab_uraian[]" required placeholder="Contoh: Konsumsi peserta" class="col-span-4 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand">
                    <input type="number" min="0" step="0.01" name="rab_anggaran_rencana[]" required placeholder="500000" class="col-span-3 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand">
                    <input name="rab_keterangan[]" placeholder="Opsional" class="col-span-4 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand">
                    <button type="button" onclick="removeRabItem(this)" class="col-span-1 rounded-lg border border-red-200 bg-red-50 text-red-600 hover:bg-red-100"><i class="ti ti-trash"></i></button>
                </div>`);
        }

        function removeRabItem(button) {
            if (document.querySelectorAll('#rab-items .rab-item').length > 1) button.closest('.rab-item').remove();
        }
    </script>
</x-app-layout>
