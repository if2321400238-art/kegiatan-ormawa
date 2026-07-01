<?php

use App\Models\TelegramConnectionCode;
use App\Models\User;
use Illuminate\Support\Facades\Http;

it('connects a user telegram account using an OTP sent to the bot', function () {
    Http::fake(['api.telegram.test/*' => Http::response(['ok' => true])]);
    config([
        'services.telegram.bot_token' => 'test-token',
        'services.telegram.api_url' => 'https://api.telegram.test',
        'services.telegram.webhook_secret' => 'webhook-secret',
    ]);
    $user = User::factory()->create(['role' => 'bauak', 'telegram_id' => null]);

    $response = $this->actingAs($user)->post(route('profile.telegram.generate'));
    $response->assertRedirect()->assertSessionHas('telegram_otp');
    $otp = session('telegram_otp');
    expect($otp)->toMatch('/^\d{6}$/');

    $this->postJson(route('telegram.webhook'), [
        'message' => ['chat' => ['id' => 987654321], 'text' => $otp],
    ], ['X-Telegram-Bot-Api-Secret-Token' => 'webhook-secret'])->assertOk();

    expect($user->refresh()->telegram_id)->toBe('987654321');
    $this->assertDatabaseMissing('telegram_connection_codes', ['user_id' => $user->id]);
    Http::assertSent(fn ($request) => str_contains($request->url(), 'sendMessage')
        && str_contains($request['text'], 'berhasil tersambung'));
});

it('rejects telegram webhooks without the configured secret', function () {
    config(['services.telegram.webhook_secret' => 'correct-secret']);

    $this->postJson(route('telegram.webhook'), [
        'message' => ['chat' => ['id' => 1], 'text' => '123456'],
    ])->assertForbidden();
});

it('allows a connected user to disconnect telegram', function () {
    $user = User::factory()->create(['role' => 'rektor', 'telegram_id' => '12345']);
    TelegramConnectionCode::create([
        'user_id' => $user->id,
        'code_hash' => 'unused',
        'code_digest' => hash('sha256', 'unused-'.$user->id),
        'expires_at' => now()->addMinute(),
    ]);

    $this->actingAs($user)->delete(route('profile.telegram.disconnect'))->assertRedirect();

    expect($user->refresh()->telegram_id)->toBeNull();
    $this->assertDatabaseMissing('telegram_connection_codes', ['user_id' => $user->id]);
});
