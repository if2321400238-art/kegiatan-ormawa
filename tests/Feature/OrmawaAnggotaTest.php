<?php

use App\Models\Ormawa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Helper to create ormawa with owner
function createOrmawaWithOwner($name = 'Test Organization'): Ormawa
{
    $owner = User::create([
        'username' => 'ormawa_' . str()->random(5),
        'email' => 'ormawa_' . str()->random(5) . '@test.com',
        'password' => bcrypt('password'),
        'role' => 'ormawa',
        'nama' => 'Ormawa Owner',
        'is_active' => true,
    ]);

    return Ormawa::create([
        'user_id' => $owner->id,
        'nama_ormawa' => $name,
        'ketua' => 'Test Ketua',
        'pembina' => 'Test Pembina',
        'kontak' => '081234567890',
        'kategori_organisasi' => 'internal',
        'tingkat_organisasi' => 'universitas',
    ]);
}

it('displays anggota list page', function () {
    $admin = User::create([
        'username' => 'admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'nama' => 'Admin Test',
        'is_active' => true,
    ]);

    $ormawa = createOrmawaWithOwner();

    $response = $this->actingAs($admin)->get(
        route('admin.ormawa.anggota.index', $ormawa->id)
    );

    $response->assertStatus(200);
    $response->assertViewIs('ormawa.anggota.index');
});

it('lists all anggota in ormawa', function () {
    $admin = User::create([
        'username' => 'admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'nama' => 'Admin Test',
        'is_active' => true,
    ]);

    $ormawa = createOrmawaWithOwner();

    $member1 = User::create([
        'username' => 'member1',
        'email' => 'member1@test.com',
        'password' => bcrypt('password'),
        'role' => 'ormawa',
        'nama' => 'Member 1',
        'is_active' => true,
    ]);

    $member2 = User::create([
        'username' => 'member2',
        'email' => 'member2@test.com',
        'password' => bcrypt('password'),
        'role' => 'ormawa',
        'nama' => 'Member 2',
        'is_active' => true,
    ]);

    $nonMember = User::create([
        'username' => 'nonmember',
        'email' => 'nonmember@test.com',
        'password' => bcrypt('password'),
        'role' => 'ormawa',
        'nama' => 'Non Member',
        'is_active' => true,
    ]);

    $ormawa->users()->attach($member1->id, [
        'jabatan' => 'ketua',
        'status' => true,
    ]);

    $ormawa->users()->attach($member2->id, [
        'jabatan' => 'anggota',
        'status' => true,
    ]);

    $response = $this->actingAs($admin)->get(
        route('admin.ormawa.anggota.index', $ormawa->id)
    );

    $response->assertStatus(200);
    $response->assertSee('Member 1');
    $response->assertSee('Member 2');
    $response->assertDontSee('Non Member');
});

it('displays create anggota form', function () {
    $admin = User::create([
        'username' => 'admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'nama' => 'Admin Test',
        'is_active' => true,
    ]);

    $ormawa = createOrmawaWithOwner();

    $response = $this->actingAs($admin)->get(
        route('admin.ormawa.anggota.create', $ormawa->id)
    );

    $response->assertStatus(200);
    $response->assertViewIs('ormawa.anggota.create');
});

it('can add anggota to ormawa', function () {
    $admin = User::create([
        'username' => 'admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'nama' => 'Admin Test',
        'is_active' => true,
    ]);

    $ormawa = createOrmawaWithOwner();

    $member = User::create([
        'username' => 'member',
        'email' => 'member@test.com',
        'password' => bcrypt('password'),
        'role' => 'ormawa',
        'nama' => 'Member',
        'is_active' => true,
    ]);

    $this->actingAs($admin)->post(
        route('admin.ormawa.anggota.store', $ormawa->id),
        [
            'user_id' => $member->id,
            'jabatan' => 'sekretaris',
            'status' => true,
        ]
    );

    expect(
        $ormawa->users()->where('user_id', $member->id)->exists()
    )->toBeTrue();

    $pivot = $ormawa->users()->where('user_id', $member->id)->first();
    expect($pivot->pivot->jabatan)->toBe('sekretaris');
});

it('cannot add duplicate anggota', function () {
    $admin = User::create([
        'username' => 'admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'nama' => 'Admin Test',
        'is_active' => true,
    ]);

    $ormawa = createOrmawaWithOwner();

    $member = User::create([
        'username' => 'member',
        'email' => 'member@test.com',
        'password' => bcrypt('password'),
        'role' => 'ormawa',
        'nama' => 'Member',
        'is_active' => true,
    ]);

    $ormawa->users()->attach($member->id, [
        'jabatan' => 'ketua',
        'status' => true,
    ]);

    $response = $this->actingAs($admin)->post(
        route('admin.ormawa.anggota.store', $ormawa->id),
        [
            'user_id' => $member->id,
            'jabatan' => 'anggota',
            'status' => true,
        ]
    );

    $response->assertSessionHasErrors();
});

