<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Modules\Fleet\Models\Chauffeur;

$kofi = Chauffeur::whereHas('user', function($q) {
    $q->where('name', 'like', '%Kofi%');
})->first();

if ($kofi) {
    echo "Kofi Mensah Profile status: " . $kofi->status . "\n";
    echo "Is Online: " . ($kofi->is_online ? 'Yes' : 'No') . "\n";
    
    // Check if he has bookings
    $bookings = \Modules\Booking\Models\Booking::where('chauffeur_id', $kofi->id)->get();
    echo "Bookings count: " . $bookings->count() . "\n";
    foreach ($bookings as $b) {
        echo "Ref: {$b->booking_reference}, Status: {$b->status}, Trip Status: {$b->trip_status}, Return Trip Status: {$b->return_trip_status}\n";
    }
} else {
    echo "Kofi Mensah not found\n";
}

echo "\nAll Chauffeurs status:\n";
$chauffeurs = Chauffeur::with('user')->get();
foreach ($chauffeurs as $c) {
    echo "Name: " . ($c->user->name ?? 'N/A') . ", Status: " . $c->status . "\n";
}
