<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use InvalidArgumentException;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use RuntimeException;

class HandleSocialLogin
{
    /**
     * Mappa provider -> colonna utente usata per l'ID esterno.
     *
     * @var array<string, string>
     */
    private const PROVIDER_COLUMNS = [
        'github' => 'github_id',
        'google' => 'google_id',
    ];

    /**
     * Risolve o crea l'utente locale partendo dal profilo OAuth.
     */
    public function execute(string $provider, SocialiteUser $socialiteUser): User
    {
        $providerColumn = self::PROVIDER_COLUMNS[$provider] ?? throw new InvalidArgumentException('Provider OAuth non supportato.');

        // L'identificativo del provider e obbligatorio per collegare correttamente l'account.
        $providerId = mb_trim((string) $socialiteUser->getId());

        if ($providerId === '') {
            throw new RuntimeException('ID provider non disponibile.');
        }

        // L'applicazione richiede una email per identificare l'utente in modo consistente.
        $email = mb_trim((string) $socialiteUser->getEmail());

        if ($email === '') {
            throw new RuntimeException('Email non disponibile dal provider.');
        }

        /** @var User|null $user */
        $user = User::query()->where($providerColumn, $providerId)->first();

        if ($user === null) {
            $user = User::query()->where('email', $email)->first();
        }

        if ($user === null) {
            return User::query()->create([
                'name'          => $this->resolveName($socialiteUser, $email),
                'email'         => $email,
                $providerColumn => $providerId,
                // Account social considerato verificato al primo accesso.
                'email_verified_at' => Carbon::now(),
                // Password casuale: login consentito solo tramite social finche non viene cambiata.
                'password' => Str::random(40),
            ]);
        }

        $updates = [];

        if ($user->{$providerColumn} !== $providerId) {
            $updates[$providerColumn] = $providerId;
        }

        // Se entra da social, consideriamo verificata l'email anche per account preesistenti.
        if ($user->email_verified_at === null) {
            $updates['email_verified_at'] = Carbon::now();
        }

        if ($updates !== []) {
            $user->forceFill([
                ...$updates,
            ])->save();
        }

        return $user;
    }

    /**
     * Ricava un nome usabile partendo dai dati social, con fallback sulla parte locale della email.
     */
    private function resolveName(SocialiteUser $socialiteUser, string $email): string
    {
        $name = mb_trim((string) ($socialiteUser->getName() ?? $socialiteUser->getNickname()));

        if ($name !== '') {
            return $name;
        }

        /** @var Stringable $localPart */
        $localPart = str($email)->before('@');

        return (string) $localPart->headline();
    }
}
