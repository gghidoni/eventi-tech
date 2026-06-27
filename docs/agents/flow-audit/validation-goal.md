# Validation goal

Questo e il goal operativo da assegnare a Codex quando deve eseguire l'audit completo dei flussi locali di Eventi Tech Livewire.

## Obiettivo

Esegui un audit end to end completo dell'app locale. Devi usare Playwright per la GUI, MCP PostgreSQL per controllare il database, MCP Mailpit per controllare le email, Docker/artisan per reset e seed, e i quality gate del repository per la verifica finale.

L'audit deve coprire tutti i flussi descritti in [flow-map.md](flow-map.md), usando la baseline in [runtime-baseline.md](runtime-baseline.md). Non limitarti agli happy path: testa autorizzazioni, errori, stati limite, flussi multiutente, notifiche, upload, admin, CFP, dashboard e ricerca.

## Vincoli non negoziabili

- Prima di agire leggi `docs/agents/README.md`, `docs/agents/quickstart.md`, `docs/agents/tools.md`, `docs/agents/mcp-postgres.md`, `docs/agents/mcp-mailpit.md`, `docs/backend/*` rilevanti, `docs/ui/frontend-testing.md`, `runtime-baseline.md` e `flow-map.md`.
- Se le specifiche in `docs/` sono mancanti, ambigue o in conflitto, fermati e segnala il punto preciso.
- Puoi distruggere e ricreare il DB locale, rifare seed e creare seed supplementari quando serve coprire i casi mancanti.
- Durante l'audit i job devono essere immediati: imposta e verifica `QUEUE_CONNECTION=sync`, poi esegui `php artisan config:clear`.
- Dopo ogni azione che dispatcha job, notifiche o email, aspettati effetti immediati su DB e Mailpit. La tabella `jobs` deve restare vuota per i job previsti in sync.
- Non usare query DB applicative come scorciatoia: le verifiche DB dell'agente passano da MCP PostgreSQL read-only.
- Le verifiche email passano da MCP Mailpit.
- Usa browser context o tab separati per simulare utenti diversi.

## Preparazione

1. Avvia o verifica i container Docker.
2. Esegui una baseline pulita, salvo istruzione contraria:

   ```bash
   docker compose exec -T app php artisan migrate:fresh --seed
   docker compose exec -T app php artisan config:clear
   ```

3. Verifica che l'app risponda su `http://127.0.0.1:8083`.
4. Verifica che Mailpit risponda su `http://127.0.0.1:8025`.
5. Registra conteggi iniziali DB per users, communities, events, cfps, cfp_submissions, cfp_submission_answers, event_user, community_user, password_reset_tokens e jobs.
6. Registra conteggio iniziale Mailpit.
7. Se serve per la ricerca, reindicizza Scout/Meilisearch.
8. Crea dati supplementari solo quando la copertura non e possibile con i seed esistenti.

## Attori minimi

Usa almeno questi ruoli:

- guest anonimo;
- organizer verificato con community active;
- speaker verificato;
- utente non verificato;
- utente verificato senza community;
- utente verificato con community pending, se non esiste crealo;
- admin seed o admin creato ad hoc;
- follower di community per testare la notifica su evento approvato.

## Copertura obbligatoria

Devi eseguire e verificare almeno:

- home pubblica, search, location filter, tag filter, detail evento e detail community;
- registrazione, verifica email, resend verifica, login, logout, rate limit e password reset;
- profilo utente, upload avatar e cambio password;
- bookmark evento e favorite community;
- creazione community, modifica community, autorizzazioni owner/non-owner e notifiche;
- creazione evento online, in presenza e hybrid, tag, address, poster e notifiche;
- modifica evento, autorizzazioni owner/non-owner e gestione CFP collegata;
- approvazione community da Filament;
- approvazione evento da Filament e notifica immediata ai follower con queue sync;
- accesso Filament con admin e con non-admin, documentando il comportamento locale osservato;
- risorse admin Users, Communities, Events, Tags e geografia;
- CFP esterna draft, published e archived;
- CFP interna con template esistente, template derivato, copy-on-write, aggiunta/rimozione field e tutti i field type;
- candidatura speaker valida, candidature multiple e validazioni negative;
- email di submission a organizer e speaker;
- lista candidature organizer, filtri, detail, 403/404, cambio status ed email di status update;
- `Le mie candidature`;
- endpoint `/find-location` e `/find-tags`;
- upload logo, poster e avatar;
- visibilita pubblica di stati pending, reject, terminate e community pending tramite URL diretto;
- casi guest, unverified, non owner e mismatch submission/CFP.

## Regola di verifica per ogni scenario

Per ogni scenario registra:

- azione UI eseguita in Playwright;
- risultato visibile atteso;
- eventuali redirect o status HTTP;
- verifica DB MCP con tabelle e condizioni controllate;
- verifica Mailpit MCP quando attesa email;
- presenza o assenza di job pendenti;
- errori console, page error o request failed;
- eventuale differenza tra specifica, codice e runtime.

## Cosa segnalare sempre

Se confermati nel runtime, segnala esplicitamente:

- accesso Filament non limitato a `is_admin` in local;
- `is_admin` non usato come gate reale dell'admin panel;
- route pubbliche evento/community senza filtro status;
- eventi non active visibili tramite community o URL diretto;
- create event per utente verificato senza community;
- create event usando community pending;
- admin seed non verificato ma capace di entrare in Filament in local;
- CFP esterna published visibile fuori finestra temporale;
- link calendario evento puntato a `/`;
- assenza di cambio email nella UI profilo;
- route 2FA presenti senza flusso UI dedicato;
- campi 2FA sensibili esposti nel resource admin Users.

## Verifica finale

Alla fine esegui i quality gate coerenti con cio che hai toccato:

- se hai modificato PHP: `docker compose exec -T app composer lint` e `docker compose exec -T app composer analyse`;
- se il lavoro non e puramente documentale o locale: `docker compose exec -T app composer test` oppure `docker compose exec -T app composer qa`;
- se hai toccato UI o asset: `npm run frontend:test`;
- se serve audit qualitativo frontend: `npm run frontend:audit`;
- se hai toccato auth, upload, middleware, query raw, dipendenze o config sensibile: `./scripts/security/run.sh`.

## Deliverable finale

Produci un report con:

- matrice scenario per scenario;
- pass/fail;
- evidenze UI sintetiche;
- verifiche DB eseguite;
- email attese e trovate;
- job immediati verificati;
- bug o rischi confermati;
- conflitti docs/codice;
- test automatizzati mancanti;
- comandi QA eseguiti e risultato.

Nel report distingui sempre tra requisiti documentati, comportamento letto nel codice, comportamento osservato nel runtime e inferenze dell'agente.
