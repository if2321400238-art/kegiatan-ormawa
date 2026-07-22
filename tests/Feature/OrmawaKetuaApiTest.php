<?php

use App\Models\AnggotaOrmawa;
use App\Models\Ormawa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('allows admin to search and replace an ormawa ketua from the UNUJA API', function () {
    config([
        'cache.default' => 'array',
        'services.unuja.base_url' => 'https://v2-api.unuja.ac.id',
        'services.unuja.login_url' => 'https://v2-api.unuja.ac.id/log/masuk',
        'services.unuja.username' => 'test-user',
        'services.unuja.password' => 'test-secret',
    ]);
    Cache::flush();

    Http::fake([
        'https://v2-api.unuja.ac.id/log/masuk' => Http::response([
            'unujasimptapikey' => 'fake-api-key',
        ]),
        'https://v2-api.unuja.ac.id/mst/mahasiswa/cari/b/nama/p/Nadia' => Http::response([
            'data' => [[
                'nim' => '22010077',
                'nama' => 'Nadia Rahma',
                'email' => '22010077@student.unuja.ac.id',
                'nama_prodi' => 'Informatika',
            ]],
        ]),
        'https://v2-api.unuja.ac.id/mst/mahasiswa/cari/b/nim/p/22010077' => Http::response([
            'data' => [[
                'nim' => '22010077',
                'nama' => 'Nadia Rahma',
                'email' => '22010077@student.unuja.ac.id',
                'nama_prodi' => 'Informatika',
            ]],
        ]),
    ]);

    $admin = User::factory()->create(['role' => 'admin']);
    $oldKetua = User::factory()->create(['role' => 'ormawa']);
    $ormawa = Ormawa::create([
        'user_id' => $oldKetua->id,
        'nama_ormawa' => 'UKM Pengujian',
        'ketua' => $oldKetua->nama,
        'kategori_organisasi' => 'internal',
        'tingkat_organisasi' => 'universitas',
        'kontak' => '08123456789',
    ]);
    AnggotaOrmawa::create([
        'ormawa_id' => $ormawa->id,
        'user_id' => $oldKetua->id,
        'jabatan' => 'ketua',
        'status' => true,
    ]);

    $this->actingAs($admin)
        ->getJson(route('admin.ormawa.search-mahasiswa', ['q' => 'Nadia']))
        ->assertOk()
        ->assertJsonPath('0.nim', '22010077')
        ->assertJsonPath('0.nama', 'Nadia Rahma');

    $this->patch(route('admin.ormawa.update', $ormawa), [
        'nama_ormawa' => $ormawa->nama_ormawa,
        'ketua_nim' => '22010077',
        'kategori_organisasi' => 'internal',
        'tingkat_organisasi' => 'universitas',
        'kontak' => $ormawa->kontak,
    ])->assertRedirect(route('admin.ormawa.index'));

    $newKetua = User::where('nim', '22010077')->firstOrFail();
    $ormawa->refresh();

    expect($ormawa->user_id)->toBe($newKetua->id)
        ->and($ormawa->ketua)->toBe('Nadia Rahma');

    $this->assertDatabaseHas('anggota_ormawa', [
        'ormawa_id' => $ormawa->id,
        'user_id' => $newKetua->id,
        'jabatan' => 'ketua',
    ]);
    $this->assertDatabaseMissing('anggota_ormawa', [
        'ormawa_id' => $ormawa->id,
        'user_id' => $oldKetua->id,
        'jabatan' => 'ketua',
    ]);
});
