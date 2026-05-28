<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;

class TripScheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;
    protected $isAdjustment;

    public function __construct(Booking $booking, $isAdjustment = false)
    {
        $this->booking = $booking;
        $this->isAdjustment = $isAdjustment;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->isAdjustment ? 'Trip Schedule Adjusted' : 'Trip Scheduled';
        $message = $this->isAdjustment 
            ? 'The schedule for your trip (' . $this->booking->booking_reference . ') has been adjusted.'
            : 'Your trip (' . $this->booking->booking_reference . ') has been scheduled.';

        $url = route('admin.bookings.show', $this->booking->id);
        if ($notifiable->hasAnyRole(['Driver', 'Chauffeur', 'Driver/Chauffeur'])) {
            $url = route('driver.schedule');
        }

        return (new MailMessage)
                    ->subject($subject . ' - ' . $this->booking->booking_reference)
                    ->greeting('Hello!')
                    ->line($message)
                    ->line('New Scheduled Date: ' . $this->booking->scheduled_at->format('M d, Y @ h:i A'))
                    ->action('View Details', $url)
                    ->line('Thank you for using Bruno Travel!');
    }

    public function toArray(object $notifiable): array
    {
        $url = route('admin.bookings.show', $this->booking->id);
        if ($notifiable->hasAnyRole(['Driver', 'Chauffeur', 'Driver/Chauffeur'])) {
            $url = route('driver.schedule');
        }

        return [
            'booking_id' => $this->booking->id,
            'reference' => $this->booking->booking_reference,
            'scheduled_at' => $this->booking->scheduled_at,
            'title' => $this->isAdjustment ? 'Trip Schedule Adjusted' : 'Trip Scheduled',
            'message' => ($this->isAdjustment ? 'The schedule for your trip ' : 'Your trip ') . $this->booking->booking_reference . ' has been ' . ($this->isAdjustment ? 'adjusted.' : 'scheduled.'),
            'url' => $url,
            'type' => 'trip_scheduled',
        ];
    }
}
