<?php

use App\Models\Ormawa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

describe('Ormawa anggota management', function () {
    beforeEach(function () {
        config([
            'cache.default' => 'array',
            'services.unuja.base_url' => 'https://v2-api.unuja.ac.id',
            'services.unuja.login_url' => 'https://v2-api.unuja.ac.id/log/masuk',
            'services.unuja.username' => 'test-user',
            'services.unuja.password' => 'test-secret',
        ]);
        Cache::flush();
    });

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

    it('searches and adds a student from the UNUJA API by NIM', function () {
        $ketua = User::factory()->create([
            'role' => 'mahasiswa',
            'nim' => '10000001',
        ]);
        $ormawa = Ormawa::create([
            'user_id' => $ketua->id,
            'nama_ormawa' => 'UKM API',
            'ketua' => $ketua->nama,
            'kategori_organisasi' => 'internal',
            'tingkat_organisasi' => 'fakultas',
            'kontak' => '0812',
        ]);

        Http::fake([
            'https://v2-api.unuja.ac.id/log/masuk' => Http::response([
                'unujasimptapikey' => 'fake-api-key',
            ]),
            'https://v2-api.unuja.ac.id/mst/mahasiswa/cari/b/nim/p/22010001' => Http::response([
                'data' => [[
                    'nim' => '22010001',
                    'nama' => 'Mahasiswa Dari API',
                    'email' => '22010001@student.unuja.ac.id',
                    'nama_prodi' => 'Informatika',
                ]],
            ]),
        ]);

        $this->actingAs($ketua)
            ->getJson(route('ormawa.anggota.search', $ormawa).'?search=22010001&cariby=nim')
            ->assertOk()
            ->assertJsonPath('data.0.nim', '22010001')
            ->assertJsonPath('data.0.program_studi', 'Informatika');

        $this->post(route('ormawa.anggota.store', $ormawa), [
            'nim' => '22010001',
            'jabatan' => 'anggota',
            'status' => true,
        ])->assertRedirect(route('ormawa.anggota.index', $ormawa));

        $student = User::where('nim', '22010001')->firstOrFail();
        expect($student->nama)->toBe('Mahasiswa Dari API')
            ->and($student->role)->toBe('mahasiswa')
            ->and(Hash::check('22010001', $student->password))->toBeTrue();

        $this->assertDatabaseHas('anggota_ormawa', [
            'ormawa_id' => $ormawa->id,
            'user_id' => $student->id,
            'jabatan' => 'anggota',
        ]);

        Http::assertSent(fn ($request) => $request->url() === 'https://v2-api.unuja.ac.id/log/masuk'
            && $request['nama_pengguna'] === 'test-user');
        Http::assertSent(fn ($request) => $request->hasHeader('unujasimptapikey', 'fake-api-key'));
    });
});
