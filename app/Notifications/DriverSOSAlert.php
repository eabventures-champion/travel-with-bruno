<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class DriverSOSAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public $driverUser;

    public function __construct(User $driverUser)
    {
        $this->driverUser = $driverUser;
    }

    public function via(object $notifiable): array
    {
        return ['database']; 
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'EMERGENCY SOS: Chauffeur Alert',
            'message' => 'An emergency SOS was triggered by ' . $this->driverUser->name . ' (' . $this->driverUser->phone . '). Please contact them immediately.',
            'url' => route('admin.fleet.chauffeurs.index'),
        ];
    }
}
