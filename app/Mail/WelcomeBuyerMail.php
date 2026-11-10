<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeBuyerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $firstName,
        public readonly string $buyerEmail,
        public readonly string $lang = 'es'
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->lang === 'en'
                ? 'You\'re on the list — Haus of Time'
                : 'Ya estás en la lista — Haus of Time'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome-buyer',
            with: ['locale' => $this->lang],
        );
    }
}
