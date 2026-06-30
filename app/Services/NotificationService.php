<?php

namespace App\Services;

use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Priority order untuk pengiriman notifikasi.
     */
    const DELIVERY_PRIORITY = ['telegram', 'email', 'in_app', 'sms'];

    /**
     * Kirim notifikasi dengan smart channel selection.
     *
     * @param  string  $tipe  (info, success, warning, error)
     * @param  array  $channels  (optional: specify channels, otherwise auto-select)
     */
    public function send(
        User $user,
        string $judul,
        string $pesan,
        string $tipe = 'info',
        ?string $link = null,
        array $channels = []
    ): Notifikasi {
        // Jika channel tidak ditentukan, tentukan secara otomatis
        if (empty($channels)) {
            $channels = $this->selectChannels($user);
        }

        // Buat record notifikasi di database
        $notifikasi = Notifikasi::create([
            'user_id' => $user->id,
            'telegram_id' => $user->telegram_id,
            'judul' => $judul,
            'pesan' => $pesan,
            'tipe' => $tipe,
            'link' => $link,
            'delivery_channels' => [], // Will be updated as channels are attempted
            'delivery_status' => 'pending',
        ]);

        // Kirim ke setiap channel sesuai prioritas
        foreach (self::DELIVERY_PRIORITY as $channel) {
            if (! in_array($channel, $channels)) {
                continue;
            }

            try {
                match ($channel) {
                    'telegram' => $this->sendTelegram($notifikasi, $user),
                    'email' => $this->sendEmail($notifikasi, $user),
                    'in_app' => $this->sendInApp($notifikasi),
                    'sms' => $this->sendSMS($notifikasi, $user),
                };

                // Update delivery channels status
                $current = $notifikasi->delivery_channels ?? [];
                $current[$channel] = 'sent';
                $notifikasi->update(['delivery_channels' => $current]);

                Log::info("Notifikasi {$notifikasi->id} sent via {$channel}");
            } catch (\Exception $e) {
                Log::error("Notifikasi {$notifikasi->id} failed on {$channel}: ".$e->getMessage());

                // Mark channel as failed but continue to next
                $current = $notifikasi->delivery_channels ?? [];
                $current[$channel] = 'failed';
                $notifikasi->update(['delivery_channels' => $current]);
            }
        }

        // Update final delivery status
        $deliveryChannels = $notifikasi->fresh()->delivery_channels ?? [];
        if (collect($deliveryChannels)->contains('sent')) {
            $notifikasi->update(['delivery_status' => 'sent']);
        } else {
            $notifikasi->update(['delivery_status' => 'failed']);
        }

        return $notifikasi->fresh();
    }

    /**
     * Tentukan channel terbaik berdasarkan user profile.
     * Priority: Telegram → Email → In-app → SMS
     */
    public function selectChannels(User $user): array
    {
        $channels = [];

        // Selalu kirim ke in-app (dalam aplikasi)
        $channels[] = 'in_app';

        // Jika user memiliki telegram_id, prioritaskan Telegram
        if (! empty($user->telegram_id)) {
            array_unshift($channels, 'telegram');
        }

        // Fallback ke email jika ada email
        if (! empty($user->email)) {
            $channels[] = 'email';
        }

        // SMS adalah fallback terakhir (opsional)
        // if (!empty($user->no_hp)) {
        //     $channels[] = 'sms';
        // }

        return $channels;
    }

    /**
     * Kirim notifikasi via Telegram Bot API.
     */
    public function sendTelegram(Notifikasi $notifikasi, User $user): void
    {
        if (empty($user->telegram_id)) {
            throw new \Exception('User does not have telegram_id');
        }

        $botToken = config('services.telegram.bot_token');
        $chatId = $user->telegram_id;

        if (empty($botToken)) {
            throw new \Exception('Telegram bot token not configured');
        }

        $message = $this->telegramMessage($notifikasi);
        $apiUrl = rtrim((string) config('services.telegram.api_url', 'https://api.telegram.org'), '/');
        $url = "{$apiUrl}/bot{$botToken}/sendMessage";
        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        $options = [];

        // Prefer project-level CA bundle if configured in .env
        $caFile = env('CURL_CAFILE') ?: config('services.curl.cafile');
        if (! empty($caFile) && file_exists($caFile)) {
            $options['verify'] = $caFile;
        } elseif (config('app.env') === 'local' || env('APP_ENV') === 'local') {
            // Fallback for local dev on Windows if no CA bundle installed
            $options['verify'] = false;
        }

        $request = Http::acceptJson()
            ->connectTimeout((int) config('services.telegram.connect_timeout', 5))
            ->timeout((int) config('services.telegram.timeout', 10));

        if (! empty($options)) {
            $request = $request->withOptions($options);
        }

        $response = $request->post($url, $data);

        if (! $response->successful() || $response->json('ok') !== true) {
            $description = $response->json('description') ?: $response->body();

            throw new \RuntimeException('Failed to send Telegram message: '.$description);
        }
    }

    /**
     * Susun pesan HTML yang aman dan sertakan tautan tujuan bila tersedia.
     */
    private function telegramMessage(Notifikasi $notifikasi): string
    {
        $escape = static fn (?string $value): string => htmlspecialchars(
            (string) $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );

        $parts = [
            '<b>'.$escape($notifikasi->judul).'</b>',
            $escape($notifikasi->pesan),
        ];

        if (! empty($notifikasi->link)) {
            $link = $escape($notifikasi->link);
            $parts[] = '<a href="'.$link.'">Buka pengajuan</a>';
        }

        $parts[] = '<i>Waktu: '.$escape(now()->format('d M Y H:i')).' WIB</i>';

        return implode("\n\n", $parts);
    }

    /**
     * Kirim notifikasi via Email.
     */
    public function sendEmail(Notifikasi $notifikasi, User $user): void
    {
        if (empty($user->email)) {
            throw new \Exception('User does not have email');
        }

        try {
            Mail::send('emails.notification', [
                'notifikasi' => $notifikasi,
                'user' => $user,
            ], function ($message) use ($user, $notifikasi) {
                $message
                    ->to($user->email)
                    ->subject($notifikasi->judul);
            });
        } catch (\Exception $e) {
            throw new \Exception('Failed to send email: '.$e->getMessage());
        }
    }

    /**
     * Kirim notifikasi in-app (sudah disimpan di database).
     * Tidak perlu action khusus, hanya mark sebagai delivered.
     */
    public function sendInApp(Notifikasi $notifikasi): void
    {
        // In-app notification already saved in DB above
        // Just mark as sent
        Log::info("In-app notification {$notifikasi->id} stored");
    }

    /**
     * Kirim notifikasi via SMS (opsional, memerlukan provider seperti Twilio).
     */
    public function sendSMS(Notifikasi $notifikasi, User $user): void
    {
        if (empty($user->no_hp)) {
            throw new \Exception('User does not have phone number');
        }

        // Implementasi SMS (Twilio, Nexmo, dll)
        // Contoh placeholder:
        // $smsProvider = new SMSProvider();
        // $smsProvider->send($user->no_hp, $notifikasi->pesan);

        throw new \Exception('SMS delivery not yet implemented');
    }

    /**
     * Mark notifikasi sebagai read (dengan timestamp).
     */
    public function markAsRead(Notifikasi $notifikasi): void
    {
        $notifikasi->update([
            'dibaca' => true,
            'dibaca_pada' => now(),
            'read_at' => now(),
        ]);

        Log::info("Notifikasi {$notifikasi->id} marked as read by user {$notifikasi->user_id}");
    }

    /**
     * Get delivery status summary.
     */
    public function getDeliveryStatus(Notifikasi $notifikasi): array
    {
        return [
            'overall_status' => $notifikasi->delivery_status,
            'channels' => $notifikasi->delivery_channels ?? [],
            'is_read' => $notifikasi->dibaca,
            'read_at' => $notifikasi->read_at,
        ];
    }
}
