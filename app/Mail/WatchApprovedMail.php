<?php

namespace App\Mail;

use App\Models\Watch;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WatchApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Watch $watch) {}

    public function envelope(): Envelope
    {
        $locale  = $this->watch->seller?->locale ?? 'es';
        $subject = $locale === 'en'
            ? "Your watch has been approved — {$this->watch->brand} {$this->watch->model}"
            : "Tu reloj ha sido aprobado — {$this->watch->brand} {$this->watch->model}";
        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.watch-approved');
    }
}
