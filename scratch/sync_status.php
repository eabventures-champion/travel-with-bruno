<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Modules\Fleet\Models\Chauffeur;
use Modules\Booking\Models\Booking;

// 1. Reset all chauffeurs to available
Chauffeur::query()->update(['status' => 'available']);

// 2. Find all active bookings (confirmed, in_progress, pending with chauffeur assigned? actually pending usually doesn't engage the driver until confirmed, but let's check)
$activeBookings = Booking::whereIn('status', ['confirmed'])
                         ->whereNotIn('trip_status', ['completed'])
                         ->whereNotNull('chauffeur_id')
                         ->get();

foreach($activeBookings as $b) {
    echo "Booking " . $b->id . " is active. Engaging Chauffeur " . $b->chauffeur_id . "\n";
    Chauffeur::where('id', $b->chauffeur_id)->update(['status' => 'engaged']);
}

echo "Done.\n";
