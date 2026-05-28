<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$notifications = DB::table('notifications')->latest()->take(10)->get();

foreach ($notifications as $notification) {
    echo "ID: " . $notification->id . "\n";
    echo "Type: " . $notification->type . "\n";
    echo "Data: " . $notification->data . "\n";
    echo "---------------------------\n";
}
