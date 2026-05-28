<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GenericNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject($this->data['title'] ?? 'System Notification')
                    ->line($this->data['message'] ?? 'You have a new notification.')
                    ->action($this->data['action_text'] ?? 'View Dashboard', $this->data['action_url'] ?? url('/'))
                    ->line($this->data['footer'] ?? 'Thank you for using our service!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $url = $this->data['action_url'] ?? '#';

        $payload = [
            'title' => $this->data['title'] ?? 'Notification',
            'message' => $this->data['message'] ?? '',
            'type' => $this->data['type'] ?? 'info',
            'url' => $url,
            'action_url' => $url,
        ];

        // Pass through booking_id if provided, so the fallback redirect logic can find the booking
        if (isset($this->data['booking_id'])) {
            $payload['booking_id'] = $this->data['booking_id'];
        }

        return $payload;
    }
}
