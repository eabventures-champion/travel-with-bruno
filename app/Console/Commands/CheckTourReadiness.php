<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Tourism\Models\TourismPackage;
use App\Notifications\AdminTourReadinessWarning;
use App\Notifications\CustomerTourPaymentCancellation;
use Illuminate\Support\Facades\Notification;

class CheckTourReadiness extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tours:check-readiness';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check organized tours departing tomorrow for missing drivers or pending payments.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = now()->addDay()->startOfDay();
        
        $tours = TourismPackage::where('package_type', 'scheduled')
            ->where('status', 'active')
            ->whereDate('departure_date', $tomorrow->toDateString())
            ->get();

        if ($tours->isEmpty()) {
            $this->info('No scheduled tours departing tomorrow.');
            return;
        }

        $admins = \App\Models\User::role(['Super Admin', 'Operations Admin'])->get();

        foreach ($tours as $tour) {
            $this->info('Checking tour: ' . $tour->title);

            $missingDriversCount = 0;
            
            // Get all bookings associated with this tour
            $bookings = \Modules\Booking\Models\Booking::whereHas('items', function($q) use ($tour) {
                $q->where('bookable_type', 'Modules\Tourism\Models\TourismPackage')
                  ->where('bookable_id', $tour->id);
            })->get();

            foreach ($bookings as $booking) {
                // 1. Check for pending payments
                if ($booking->payment_status !== 'paid' && $booking->status !== 'cancelled') {
                    $this->info('Found pending booking: ' . $booking->booking_reference);
                    
                    // Mark as cancelled or simply notify customer
                    $booking->status = 'cancelled';
                    $booking->save();

                    // Send apology email
                    if ($booking->user) {
                        $booking->user->notify(new CustomerTourPaymentCancellation($booking, $tour->title));
                    }
                    continue;
                }

                // 2. Check for missing drivers for valid (paid) passengers
                if ($booking->payment_status === 'paid' && in_array($booking->status, ['confirmed', 'completed'])) {
                    if (empty($booking->chauffeur_id)) {
                        $missingDriversCount++;
                    }
                }
            }

            // If there are valid passengers but no drivers, notify admins
            if ($missingDriversCount > 0) {
                $this->warn('Missing drivers for ' . $missingDriversCount . ' bookings in tour: ' . $tour->title);
                Notification::send($admins, new AdminTourReadinessWarning($tour, $missingDriversCount));
            } else {
                $this->info('Tour readiness checks passed for: ' . $tour->title);
            }
        }

        $this->info('Tour readiness check completed.');
    }
}
