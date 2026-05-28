<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;

class BookingConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('Booking Confirmed - ' . $this->booking->booking_reference)
                    ->greeting('Hello ' . $this->booking->customer_name . '!')
                    ->line('Your booking with reference ' . $this->booking->booking_reference . ' has been confirmed.')
                    ->action('View Booking', route('admin.bookings.show', $this->booking->id))
                    ->line('Thank you for choosing Bruno Heights Ventures!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'reference' => $this->booking->booking_reference,
            'message' => 'Your booking ' . $this->booking->booking_reference . ' has been confirmed.',
            'url' => route('admin.bookings.show', $this->booking->id),
            'type' => 'booking_confirmed',
        ];
    }
}
