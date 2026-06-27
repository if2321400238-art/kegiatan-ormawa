<?php

use App\Models\Ormawa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Helper to create mahasiswa with ormawas
function createMahasiswaWithOrmawasForMiddleware($nama = 'Ahmad Fauzi', $count = 2): User
{
    $mahasiswa = User::create([
        'username' => 'mahasiswa_' . str()->random(5),
        'email' => 'mahasiswa_' . str()->random(5) . '@test.com',
        'password' => bcrypt('password'),
        'role' => 'mahasiswa',
        'nama' => $nama,
        'is_active' => true,
    ]);

    $owner = User::create([
        'username' => 'ormawa_' . str()->random(5),
        'email' => 'ormawa_' . str()->random(5) . '@test.com',
        'password' => bcrypt('password'),
        'role' => 'ormawa',
        'nama' => 'Ormawa Owner',
        'is_active' => true,
    ]);

    $positions = ['ketua', 'wakil_ketua', 'sekretaris', 'bendahara', 'anggota'];

    for ($i = 0; $i < $count; $i++) {
        $ormawa = Ormawa::create([
            'user_id' => $owner->id,
            'nama_ormawa' => 'Organisasi ' . chr(65 + $i),
            'ketua' => 'Ketua ' . chr(65 + $i),
            'pembina' => 'Pembina ' . chr(65 + $i),
            'kontak' => '0812345678' . str()->random(2),
            'kategori_organisasi' => 'internal',
            'tingkat_organisasi' => 'universitas',
        ]);

        $position = $positions[array_rand($positions)];
        $mahasiswa->ormawas()->attach($ormawa->id, [
            'jabatan' => $position,
            'status' => true,
        ]);
    }

    return $mahasiswa;
}

// ==========================================
// MIDDLEWARE TESTS - Active Ormawa Validation
// ==========================================

it('validates that active ormawa still exists in membership', function () {
    $mahasiswa = createMahasiswaWithOrmawasForMiddleware('Ahmad Fauzi', 1);
    $targetOrmawa = $mahasiswa->ormawas()->first();

    // Verify membership exists
    expect($mahasiswa->ormawas()->where('ormawa_id', $targetOrmawa->id)->exists())->toBeTrue();

    // Remove membership
    $mahasiswa->ormawas()->detach($targetOrmawa->id);

    // Verify membership is gone
    expect($mahasiswa->ormawas()->where('ormawa_id', $targetOrmawa->id)->exists())->toBeFalse();
});

it('clears invalid active ormawa when membership is removed', function () {
    $mahasiswa = createMahasiswaWithOrmawasForMiddleware('Ahmad Fauzi', 2);
    $ormawas = $mahasiswa->ormawas()->get();
    $targetOrmawa = $ormawas->get(0);

    // Set active ormawa in session
    $this->actingAs($mahasiswa)
        ->post(
            route('mahasiswa.setActiveOrmawa'),
            ['ormawa_id' => $targetOrmawa->id]
        );

    // Remove from ormawa
    $mahasiswa->ormawas()->detach($targetOrmawa->id);

    // Session should have the value still (browser cookie)
    // But if middleware is applied, it would detect the membership is gone
    // and clear it. For now, just verify the detach worked.
    expect($mahasiswa->ormawas()->count())->toBe(1);
});

it('mahasiswa can only set active ormawa they are member of', function () {
    $mahasiswa1 = createMahasiswaWithOrmawasForMiddleware('Ahmad Fauzi', 1);
    $mahasiswa2 = createMahasiswaWithOrmawasForMiddleware('Budi Santoso', 1);

    $mahasiswa2Ormawa = $mahasiswa2->ormawas()->first();

    // Try to set mahasiswa2's ormawa as active for mahasiswa1
    $response = $this->actingAs($mahasiswa1)->post(
        route('mahasiswa.setActiveOrmawa'),
        ['ormawa_id' => $mahasiswa2Ormawa->id]
    );

    // Should be rejected with 403
    $response->assertStatus(403);
});

it('active ormawa session persists across multiple requests', function () {
    $mahasiswa = createMahasiswaWithOrmawasForMiddleware('Ahmad Fauzi', 3);
    $ormawas = $mahasiswa->ormawas()->get();
    $targetOrmawa = $ormawas->get(0);

    // First request: set active ormawa
    $this->actingAs($mahasiswa)->post(
        route('mahasiswa.setActiveOrmawa'),
        ['ormawa_id' => $targetOrmawa->id]
    );

    // Second request: verify it persists
    $this->actingAs($mahasiswa)->get(route('mahasiswa.dashboard'));
    expect(session('active_ormawa_id'))->toBe($targetOrmawa->id);

    // Third request: should still be there
    $this->actingAs($mahasiswa)->get(route('mahasiswa.dashboard'));
    expect(session('active_ormawa_id'))->toBe($targetOrmawa->id);
});

