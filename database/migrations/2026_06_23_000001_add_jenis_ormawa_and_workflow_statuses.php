<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ormawa', function (Blueprint $table) {
            if (!Schema::hasColumn('ormawa', 'jenis_ormawa')) {
                $table->enum('jenis_ormawa', ['fakultas', 'universitas'])->default('fakultas')->after('pembina');
            }
        });

        // Update status enum with the new workflow stages (only on MySQL)
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `pengajuan_kegiatan` MODIFY `status` ENUM('draft','menunggu_dosen','revisi_dosen','menunggu_dekan','revisi_dekan','menunggu_bauak','revisi_bauak','menunggu_warek3','revisi_warek3','menunggu_rektor','revisi_rektor','disetujui','ditolak','selesai') NOT NULL DEFAULT 'draft'");
        }
    }

    public function down(): void
    {
        Schema::table('ormawa', function (Blueprint $table) {
            if (Schema::hasColumn('ormawa', 'jenis_ormawa')) {
                $table->dropColumn('jenis_ormawa');
            }
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `pengajuan_kegiatan` MODIFY `status` ENUM('draft','menunggu_dosen','revisi_bauak','menunggu_warek3','revisi_warek3','disetujui','ditolak','selesai') NOT NULL DEFAULT 'draft'");
        }
    }
};
