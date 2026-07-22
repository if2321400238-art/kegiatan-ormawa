<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('exposes modular proposal-kegiatan index route for ormawa users', function () {
    $user = User::factory()->create(['role' => 'ormawa']);

    $this->actingAs($user)
        ->get(route('proposal-kegiatan.index'))
        ->assertOk();
});
