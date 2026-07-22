<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('group');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('group');
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->primary(['role_id', 'user_id']);
            $table->index('user_id');
        });

        $now = now();
        $roles = [
            ['name' => 'Administrator', 'slug' => 'admin', 'description' => 'Akses penuh ke seluruh sistem dan pengaturan RBAC.'],
            ['name' => 'Ormawa', 'slug' => 'ormawa', 'description' => 'Pengelola organisasi mahasiswa dan pengajuan kegiatan.'],
            ['name' => 'Mahasiswa', 'slug' => 'mahasiswa', 'description' => 'Mahasiswa anggota ormawa yang dapat membuat pengajuan.'],
            ['name' => 'BAUAK', 'slug' => 'bauak', 'description' => 'Verifikator administrasi dan LPJ.'],
            ['name' => 'Kepala Program Studi', 'slug' => 'kaprodi', 'description' => 'Pemberi persetujuan tingkat program studi.'],
            ['name' => 'Dekan', 'slug' => 'dekan', 'description' => 'Pemberi persetujuan tingkat fakultas.'],
            ['name' => 'Wakil Rektor III', 'slug' => 'warek3', 'description' => 'Pemberi persetujuan bidang kemahasiswaan.'],
            ['name' => 'Rektor', 'slug' => 'rektor', 'description' => 'Pemberi persetujuan akhir tingkat rektorat.'],
            ['name' => 'Kepala/Wakil PP', 'slug' => 'pp', 'description' => 'Pemberi persetujuan PP.'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insertOrIgnore($role + [
                'is_system' => true,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permissions = [
            ['group' => 'Dashboard', 'name' => 'Lihat dashboard', 'slug' => 'dashboard.view'],
            ['group' => 'Pengajuan', 'name' => 'Lihat pengajuan', 'slug' => 'pengajuan.view'],
            ['group' => 'Pengajuan', 'name' => 'Buat pengajuan', 'slug' => 'pengajuan.create'],
            ['group' => 'Pengajuan', 'name' => 'Ubah pengajuan', 'slug' => 'pengajuan.update'],
            ['group' => 'Pengajuan', 'name' => 'Hapus pengajuan', 'slug' => 'pengajuan.delete'],
            ['group' => 'Pengajuan', 'name' => 'Ekspor pengajuan', 'slug' => 'pengajuan.export'],
            ['group' => 'Proposal', 'name' => 'Kelola proposal kegiatan', 'slug' => 'proposal.manage'],
            ['group' => 'LPJ', 'name' => 'Lihat LPJ', 'slug' => 'lpj.view'],
            ['group' => 'LPJ', 'name' => 'Buat LPJ', 'slug' => 'lpj.create'],
            ['group' => 'LPJ', 'name' => 'Ubah LPJ', 'slug' => 'lpj.update'],
            ['group' => 'LPJ', 'name' => 'Verifikasi LPJ', 'slug' => 'lpj.verify'],
            ['group' => 'Persetujuan', 'name' => 'Persetujuan BAUAK', 'slug' => 'approval.bauak'],
            ['group' => 'Persetujuan', 'name' => 'Persetujuan Kaprodi', 'slug' => 'approval.kaprodi'],
            ['group' => 'Persetujuan', 'name' => 'Persetujuan Dekan', 'slug' => 'approval.dekan'],
            ['group' => 'Persetujuan', 'name' => 'Persetujuan Warek III', 'slug' => 'approval.warek3'],
            ['group' => 'Persetujuan', 'name' => 'Persetujuan Rektor', 'slug' => 'approval.rektor'],
            ['group' => 'Persetujuan', 'name' => 'Persetujuan PP', 'slug' => 'approval.pp'],
            ['group' => 'Ormawa', 'name' => 'Kelola ormawa', 'slug' => 'ormawa.manage'],
            ['group' => 'Mahasiswa', 'name' => 'Kelola mahasiswa', 'slug' => 'mahasiswa.manage'],
            ['group' => 'Akademik', 'name' => 'Kelola fakultas/prodi/dekan/kaprodi', 'slug' => 'akademik.manage'],
            ['group' => 'Laporan', 'name' => 'Lihat laporan', 'slug' => 'laporan.view'],
            ['group' => 'Notifikasi', 'name' => 'Kelola notifikasi', 'slug' => 'notifikasi.manage'],
            ['group' => 'RBAC', 'name' => 'Kelola role dan permission', 'slug' => 'rbac.manage'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insertOrIgnore($permission + [
                'description' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $permissionIds = DB::table('permissions')->pluck('id', 'slug');
        $roleIds = DB::table('roles')->pluck('id', 'slug');
        $rolePermissions = [
            'admin' => $permissionIds->keys()->all(),
            'ormawa' => ['dashboard.view', 'pengajuan.view', 'pengajuan.create', 'pengajuan.update', 'proposal.manage', 'lpj.view', 'lpj.create', 'lpj.update', 'notifikasi.manage'],
            'mahasiswa' => ['dashboard.view', 'pengajuan.view', 'pengajuan.create', 'pengajuan.update', 'lpj.view', 'lpj.create', 'lpj.update', 'notifikasi.manage'],
            'bauak' => ['dashboard.view', 'pengajuan.view', 'pengajuan.export', 'approval.bauak', 'lpj.view', 'lpj.verify', 'ormawa.manage', 'laporan.view', 'notifikasi.manage'],
            'kaprodi' => ['dashboard.view', 'pengajuan.view', 'approval.kaprodi', 'lpj.view', 'notifikasi.manage'],
            'dekan' => ['dashboard.view', 'pengajuan.view', 'approval.dekan', 'lpj.view', 'notifikasi.manage'],
            'warek3' => ['dashboard.view', 'pengajuan.view', 'approval.warek3', 'lpj.view', 'laporan.view', 'notifikasi.manage'],
            'rektor' => ['dashboard.view', 'pengajuan.view', 'approval.rektor', 'lpj.view', 'laporan.view', 'notifikasi.manage'],
            'pp' => ['dashboard.view', 'pengajuan.view', 'approval.pp', 'lpj.view', 'notifikasi.manage'],
        ];

        foreach ($rolePermissions as $roleSlug => $slugs) {
            foreach ($slugs as $permissionSlug) {
                if (isset($roleIds[$roleSlug], $permissionIds[$permissionSlug])) {
                    DB::table('permission_role')->insertOrIgnore([
                        'role_id' => $roleIds[$roleSlug],
                        'permission_id' => $permissionIds[$permissionSlug],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        DB::table('users')->select(['id', 'role'])->orderBy('id')->chunk(100, function ($users) use ($roleIds, $now) {
            foreach ($users as $user) {
                if (isset($roleIds[$user->role])) {
                    DB::table('role_user')->insertOrIgnore([
                        'role_id' => $roleIds[$user->role],
                        'user_id' => $user->id,
                        'assigned_by' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
