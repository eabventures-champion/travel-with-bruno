<?php

use Modules\Booking\Models\Booking;
use Modules\Tourism\Models\TourismPackage;
use Carbon\Carbon;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Fix existing organized tour bookings
$bookings = Booking::where('status', 'confirmed')
    ->where('payment_status', 'paid')
    ->whereNull('scheduled_at')
    ->whereHas('items', function($q) {
        $q->where('bookable_type', TourismPackage::class);
    })->get();

echo "Found " . $bookings->count() . " bookings to update.\n";

foreach ($bookings as $booking) {
    foreach ($booking->items as $item) {
        if ($item->bookable_type === TourismPackage::class) {
            $package = $item->bookable;
            if ($package && $package->package_type === 'scheduled' && $package->departure_date) {
                // Set to departure date at 9:00 AM
                $scheduledAt = Carbon::parse($package->departure_date->toDateString() . ' 09:00:00');
                
                $booking->update([
                    'scheduled_at' => $scheduledAt,
                    'driver_schedule_status' => 'pending',
                    'customer_schedule_status' => 'pending'
                ]);
                
                echo "Updated Booking #{$booking->booking_reference} for {$booking->customer_name} to {$scheduledAt->format('Y-m-d H:i')}\n";
                
                // Notify Customer
                try {
                    if ($booking->user) {
                        $booking->user->notify(new \App\Notifications\TripScheduledNotification($booking, false));
                    }
                } catch (\Exception $e) {
                    echo "Notification failed for #{$booking->booking_reference}: " . $e->getMessage() . "\n";
                }
            }
        }
    }
}

echo "Done!\n";
