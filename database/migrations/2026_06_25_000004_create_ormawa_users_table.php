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
        Schema::create('ormawa_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ormawa_id')->constrained('ormawa')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('jabatan')->nullable(); // ketua, wakil_ketua, sekretaris, bendahara, anggota
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            // Unique constraint: setiap user hanya bisa menjadi member satu kali di ormawa yang sama
            $table->unique(['ormawa_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ormawa_users');
    }
};
