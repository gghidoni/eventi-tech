<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

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
        return redirect('/');
    }

}
