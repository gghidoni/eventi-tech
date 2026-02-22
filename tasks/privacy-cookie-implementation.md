# Task: Implementazione Cookie e Privacy (baseline)

## Obiettivo

Portare il progetto a una baseline privacy/cookie coerente con lo stato attuale:

- servizi gratuiti
- niente analytics/profilazione
- autenticazione locale + social login

Riferimento tecnico: `docs/backend/privacy-cookie.md`.

## Scope

Dentro scope:

1. Pagine informative privacy/cookie pubbliche.
2. Hardening cookie/sessione per ambiente produzione.
3. Riduzione superficie dati sensibili in admin (2FA secret/recovery).
4. Tracciamento decisioni in documentazione.

Fuori scope:

1. CMP/banner per marketing (da fare solo se si introducono tracker non tecnici).
2. Automazione completa per gestione richieste GDPR (ticketing/backoffice).

## Deliverable

1. Nuova pagina `Privacy Policy`.
2. Nuova pagina `Cookie Policy`.
3. Link globali a privacy/cookie nel layout base.
4. Rimozione campi 2FA sensibili da UI Filament utenti.
5. Config produzione documentata per cookie e logging retention.

## Piano di implementazione

## Fase 1 - Pagine policy e routing

1. Aggiungere due route pubbliche, esempio:
   - `/privacy`
   - `/cookie`
2. Creare due view in `resources/views/pages/`.
3. Inserire link nel layout pubblico (`resources/views/components/layouts/base.blade.php`) in zona footer/nav.

Acceptance:

- le due pagine sono raggiungibili da utente guest
- i link sono visibili su tutte le pagine pubbliche

## Fase 2 - Hardening cookie/sessione

1. Verificare `.env.example` e documentare variabili produzione:
   - `SESSION_SECURE_COOKIE=true`
   - `SESSION_HTTP_ONLY=true`
   - `SESSION_SAME_SITE=lax` (o `strict` se validato)
2. Valutare esplicitazione `SESSION_COOKIE` con nome stabile applicativo.
3. Aggiornare documentazione deploy con questi requisiti.

Acceptance:

- configurazione produzione definita e verificabile in doc
- nessun cambiamento regressivo su login/logout/sessione

## Fase 3 - Admin hardening dati sensibili

1. Rimuovere da Filament User form/infolist:
   - `two_factor_secret`
   - `two_factor_recovery_codes`
2. Mantenere eventualmente solo stato sintetico:
   - `two_factor_confirmed_at`

File coinvolti:

- `app/Filament/Resources/Users/Schemas/UserForm.php`
- `app/Filament/Resources/Users/Schemas/UserInfolist.php`

Acceptance:

- i segreti 2FA non sono piu visibili/modificabili da UI admin
- resta disponibile solo indicatore di stato 2FA

## Fase 4 - Terze parti e minimizzazione

1. Inventario finale servizi terzi lato UI:
   - Bunny Fonts
   - UI Avatars
   - Simple Icons CDN
2. Decisione per ciascuno:
   - accettato e documentato
   - oppure sostituito con asset self-host

Acceptance:

- cookie/privacy policy allineate alle integrazioni effettive
- decisioni registrate in docs

## Test e verifica

1. Smoke test manuale:
   - registrazione/login/logout
   - login social
   - apertura pagine policy
2. Eseguire test suite applicativa (`composer test`).
3. Verificare assenza regressioni su pannello admin utenti.

## Note operative

1. Implementare in modo minimale, leggibile e incrementale.
2. Evitare aggiunta di strumenti consenso finche non sono presenti tracker non tecnici.
3. Tenere testo policy aderente al comportamento reale del codice.

