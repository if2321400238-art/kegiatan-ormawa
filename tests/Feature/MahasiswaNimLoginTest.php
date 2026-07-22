<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('allows a synchronized student to log in using NIM as login and initial password', function () {
    $nim = '2321400238';
    $student = User::factory()->create([
        'role' => User::ROLE_MAHASISWA,
        'nim' => $nim,
        'password' => Hash::make($nim),
        'must_change_password' => true,
        'is_active' => true,
    ]);

    $this->post('/login', [
        'login' => $nim,
        'password' => $nim,
    ])->assertRedirect(route('password.initial.edit'));

    $this->assertAuthenticatedAs($student);

    $this->get(route('dashboard'))->assertRedirect(route('password.initial.edit'));

    $this->put(route('password.initial.update'), [
        'current_password' => $nim,
        'password' => 'Password-Baru-123',
        'password_confirmation' => 'Password-Baru-123',
    ])->assertRedirect(route('dashboard'));

    expect($student->fresh()->must_change_password)->toBeFalse()
        ->and(Hash::check('Password-Baru-123', $student->fresh()->password))->toBeTrue();
});

it('provisions an unknown student from the API on first NIM login', function () {
    $nim = '03010333';
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
        "https://v2-api.unuja.ac.id/mst/mahasiswa/cari/b/nim/p/{$nim}" => Http::response([
            'data' => [[
                'nim' => $nim,
                'nama' => 'Mahasiswa Login Pertama',
                'email' => '03010333@student.unuja.ac.id',
            ]],
        ]),
    ]);

    $this->post('/login', [
        'login' => $nim,
        'password' => $nim,
    ])->assertRedirect(route('password.initial.edit'));

    $student = User::where('nim', $nim)->firstOrFail();
    $this->assertAuthenticatedAs($student);
    expect($student->must_change_password)->toBeTrue();
});
