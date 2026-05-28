<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;

class BookingCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;
    protected $reason;

    public function __construct(Booking $booking, string $reason)
    {
        $this->booking = $booking;
        $this->reason = $reason;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('admin.bookings.show', $this->booking->id);
        if ($notifiable->hasAnyRole(['Driver', 'Chauffeur', 'Driver/Chauffeur'])) {
            $url = url('/driver/schedule');
        }

        return (new MailMessage)
                    ->subject('Booking Cancelled - ' . $this->booking->booking_reference)
                    ->greeting('Hello ' . ($notifiable->name ?? 'Driver') . ',')
                    ->line('A booking assigned to you has been cancelled.')
                    ->line('**Booking Reference:** ' . $this->booking->booking_reference)
                    ->line('**Customer:** ' . $this->booking->customer_name)
                    ->line('**Reason for Cancellation:**')
                    ->line('"' . $this->reason . '"')
                    ->action('View Details', $url)
                    ->line('You have been released from this assignment. Thank you for your understanding.');
    }

    public function toArray(object $notifiable): array
    {
        $url = route('admin.bookings.show', $this->booking->id);
        if ($notifiable->hasAnyRole(['Driver', 'Chauffeur', 'Driver/Chauffeur'])) {
            $url = '/driver/schedule';
        }

        return [
            'booking_id' => $this->booking->id,
            'reference' => $this->booking->booking_reference,
            'title' => 'Booking Cancelled',
            'message' => 'Booking ' . $this->booking->booking_reference . ' has been cancelled. Reason: ' . $this->reason,
            'cancellation_reason' => $this->reason,
            'url' => $url,
            'type' => 'booking_cancelled',
        ];
    }
}
