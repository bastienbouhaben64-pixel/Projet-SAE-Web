<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $code, public int $ttlMinutes) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Code de connexion SAE Stages');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
            with: ['code' => $this->code, 'ttl' => $this->ttlMinutes],
        );
    }
}
