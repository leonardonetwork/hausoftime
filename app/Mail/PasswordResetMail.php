<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $resetUrl,
        public readonly string $userEmail,
        public readonly string $lang = 'es'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->lang === 'en'
                ? 'Reset your password — Haus of Time'
                : 'Recupera tu contraseña — Haus of Time',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset',
            with: ['locale' => $this->lang],
        );
    }
}
