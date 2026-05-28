<?php
// Check and fix Kofi's status

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$chauffeur = \Modules\Fleet\Models\Chauffeur::whereHas('user', function($q) {
    $q->where('name', 'like', '%Kofi%');
})->first();

if ($chauffeur) {
    echo "Chauffeur: " . $chauffeur->user->name . "\n";
    echo "Current DB status: " . $chauffeur->status . "\n";
    
    $hasActiveTrip = \Modules\Booking\Models\Booking::where('chauffeur_id', $chauffeur->id)
        ->where('trip_status', 'in_progress')
        ->exists();
    echo "Has active trip (in_progress): " . ($hasActiveTrip ? 'yes' : 'no') . "\n";
    
    $hasAccepted = \Modules\Booking\Models\Booking::where('chauffeur_id', $chauffeur->id)
        ->where('driver_schedule_status', 'accepted')
        ->where('trip_status', 'idle')
        ->whereNotIn('status', ['completed', 'cancelled'])
        ->exists();
    echo "Has accepted schedule: " . ($hasAccepted ? 'yes' : 'no') . "\n";
    
    // Fix the status
    if (!$hasActiveTrip && $hasAccepted) {
        $chauffeur->update(['status' => 'schedule_accepted']);
        echo "\n✅ Status updated to: schedule_accepted\n";
    } elseif (!$hasActiveTrip && !$hasAccepted) {
        $chauffeur->update(['status' => 'available']);
        echo "\n✅ Status updated to: available\n";
    } else {
        echo "\nStatus is correct (engaged - active trip in progress)\n";
    }
} else {
    echo "Kofi not found.\n";
}
