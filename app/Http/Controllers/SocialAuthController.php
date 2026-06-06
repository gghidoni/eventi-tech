<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\HandleSocialLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider as OAuthTwoProvider;
use RuntimeException;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;
use Throwable;

class SocialAuthController extends Controller
{
    /**
     * Reindirizza l'utente verso il provider OAuth selezionato.
     */
    public function redirect(string $provider): SymfonyRedirectResponse
    {
        return $this->socialiteDriver($provider)->redirect();
    }

    /**
     * Gestisce la callback OAuth, risolve l'utente locale ed effettua il login.
     */
    public function callback(string $provider, HandleSocialLogin $handleSocialLogin): RedirectResponse
    {
        try {
            $socialiteUser = $this->socialiteDriver($provider)->user();
            $user = $handleSocialLogin->execute($provider, $socialiteUser);

            Auth::login($user);
            request()->session()->regenerate();

            return redirect()->intended(route('dashboard.index'));
        } catch (Throwable $exception) {
            // In caso di errore mostriamo un messaggio utente e manteniamo il dettaglio nei log.
            report($exception);

            return redirect()->route('login')->with('error', __('auth.social.errors.generic'));
        }
    }

    /**
     * Crea il driver Socialite con configurazione minima e sicura per provider.
     */
    private function socialiteDriver(string $provider): OAuthTwoProvider
    {
        return match ($provider) {
            // Richiediamo sempre email e profilo per identificazione utente.
            'google' => $this->oauthTwoDriver('google')
                ->scopes(['openid', 'profile', 'email'])
                ->with(['prompt' => 'select_account']),

            // Scope email necessario per utenti GitHub con email non pubblica.
            'github' => $this->oauthTwoDriver('github')->scopes(['user:email']),

            default => abort(404),
        };
    }

    /**
     * Restituisce un provider OAuth2 concreto per mantenere il fluent typing compatibile con Larastan.
     *
     * @param 'google'|'github' $provider
     */
    private function oauthTwoDriver(string $provider): OAuthTwoProvider
    {
        $driver = Socialite::driver($provider);

        // Guardrail difensivo: in questo controller accettiamo solo provider OAuth2.
        if (!$driver instanceof OAuthTwoProvider) {
            throw new RuntimeException("Provider OAuth non supportato: {$provider}");
        }

        return $driver;
    }
}
