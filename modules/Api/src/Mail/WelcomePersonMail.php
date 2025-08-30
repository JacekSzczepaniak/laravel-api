<?php

namespace Modules\Api\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Modules\Api\Models\Person;

class WelcomePersonMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $fullName;

    public function __construct(string|Person $personOrName)
    {
        $this->fullName = $personOrName instanceof Person
            ? trim(($personOrName->first_name ?? '').' '.($personOrName->last_name ?? ''))
            : $personOrName;
    }

    public function build()
    {
        return $this->subject("Witamy użytkownika {$this->fullName}")
            ->html("<p>Witamy użytkownika {$this->fullName}</p>");
    }
}
