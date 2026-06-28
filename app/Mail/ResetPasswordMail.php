<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $uid;
    public $valid_until;

    /**
     * Create a new message instance.
     */
    public function __construct($token, $uid, $valid_until)
    {
        $this->token = $token;
        $this->uid = $uid;
        $this->valid_until = $valid_until;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Atur Ulang Kata Sandi - Khafid Swimming Club')
                    ->view('email.reset-password-notification');
    }
}
