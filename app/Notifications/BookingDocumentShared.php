<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;

class BookingDocumentShared extends Notification
{
    use Queueable;

    protected $booking;
    protected $documentTitle;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking, $documentTitle)
    {
        $this->booking = $booking;
        $this->documentTitle = $documentTitle;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('admin.bookings.show', $this->booking);
        if ($notifiable->hasAnyRole(['Driver', 'Chauffeur', 'Driver/Chauffeur'])) {
            $url = route('driver.schedule');
        }

        return (new MailMessage)
                    ->subject('New Document Shared: ' . $this->documentTitle)
                    ->line('A new document has been shared for your booking ' . $this->booking->booking_reference . '.')
                    ->line('Document: ' . $this->documentTitle)
                    ->action('View Details', $url)
                    ->line('Thank you for choosing Bruno Heights Ventures!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $url = route('admin.bookings.show', $this->booking);
        if ($notifiable->hasAnyRole(['Driver', 'Chauffeur', 'Driver/Chauffeur'])) {
            $url = route('driver.schedule');
        }

        return [
            'title' => 'New Document Shared',
            'message' => 'Document "' . $this->documentTitle . '" is now available for your booking.',
            'booking_id' => $this->booking->id,
            'reference' => $this->booking->booking_reference,
            'url' => $url,
        ];
    }
}
