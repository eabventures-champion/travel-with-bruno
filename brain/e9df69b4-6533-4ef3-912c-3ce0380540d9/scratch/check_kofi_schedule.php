<?php

use Illuminate\Support\Facades\DB;

define('LARAVEL_START', microtime(true));
require __DIR__ . '/../../../vendor/autoload.php';
$app = require_once __DIR__ . '/../../../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

$bookingsWithChauffeur = \Modules\Booking\Models\Booking::whereNotNull('chauffeur_id')->get();
echo "Bookings with any Chauffeur assigned: " . $bookingsWithChauffeur->count() . "\n";
foreach ($bookingsWithChauffeur as $b) {
    echo "ID: " . $b->id . ", Reference: " . $b->booking_reference . ", Chauffeur ID: " . $b->chauffeur_id . "\n";
}

$allChauffeurs = \Modules\Fleet\Models\Chauffeur::all();
echo "\nTotal Chauffeurs: " . $allChauffeurs->count() . "\n";
foreach ($allChauffeurs as $c) {
    echo "ID: " . $c->id . ", User: " . $c->user->name . ", Email: " . $c->user->email . "\n";
}
