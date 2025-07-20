<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountActivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function build(): static
    {
        return $this->view('mail.account-activated');
    }
}
