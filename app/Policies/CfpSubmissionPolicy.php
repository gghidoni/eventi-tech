<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CfpSubmission;
use App\Models\User;

class CfpSubmissionPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->is_admin && $user->hasVerifiedEmail()) {
            return true;
        }

        return null;
    }

    public function view(User $user, CfpSubmission $submission): bool
    {
        return $submission->user_id === $user->id || $this->review($user, $submission);
    }

    public function review(User $user, CfpSubmission $submission): bool
    {
        return $submission->cfp()
            ->whereHas('event.community', fn ($query) => $query->where('user_id', $user->id))
            ->exists();
    }

    public function updateStatus(User $user, CfpSubmission $submission): bool
    {
        return $this->review($user, $submission);
    }
}
