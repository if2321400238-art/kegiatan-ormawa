<?php

namespace App\Http\Controllers;

use App\Models\TelegramConnectionCode;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;

class TelegramConnectionController extends Controller
{
    public function generate(Request $request): RedirectResponse
    {
        if ($request->user()->hasTelegram()) {
            return back()->with('telegram_error', 'Akun ini sudah tersambung dengan Telegram.');
        }

        $code = (string) random_int(100000, 999999);
        TelegramConnectionCode::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'code_hash' => Hash::make($code),
                'code_digest' => $this->digest($code),
                'attempts' => 0,
                'expires_at' => now()->addMinutes(10),
            ]
        );

        return back()->with('telegram_otp', $code)
            ->with('telegram_success', 'Kode OTP berhasil dibuat dan berlaku selama 10 menit.');
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $request->user()->update(['telegram_id' => null]);
        TelegramConnectionCode::where('user_id', $request->user()->id)->delete();

        return back()->with('telegram_success', 'Akun Telegram berhasil diputuskan.');
    }

    public function webhook(Request $request): JsonResponse
    {
        $secret = (string) config('services.telegram.webhook_secret');
        if ($secret === '' || ! hash_equals($secret, (string) $request->header('X-Telegram-Bot-Api-Secret-Token'))) {
            abort(403);
        }

        $message = $request->input('message');
        if (! is_array($message) || empty($message['chat']['id'])) {
            return response()->json(['ok' => true]);
        }

        $chatId = (string) $message['chat']['id'];
        $text = trim((string) ($message['text'] ?? ''));

        if (str_starts_with($text, '/start')) {
            $this->reply($chatId, 'Selamat datang. Buat kode OTP melalui menu Pengaturan Profil pada Sistem Ormawa, lalu kirimkan 6 digit kode tersebut ke chat ini.');

            return response()->json(['ok' => true]);
        }

        if (! preg_match('/^\d{6}$/', $text)) {
            $this->reply($chatId, 'Kode tidak valid. Kirimkan kode OTP 6 digit dari menu Pengaturan Profil.');

            return response()->json(['ok' => true]);
        }

        $limiterKey = 'telegram-otp:'.$chatId;
        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            $this->reply($chatId, 'Terlalu banyak percobaan. Tunggu 10 menit atau buat OTP baru dari profil.');

            return response()->json(['ok' => true]);
        }

        $result = DB::transaction(function () use ($text, $chatId) {
            $match = TelegramConnectionCode::with('user')->where('code_digest', $this->digest($text))
                ->where('expires_at', '>', now())->where('attempts', '<', 5)->lockForUpdate()->first();

            if (! $match || ! Hash::check($text, $match->code_hash)) {
                return null;
            }

            $alreadyUsed = User::where('telegram_id', $chatId)->where('id', '!=', $match->user_id)->exists();
            if ($alreadyUsed) {
                return false;
            }

            $match->user->update(['telegram_id' => $chatId]);
            $userName = $match->user->nama;
            $match->delete();

            return $userName;
        });

        if (is_string($result)) {
            RateLimiter::clear($limiterKey);
            $this->reply($chatId, "Telegram berhasil tersambung dengan akun {$result}. Notifikasi sistem akan dikirimkan melalui bot ini.");
        } elseif ($result === false) {
            $this->reply($chatId, 'Akun Telegram ini sudah tersambung dengan pengguna lain. Putuskan koneksi lama terlebih dahulu.');
        } else {
            RateLimiter::hit($limiterKey, 600);
            $this->reply($chatId, 'OTP salah atau sudah kedaluwarsa. Buat kode baru melalui Pengaturan Profil.');
        }

        return response()->json(['ok' => true]);
    }

    private function digest(string $code): string
    {
        return hash_hmac('sha256', $code, (string) config('app.key'));
    }

    private function reply(string $chatId, string $text): void
    {
        $token = (string) config('services.telegram.bot_token');
        if ($token === '') {
            return;
        }

        $this->telegramClient()->post('/bot'.$token.'/sendMessage', ['chat_id' => $chatId, 'text' => $text]);
    }

    private function telegramClient(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.telegram.api_url'), '/'))
            ->connectTimeout((int) config('services.telegram.connect_timeout', 5))
            ->timeout((int) config('services.telegram.timeout', 10));
    }
}
