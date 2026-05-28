<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Tourism\Models\TourismPackage;

class AdminTourReadinessWarning extends Notification implements ShouldQueue
{
    use Queueable;

    public $tour;
    public $missingDriversCount;

    public function __construct(TourismPackage $tour, $missingDriversCount)
    {
        $this->tour = $tour;
        $this->missingDriversCount = $missingDriversCount;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('URGENT: Tour Starts Tomorrow - Missing Drivers')
            ->greeting('Hello ' . ($notifiable->name ?? 'Admin') . ',')
            ->line('The organized tour "' . $this->tour->title . '" is scheduled to depart tomorrow (' . $this->tour->departure_date->format('M d, Y') . ').')
            ->line('Warning: There are currently ' . $this->missingDriversCount . ' valid (paid) passenger group(s) that DO NOT have a driver assigned.')
            ->line('The tour will not be able to "take off" and go live until drivers are assigned to these passengers and they start the trip.')
            ->action('Assign Drivers Now', route('admin.bookings.index'))
            ->line('Please take action immediately to prevent service disruption.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Missing Drivers for Tomorrow\'s Tour',
            'message' => 'The tour "' . $this->tour->title . '" departs tomorrow but ' . $this->missingDriversCount . ' passenger group(s) are missing assigned drivers.',
            'url' => route('admin.bookings.index'),
        ];
    }
}
