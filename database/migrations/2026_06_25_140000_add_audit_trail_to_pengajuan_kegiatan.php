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
        Schema::table('pengajuan_kegiatan', function (Blueprint $table) {
            // Add audit trail for who created the pengajuan
            $table->foreignId('created_by_user_id')
                ->after('ormawa_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Optional: track who last updated the pengajuan
            $table->foreignId('updated_by_user_id')
                ->after('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Index for audit queries
            $table->index('created_by_user_id');
            $table->index('updated_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuan_kegiatan', function (Blueprint $table) {
            // Drop foreign keys by name
            $table->dropForeign('pengajuan_kegiatan_created_by_user_id_foreign');
            $table->dropForeign('pengajuan_kegiatan_updated_by_user_id_foreign');
            // Drop columns
            $table->dropColumn('created_by_user_id');
            $table->dropColumn('updated_by_user_id');
        });
    }
};
