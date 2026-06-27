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
        Schema::table('users', function (Blueprint $table) {
            // Modify the role enum to include 'mahasiswa'
            $table->enum('role', ['ormawa', 'mahasiswa', 'bauak', 'warek3', 'admin', 'dosen', 'dekan', 'rektor', 'pp'])
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove 'mahasiswa' from role enum
            $table->enum('role', ['ormawa', 'bauak', 'warek3', 'admin', 'dosen', 'dekan', 'rektor', 'pp'])
                  ->change();
        });
    }
};
