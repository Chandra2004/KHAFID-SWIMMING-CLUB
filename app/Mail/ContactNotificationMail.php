<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $ip_address;

    /**
     * Create a new message instance.
     */
    public function __construct($data, $ip_address)
    {
        $this->data = $data;
        $this->ip_address = $ip_address;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Notifikasi Kontak Masuk - ' . $this->data['subjek'])
                    ->view('email.contact-notification');
    }
}
