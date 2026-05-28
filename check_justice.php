<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Booking\Models\Booking;

$booking = Booking::where('customer_name', 'like', '%Justice Appiah%')->with('items')->first();
if($booking) {
    echo json_encode($booking->toArray(), JSON_PRETTY_PRINT);
} else {
    echo "Not found\n";
}
