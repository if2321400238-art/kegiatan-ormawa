<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('surat_rekomendasi');
    }

    public function down(): void
    {
        Schema::create('surat_rekomendasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_kegiatan')->cascadeOnDelete();
            $table->string('nomor_surat')->unique();
            $table->string('file_surat_draft')->nullable();
            $table->string('file_surat_final')->nullable();
            $table->enum('status', ['draft', 'menunggu_warek', 'ttd_warek3'])->default('draft');
            $table->timestamp('tanggal_ttd')->nullable();
            $table->timestamps();
        });
    }
};
