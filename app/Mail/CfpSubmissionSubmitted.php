<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\CfpSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CfpSubmissionSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CfpSubmission $submission,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Candidatura CFP inviata',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cfps.submission-submitted',
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
