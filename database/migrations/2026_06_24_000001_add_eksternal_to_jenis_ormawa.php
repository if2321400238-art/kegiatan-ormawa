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
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE `ormawa` MODIFY `jenis_ormawa` ENUM('fakultas','universitas','eksternal') NOT NULL DEFAULT 'fakultas'");
            }
        });
    }

    public function down(): void
    {
        Schema::table('ormawa', function (Blueprint $table) {
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE `ormawa` MODIFY `jenis_ormawa` ENUM('fakultas','universitas') NOT NULL DEFAULT 'fakultas'");
            }
        });
    }
};
