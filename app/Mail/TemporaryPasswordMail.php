<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TemporaryPasswordMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public User $user;

    public string $temporaryPassword;

    public function __construct(
        User $user,
        string $temporaryPassword
    ) {
        $this->user = $user;
        $this->temporaryPassword = $temporaryPassword;
    }

    public function build(): self
    {
        return $this
            ->subject('Your UCC LabTech Account')
            ->view('emails.temporary-password');
    }
}