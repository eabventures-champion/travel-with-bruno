<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TourInterestNotification extends Notification
{
    use Queueable;

    protected $interest;

    /**
     * Create a new notification instance.
     */
    public function __construct($interest)
    {
        $this->interest = $interest;
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
        $packageName = $this->interest->package ? $this->interest->package->title : 'Unknown Tour';
        
        return (new MailMessage)
            ->subject('New Tour Interest: ' . $packageName)
            ->greeting('Hello Admin,')
            ->line('A new interest has been recorded for the tour: **' . $packageName . '**.')
            ->line('**Customer Details:**')
            ->line('Name: ' . $this->interest->name)
            ->line('Email: ' . $this->interest->email)
            ->line('Phone: ' . ($this->interest->phone ?? 'N/A'))
            ->line('**Notes:**')
            ->line($this->interest->notes ?? 'No additional notes provided.')
            ->action('View Tour Interests', url('/admin/tourism/interests'))
            ->line('Please follow up with the customer as soon as possible.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $packageName = $this->interest->package ? $this->interest->package->title : 'Unknown Tour';

        return [
            'type' => 'tour_interest',
            'title' => 'New Tour Interest',
            'message' => $this->interest->name . ' is interested in ' . $packageName,
            'package_id' => $this->interest->package_id,
            'interest_id' => $this->interest->id,
            'url' => '/admin/tourism/interests'
        ];
    }
}
