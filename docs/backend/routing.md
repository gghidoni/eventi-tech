# Backend: Routing e Livewire “pages::...”

## Convenzione rotte pagine

Le pagine principali sono Livewire pages risolte con la sintassi `pages::...`:

- Event show: `pages::events.show`
- Community show: `pages::communities.show`
- Dashboard: `pages::dashboard.*`
- Auth: `pages::auth.*`

Fonte:

- `routes/web.php`

## Autorizzazione delle route

- Le route pubbliche evento e community applicano rispettivamente
  `EventPolicy::viewPublic` e `CommunityPolicy::viewPublic`.
- Una risorsa non pubblica restituisce `404` anche a owner e admin; le route
  pubbliche non sono route di preview.
- Le route dashboard applicano `auth` e `verified`; le route su una risorsa
  applicano anche la relativa ability di Policy.
- I componenti Livewire ripetono l'autorizzazione prima delle mutazioni, perche
  il middleware della richiesta iniziale non protegge le richieste Livewire
  successive.

La matrice completa e in `docs/backend/authorization.md`.

## Dove sono i file

Le pagine `pages::...` corrispondono a Blade “Volt-like” in:

- `resources/views/pages/**/⚡*.blade.php`

Dentro questi file c’e:

- un `new class extends Component { ... }`
- seguito dalla view Blade/HTML

Esempio:

- `resources/views/pages/auth/⚡login.blade.php`
