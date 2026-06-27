<?php

use App\Models\User;
use App\Services\NotificationService;

if (!function_exists('sendNotification')) {
    /**
     * Send notification to a user via multiple channels
     *
     * @param User $user
     * @param string $title
     * @param string $message
     * @param string $type (info, success, warning, error)
     * @param string|null $link
     * @param array $channels (telegram, email, in_app, sms)
     * @return \App\Models\Notifikasi
     */
    function sendNotification(
        User $user,
        string $title,
        string $message,
        string $type = 'info',
        ?string $link = null,
        array $channels = []
    ) {
        $notificationService = new NotificationService();
        return $notificationService->send($user, $title, $message, $type, $link, $channels);
    }
}
