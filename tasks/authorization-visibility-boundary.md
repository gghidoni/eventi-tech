# Task: Confine uniforme di autorizzazione e visibilita

## Stato implementazione

Implementazione applicativa completata il 14 luglio 2026:

- specifica e matrice autorizzativa pubblicate;
- Policy registrate per community, eventi, CFP e candidature;
- route pubbliche e dashboard allineate alla semantica `404`/`403`;
- mutazioni Livewire e Actions sensibili ri-autorizzate;
- accesso Filament limitato ad admin con email verificata;
- middleware ownership legacy rimosso;
- test PHP, analisi statica, lint, Semgrep e Playwright verdi.

La chiusura formale resta sospesa per advisory runtime gia presenti nei lockfile:

- Composer: quattro advisory medium su `guzzlehttp/guzzle`,
  `guzzlehttp/psr7` e `phpseclib/phpseclib`;
- npm: un advisory high su `form-data`.

L'aggiornamento delle dipendenze e fuori dallo scope di questo task. Dopo la
relativa remediation va rieseguito `./scripts/security/run.sh` e rimosso il
rilievo P0 dalla flow map.

## Obiettivo

Rendere autorizzazione e visibilita invarianti applicative esplicite e verificabili
su tutte le superfici del monolite:

- pagine pubbliche;
- dashboard Livewire;
- Actions invocate dalle pagine;
- backoffice Filament;
- CFP e relative candidature.

Il risultato atteso non e un nuovo sistema di ruoli general purpose. Il progetto
continua a usare i ruoli gia presenti nel dominio: visitatore, utente autenticato,
owner di community e admin.

Riferimenti:

- `docs/project/intent.md`
- `docs/project/architecture.md`
- `docs/backend/routing.md`
- `docs/agents/flow-audit/runtime-baseline.md`
- `docs/agents/flow-audit/flow-map.md`

## Problema

I controlli attuali sono distribuiti tra:

- middleware `auth`, `verified` e `IsMyCommunity`;
- `abort(403)` e `abort(404)` dentro pagine Livewire;
- filtri Eloquent locali;
- accessor dei model dipendenti da `auth()`;
- autenticazione Filament senza gate esplicito su `is_admin`.

Questa distribuzione rende difficile garantire che lo stesso soggetto riceva la
stessa decisione quando accede alla stessa risorsa da route, richiesta Livewire,
Action o backoffice. Rende inoltre fragile la protezione contro parametri Livewire
manomessi e ID di risorse non pubbliche conosciuti direttamente.

## Decisioni di prodotto confermate

1. **Risorse non attive su route pubbliche**
   - Evento non `active` e community non `active` restituiscono
     `404` a chiunque, inclusi owner e admin.
   - Owner e admin continuano ad accedere dalle rispettive superfici di gestione.
   - Un'eventuale preview futura deve avere una route esplicita e protetta, non
     cambiare il significato della route pubblica.
2. **Accesso Filament e verifica email**
   - Sono obbligatori sia `is_admin=true` sia `email_verified_at` valorizzato.
   - Il seeder admin deve essere allineato.
3. **Community non attive e creazione eventi**
   - Si possono creare eventi solo per community proprie e
     `active`; community `pending` o `reject` restano consultabili in dashboard
     ma non possono pubblicare nuovi eventi.
4. **Modifica dopo rifiuto o terminazione**
   - Eventi `reject` o `terminate` e community `reject` sono consultabili ma non
     modificabili dall'owner.
   - Motivazione del rifiuto, correzione e reinvio saranno definiti in un task
     futuro; non fanno parte di questa implementazione.

Queste decisioni sono registrate in `docs/backend/authorization.md`, source of
truth del comportamento autorizzativo.

## Matrice permessi proposta

