<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Booking\Models\Booking;

$statuses = Booking::distinct()->pluck('status');
echo "Booking statuses: " . $statuses->implode(', ') . "\n";

$tripStatuses = Booking::distinct()->pluck('trip_status');
echo "Trip statuses: " . $tripStatuses->implode(', ') . "\n";
