<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_studi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fakultas_id')->nullable()->constrained('fakultas')->nullOnDelete();
            $table->foreignId('kaprodi_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama');
            $table->string('kode')->unique();
            $table->string('profile_url')->nullable();
            $table->boolean('is_lainnya')->default(false);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('prodi_id')->nullable()->after('fakultas_id')->constrained('program_studi')->nullOnDelete();
        });

        Schema::table('ormawa', function (Blueprint $table) {
            $table->foreignId('prodi_id')->nullable()->after('fakultas_id')->constrained('program_studi')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ormawa', fn (Blueprint $table) => $table->dropConstrainedForeignId('prodi_id'));
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('prodi_id'));
        Schema::dropIfExists('program_studi');
    }
};
