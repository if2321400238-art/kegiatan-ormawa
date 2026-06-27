<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggota_ormawa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ormawa_id')->constrained('ormawa')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('jabatan')->default('anggota');
            $table->boolean('status')->default(true);
            $table->timestamps();

            $table->unique(['ormawa_id', 'user_id']);
        });

        if (Schema::hasTable('ormawa_membership_requests')) {
            Schema::dropIfExists('ormawa_membership_requests');
        }

        if (Schema::hasTable('ormawa_users')) {
            Schema::dropIfExists('ormawa_users');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota_ormawa');
    }
};
