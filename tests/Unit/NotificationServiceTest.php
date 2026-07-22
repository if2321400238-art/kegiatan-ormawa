<?php

use App\Models\Notifikasi;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

test('telegram notification escapes html and includes its destination link', function () {
    config([
        'services.telegram.bot_token' => 'test-token',
        'services.telegram.api_url' => 'https://api.telegram.test',
    ]);

    Http::fake([
        'api.telegram.test/*' => Http::response(['ok' => true, 'result' => []]),
    ]);

    $user = new User(['telegram_id' => '123456']);
    $notification = new Notifikasi([
        'judul' => 'Persetujuan <Kegiatan>',
        'pesan' => 'Proposal A & B sudah diperiksa.',
        'link' => 'https://kegiatan.test/pengajuan/1?tab=a&status=baru',
    ]);

    app(NotificationService::class)->sendTelegram($notification, $user);

    Http::assertSent(function ($request) {
        $text = $request['text'];

        return $request->url() === 'https://api.telegram.test/bottest-token/sendMessage'
            && $request['chat_id'] === '123456'
            && $request['parse_mode'] === 'HTML'
            && str_contains($text, '<b>Persetujuan &lt;Kegiatan&gt;</b>')
            && str_contains($text, 'Proposal A &amp; B sudah diperiksa.')
            && str_contains($text, 'href="https://kegiatan.test/pengajuan/1?tab=a&amp;status=baru"')
            && str_contains($text, 'Buka pengajuan');
    });
});

test('telegram notification rejects an unsuccessful api payload', function () {
    config([
        'services.telegram.bot_token' => 'test-token',
        'services.telegram.api_url' => 'https://api.telegram.test',
    ]);

    Http::fake([
        'api.telegram.test/*' => Http::response([
            'ok' => false,
            'description' => 'Bad Request: chat not found',
        ]),
    ]);

    $user = new User(['telegram_id' => 'invalid-chat']);
    $notification = new Notifikasi([
        'judul' => 'Tes',
        'pesan' => 'Pesan tes',
    ]);

    expect(fn () => app(NotificationService::class)->sendTelegram($notification, $user))
        ->toThrow(RuntimeException::class, 'Bad Request: chat not found');
});
