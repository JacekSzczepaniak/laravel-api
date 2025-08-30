<?php

namespace Modules\Api\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $fullName, public string $code) {}

    public function build()
    {
        return $this->subject('Weryfikacja kontaktu')
            ->html("<p>Cześć {$this->fullName}, Twój kod weryfikacyjny: <strong>{$this->code}</strong></p>");
    }
}
