<?php

namespace App\Providers;

use App\Actions\Fortify\AuthenticateUser;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::authenticateUsing(new AuthenticateUser);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        /** @var view-string $loginView */
        $loginView = 'login';
        Fortify::loginView(fn () => view($loginView));

        /** @var view-string $verifyEmailView */
        $verifyEmailView = 'livewire.auth.verify-email';
        Fortify::verifyEmailView(fn () => view($verifyEmailView));

        // Fortify::twoFactorChallengeView(fn () => view('livewire.auth.two-factor-challenge'));
        // Fortify::confirmPasswordView(fn () => view('livewire.auth.confirm-password'));

        /** @var view-string $registerView */
        $registerView = 'livewire.auth.register';
        Fortify::registerView(fn () => view($registerView));

        /** @var view-string $resetPasswordView */
        $resetPasswordView = 'livewire.auth.reset-password';
        Fortify::resetPasswordView(fn () => view($resetPasswordView));

        /** @var view-string $forgotPasswordView */
        $forgotPasswordView = 'livewire.auth.forgot-password';
        Fortify::requestPasswordResetLinkView(fn () => view($forgotPasswordView));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
