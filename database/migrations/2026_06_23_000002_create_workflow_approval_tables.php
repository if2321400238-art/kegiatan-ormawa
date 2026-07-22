<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifikasi_dosen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_kegiatan')->onDelete('cascade');
            $table->foreignId('user_dosen_id')->constrained('users')->onDelete('cascade');
            $table->text('catatan')->nullable();
            $table->enum('status', ['disetujui', 'revisi', 'ditolak']);
            $table->timestamp('tanggal_verifikasi');
            $table->timestamps();

            $table->index('pengajuan_id');
        });

        Schema::create('persetujuan_dekan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_kegiatan')->onDelete('cascade');
            $table->foreignId('user_dekan_id')->constrained('users')->onDelete('cascade');
            $table->text('catatan')->nullable();
            $table->enum('status', ['disetujui', 'revisi', 'ditolak']);
            $table->timestamp('tanggal_acc');
            $table->timestamps();

            $table->index('pengajuan_id');
        });

        Schema::create('persetujuan_rektor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_kegiatan')->onDelete('cascade');
            $table->foreignId('user_rektor_id')->constrained('users')->onDelete('cascade');
            $table->text('catatan')->nullable();
            $table->enum('status', ['disetujui', 'ditolak']);
            $table->timestamp('tanggal_acc');
            $table->timestamps();

            $table->index('pengajuan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persetujuan_rektor');
        Schema::dropIfExists('persetujuan_dekan');
        Schema::dropIfExists('verifikasi_dosen');
    }
};
