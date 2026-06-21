<?php

/**
 * Helper untuk mengirim notifikasi dengan NotificationService.
 *
 * @param User $user
 * @param string $judul
 * @param string $pesan
 * @param string $tipe (info, success, warning, error)
 * @param string|null $link
 * @param array $channels (optional: telegram, email, in_app, sms)
 * @return Notifikasi
 */
if (!function_exists('sendNotification')) {
    function sendNotification(
        \App\Models\User $user,
        string $judul,
        string $pesan,
        string $tipe = 'info',
        ?string $link = null,
        array $channels = []
    ): \App\Models\Notifikasi {
        $service = app(\App\Services\NotificationService::class);
        return $service->send($user, $judul, $pesan, $tipe, $link, $channels);
    }
}

/**
 * Kirim notifikasi ke multiple users.
 */
if (!function_exists('sendNotificationToMany')) {
    function sendNotificationToMany(
        array $users,
        string $judul,
        string $pesan,
        string $tipe = 'info',
        ?string $link = null,
        array $channels = []
    ): array {
        $service = app(\App\Services\NotificationService::class);
        $results = [];

        foreach ($users as $user) {
            $results[] = $service->send($user, $judul, $pesan, $tipe, $link, $channels);
        }

        return $results;
    }
}

/**
 * Broadcast notifikasi ke semua users dengan role tertentu.
 */
if (!function_exists('broadcastNotificationByRole')) {
    function broadcastNotificationByRole(
        string $role,
        string $judul,
        string $pesan,
        string $tipe = 'info',
        ?string $link = null,
        array $channels = []
    ): array {
        $users = \App\Models\User::where('role', $role)->get();
        return sendNotificationToMany($users, $judul, $pesan, $tipe, $link, $channels);
    }
}
