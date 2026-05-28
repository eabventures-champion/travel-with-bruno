<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;

class CustomerTourPaymentCancellation extends Notification implements ShouldQueue
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
            ->subject('Important Update: Action Required for Your Upcoming Tour')
            ->greeting('Dear ' . ($notifiable->name ?? $this->booking->customer_name ?? 'Customer') . ',')
            ->line('We are writing to inform you regarding your upcoming booking (#' . $this->booking->booking_reference . ') for the organized tour: "' . $this->tourTitle . '".')
            ->line('The tour is scheduled to depart tomorrow. Unfortunately, because we have not received full payment for this booking, we have had to remove your reservation from the active passenger list.')
            ->line('We sincerely apologize for any inconvenience this may cause. If you believe this is an error or if you wish to make an immediate payment to secure your spot (subject to availability), please contact our support team immediately.')
            ->action('View Booking', route('admin.bookings.show', $this->booking->id))
            ->line('Thank you for choosing Travel with Bruno.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Tour Reservation Cancelled (Pending Payment)',
            'message' => 'Your reservation for "' . $this->tourTitle . '" was removed from the active list due to pending payment.',
            'booking_id' => $this->booking->id,
            'url' => route('admin.bookings.show', $this->booking->id),
        ];
    }
}
