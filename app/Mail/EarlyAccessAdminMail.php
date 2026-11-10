<?php
namespace App\Mail;

use App\Models\EarlyAccessRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EarlyAccessAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly EarlyAccessRequest $ear,
        public readonly ?User $user = null
    ) {}

    public function envelope(): Envelope
    {
        $type  = strtoupper($this->ear->type ?? 'UNKNOWN');
        $name  = $this->ear->first_name . ' ' . ($this->ear->last_name ?? '');
        $email = $this->ear->email;

        return new Envelope(
            subject:  "[Early Access] Nuevo registro {$type} — {$name}",
            replyTo: [new Address($email, trim($name))],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.early-access-admin');
    }
}
