<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CfpSubmissionStatus;
use App\Mail\CfpSubmissionStatusUpdated;
use App\Models\CfpSubmission;
use Illuminate\Support\Facades\Mail;

class UpdateCfpSubmissionStatus
{
    public function execute(CfpSubmission $submission, CfpSubmissionStatus $status): CfpSubmission
    {
        $submission->update([
            'status' => $status,
        ]);

        $statusChanged = $submission->wasChanged('status');
        $submission = $submission->refresh();

        if ($statusChanged) {
            $submission->loadMissing(['user', 'cfp.event']);
            Mail::to($submission->user)->send(new CfpSubmissionStatusUpdated($submission));
        }

        return $submission;
    }
}
