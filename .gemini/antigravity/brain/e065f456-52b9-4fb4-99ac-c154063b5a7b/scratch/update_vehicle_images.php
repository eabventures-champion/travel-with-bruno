<?php

require 'c:/laragon/www/bruno/vendor/autoload.php';
$app = require_once 'c:/laragon/www/bruno/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
$app->make(Kernel::class)->bootstrap();

use Modules\Fleet\Models\Vehicle;

$toyota = Vehicle::where('make', 'Toyota')->first();
if ($toyota) {
    $toyota->update(['image' => 'vehicles/toyota.png']);
}

$mercedes = Vehicle::where('make', 'Mercedes-Benz')->first();
if ($mercedes) {
    $mercedes->update(['image' => 'vehicles/mercedes.png']);
}

$hyundai = Vehicle::where('make', 'Hyundai')->first();
if ($hyundai) {
    $hyundai->update(['image' => 'vehicles/hyundai.png']);
}

echo "Vehicle images updated in database.\n";
