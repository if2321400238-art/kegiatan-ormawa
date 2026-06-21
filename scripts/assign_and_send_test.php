<?php
// scripts/assign_and_send_test.php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$email = 'ormawa@test.com';
$chatId = 5151202885; // from getUpdates

$user = User::where('email', $email)->first();
if (!$user) {
    echo "User not found: $email\n";
    exit(1);
}

$user->telegram_id = $chatId;
$user->save();

echo "Set telegram_id for {$user->email} => {$chatId}\n";

try {
    if (function_exists('sendNotification')) {
        sendNotification(
            $user,
            '🧪 Test Notification',
            'Ini pesan test dari sistem (Telegram + In-app).',
            'info',
            url('/'),
            ['telegram', 'in_app']
        );
        echo "Called sendNotification helper.\n";
    } else {
        $svc = new \App\Services\NotificationService();
        $svc->send(
            $user,
            '🧪 Test Notification',
            'Ini pesan test dari sistem (Telegram + In-app).',
            'info',
            url('/'),
            ['telegram', 'in_app']
        );
        echo "Called NotificationService->send().\n";
    }
} catch (\Exception $e) {
    echo "Error sending notification: " . $e->getMessage() . "\n";
}
