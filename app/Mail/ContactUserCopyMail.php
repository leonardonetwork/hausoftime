<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactUserCopyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly array $data // same as ContactSupportMail
    ) {}

    public function envelope(): Envelope
    {
        $locale = $this->data['locale'] ?? 'es';
        return new Envelope(
            subject: $locale === 'en'
                ? 'We received your message — Haus of Time'
                : 'Hemos recibido tu mensaje — Haus of Time'
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-user-copy');
    }
}
