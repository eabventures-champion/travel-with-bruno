<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$notifications = DB::table('notifications')->orderBy('created_at', 'desc')->take(10)->get();

echo "--- DUMPING LATEST 10 NOTIFICATIONS IN DATABASE ---" . PHP_EOL;
foreach ($notifications as $n) {
    echo "ID: " . $n->id . PHP_EOL;
    echo "Type (Class): " . $n->type . PHP_EOL;
    echo "Notifiable ID: " . $n->notifiable_id . PHP_EOL;
    echo "Data: " . $n->data . PHP_EOL;
    echo "Created At: " . $n->created_at . PHP_EOL;
    echo "-----------------------------------------------" . PHP_EOL;
}
