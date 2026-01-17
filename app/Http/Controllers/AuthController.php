<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    // use ApiResponses;

    public function redirectEmailVerification(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect(route('dashboard.index').'?verified=1');
    }
}
