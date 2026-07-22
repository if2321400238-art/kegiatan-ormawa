<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ormawa', function (Blueprint $table) {
            if (! Schema::hasColumn('ormawa', 'kategori_organisasi')) {
                $column = $table->enum('kategori_organisasi', ['internal', 'eksternal'])->default('internal');
                $column->after(Schema::hasColumn('ormawa', 'pembina') ? 'pembina' : 'ketua');
            }

            if (! Schema::hasColumn('ormawa', 'tingkat_organisasi')) {
                $table->enum('tingkat_organisasi', ['universitas', 'fakultas', 'prodi'])->nullable()->after('kategori_organisasi');
            }

            if (! Schema::hasColumn('ormawa', 'fakultas_id')) {
                $table->unsignedBigInteger('fakultas_id')->nullable()->after('tingkat_organisasi');
                $table->index('fakultas_id');
            }
        });

        if (Schema::hasColumn('ormawa', 'jenis_ormawa')) {
            DB::table('ormawa')
                ->where('jenis_ormawa', 'eksternal')
                ->update(['kategori_organisasi' => 'eksternal', 'tingkat_organisasi' => null]);

            DB::table('ormawa')
                ->where('jenis_ormawa', 'universitas')
                ->update(['kategori_organisasi' => 'internal', 'tingkat_organisasi' => 'universitas']);

            DB::table('ormawa')
                ->where('jenis_ormawa', 'fakultas')
                ->update(['kategori_organisasi' => 'internal', 'tingkat_organisasi' => 'fakultas']);

            Schema::table('ormawa', function (Blueprint $table) {
                $table->dropColumn('jenis_ormawa');
            });
        }
    }

    public function down(): void
    {
        Schema::table('ormawa', function (Blueprint $table) {
            if (! Schema::hasColumn('ormawa', 'jenis_ormawa')) {
                $table->enum('jenis_ormawa', ['fakultas', 'universitas', 'eksternal'])->default('fakultas')->after('pembina');
            }
        });

        if (Schema::hasColumn('ormawa', 'kategori_organisasi')) {
            DB::table('ormawa')
                ->where('kategori_organisasi', 'eksternal')
                ->update(['jenis_ormawa' => 'eksternal']);

            DB::table('ormawa')
                ->where('kategori_organisasi', 'internal')
                ->where('tingkat_organisasi', 'universitas')
                ->update(['jenis_ormawa' => 'universitas']);

            DB::table('ormawa')
                ->where('kategori_organisasi', 'internal')
                ->where('tingkat_organisasi', 'fakultas')
                ->update(['jenis_ormawa' => 'fakultas']);

            Schema::table('ormawa', function (Blueprint $table) {
                $table->dropColumn(['kategori_organisasi', 'tingkat_organisasi', 'fakultas_id']);
            });
        }
    }
};
