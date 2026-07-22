<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('shows synchronized students to admin without manual CRUD actions', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $student = User::factory()->create([
        'role' => User::ROLE_MAHASISWA,
        'nim' => '2321400238',
        'nama' => 'Mahasiswa Sinkron',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.mahasiswa.index'))
        ->assertOk()
        ->assertSee('Mahasiswa Tersinkron')
        ->assertSee($student->nama)
        ->assertSee($student->nim)
        ->assertDontSee('Tambah Mahasiswa')
        ->assertDontSee('Hapus');

    expect(Route::has('admin.mahasiswa.create'))->toBeFalse()
        ->and(Route::has('admin.mahasiswa.store'))->toBeFalse()
        ->and(Route::has('admin.mahasiswa.edit'))->toBeFalse()
        ->and(Route::has('admin.mahasiswa.update'))->toBeFalse()
        ->and(Route::has('admin.mahasiswa.destroy'))->toBeFalse();

    $student->forceFill([
        'password' => Hash::make('password-lama'),
        'must_change_password' => false,
    ])->save();

    $this->post(route('admin.mahasiswa.reset-password', $student))
        ->assertRedirect();

    $student->refresh();
    expect(Hash::check($student->nim, $student->password))->toBeTrue()
        ->and($student->must_change_password)->toBeTrue();
});
