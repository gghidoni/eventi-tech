<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    // use ApiResponses;

    public function login(): View
    {
        return view('auth.login');
    }

    public function register(Request $request): View
    {
        return view('auth.register');
    }

    public function thanksRegister(): View
    {
        return view('auth.thanks-register');
    }

    public function verificationNotice(): View
    {
        return view('auth.verify-email');
    }

    public function redirectEmailVerification(EmailVerificationRequest $request): RedirectResponse
    {
        $request->fulfill();

        return redirect(route('dashboard.index').'?verified=1');
    }
}
