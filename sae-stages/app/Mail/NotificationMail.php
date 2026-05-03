<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $titre,
        public ?string $contenu = null,
        public ?string $url = null,
        public ?string $destinataire = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[SAE Stages] '.$this->titre,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.notification',
            with: [
                'titre' => $this->titre,
                'contenu' => $this->contenu,
                'url' => $this->url,
                'destinataire' => $this->destinataire,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
