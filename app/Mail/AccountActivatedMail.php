<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User; // Importer le modèle User
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountActivatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user; // Déclarer la propriété publique

    public string $password; // Déclarer la propriété publique

    public function __construct(User $user, string $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    public function build(): static
    {
        return $this->subject('Votre compte GestionMySoutenance a été activé !') // Ajouter un sujet
            ->view('mail.account-activated');
    }
}
