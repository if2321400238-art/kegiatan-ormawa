<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rab', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_kegiatan')->onDelete('cascade');
            $table->string('file_rab'); // path to PDF
            $table->decimal('total_anggaran', 15, 2)->nullable();
            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->integer('versi')->default(1);
            $table->timestamps();

            $table->index('pengajuan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rab');
    }
};
