<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;

class TripReminderNotification extends Notification implements ShouldQueue
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
                    ->subject('Trip Reminder: Tomorrow! - ' . $this->booking->booking_reference)
                    ->greeting('Hello!')
                    ->line('This is a reminder that your trip (' . $this->booking->booking_reference . ') is scheduled for ' . $this->booking->scheduled_at->format('M d, Y H:i') . '.')
                    ->line('We look forward to serving you!')
                    ->action('View Details', route('admin.bookings.show', $this->booking->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'reference' => $this->booking->booking_reference,
            'scheduled_at' => $this->booking->scheduled_at,
            'message' => 'Reminder: Trip ' . $this->booking->booking_reference . ' is tomorrow.',
            'url' => route('admin.bookings.show', $this->booking->id),
            'type' => 'trip_reminder',
        ];
    }
}
