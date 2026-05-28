<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;

class TripStarted extends Notification implements ShouldQueue
{
    use Queueable;

    public $booking;
    public $leg;

    public function __construct(Booking $booking, $leg = null)
    {
        $this->booking = $booking;
        $this->leg = $leg;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $chauffeurName = $this->booking->chauffeur->user->name ?? 'the assigned chauffeur';
        $title = 'Trip Started';
        $message = 'The trip for booking #' . $this->booking->booking_reference . ' has been started by ' . $chauffeurName . '.';

        if ($this->leg === 'outbound') {
            $title = 'Outbound Trip Started';
            $message = 'The outbound trip for booking #' . $this->booking->booking_reference . ' has been started by ' . $chauffeurName . '.';
        } elseif ($this->leg === 'return') {
            $title = 'Return Trip Started';
            $message = 'The return trip for booking #' . $this->booking->booking_reference . ' has been started by ' . $chauffeurName . '.';
        }

        return (new MailMessage)
            ->subject($title . ' - Bruno Heights Ventures')
            ->greeting('Hello ' . ($notifiable->name ?? 'Admin') . ',')
            ->line($message)
            ->action('View Booking Details', route('admin.bookings.show', $this->booking->id))
            ->line('Thank you for choosing Bruno Heights Ventures!');
    }

    public function toArray(object $notifiable): array
    {
        $chauffeurName = $this->booking->chauffeur->user->name ?? 'the assigned chauffeur';
        $title = 'Trip Started';
        $message = 'The trip for booking #' . $this->booking->booking_reference . ' has been started by ' . $chauffeurName . '.';

        if ($this->leg === 'outbound') {
            $title = 'Outbound Trip Started';
            $message = 'The outbound trip for booking #' . $this->booking->booking_reference . ' has been started by ' . $chauffeurName . '.';
        } elseif ($this->leg === 'return') {
            $title = 'Return Trip Started';
            $message = 'The return trip for booking #' . $this->booking->booking_reference . ' has been started by ' . $chauffeurName . '.';
        }

        return [
            'title' => $title,
            'message' => $message,
            'booking_id' => $this->booking->id,
            'url' => route('admin.bookings.show', $this->booking->id),
        ];
    }
}
