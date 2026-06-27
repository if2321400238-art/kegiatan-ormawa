<?php

use App\Models\Ormawa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Ormawa anggota management', function () {
    it('identifies the ketua through the organization model helper', function () {
        $ketua = User::factory()->create([
            'role' => 'mahasiswa',
            'nama' => 'Ketua Organisasi',
            'is_active' => true,
        ]);

        $otherUser = User::factory()->create([
            'role' => 'mahasiswa',
            'nama' => 'Bukan Ketua',
            'is_active' => true,
        ]);

        $ormawa = Ormawa::create([
            'user_id' => $ketua->id,
            'nama_ormawa' => 'UKM Testing',
            'ketua' => $ketua->nama,
            'kategori_organisasi' => 'internal',
            'tingkat_organisasi' => 'fakultas',
            'kontak' => '0812',
        ]);

        expect($ormawa->isKetua($ketua))->toBeTrue();
        expect($ormawa->isKetua($otherUser))->toBeFalse();
    });

    it('allows the ketua to manage anggota through the simplified membership flow', function () {
        $ketua = User::factory()->create([
            'role' => 'mahasiswa',
            'nama' => 'Ketua Organisasi',
            'is_active' => true,
        ]);

        $anggotaBaru = User::factory()->create([
            'role' => 'mahasiswa',
            'nama' => 'Anggota Baru',
            'is_active' => true,
        ]);

        $ormawa = Ormawa::create([
            'user_id' => $ketua->id,
            'nama_ormawa' => 'UKM Testing',
            'ketua' => $ketua->nama,
            'kategori_organisasi' => 'internal',
            'tingkat_organisasi' => 'fakultas',
            'kontak' => '0812',
        ]);

        $this->actingAs($ketua);

        $this->get(route('ormawa.anggota.index', $ormawa))
            ->assertOk();

        $this->post(route('ormawa.anggota.store', $ormawa), [
            'user_id' => $anggotaBaru->id,
            'jabatan' => 'anggota',
            'status' => true,
        ])->assertRedirect(route('ormawa.anggota.index', $ormawa));

        $this->assertDatabaseHas('anggota_ormawa', [
            'ormawa_id' => $ormawa->id,
            'user_id' => $anggotaBaru->id,
            'jabatan' => 'anggota',
            'status' => true,
        ]);
    });
});
