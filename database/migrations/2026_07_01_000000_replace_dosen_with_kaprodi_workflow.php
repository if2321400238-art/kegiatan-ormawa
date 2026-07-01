<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['ormawa', 'mahasiswa', 'bauak', 'warek3', 'admin', 'dosen', 'kaprodi', 'dekan', 'rektor', 'pp'])->change();
        });
        Schema::table('ormawa', function (Blueprint $table) {
            $table->enum('tingkat_organisasi', ['universitas', 'fakultas', 'prodi'])->nullable()->change();
        });
        $statuses = ['draft', 'menunggu_dosen', 'revisi_dosen', 'ditolak_dosen', 'menunggu_kaprodi', 'revisi_kaprodi', 'ditolak_kaprodi', 'menunggu_dekan', 'revisi_dekan', 'ditolak_dekan', 'menunggu_bauak', 'revisi_bauak', 'ditolak_bauak', 'menunggu_warek3', 'revisi_warek3', 'ditolak_warek3', 'menunggu_rektor', 'revisi_rektor', 'ditolak_rektor', 'menunggu_pp', 'ditolak_pp', 'disetujui', 'selesai'];
        Schema::table('pengajuan_kegiatan', function (Blueprint $table) use ($statuses) {
            $table->enum('status', $statuses)->default('draft')->change();
        });
        Schema::table('ormawa', function (Blueprint $table) {
            $table->string('program_studi')->nullable()->after('fakultas_id');
        });

        Schema::create('persetujuan_kaprodi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengajuan_id')->constrained('pengajuan_kegiatan')->cascadeOnDelete();
            $table->foreignId('user_kaprodi_id')->constrained('users')->cascadeOnDelete();
            $table->text('catatan')->nullable();
            $table->string('status');
            $table->timestamp('tanggal_acc');
            $table->timestamps();
        });

        DB::table('pengajuan_kegiatan')->where('status', 'menunggu_dosen')->update(['status' => 'menunggu_bauak']);
        DB::table('pengajuan_kegiatan')->where('status', 'revisi_dosen')->update(['status' => 'revisi_bauak']);
        DB::table('pengajuan_kegiatan')->where('status', 'ditolak_dosen')->update(['status' => 'ditolak_bauak']);
        DB::table('users')->where('role', 'dosen')->update(['role' => 'kaprodi']);
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['ormawa', 'mahasiswa', 'bauak', 'warek3', 'admin', 'kaprodi', 'dekan', 'rektor', 'pp'])->change();
        });

        Schema::dropIfExists('verifikasi_dosen');
        Schema::table('ormawa', function (Blueprint $table) {
            if (Schema::hasColumn('ormawa', 'pembina_user_id')) $table->dropForeign(['pembina_user_id']);
            $columns = array_values(array_filter(['pembina', 'pembina_user_id'], fn ($column) => Schema::hasColumn('ormawa', $column)));
            if ($columns) $table->dropColumn($columns);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persetujuan_kaprodi');
        Schema::table('ormawa', function (Blueprint $table) {
            $table->string('pembina')->nullable();
            $table->foreignId('pembina_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dropColumn('program_studi');
        });
    }
};
