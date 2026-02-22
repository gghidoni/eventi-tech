# Backend: Cookie e Privacy (stato repository)

## Scopo

Questo documento descrive:

- cosa emerge dal codice attuale su cookie, dati personali e servizi terzi
- cosa serve in modo minimo per gestire privacy/cookie in produzione
- quali interventi tecnici sono prioritari

Nota: non sostituisce una consulenza legale. Serve come base tecnica verificabile nel progetto.

## Fonti interne usate

- `config/session.php`
- `database/migrations/0001_01_01_000001_create_users_table.php`
- `routes/web.php`
- `app/Http/Controllers/SocialAuthController.php`
- `app/Actions/HandleSocialLogin.php`
- `resources/views/partials/head.blade.php`
- `app/Models/User.php`
- `app/Models/Community.php`
- `resources/views/pages/events/⚡show.blade.php`
- `app/Jobs/NotifyCommunityFollowersOfApprovedEvent.php`
- `app/Filament/Resources/Users/Schemas/UserForm.php`
- `app/Filament/Resources/Users/Schemas/UserInfolist.php`

## Stato tecnico attuale

## Cookie tecnici

- Sessione Laravel attiva (`SESSION_DRIVER=database` in `.env.example` e config in `config/session.php`).
- Cookie di sessione configurabile via `SESSION_COOKIE`, con default `APP_NAME-session`.
- Attributi cookie supportati: `secure`, `http_only`, `same_site`, `path`, `domain`.
- Sessioni persistite su tabella `sessions` con:
  - `user_id`
  - `ip_address`
  - `user_agent`
  - `last_activity`

Impatto: c'e trattamento di dati personali anche senza analytics.

## Autenticazione e dati account

Dati utente gestiti:

- `name`, `email`, `password` (hash)
- `remember_token` (remember me)
- `github_id`, `google_id` (login social)
- `avatar`, `website`, `linkedin`, `instagram`, `facebook`
- metadati 2FA (`two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`)

Flussi presenti:

- registrazione
- login classico
- login OAuth Google/GitHub
- reset password
- verifica email

## Comunicazioni email

Sono presenti email transazionali e notifiche applicative, incluse:

- verifica email
- reset password
- notifiche su eventi/community
- notifica evento approvato agli utenti che seguono una community

Opt-out funzionale: per le notifiche su community favorite, l'utente puo rimuovere la community dai preferiti.

## Servizi terzi caricati lato UI

Nel rendering pubblico risultano chiamate verso terzi:

- Bunny Fonts (`fonts.bunny.net`)
- UI Avatars (`ui-avatars.com`) come fallback avatar/logo
- Simple Icons CDN (`cdn.simpleicons.org`) per icone tag

Sono anche presenti integrazioni OAuth con provider esterni:

- Google
- GitHub

Non risultano integrazioni analytics/profilazione (GA, Meta Pixel, Hotjar, Mixpanel, PostHog) nel codice applicativo corrente.

## Rischi e priorita

## Priorita alta

1. Nel pannello admin sono esposti anche i campi sensibili 2FA:
   - `two_factor_secret`
   - `two_factor_recovery_codes`
   Questi valori non dovrebbero essere mostrati/modificati da UI operativa.

## Priorita media

1. Mancano nel repository pagine informative esplicite:
   - privacy policy
   - cookie policy
2. Font/icone/avatar da CDN esterne comportano trasferimento dati verso terzi.
3. Mancano regole documentate su tempi di conservazione (sessioni/log/account).

## Priorita bassa (ma utile)

1. Procedura operativa per richieste privacy (accesso/cancellazione/rettifica).
2. Versionamento interno della documentazione privacy/cookie.

## Gestione consigliata (caso attuale: servizi gratuiti, nessun analytics)

## Banner cookie

Con lo stato attuale (solo cookie tecnici necessari), approccio minimo:

- niente banner di consenso marketing/profilazione
- si: cookie policy e privacy policy accessibili dal layout pubblico
- si: descrizione chiara dei cookie tecnici usati

Se in futuro aggiungi analytics o tracciamento non tecnico:

- introdurre consenso preventivo
- bloccare script non tecnici fino al consenso
- permettere revoca/modifica consenso

## Contenuti minimi da pubblicare

## Privacy policy (minimo)

- titolare del trattamento (persona fisica)
- dati trattati (account, sessione, social login, log tecnici)
- finalita e base giuridica
- tempi di conservazione
- destinatari/categorie (hosting, email provider, provider OAuth)
- diritti utente e canale di contatto

## Cookie policy (minimo)

- elenco cookie tecnici
- finalita
- durata
- prima/terza parte
- come gestire i cookie dal browser

## Hardening tecnico minimo

In produzione:

- `SESSION_SECURE_COOKIE=true`
- `SESSION_HTTP_ONLY=true`
- `SESSION_SAME_SITE=lax` (o `strict` se compatibile con UX)
- `APP_DEBUG=false`
- revisione retention log (`LOG_DAILY_DAYS`)

## Checklist sintetica

1. Pubblicare pagine privacy/cookie e linkarle nel layout.
2. Rimuovere campi 2FA sensibili da form/infolist admin.
3. Definire e documentare retention dati (sessioni, log, utenti inattivi).
4. Valutare self-host di font/icone/avatar fallback per ridurre trasferimenti a terzi.
5. Preparare procedura interna per gestione richieste privacy.

