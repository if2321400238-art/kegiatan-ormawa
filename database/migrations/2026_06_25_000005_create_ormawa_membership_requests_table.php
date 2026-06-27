<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ormawa_membership_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ormawa_id')->constrained('ormawa')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('desired_jabatan')->default('anggota');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->unique(['ormawa_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ormawa_membership_requests');
    }
};
