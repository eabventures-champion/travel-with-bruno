<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$chauffeurs = \Modules\Fleet\Models\Chauffeur::with('user')->get();
$fixedCount = 0;

foreach ($chauffeurs as $chauffeur) {
    if ($chauffeur->user) {
        if (!$chauffeur->user->hasRole('Driver')) {
            $chauffeur->user->assignRole('Driver');
            echo "Assigned Driver role to: " . $chauffeur->user->email . PHP_EOL;
            $fixedCount++;
        }
    } else {
        echo "Chauffeur ID {$chauffeur->id} has no associated user!" . PHP_EOL;
    }
}

echo "Checked all " . $chauffeurs->count() . " chauffeurs. Fixed {$fixedCount} users." . PHP_EOL;
