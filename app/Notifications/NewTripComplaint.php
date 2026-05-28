<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\TripComplaint;

class NewTripComplaint extends Notification implements ShouldQueue
{
    use Queueable;

    public $complaint;

    public function __construct(TripComplaint $complaint)
    {
        $this->complaint = $complaint;
    }

    public function via(object $notifiable): array
    {
        return ['database']; 
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Urgent: Trip Complaint Filed',
            'message' => 'A complaint regarding booking #' . $this->complaint->booking->booking_reference . ' has been filed. Subject: ' . $this->complaint->subject,
            'booking_id' => $this->complaint->booking_id,
            'complaint_id' => $this->complaint->id,
            'url' => route('admin.bookings.show', $this->complaint->booking_id),
        ];
    }
}
