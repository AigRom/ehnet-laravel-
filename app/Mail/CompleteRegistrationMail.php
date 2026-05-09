<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompleteRegistrationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $link
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'EHNET – lõpeta registreerimine',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'emails.auth.complete-registration',
            with: [
                'link' => $this->link,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}