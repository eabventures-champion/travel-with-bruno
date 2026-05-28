<?php

require 'c:/laragon/www/bruno/vendor/autoload.php';
$app = require_once 'c:/laragon/www/bruno/bootstrap/app.php';

use Illuminate\Contracts\Console\Kernel;
$app->make(Kernel::class)->bootstrap();

// Manually require the seeder file since it's not in PSR-4
require_once 'c:/laragon/www/bruno/Modules/Fleet/database/seeders/FleetDatabaseSeeder.php';

// Run the seeder logic directly
$seeder = new \Modules\Fleet\Database\Seeders\FleetDatabaseSeeder();
$seeder->run();

echo "Seeding completed.\n";
echo "Vehicle Types: " . \Modules\Fleet\Models\VehicleType::count() . "\n";
echo "Vehicles: " . \Modules\Fleet\Models\Vehicle::count() . "\n";
echo "Chauffeurs: " . \Modules\Fleet\Models\Chauffeur::count() . "\n";