| Risorsa/azione | Guest | Autenticato non verificato | Utente verificato | Owner | Admin Filament |
| --- | --- | --- | --- | --- | --- |
| Vedere evento pubblico `active` | si | si | si | si | si |
| Vedere evento pubblico non `active` | no (`404`) | no (`404`) | no (`404`) | no sulla route pubblica | no sulla route pubblica |
| Vedere community pubblica `active` | si | si | si | si | si |
| Vedere community pubblica non `active` | no (`404`) | no (`404`) | no (`404`) | no sulla route pubblica | no sulla route pubblica |
| Gestire community | no | no | no | solo proprie | tutte nel panel |
| Creare evento | no | no | no senza community valida | solo per community propria `active` | tramite panel secondo permessi admin |
| Modificare evento | no | no | no | solo eventi propri non `reject`/`terminate` | tutti nel panel |
| Bookmark/favorite | redirect login | redirect verifica | solo risorse pubbliche | non sulle proprie risorse | fuori scope panel |
| Candidarsi a CFP interna | no | no | solo CFP pubblicata, aperta e su evento/community pubblici | no alla propria CFP | fuori scope panel |
| Vedere proprie candidature | no | no | solo proprie | proprie come speaker | tutte solo se previsto dal panel |
| Revisionare candidatura | no | no | no | solo CFP di eventi appartenenti a community proprie | tutte solo se previsto dal panel |
| Accedere a Filament | no | no | no | no salvo `is_admin` | si |

## Disegno del confine autorizzativo

### Policy come source of truth eseguibile

Introdurre Policy Laravel dedicate alle risorse con operazioni protette:

- `CommunityPolicy`;
- `EventPolicy`;
- `CfpPolicy`;
- `CfpSubmissionPolicy`.

Le ability devono distinguere visibilita pubblica e gestione. Esempi concettuali:

- `viewPublic` per evento/community;
- `update` per ownership;
- `createEvent` per una community attiva posseduta dall'utente;
- `apply` per una CFP interna pubblicata e aperta;
- `review` per una submission appartenente a una CFP dell'owner.

Le Policy non devono dipendere da componenti Livewire o da stato UI.

### Enforcement su ogni ingresso

- Le route pubbliche applicano l'ability di visibilita pubblica e restituiscono
  `404` quando la risorsa non deve essere enumerabile.
- Le route dashboard applicano `auth` e `verified`; le pagine autorizzano la
  risorsa sia in `mount()` sia subito prima di ogni mutazione.
- I valori ricevuti da proprieta Livewire non sono considerati attendibili:
  community, evento, CFP e submission vengono risolti/scoped nuovamente prima
  della scrittura.
- Le Actions ricevono risorse gia autorizzate e non ricostruiscono ownership da
  dati arbitrari. Le invarianti di dominio, come community `active` per creare un
  evento, restano validate anche nel livello applicativo.
- Filament applica il gate di accesso al panel sul model `User`; le autorizzazioni
  delle Resource restano coerenti con il ruolo admin.

### Separazione dalla presentazione

- Sostituire progressivamente gli accessor `is_mine` dipendenti da `auth()` con
  controlli `@can`, `Gate` o boolean calcolati esplicitamente dal componente.
- Rimuovere controlli duplicati e il middleware `IsMyCommunity` solo dopo che le
  Policy equivalenti sono coperte da test.
- Non introdurre un package RBAC: `is_admin` e ownership sono sufficienti per il
  dominio corrente.

## Piano di implementazione

### Fase 0 - Specifica autorizzativa

1. Confermare le quattro decisioni di prodotto aperte.
2. Creare `docs/backend/authorization.md` con:
   - attori e significato di `is_admin`;
   - matrice definitiva;
   - semantica `403` vs `404`;
   - stati che abilitano visibilita e mutazioni;
   - regole per parametri Livewire e route model binding.
3. Collegare la spec da `docs/README.md`, `docs/agents/README.md` e
   `docs/project/architecture.md`.

Acceptance:

- non restano decisioni implicite sui casi elencati;
- ogni test previsto puo essere derivato dalla matrice.

### Fase 1 - Characterization test e Policy

1. Aggiungere test di caratterizzazione per il comportamento corrente critico.
2. Implementare e registrare le quattro Policy.
3. Testare le ability direttamente per guest, utente, owner, altro owner e admin.
4. Rendere esplicita la semantica delle relazioni annidate:
   - evento appartenente alla community dell'owner;
   - submission appartenente al CFP presente nella route;
   - CFP appartenente all'evento atteso.

Acceptance:

- la matrice e coperta da test unitari/feature sulle Policy;
- mismatch di risorse annidate produce sempre `404` o `403` secondo la spec.

### Fase 2 - Pagine pubbliche

1. Applicare `viewPublic` a `events.show` e `communities.show`.
2. Filtrare gli eventi mostrati nella pagina community con la stessa regola
   pubblica usata dalla home.
3. Applicare la regola pubblica alle CTA ticket, bookmark/favorite e CFP.
4. Verificare che ID noti di risorse non attive non ne rivelino contenuti.

