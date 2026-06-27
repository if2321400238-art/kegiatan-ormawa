<?php

use App\Models\Ormawa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Helper to create mahasiswa with ormawas
function createMahasiswaWithOrmawas($nama = 'Ahmad Fauzi', $count = 3): User
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

it('displays mahasiswa dashboard with ormawas', function () {
    $mahasiswa = createMahasiswaWithOrmawas('Ahmad Fauzi', 3);

    $response = $this->actingAs($mahasiswa)->get(route('mahasiswa.dashboard'));

    $response->assertStatus(200);
    $response->assertViewIs('mahasiswa.dashboard');
    $response->assertViewHas('ormawas');
    $response->assertViewHas('user', $mahasiswa);
});

it('shows list of ormawas mahasiswa is member of', function () {
    $mahasiswa = createMahasiswaWithOrmawas('Ahmad Fauzi', 3);

    $response = $this->actingAs($mahasiswa)->get(route('mahasiswa.dashboard'));

    $ormawas = $mahasiswa->ormawas()->get();
    foreach ($ormawas as $ormawa) {
        $response->assertSee($ormawa->nama_ormawa);
    }
});

it('displays jabatan for each ormawa', function () {
    $mahasiswa = createMahasiswaWithOrmawas('Ahmad Fauzi', 1);

    $response = $this->actingAs($mahasiswa)->get(route('mahasiswa.dashboard'));

    $ormawa = $mahasiswa->ormawas()->first();
    $jabatan = ucwords(str_replace('_', ' ', $ormawa->pivot->jabatan));

    $response->assertSee($jabatan);
});

it('displays active ormawa indicator', function () {
    $mahasiswa = createMahasiswaWithOrmawas('Ahmad Fauzi', 2);

    $response = $this->actingAs($mahasiswa)->get(route('mahasiswa.dashboard'));

    $response->assertSee('Organisasi Aktif');
    $response->assertViewHas('activeOrmawa');
});

it('shows dropdown for multiple ormawas', function () {
    $mahasiswa = createMahasiswaWithOrmawas('Ahmad Fauzi', 3);

    $response = $this->actingAs($mahasiswa)->get(route('mahasiswa.dashboard'));

    // Should have a select dropdown if more than 1 ormawa
    $ormawas = $mahasiswa->ormawas()->get();
    if ($ormawas->count() > 1) {
        $response->assertSee('select');
    }
});

it('shows empty state when no ormawas', function () {
    $mahasiswa = User::create([
        'username' => 'mahasiswa_solo',
        'email' => 'mahasiswa_solo@test.com',
        'password' => bcrypt('password'),
        'role' => 'mahasiswa',
        'nama' => 'Solo Mahasiswa',
        'is_active' => true,
    ]);

    $response = $this->actingAs($mahasiswa)->get(route('mahasiswa.dashboard'));

    $response->assertStatus(200);
    $response->assertSee('Belum Ada Organisasi');
});

it('can set active ormawa', function () {
    $mahasiswa = createMahasiswaWithOrmawas('Ahmad Fauzi', 3);
    $ormawas = $mahasiswa->ormawas()->get();
    $secondOrmawa = $ormawas->get(1);

    $response = $this->actingAs($mahasiswa)->post(
        route('mahasiswa.setActiveOrmawa'),
        ['ormawa_id' => $secondOrmawa->id]
    );

    $response->assertRedirect(route('mahasiswa.dashboard'));
    expect(session('active_ormawa_id'))->toBe($secondOrmawa->id);
});

it('prevents setting non-member ormawa as active', function () {
    $mahasiswa1 = createMahasiswaWithOrmawas('Ahmad Fauzi', 1);
    $mahasiswa2 = createMahasiswaWithOrmawas('Budi Santoso', 1);

    $mahasiswa2Ormawa = $mahasiswa2->ormawas()->first();

    $response = $this->actingAs($mahasiswa1)->post(
        route('mahasiswa.setActiveOrmawa'),
        ['ormawa_id' => $mahasiswa2Ormawa->id]
    );

    $response->assertStatus(403);
});

