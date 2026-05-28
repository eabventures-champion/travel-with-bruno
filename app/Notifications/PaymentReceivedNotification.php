<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Booking\Models\Booking;

class PaymentReceivedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $booking;
    protected $paymentAmount;
    protected $paymentNote;

    public function __construct(Booking $booking, float $paymentAmount, string $paymentNote)
    {
        $this->booking = $booking;
        $this->paymentAmount = $paymentAmount;
        $this->paymentNote = $paymentNote;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $remaining = floatval($this->booking->total_amount) - floatval($this->booking->partial_amount ?? 0);
        $isFinal = ($this->booking->payment_status === 'paid');

        $mail = (new MailMessage)
                    ->subject('Payment Received - ' . $this->paymentNote . ' - ' . $this->booking->booking_reference)
                    ->greeting('Hello ' . $this->booking->customer_name . '!')
                    ->line('We have successfully received a payment of ₵' . number_format($this->paymentAmount, 2) . ' for your booking ' . $this->booking->booking_reference . '.')
                    ->line('Payment Installment: ' . $this->paymentNote);

        if ($isFinal) {
            $mail->line('Status: Fully Paid!')
                 ->line('Your booking is now fully funded. We look forward to hosting you!');
        } else {
            $mail->line('Remaining Balance: ₵' . number_format($remaining, 2));
        }

        return $mail->action('View Booking Details', route('admin.bookings.show', $this->booking->id))
                    ->line('Thank you for choosing Bruno Heights Ventures!');
    }

    public function toArray(object $notifiable): array
    {
        $remaining = floatval($this->booking->total_amount) - floatval($this->booking->partial_amount ?? 0);
        $isFinal = ($this->booking->payment_status === 'paid');

        $msg = 'Payment of ₵' . number_format($this->paymentAmount, 2) . ' received (' . $this->paymentNote . ').';
        if ($isFinal) {
            $msg .= ' Status: Fully Paid!';
        } else {
            $msg .= ' Remaining balance: ₵' . number_format($remaining, 2);
        }

        return [
            'booking_id' => $this->booking->id,
            'reference' => $this->booking->booking_reference,
            'message' => $msg,
            'url' => route('admin.bookings.show', $this->booking->id),
            'type' => 'payment_received',
        ];
    }
}
