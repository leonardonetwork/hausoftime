<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactSupportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly array $data // name, email, phone_prefix, phone, reason, other_reason, message, locale
    ) {}

    public function envelope(): Envelope
    {
        $name  = $this->data['name'] ?? '';
        $email = $this->data['email'] ?? '';

        return new Envelope(
            subject:  "Contacto desde {$name}",
            replyTo: [new Address($email, $name)],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-support');
    }
}
