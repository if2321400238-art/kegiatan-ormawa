<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `pengajuan_kegiatan` MODIFY `status` ENUM('draft','menunggu_dosen','revisi_dosen','menunggu_dekan','revisi_dekan','menunggu_bauak','revisi_bauak','menunggu_warek3','revisi_warek3','menunggu_rektor','revisi_rektor','menunggu_pp','disetujui','ditolak_dosen','ditolak_dekan','ditolak_bauak','ditolak_warek3','ditolak_rektor','ditolak_pp','selesai') NOT NULL DEFAULT 'draft'");

        Schema::create('persetujuan_pp', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_kegiatan')->cascadeOnDelete();
            $table->foreignId('user_pp_id')->constrained('users')->cascadeOnDelete();
            $table->text('catatan')->nullable();
            $table->enum('status', ['disetujui', 'ditolak']);
            $table->timestamp('tanggal_acc');
            $table->timestamps();
            $table->index('pengajuan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persetujuan_pp');
        DB::table('pengajuan_kegiatan')->where('status', 'menunggu_pp')->update(['status' => 'menunggu_rektor']);
        DB::table('pengajuan_kegiatan')->where('status', 'ditolak_pp')->update(['status' => 'ditolak_rektor']);
        DB::statement("ALTER TABLE `pengajuan_kegiatan` MODIFY `status` ENUM('draft','menunggu_dosen','revisi_dosen','menunggu_dekan','revisi_dekan','menunggu_bauak','revisi_bauak','menunggu_warek3','revisi_warek3','menunggu_rektor','revisi_rektor','disetujui','ditolak_dosen','ditolak_dekan','ditolak_bauak','ditolak_warek3','ditolak_rektor','selesai') NOT NULL DEFAULT 'draft'");
    }
};
