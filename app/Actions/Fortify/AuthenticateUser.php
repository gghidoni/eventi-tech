<?php

namespace App\Actions\Fortify;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;

class AuthenticateUser
{
    public function __invoke(Request $request)
    {
        $request->validate([
            Fortify::username() => 'required|email',
            'password'          => 'required',
        ], [
            'email.required'    => 'Inserisci la tua email',
            'password.required' => 'Inserisci la password',
        ]);

        // Tentativo di autenticazione
        if (!Auth::attempt(
            $request->only(Fortify::username(), 'password'),
            $request->boolean('remember'),
        )) {
            throw ValidationException::withMessages([
                Fortify::username() => ['Le credenziali fornite non corrispondono.'],
            ]);
        }

        // Rigenera sessione per sicurezza
        $request->session()->regenerate();

        // Ritorna l'utente autenticato
        return Auth::user();
    }
}
