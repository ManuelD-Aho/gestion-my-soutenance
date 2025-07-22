<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportNeedsCorrectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function build(): static
    {
        return $this->view('mail.report-needs-correction');
    }
}
