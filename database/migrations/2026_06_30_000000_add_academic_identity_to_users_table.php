<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nidn')->nullable()->unique()->after('nim');
            $table->string('program_studi')->nullable()->after('fakultas_id');
            $table->string('jabatan_fungsional')->nullable()->after('program_studi');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['nidn']);
            $table->dropColumn(['nidn', 'program_studi', 'jabatan_fungsional']);
        });
    }
};
