<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ormawa', function (Blueprint $table) {
            $column = $table->unsignedBigInteger('pembina_user_id')->nullable();
            $column->after(Schema::hasColumn('ormawa', 'pembina') ? 'pembina' : 'ketua');
            $table->foreign('pembina_user_id')->references('id')->on('users')->nullOnDelete();
        });

        // Backfill pembina_user_id by matching user.nama with ormawa.pembina
        if (! Schema::hasColumn('ormawa', 'pembina')) {
            return;
        }

        $ormawas = DB::table('ormawa')->get();
        foreach ($ormawas as $o) {
            if (empty($o->pembina)) {
                continue;
            }
            $user = DB::table('users')->where('nama', $o->pembina)->first();
            if ($user) {
                DB::table('ormawa')->where('id', $o->id)->update(['pembina_user_id' => $user->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ormawa', function (Blueprint $table) {
            $table->dropForeign(['pembina_user_id']);
            $table->dropColumn('pembina_user_id');
        });
    }
};
