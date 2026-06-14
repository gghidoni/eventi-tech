<?php

declare(strict_types=1);

namespace App\Mail;

use App\Enums\CfpSubmissionStatus;
use App\Models\CfpSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CfpSubmissionStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public CfpSubmission $submission,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Aggiornamento candidatura CFP: '.$this->statusLabel($this->submission->status),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cfps.submission-status-updated',
            with: [
                'submission'  => $this->submission,
                'statusLabel' => $this->statusLabel($this->submission->status),
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

    private function statusLabel(CfpSubmissionStatus $status): string
    {
        return match ($status) {
            CfpSubmissionStatus::Draft       => 'bozza',
            CfpSubmissionStatus::Submitted   => 'inviata',
            CfpSubmissionStatus::UnderReview => 'in review',
            CfpSubmissionStatus::Accepted    => 'accettata',
            CfpSubmissionStatus::Rejected    => 'rifiutata',
            CfpSubmissionStatus::Withdrawn   => 'ritirata',
        };
    }
}
