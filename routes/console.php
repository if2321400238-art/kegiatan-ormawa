<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('telegram:webhook:set {--url=}', function () {
    $token = (string) config('services.telegram.bot_token');
    $secret = (string) config('services.telegram.webhook_secret');
    $url = $this->option('url') ?: route('telegram.webhook');

    if ($token === '' || $secret === '') {
        $this->error('TELEGRAM_BOT_TOKEN dan TELEGRAM_WEBHOOK_SECRET wajib diisi.');

        return 1;
    }
    if (! str_starts_with($url, 'https://')) {
        $this->error('Telegram mensyaratkan URL webhook HTTPS. Gunakan --url=https://domain/telegram/webhook.');

        return 1;
    }

    $response = Http::baseUrl(rtrim((string) config('services.telegram.api_url'), '/'))
        ->post('/bot'.$token.'/setWebhook', ['url' => $url, 'secret_token' => $secret]);

    if (! $response->successful() || ! $response->json('ok')) {
        $this->error('Gagal mendaftarkan webhook: '.$response->body());

        return 1;
    }

    $this->info('Webhook Telegram aktif: '.$url);

    return 0;
})->purpose('Register the Telegram bot webhook for account connections');
