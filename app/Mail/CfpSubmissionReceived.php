<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\CfpSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CfpSubmissionReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CfpSubmission $submission,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nuova candidatura CFP ricevuta',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cfps.submission-received',
            with: [
                'submission' => $this->submission,
            ],
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
