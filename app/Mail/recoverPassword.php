<?php

namespace App\Mail;

use App\User;
use App\PassRecover;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class recoverPassword extends Mailable
{
    use Queueable, SerializesModels;
    public $user;
    public $passRecover;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user, PassRecover $passRecover)
    {
        $this->user = $user;
        $this->passRecover = $passRecover;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.recoverPassword')->subject('Recuperación Contraseña.');
    }
}
