<?php

namespace App\Mail;

use App\Models\Watch;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WatchRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Watch $watch) {}

    public function envelope(): Envelope
    {
        $locale  = $this->watch->seller?->locale ?? 'es';
        $subject = $locale === 'en'
            ? "Update on your watch — {$this->watch->brand} {$this->watch->model}"
            : "Actualización sobre tu reloj — {$this->watch->brand} {$this->watch->model}";
        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.watch-rejected');
    }
}
