<?php
 
namespace App\Notifications;
 
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;
 
class ScheduleDeclinedNotification extends Notification
{
    use Queueable;
 
    protected $booking;
    protected $feedback;
 
    public function __construct(Booking $booking, $feedback)
    {
        $this->booking = $booking;
        $this->feedback = $feedback;
    }
 
    public function via(object $notifiable): array
    {
        return ['database'];
    }
 
    public function toArray(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'reference' => $this->booking->booking_reference,
            'title' => 'Driver Declined Schedule',
            'message' => 'Driver ' . ($this->booking->chauffeur->user->name ?? 'Unknown') . ' has declined the schedule for ' . $this->booking->booking_reference . '. Feedback: ' . ($this->feedback ?? 'No feedback provided'),
            'url' => route('admin.bookings.show', $this->booking),
        ];
    }
}
