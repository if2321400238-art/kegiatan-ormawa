<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_pertanggungjawaban', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->unique()->constrained('pengajuan_kegiatan')->cascadeOnDelete();
            $table->text('ringkasan_pelaksanaan');
            $table->text('hasil_kegiatan');
            $table->text('kendala')->nullable();
            $table->date('tanggal_pelaksanaan_mulai');
            $table->date('tanggal_pelaksanaan_selesai');
            $table->unsignedInteger('jumlah_peserta')->default(0);
            $table->decimal('realisasi_anggaran', 15, 2)->default(0);
            $table->decimal('sisa_anggaran', 15, 2)->default(0);
            $table->string('file_laporan');
            $table->enum('status', ['draft', 'diajukan', 'revisi', 'diterima', 'ditolak'])->default('draft');
            $table->text('catatan_verifikator')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('lpj_realisasi_anggaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lpj_id')->constrained('laporan_pertanggungjawaban')->cascadeOnDelete();
            $table->string('uraian');
            $table->decimal('anggaran_rencana', 15, 2)->default(0);
            $table->decimal('anggaran_realisasi', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('lpj_lampiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lpj_id')->constrained('laporan_pertanggungjawaban')->cascadeOnDelete();
            $table->enum('jenis', ['dokumentasi', 'bukti_transaksi', 'lainnya']);
            $table->string('nama_file');
            $table->string('file_path');
            $table->timestamps();
        });

        Schema::create('lpj_versi_dokumen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lpj_id')->constrained('laporan_pertanggungjawaban')->cascadeOnDelete();
            $table->unsignedInteger('versi');
            $table->string('nama_file');
            $table->string('file_path');
            $table->foreignId('uploaded_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['lpj_id', 'versi']);
        });

        Schema::create('verifikasi_lpj', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lpj_id')->constrained('laporan_pertanggungjawaban')->cascadeOnDelete();
            $table->foreignId('user_bauak_id')->constrained('users')->restrictOnDelete();
            $table->enum('status', ['revisi', 'diterima', 'ditolak']);
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_verifikasi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikasi_lpj');
        Schema::dropIfExists('lpj_versi_dokumen');
        Schema::dropIfExists('lpj_lampiran');
        Schema::dropIfExists('lpj_realisasi_anggaran');
        Schema::dropIfExists('laporan_pertanggungjawaban');
    }
};
