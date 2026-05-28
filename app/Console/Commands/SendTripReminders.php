<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Booking\Models\Booking;
use App\Notifications\TripReminderNotification;
use Carbon\Carbon;

class SendTripReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trips:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders to customers and drivers 2 days before the scheduled trip';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $oneDayFromNowStart = Carbon::now()->addDay()->startOfDay();
        $oneDayFromNowEnd = Carbon::now()->addDay()->endOfDay();

        $bookings = Booking::whereBetween('scheduled_at', [$oneDayFromNowStart, $oneDayFromNowEnd])
            ->where('status', 'confirmed')
            ->where('trip_status', 'idle')
            ->get();

        $this->info('Found ' . $bookings->count() . ' bookings for reminders.');

        $admins = \App\Models\User::role(['Super Admin', 'Operations Admin'])->get();

        foreach ($bookings as $booking) {
            // Notify Customer
            if ($booking->user) {
                $booking->user->notify(new TripReminderNotification($booking));
                $this->info('Notified customer for booking: ' . $booking->booking_reference);
            }

            // Notify Driver
            if ($booking->chauffeur && $booking->chauffeur->user) {
                $booking->chauffeur->user->notify(new TripReminderNotification($booking));
                $this->info('Notified driver for booking: ' . $booking->booking_reference);
            }

            // Notify Admins
            \Illuminate\Support\Facades\Notification::send($admins, new TripReminderNotification($booking));
            $this->info('Notified admins for booking: ' . $booking->booking_reference);
        }

        $this->info('Reminders sent successfully!');
    }
}