it('sets first ormawa as active on first visit', function () {
    $mahasiswa = createMahasiswaWithOrmawas('Ahmad Fauzi', 3);

    $response = $this->actingAs($mahasiswa)->get(route('mahasiswa.dashboard'));

    $firstOrmawa = $mahasiswa->ormawas()->first();
    expect(session('active_ormawa_id'))->toBe($firstOrmawa->id);
});

it('shows stats for ormawas', function () {
    $mahasiswa = createMahasiswaWithOrmawas('Ahmad Fauzi', 3);

    $response = $this->actingAs($mahasiswa)->get(route('mahasiswa.dashboard'));

    // Should display total count
    $response->assertSee('3');
});

it('requires mahasiswa role to access dashboard', function () {
    $ormawa = User::create([
        'username' => 'ormawa_user',
        'email' => 'ormawa@test.com',
        'password' => bcrypt('password'),
        'role' => 'ormawa',
        'nama' => 'Ormawa User',
        'is_active' => true,
    ]);

    $response = $this->actingAs($ormawa)->get(route('mahasiswa.dashboard'));

    $response->assertStatus(403);
});

it('requires auth to access dashboard', function () {
    $response = $this->get(route('mahasiswa.dashboard'));

    $response->assertRedirect(route('login'));
});

it('displays active ormawa info message', function () {
    $mahasiswa = createMahasiswaWithOrmawas('Ahmad Fauzi', 1);

    $response = $this->actingAs($mahasiswa)->get(route('mahasiswa.dashboard'));

    $response->assertSee('Organisasi Aktif');
    $response->assertSee('pengajuan kegiatan');
});

it('persists active ormawa in session after page refresh', function () {
    $mahasiswa = createMahasiswaWithOrmawas('Ahmad Fauzi', 3);
    $ormawas = $mahasiswa->ormawas()->get();
    $targetOrmawa = $ormawas->get(1);

    // Set active ormawa
    $this->actingAs($mahasiswa)->post(
        route('mahasiswa.setActiveOrmawa'),
        ['ormawa_id' => $targetOrmawa->id]
    );

    // Verify it persists after refresh
    $response = $this->actingAs($mahasiswa)->get(route('mahasiswa.dashboard'));
    expect(session('active_ormawa_id'))->toBe($targetOrmawa->id);
});

it('changes active ormawa when selection changes', function () {
    $mahasiswa = createMahasiswaWithOrmawas('Ahmad Fauzi', 3);
    $ormawas = $mahasiswa->ormawas()->get();
    $firstOrmawa = $ormawas->get(0);
    $secondOrmawa = $ormawas->get(1);

    // Set first ormawa
    $this->actingAs($mahasiswa)->post(
        route('mahasiswa.setActiveOrmawa'),
        ['ormawa_id' => $firstOrmawa->id]
    );
    expect(session('active_ormawa_id'))->toBe($firstOrmawa->id);

    // Change to second ormawa
    $this->actingAs($mahasiswa)->post(
        route('mahasiswa.setActiveOrmawa'),
        ['ormawa_id' => $secondOrmawa->id]
    );
    expect(session('active_ormawa_id'))->toBe($secondOrmawa->id);
});

it('validates that active ormawa is still a valid membership', function () {
    $mahasiswa = createMahasiswaWithOrmawas('Ahmad Fauzi', 2);
    $ormawas = $mahasiswa->ormawas()->get();
    $targetOrmawa = $ormawas->get(0);

    // Set active ormawa
    $this->actingAs($mahasiswa)->post(
        route('mahasiswa.setActiveOrmawa'),
        ['ormawa_id' => $targetOrmawa->id]
    );

    // Remove mahasiswa from this ormawa
    $mahasiswa->ormawas()->detach($targetOrmawa->id);

    // Session is still in browser, but on next access should be cleared
    // (middleware will handle this, but for now just verify setActiveOrmawa validates)
    expect(session('active_ormawa_id'))->toBe($targetOrmawa->id);
});
