<x-app-layout>
    <x-slot name="title">Ajukan Kegiatan Baru</x-slot>

    @php
        $temporaryUploads = session('pengajuan_temp_uploads', []);
        $temporaryProposal = old('temp_file_proposal') ? ($temporaryUploads[old('temp_file_proposal')] ?? null) : null;
        $temporaryRab = old('temp_file_rab') ? ($temporaryUploads[old('temp_file_rab')] ?? null) : null;
    @endphp

    <div class="mb-6">
        <h2 class="text-lg font-semibold text-gray-900">Form Pengajuan Kegiatan</h2>
        <p class="text-[12px] text-gray-500">Lengkapi formulir di bawah ini untuk mengajukan kegiatan baru</p>
    </div>

    <div class="max-w-4xl">
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

                <form action="{{ route('pengajuan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    <input type="hidden" name="temp_file_proposal" value="{{ old('temp_file_proposal') }}">
                    <input type="hidden" name="temp_file_rab" value="{{ old('temp_file_rab') }}">

                    {{-- Informasi Kegiatan --}}
                    <div>
                        <h3 class="text-[15px] font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                            <i class="ti ti-file-info text-brand"></i> Informasi Kegiatan
                        </h3>

                        <div class="space-y-4">
                            {{-- Judul Kegiatan --}}
                            <div>
                                <label for="judul_kegiatan" class="block text-[13px] font-medium text-gray-700 mb-1">
                                    Judul Kegiatan <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="judul_kegiatan" id="judul_kegiatan" required
                                    value="{{ old('judul_kegiatan') }}"
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors"
                                    placeholder="Contoh: Seminar Nasional Teknologi 2024">
                            </div>

                            {{-- Tujuan Kegiatan --}}
                            <div>
                                <label for="tujuan_kegiatan" class="block text-[13px] font-medium text-gray-700 mb-1">
                                    Tujuan Kegiatan <span class="text-danger">*</span>
                                </label>
                                <textarea name="tujuan_kegiatan" id="tujuan_kegiatan" required rows="3"
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors"
                                    placeholder="Jelaskan tujuan kegiatan...">{{ old('tujuan_kegiatan') }}</textarea>
                            </div>

                            {{-- Lokasi Kegiatan --}}
                            <div>
                                <label for="lokasi_kegiatan" class="block text-[13px] font-medium text-gray-700 mb-1">
                                    Lokasi Kegiatan <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="lokasi_kegiatan" id="lokasi_kegiatan" required
                                    value="{{ old('lokasi_kegiatan') }}"
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors"
                                    placeholder="Contoh: Aula Kampus">
                            </div>

                            {{-- Tempat Pesantren --}}
                            <div>
                                <label for="tempat_pesantren" class="block text-[13px] font-medium text-gray-700 mb-1">
                                    Tempat Pesantren/Mitra <span class="text-gray-400 font-normal">(Opsional)</span>
                                </label>
                                <input type="text" name="tempat_pesantren" id="tempat_pesantren"
                                    value="{{ old('tempat_pesantren') }}"
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors"
                                    placeholder="Contoh: Pesantren Al-Hikmah">
                            </div>

                            {{-- Tanggal --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="tanggal_mulai" class="block text-[13px] font-medium text-gray-700 mb-1">
                                        Tanggal Mulai <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" required
                                        value="{{ old('tanggal_mulai') }}"
                                        class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors">
                                </div>

                                <div>
                                    <label for="tanggal_selesai" class="block text-[13px] font-medium text-gray-700 mb-1">
                                        Tanggal Selesai <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" required
                                        value="{{ old('tanggal_selesai') }}"
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
                                <input type="text" name="ketua_pelaksana" id="ketua_pelaksana" required
                                    value="{{ old('ketua_pelaksana') }}"
                                    class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg text-[13px] focus:outline-none focus:border-brand focus:ring-1 focus:ring-brand focus:bg-white transition-colors"
                                    placeholder="Nama lengkap ketua pelaksana">
                            </div>

                            <div>
                                <label for="nama_pemohon" class="block text-[13px] font-medium text-gray-700 mb-1">
                                    Nama Pemohon <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="nama_pemohon" id="nama_pemohon" required
                                    value="{{ old('nama_pemohon', auth()->user()->nama) }}" readonly
                                    class="w-full px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg text-[13px] text-gray-500 cursor-not-allowed">
                            </div>
                        </div>
                    </div>

                    {{-- Dokumen --}}
                    <div>
                        <h3 class="text-[15px] font-bold text-gray-900 mb-4 pb-2 border-b border-gray-100 flex items-center gap-2">
                            <i class="ti ti-upload text-brand"></i> Upload Dokumen
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label for="file_proposal" class="block text-[13px] font-medium text-gray-700 mb-1">
                                    File Proposal (PDF) <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="file_proposal" id="file_proposal" @required(!$temporaryProposal) accept=".pdf"
                                    class="block w-full text-[13px] text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-[13px] file:font-semibold file:bg-brand/10 file:text-brand hover:file:bg-brand/20 transition-colors border border-gray-200 rounded-lg bg-gray-50">
                                @if($temporaryProposal)
                                    <p class="text-[11px] text-emerald-700 mt-1">
                                        <i class="ti ti-check"></i> File sebelumnya sudah tersimpan sementara: {{ $temporaryProposal['original_name'] }}
                                    </p>
                                @endif
                                <p class="text-[11px] text-gray-500 mt-1"><i class="ti ti-info-circle"></i> Format: PDF, Max: 5MB</p>
                            </div>

                            <div>
                                <label for="file_rab" class="block text-[13px] font-medium text-gray-700 mb-1">
                                    File RAB - Rencana Anggaran Biaya (PDF) <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="file_rab" id="file_rab" @required(!$temporaryRab) accept=".pdf"
                                    class="block w-full text-[13px] text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-[13px] file:font-semibold file:bg-brand/10 file:text-brand hover:file:bg-brand/20 transition-colors border border-gray-200 rounded-lg bg-gray-50">
                                @if($temporaryRab)
                                    <p class="text-[11px] text-emerald-700 mt-1">
                                        <i class="ti ti-check"></i> File sebelumnya sudah tersimpan sementara: {{ $temporaryRab['original_name'] }}
                                    </p>
                                @endif
                                <p class="text-[11px] text-gray-500 mt-1"><i class="ti ti-info-circle"></i> Format: PDF, Max: 5MB</p>
                            </div>
                        </div>
                    </div>

                    {{-- Info Box --}}
                    <div class="bg-info-light border border-info/20 rounded-xl p-4 flex gap-3">
                        <i class="ti ti-info-circle-filled text-info text-xl"></i>
                        <div>
                            <h4 class="text-[13px] font-bold text-blue-900 mb-1">Informasi Penting</h4>
                            <ul class="text-[12px] text-blue-800 list-disc list-inside space-y-0.5">
                                <li>Pastikan semua data yang diinput sudah benar</li>
                                <li>File proposal dan RAB harus dalam format PDF</li>
                                <li>Alur persetujuan ditentukan oleh tingkat organisasi: Prodi melalui Kaprodi dan Dekan, Fakultas melalui Dekan, Universitas langsung ke BAUAK.</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Buttons --}}
                    <div class="flex items-center justify-end gap-5 space-x-3 pt-4 border-t border-gray-100">
                        <a href="{{ route('pengajuan.index') }}"
                            class="px-5 py-2 bg-gray-100 text-gray-700 rounded-lg text-[13px] font-medium hover:bg-gray-200 transition">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-5 py-2 bg-brand text-white rounded-lg text-[13px] font-medium hover:bg-brand-active transition flex items-center gap-2 shadow-sm">
                            <i class="ti ti-send"></i> Ajukan Kegiatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Set minimum date to today
        document.getElementById('tanggal_mulai').min = new Date().toISOString().split('T')[0];
        document.getElementById('tanggal_selesai').min = new Date().toISOString().split('T')[0];

        // Update tanggal_selesai min when tanggal_mulai changes
        document.getElementById('tanggal_mulai').addEventListener('change', function() {
            document.getElementById('tanggal_selesai').min = this.value;
        });
    </script>
</x-app-layout>
