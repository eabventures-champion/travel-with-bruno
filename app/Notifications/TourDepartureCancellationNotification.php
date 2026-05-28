<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;

class TourDepartureCancellationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $booking;
    public $tourTitle;

    public function __construct(Booking $booking, $tourTitle)
    {
        $this->booking = $booking;
        $this->tourTitle = $tourTitle;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Important Update: Tour Booking Cancelled (Pending Payment)')
            ->greeting('Dear ' . ($notifiable->name ?? $this->booking->customer_name ?? 'Customer') . ',')
            ->line('We are writing to inform you regarding your booking (#' . $this->booking->booking_reference . ') for the organized tour: "' . $this->tourTitle . '".')
            ->line('The tour has now officially departed. Unfortunately, because we did not receive full payment for your reservation prior to departure, your booking has been cancelled and you have been removed from the passenger list.')
            ->line('We sincerely apologize for any inconvenience. If you believe this is an error, please contact our support team immediately.')
            ->action('View Booking', route('admin.bookings.show', $this->booking->id))
            ->line('Thank you for your understanding.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Tour Cancelled (Pending Payment)',
            'message' => 'Your reservation for "' . $this->tourTitle . '" was cancelled at departure time due to pending payment.',
            'booking_id' => $this->booking->id,
            'url' => route('admin.bookings.show', $this->booking->id),
        ];
    }
}
