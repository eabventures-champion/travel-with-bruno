<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;

class NewBookingNotification extends Notification
{
    use Queueable;

    protected $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
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
        return (new MailMessage)
                    ->subject('New Booking Received: ' . $this->booking->booking_reference)
                    ->greeting('Hello Admin,')
                    ->line('A new booking has been placed on the platform.')
                    ->line('Customer: ' . ($this->booking->customer_name ?: 'Guest'))
                    ->line('Reference: ' . $this->booking->booking_reference)
                    ->line('Total Amount: ₵' . number_format($this->booking->total_amount, 2))
                    ->action('View Booking Details', route('admin.bookings.show', $this->booking->id))
                    ->line('Please review and confirm the booking as soon as possible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Booking Received',
            'booking_id' => $this->booking->id,
            'reference' => $this->booking->booking_reference,
            'customer' => $this->booking->customer_name,
            'message' => 'New booking received: ' . $this->booking->booking_reference . ' from ' . ($this->booking->customer_name ?: 'Guest'),
            'amount' => $this->booking->total_amount,
            'url' => route('admin.bookings.show', $this->booking->id),
            'icon' => 'fas fa-ticket-alt'
        ];
    }
}
