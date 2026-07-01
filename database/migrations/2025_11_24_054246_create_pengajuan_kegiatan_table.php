<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pengajuan_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ormawa_id')->constrained('ormawa')->onDelete('cascade');
            $table->string('judul_kegiatan');
            $table->text('tujuan_kegiatan')->nullable();
            $table->string('lokasi_kegiatan');
            $table->string('tempat_pesantren')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('ketua_pelaksana');
            $table->string('nama_pemohon');
            $table->enum('status', [
                'draft',
                'menunggu_kaprodi',
                'revisi_kaprodi',
                'menunggu_dekan',
                'revisi_dekan',
                'menunggu_bauak',
                'revisi_bauak',
                'menunggu_warek3',
                'revisi_warek3',
                'menunggu_rektor',
                'revisi_rektor',
                'disetujui',
                'ditolak',
                'ditolak_kaprodi',
                'selesai'
            ])->default('draft');
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['ormawa_id', 'status']);
            $table->index('tanggal_mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_kegiatan');
    }
};