Acceptance:

- solo community ed eventi pubblicabili sono visibili dalle route pubbliche;
- home, dettaglio e pagina community usano la stessa definizione di pubblico;
- owner e admin non aggirano la regola usando la route pubblica.

### Fase 3 - Dashboard e mutazioni Livewire

1. Sostituire i controlli ownership locali con `authorize()`/Policy.
2. Ri-autorizzare immediatamente prima di create/update/toggle/status change.
3. Validare `selectedCommunity` contro community proprie e abilitate, senza
   affidarsi alle sole opzioni renderizzate dal select.
4. Proteggere edit evento, edit community, liste CFP, dettaglio submission e
   cambio stato con ability esplicite.
5. Impedire bookmark e favorite di risorse non pubbliche tramite ID manipolato.

Acceptance:

- la modifica di una proprieta Livewire non consente accesso a risorse altrui;
- un utente verificato senza community attiva riceve un esito gestito sulla
  creazione evento;
- tutte le mutazioni sensibili hanno un test negativo non-owner.

### Fase 4 - Backoffice Filament

1. Rendere `User` compatibile con il gate panel Filament e implementare
   `canAccessPanel()` usando la decisione definitiva su admin/verifica email.
2. Verificare accesso diretto a `/admin` con:
   - guest;
   - utente normale;
   - owner non-admin;
   - admin non verificato;
   - admin verificato.
3. Verificare che le Resource non siano raggiungibili aggirando la dashboard.
4. Allineare il seeder admin alla policy scelta.

Acceptance:

- nessun `is_admin=false` puo accedere al panel o alle sue Resource;
- il comportamento dell'admin non verificato coincide con la spec;
- il test non dipende dal fatto che l'ambiente sia `local` o `production`.

### Fase 5 - Consolidamento

1. Rimuovere controlli duplicati, accessor auth-aware e middleware sostituiti.
2. Aggiornare `docs/backend/routing.md`, `docs/backend/livewire.md` e flow map.
3. Eseguire la matrice E2E con attori separati e URL diretti noti.
4. Chiudere il rilievo P0 solo dopo verifica PHP, browser e runtime locale.

Acceptance:

- una sola regola determina ogni decisione autorizzativa;
- non restano `abort(403)` di ownership dispersi nelle pagine interessate;
- documentazione, Policy, route e test descrivono lo stesso comportamento.

## Strategia di test

### Test PHP

- Policy test per tutte le righe della matrice.
- Feature test delle route pubbliche per ogni status evento/community.
- Livewire test con proprieta e parametri manomessi.
- Feature test per mismatch `{cfp}` / `{submission}`.
- Feature test Filament con admin e non-admin.
- Regression test su bookmark, favorite, CFP apply e review.

### Test E2E

- guest apre risorsa active e tenta URL non active noto;
- utente verificato tenta edit di risorsa altrui;
- owner gestisce risorse proprie ma non usa la route pubblica come preview;
- non-admin tenta `/admin` e una Resource diretta;
- admin valido entra nel panel;
- speaker e organizer verificano isolamento delle candidature.

### Verifiche obbligatorie

Poiche il task tocchera auth, middleware e autorizzazione:

```bash
docker compose exec -T app composer lint
docker compose exec -T app composer analyse
docker compose exec -T app composer test
npm run frontend:test
./scripts/security/run.sh
```

## Rollout

Implementare per slice verticali e mantenere ogni commit verificabile:

1. spec + test Policy;
2. gate Filament;
3. visibilita pubblica;
4. dashboard community/eventi;
5. CFP/submission;
6. cleanup dei controlli legacy.

Non cambiare contemporaneamente status enum, workflow di approvazione o schema
ruoli: sarebbero espansioni di scope che renderebbero difficile attribuire le
regressioni.

## Definition of Done

- matrice permessi confermata e documentata;
- accesso Filament limitato agli admin secondo la regola scelta;
- route pubbliche coerenti per tutti gli status;
- ownership applicata via Policy a ogni mutazione dashboard;
- parametri Livewire manipolati coperti da test negativi;
- CFP e submission protette anche contro mismatch di route;
- controlli legacy duplicati rimossi;
- suite PHP, frontend e security verdi;
- flow map aggiornata con evidenze runtime;
- rilievo P0 rimosso solo dopo verifica completa.
