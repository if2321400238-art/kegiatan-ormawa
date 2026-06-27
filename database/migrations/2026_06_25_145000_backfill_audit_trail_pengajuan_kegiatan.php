<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Backfill created_by_user_id for existing pengajuan based on ormawa.user_id
     * This assumes that in the old model, the ormawa account owner created the pengajuan
     *
     * Strategy:
     * 1. For each pengajuan, find its associated ormawa
     * 2. Use ormawa.user_id as the created_by_user_id (the historic owner)
     * 3. Leave updated_by_user_id as NULL (not available from history)
     */
    public function up(): void
    {
        DB::statement('
            UPDATE pengajuan_kegiatan
            SET created_by_user_id = (
                SELECT ormawa.user_id
                FROM ormawa
                WHERE ormawa.id = pengajuan_kegiatan.ormawa_id
                LIMIT 1
            )
            WHERE created_by_user_id IS NULL
            AND ormawa_id IS NOT NULL
        ');

        // Log the result
        $updated = DB::selectOne('
            SELECT COUNT(*) as count
            FROM pengajuan_kegiatan
            WHERE created_by_user_id IS NOT NULL
        ')->count;

        echo "Backfilled {$updated} pengajuan records with created_by_user_id\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Clear backfilled created_by_user_id values
        // Note: This only clears values that were backfilled during this migration
        // If new pengajuan were created and updated manually, those will remain
        DB::statement('
            UPDATE pengajuan_kegiatan pk
            SET created_by_user_id = NULL
            WHERE created_by_user_id IN (
                SELECT ormawa.user_id
                FROM ormawa
                WHERE ormawa.id = pk.ormawa_id
            )
        ');

        echo "Rolled back audit trail backfill\n";
    }
};