it('can update anggota jabatan', function () {
    $admin = User::create([
        'username' => 'admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'nama' => 'Admin Test',
        'is_active' => true,
    ]);

    $ormawa = createOrmawaWithOwner();

    $member = User::create([
        'username' => 'member',
        'email' => 'member@test.com',
        'password' => bcrypt('password'),
        'role' => 'ormawa',
        'nama' => 'Member',
        'is_active' => true,
    ]);

    $ormawa->users()->attach($member->id, [
        'jabatan' => 'ketua',
        'status' => true,
    ]);

    $this->actingAs($admin)->patch(
        route('admin.ormawa.anggota.update', [$ormawa->id, $member->id]),
        [
            'jabatan' => 'bendahara',
            'status' => true,
        ]
    );

    $pivot = $ormawa->users()->where('user_id', $member->id)->first();
    expect($pivot->pivot->jabatan)->toBe('bendahara');
});

it('can remove anggota from ormawa', function () {
    $admin = User::create([
        'username' => 'admin',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'role' => 'admin',
        'nama' => 'Admin Test',
        'is_active' => true,
    ]);

    $ormawa = createOrmawaWithOwner();

    $member = User::create([
        'username' => 'member',
        'email' => 'member@test.com',
        'password' => bcrypt('password'),
        'role' => 'ormawa',
        'nama' => 'Member',
        'is_active' => true,
    ]);

    $ormawa->users()->attach($member->id, [
        'jabatan' => 'ketua',
        'status' => true,
    ]);

    expect(
        $ormawa->users()->where('user_id', $member->id)->exists()
    )->toBeTrue();

    $this->actingAs($admin)->delete(
        route('admin.ormawa.anggota.destroy', [$ormawa->id, $member->id])
    );

    expect(
        $ormawa->users()->where('user_id', $member->id)->exists()
    )->toBeFalse();
});

it('ketua can search mahasiswa through anggota search endpoint', function () {
    $ormawa = createOrmawaWithOwner();
    $ketua = User::find($ormawa->user_id);

    $candidate = User::create([
        'username' => 'candidate',
        'email' => 'candidate@test.com',
        'password' => bcrypt('password'),
        'role' => 'mahasiswa',
        'nama' => 'Candidate Student',
        'nim' => '220101001',
        'is_active' => true,
    ]);

    $this->actingAs($ketua)
        ->get(route('ormawa.anggota.search', ['ormawa' => $ormawa->id, 'search' => 'Candidate']))
        ->assertOk()
        ->assertJsonFragment(['id' => $candidate->id]);
});

it('search does not return mahasiswa who are already anggota', function () {
    $ormawa = createOrmawaWithOwner();
    $ketua = User::find($ormawa->user_id);

    $existingMember = User::create([
        'username' => 'memberexisting',
        'email' => 'memberexisting@test.com',
        'password' => bcrypt('password'),
        'role' => 'mahasiswa',
        'nama' => 'Existing Member',
        'nim' => '220101002',
        'is_active' => true,
    ]);

    $ormawa->users()->attach($existingMember->id, [
        'jabatan' => 'anggota',
        'status' => true,
    ]);

    $response = $this->actingAs($ketua)
        ->get(route('ormawa.anggota.search', ['ormawa' => $ormawa->id, 'search' => 'Existing Member']));

    $response->assertOk();
    $response->assertJsonMissing(['id' => $existingMember->id]);
});

it('does not allow adding the ketua as anggota', function () {
    $ormawa = createOrmawaWithOwner();
    $ketua = User::find($ormawa->user_id);

    $response = $this->actingAs($ketua)->post(
        route('ormawa.anggota.store', $ormawa->id),
        [
            'user_id' => $ketua->id,
            'jabatan' => 'anggota',
            'status' => true,
        ]
    );

    $response->assertSessionHasErrors('user_id');
});

it('returns 403 for non-ketua when accessing anggota index', function () {
    $ormawa = createOrmawaWithOwner();
    $user = User::create([
        'username' => 'randomuser',
        'email' => 'randomuser@test.com',
        'password' => bcrypt('password'),
        'role' => 'mahasiswa',
        'nama' => 'Random User',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('ormawa.anggota.index', $ormawa->id))
        ->assertForbidden();
});

it('requires auth to manage anggota', function () {
    $ormawa = createOrmawaWithOwner();

    $response = $this->get(
        route('admin.ormawa.anggota.index', $ormawa->id)
    );

    $response->assertRedirect(route('login'));
});
