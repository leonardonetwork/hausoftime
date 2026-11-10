<?php
namespace App\Mail;

use App\Models\EarlyAccessRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SellerWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly string $accessType = 'seller', // seller | both
        public readonly ?EarlyAccessRequest $ear = null
    ) {}

    public function envelope(): Envelope
    {
        $en = $this->accessType === 'both'
            ? 'Welcome to Haus of Time — Seller & Buyer account'
            : 'Welcome to Haus of Time — Your seller account';
        $es = $this->accessType === 'both'
            ? 'Bienvenido a Haus of Time — Cuenta Vendedor y Comprador'
            : 'Bienvenido a Haus of Time — Tu cuenta de vendedor';

        return new Envelope(subject: $this->user->locale === 'en' ? $en : $es);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.seller-welcome');
    }
}
