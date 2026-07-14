# Backend: Autorizzazione e visibilita

## Scopo

Questa specifica e la source of truth per autorizzazione e visibilita di eventi,
community, CFP, candidature e backoffice Filament.

Il progetto usa quattro attori applicativi:

- visitatore non autenticato;
- utente autenticato;
- owner di community;
- admin con `is_admin=true` ed email verificata.

L'email verificata e obbligatoria per tutte le route dashboard e per l'accesso
al panel Filament.

## Regole pubbliche

- Una community e pubblica solo con status `active`.
- Un evento e pubblico solo con status `active` e community `active`.
- Le route pubbliche di risorse non attive restituiscono `404` a tutti, inclusi
  owner e admin. La route pubblica non svolge funzione di preview.
- Owner e admin consultano le risorse non pubbliche dalle rispettive superfici
  di gestione.
- La pagina pubblica community mostra solo eventi pubblici.
- Bookmark e favorite sono consentiti solo su risorse pubbliche non possedute
  dall'utente.

Una eventuale preview futura deve avere una route dedicata e protetta.

## Regole dashboard

- Le route dashboard richiedono utente autenticato ed email verificata.
- Un owner puo consultare tutte le proprie community e tutti i propri eventi.
- Una community `reject` e consultabile ma non modificabile.
- Un evento `reject` o `terminate` e consultabile ma non modificabile.
- Si possono creare eventi solo per community proprie e `active`.
- Ogni mutazione Livewire deve autorizzare nuovamente la risorsa immediatamente
  prima della scrittura: le proprieta pubbliche Livewire non sono attendibili.
- L'admin puo gestire tutte le risorse tramite Filament.

Il futuro workflow di rifiuto con motivazione, correzione e reinvio e fuori dallo
scope corrente. Finche non viene specificato, le risorse rifiutate restano
read-only per l'owner.

## Regole CFP e candidature

- Una candidatura e consentita solo a utente verificato su CFP interna,
  `published`, aperta e collegata a evento e community pubblici.
- L'owner dell'evento non puo candidarsi alla propria CFP.
- Lo speaker vede solo le proprie candidature.
- L'owner revisiona solo candidature appartenenti a CFP di eventi delle proprie
  community.
- I parametri `{cfp}` e `{submission}` devono appartenere allo stesso aggregato;
  un mismatch restituisce `404`.

## Backoffice Filament

- Il panel richiede contemporaneamente `is_admin=true` ed email verificata.
- La regola vale in ogni ambiente, incluso `local`.
- Utenti normali e owner non-admin non possono accedere al panel o alle Resource
  tramite URL diretto.

## Enforcement

Le Policy Laravel sono il confine eseguibile principale:

- `CommunityPolicy`;
- `EventPolicy`;
- `CfpPolicy`;
- `CfpSubmissionPolicy`.

Le ability distinguono visibilita pubblica e gestione. Route, componenti
Livewire e Filament devono delegare alle Policy invece di duplicare confronti di
ownership. Le invarianti di dominio restano validate anche nel livello
applicativo quando una Action puo essere invocata da piu adapter.

## Semantica HTTP

- `404`: risorsa non pubblica su route pubblica o mismatch tra risorse annidate;
- `403`: risorsa esistente ma operazione non consentita nella dashboard;
- redirect login/verifica: assenza di autenticazione o email non verificata sulle
  route che dichiarano questi middleware.
