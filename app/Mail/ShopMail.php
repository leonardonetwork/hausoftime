<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email transazionali del marketplace (ordine, vendita, spedizione, liquidazione...).
 * Template in resources/views/emails/shop/{template}.blade.php, nella lingua del destinatario.
 */
class ShopMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $template,
        public string $subjectLine,
        public array $data = [],
        public string $lang = 'es',
    ) {
        $this->locale($lang);
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subjectLine);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.shop.' . $this->template, with: $this->data + ['lang' => $this->lang]);
    }
}
