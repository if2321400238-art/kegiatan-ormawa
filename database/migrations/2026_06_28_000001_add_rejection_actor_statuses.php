<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `pengajuan_kegiatan` MODIFY `status` ENUM('draft','menunggu_dosen','revisi_dosen','menunggu_dekan','revisi_dekan','menunggu_bauak','revisi_bauak','menunggu_warek3','revisi_warek3','menunggu_rektor','revisi_rektor','disetujui','ditolak','ditolak_dosen','ditolak_dekan','ditolak_bauak','ditolak_warek3','ditolak_rektor','selesai') NOT NULL DEFAULT 'draft'");

        DB::statement("UPDATE pengajuan_kegiatan p SET status = CASE
            WHEN EXISTS (SELECT 1 FROM persetujuan_rektor r WHERE r.pengajuan_id = p.id AND r.status = 'ditolak') THEN 'ditolak_rektor'
            WHEN EXISTS (SELECT 1 FROM persetujuan_warek3 w WHERE w.pengajuan_id = p.id AND w.status = 'ditolak') THEN 'ditolak_warek3'
            WHEN EXISTS (SELECT 1 FROM verifikasi_bauak b WHERE b.pengajuan_id = p.id AND b.status = 'ditolak') THEN 'ditolak_bauak'
            WHEN EXISTS (SELECT 1 FROM persetujuan_dekan d WHERE d.pengajuan_id = p.id AND d.status = 'ditolak') THEN 'ditolak_dekan'
            ELSE 'ditolak_dosen'
        END WHERE p.status = 'ditolak'");

        DB::statement("ALTER TABLE `pengajuan_kegiatan` MODIFY `status` ENUM('draft','menunggu_dosen','revisi_dosen','menunggu_dekan','revisi_dekan','menunggu_bauak','revisi_bauak','menunggu_warek3','revisi_warek3','menunggu_rektor','revisi_rektor','disetujui','ditolak_dosen','ditolak_dekan','ditolak_bauak','ditolak_warek3','ditolak_rektor','selesai') NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `pengajuan_kegiatan` MODIFY `status` ENUM('draft','menunggu_dosen','revisi_dosen','menunggu_dekan','revisi_dekan','menunggu_bauak','revisi_bauak','menunggu_warek3','revisi_warek3','menunggu_rektor','revisi_rektor','disetujui','ditolak','ditolak_dosen','ditolak_dekan','ditolak_bauak','ditolak_warek3','ditolak_rektor','selesai') NOT NULL DEFAULT 'draft'");
        DB::table('pengajuan_kegiatan')->whereIn('status', [
            'ditolak_dosen', 'ditolak_dekan', 'ditolak_bauak', 'ditolak_warek3', 'ditolak_rektor',
        ])->update(['status' => 'ditolak']);
        DB::statement("ALTER TABLE `pengajuan_kegiatan` MODIFY `status` ENUM('draft','menunggu_dosen','revisi_dosen','menunggu_dekan','revisi_dekan','menunggu_bauak','revisi_bauak','menunggu_warek3','revisi_warek3','menunggu_rektor','revisi_rektor','disetujui','ditolak','selesai') NOT NULL DEFAULT 'draft'");
    }
};
