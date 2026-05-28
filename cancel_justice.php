<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Booking\Models\Booking;

$booking = Booking::where('customer_name', 'like', '%Justice Appiah%')->first();
if($booking) {
    $booking->status = 'cancelled';
    $booking->notes = ($booking->notes ? $booking->notes . "\n" : "") . "[Auto-Cancelled] Missed tour departure due to pending payment.";
    $booking->save();
    echo "Cancelled booking for " . $booking->customer_name . "\n";
}
