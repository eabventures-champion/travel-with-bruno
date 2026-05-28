<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;

class BookingReversedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;
    protected $restoredStatus;

    public function __construct(Booking $booking, string $restoredStatus)
    {
        $this->booking = $booking;
        $this->restoredStatus = $restoredStatus;
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
                    ->subject('Booking Restored - ' . $this->booking->booking_reference)
                    ->greeting('Hello ' . ($notifiable->name ?? 'Driver') . ',')
                    ->line('A previously cancelled booking assigned to you has been restored.')
                    ->line('**Booking Reference:** ' . $this->booking->booking_reference)
                    ->line('**Customer:** ' . $this->booking->customer_name)
                    ->line('**Restored Status:** ' . ucfirst($this->restoredStatus))
                    ->line('This trip is now active again on your schedule.')
                    ->action('View Schedule', $url)
                    ->line('Please review your schedule accordingly. Thank you!');
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
            'title' => 'Booking Restored',
            'message' => 'Booking ' . $this->booking->booking_reference . ' has been restored to ' . ucfirst($this->restoredStatus) . '. This trip is back on your schedule.',
            'restored_status' => $this->restoredStatus,
            'url' => $url,
            'type' => 'booking_reversed',
        ];
    }
}
