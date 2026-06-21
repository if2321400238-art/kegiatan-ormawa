<?php
// scripts/create_test_users.php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$users = [
    [
        'email' => 'ormawa@test.com',
        'name' => 'Test Ormawa',
        'role' => 'ormawa',
        'telegram_id' => null,
        'password' => 'password',
    ],
    [
        'email' => 'bauak@test.com',
        'name' => 'Test BAUAK',
        'role' => 'bauak',
        'telegram_id' => null,
        'password' => 'password',
    ],
    [
        'email' => 'warek3@test.com',
        'name' => 'Test Warek3',
        'role' => 'warek3',
        'telegram_id' => null,
        'password' => 'password',
    ],
];

foreach ($users as $u) {
    // create a username from the email local part and ensure uniqueness
    $base = explode('@', $u['email'])[0];
    $username = $base;
    $i = 1;
    while (User::where('username', $username)->exists()) {
        $username = $base . $i;
        $i++;
    }

    $model = User::firstOrCreate(
        ['email' => $u['email']],
        [
            'name' => $u['name'],
            'nama' => $u['name'],
            'username' => $username,
            'role' => $u['role'],
            'telegram_id' => $u['telegram_id'],
            'password' => bcrypt($u['password']),
        ]
    );
    echo "Created/Found user: {$model->email} (id={$model->id})\n";
}

echo "Done.\n";
