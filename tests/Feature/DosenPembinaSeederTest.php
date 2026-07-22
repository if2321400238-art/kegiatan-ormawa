<?php

use App\Models\User;
use Database\Seeders\DosenPembinaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds the LP3M lecturer snapshot idempotently', function () {
    $this->seed(DosenPembinaSeeder::class);
    $this->seed(DosenPembinaSeeder::class);

    expect(User::where('role', User::ROLE_DOSEN)->whereNotNull('nidn')->count())->toBe(191)
        ->and(User::where('role', User::ROLE_DOSEN)->whereNull('program_studi')->count())->toBe(0);

    $this->assertDatabaseHas('users', [
        'nidn' => '0713059104',
        'nama' => 'Ahmad Zubaidi, M.Pd.',
        'program_studi' => 'Pendidikan Agama Islam',
        'role' => User::ROLE_DOSEN,
    ]);
});
