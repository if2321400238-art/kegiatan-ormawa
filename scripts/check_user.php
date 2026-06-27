<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function printUser($u, $label){
    if(!$u){
        echo "$label: NOTFOUND\n";
        return;
    }
    $data = $u->only(['id','nama','email']);
    echo "$label: " . json_encode($data) . "\n";
}

$ormawaUserId = \App\Models\Ormawa::find(1)?->user_id ?? null;
echo "ORMAWA_USER_ID: " . ($ormawaUserId ?? 'NULL') . "\n";

$u = \App\Models\User::where('email','210001@student.unuja.ac.id')->first();
printUser($u, 'LOGGED_USER');

$u22 = \App\Models\User::find(22);
printUser($u22, 'USER_22');
