<?php

namespace Modules\Tourism\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Tourism\Models\TourismPackage;

class NewTourNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $package;

    /**
     * Create a new message instance.
     */
    public function __construct(TourismPackage $package)
    {
        $this->package = $package;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('New Adventure Awaits: ' . $this->package->title)
                    ->view('tourism::emails.new-tour');
    }
}
