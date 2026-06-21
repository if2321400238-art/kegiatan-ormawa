<?php
// scripts/print_latest_notif.php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Notifikasi;

$email = 'ormawa@test.com';
$user = User::where('email', $email)->first();
if (!$user) { echo "User not found\n"; exit(1); }

$notif = Notifikasi::where('user_id', $user->id)->latest()->first();
if (!$notif) { echo "No notifications found for {$email}\n"; exit(0); }

echo "Notification ID: {$notif->id}\n";
echo "Title: {$notif->judul}\n";
echo "Message: {$notif->pesan}\n";
echo "Delivery Channels: " . json_encode($notif->delivery_channels) . "\n";
echo "Delivery Status: {$notif->delivery_status}\n";
echo "Read At: " . ($notif->read_at ? $notif->read_at : 'null') . "\n";
echo "Created At: {$notif->created_at}\n";
