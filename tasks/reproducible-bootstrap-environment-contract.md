# Task: Bootstrap riproducibile e contratto ambiente

## Obiettivo

Rendere possibile partire da un checkout pulito con un percorso locale unico,
idempotente e verificabile, senza dipendere da file, cache, volumi o conoscenze
gia presenti sulla macchina dello sviluppatore.

Il task deve inoltre rendere esplicita la differenza tra:

- runtime locale Docker;
- suite test rapida ed ermetica;
- requisiti di configurazione della produzione.

Non rientrano in questo task la topologia production completa, il disegno
definitivo di queue/worker o il lifecycle operativo di Meilisearch: sono rilievi
architetturali separati gia presenti nella flow map.

## Stato implementazione

Implementato il 14 luglio 2026.

- Docker e il solo runtime locale canonico; SQLite resta confinato ai test.
- `.env.example`, Laravel e Compose condividono PostgreSQL, Meilisearch,
  Mailpit e queue `sync`.
- Il provider Volt obsoleto e stato rimosso e coperto da regression test.
- `./scripts/bootstrap.sh` e idempotente; il seed e solo opt-in con `--seed`.
- `./scripts/doctor.sh` verifica il runtime senza modificarlo.
- lo smoke isolato copre volumi vuoti, migrazioni, seed, query Scout, HTTP e
  secondo bootstrap non distruttivo.
- la CI valida l'interpolazione Compose tramite `.env.example` e usa `npm ci`.
- la fixture dei comuni e versionata, quindi il seed non dipende piu da un file
  locale ignorato.

Evidenze finali:

- fresh-checkout smoke: verde;
- bootstrap ripetuto sul runtime principale: verde, nessuna migrazione pending;
- Pint: 283 file, verde;
- PHPStan: 139 file, zero errori;
- Pest: 214 test, 570 assertion, verdi;
- Playwright: 6 test, verdi;
- Semgrep: 534 file, zero finding;
- security gate dipendenze: rosso per debito preesistente e separato, con
  quattro advisory Composer medium e un advisory npm high.

Riferimenti:

- `docs/agents/quickstart.md`
- `docs/agents/workflow.md`
- `docs/project/architecture.md`
- `docs/project/technical-decisions.md`
- `docs/agents/flow-audit/runtime-baseline.md`
- `docs/agents/flow-audit/flow-map.md`

## Evidenze confermate

### Bootstrap Laravel

- `bootstrap/providers.php` registra
  `App\Providers\VoltServiceProvider`.
- La classe e stata eliminata dal repository e non e presente nel runtime.
- Il vecchio provider montava le directory Volt; Livewire 4 risolve ora il
  namespace `pages` tramite `config/livewire.php`.
- Il runtime corrente continua ad avviarsi, ma contiene una registrazione stale
  che rende ambiguo il contratto di bootstrap e puo essere mascherata da cache o
  comportamento tollerante del framework.

### Contratto `.env`

- `.env.example` usa `DB_CONNECTION=sqlite` e non valorizza le variabili
  PostgreSQL richieste da Compose.
- `docker compose --env-file .env.example config` emette warning, produce
  credenziali PostgreSQL vuote e un healthcheck incompleto.
- `.env.example` non dichiara `SCOUT_DRIVER=meilisearch`,
  `MEILISEARCH_URL`, `MEILI_MASTER_KEY` o la configurazione SMTP di Mailpit.
- `config/scout.php` usa ancora `algolia` come default, in conflitto con lo stack
  dichiarato.
- `APP_URL` usa `localhost`, mentre browser tooling e documentazione agentica
  usano `127.0.0.1:8083`.

### Avvio Docker

- `vendor/` e `node_modules/` vivono in volumi Docker inizialmente vuoti.
- `docker compose up -d` avvia PHP-FPM prima che le dipendenze siano installate;
  il quickstart richiede poi comandi manuali separati.
- PostgreSQL e l'unico servizio con healthcheck; Meilisearch e Mailpit sono
  considerati pronti quando il processo e soltanto avviato.
- Migrazioni, seed, build asset e import Scout non sono coordinati da un comando
  di bootstrap idempotente.

### Drift documentale e test

- Il README principale descrive Laravel 12, Vite 7, Node 18 e Docker opzionale;
  il runtime canonico usa Laravel 13, Vite 8, Node 24 e Docker-first.
- `composer setup` e ancora orientato al setup Laravel standard su host.
- `phpunit.xml` usa deliberatamente SQLite in-memory, queue sync, cache/session
  array e Scout null: e un profilo test rapido, non il runtime locale reale.
- La CI verifica questa suite rapida, ma non prova che un checkout pulito possa
  completare il bootstrap Docker.

## Decisioni da confermare prima dell'implementazione

### 1. Runtime locale canonico

Raccomandazione: rendere Docker l'unico runtime locale supportato e documentato.

- PostgreSQL, Meilisearch e Mailpit sono parte del contratto locale.
- SQLite resta supportato esclusivamente dal profilo test rapido definito in
  `phpunit.xml`.
- Il percorso `composer dev`/SQLite su host viene rimosso dal quickstart oppure
  marcato esplicitamente come non canonico e non coperto.

Alternativa: mantenere due setup locali pienamente supportati. Questa scelta
richiederebbe due `.env` di esempio, due smoke test e una matrice di feature
degradate senza Meilisearch/Mailpit, aumentando il costo di manutenzione.

### 2. Queue locale transitoria

Raccomandazione: usare `QUEUE_CONNECTION=sync` nel contratto locale finche il
rilievo dedicato a worker, retry e after-commit non viene implementato.

Usare `database` senza un worker rende il checkout apparentemente funzionante ma
lascia job non processati.

### 3. Seed iniziale

Raccomandazione: il bootstrap base esegue migrazioni ma non seed distruttivi.

- `bootstrap` prepara un'app vuota e funzionante;
- `bootstrap --seed` oppure un comando separato popola dati demo;
- `migrate:fresh --seed` resta riservato a reset espliciti e audit E2E.

### 4. Indice di ricerca

Raccomandazione: il bootstrap applica le impostazioni Scout e, solo con dati
seedati, importa gli eventi. Il task garantisce readiness iniziale, senza
anticipare policy di rebuild e consistenza operativa.

## Matrice ambiente proposta

| Profilo | Database | Search | Queue | Mail | Scopo |
| --- | --- | --- | --- | --- | --- |
| `local` canonico | PostgreSQL Compose | Meilisearch Compose | `sync` temporaneo | Mailpit SMTP | sviluppo e browser/E2E |
| `testing-fast` | SQLite `:memory:` | Scout `null` | `sync` | `array` | Pest rapido e CI attuale |
| `testing-integration` | PostgreSQL | Meilisearch dove richiesto | `sync` | `array`/Mailpit secondo test | smoke infrastrutturale mirato |
| `production` | variabili obbligatorie, nessun default locale | endpoint/key obbligatori se search attiva | driver esplicito | provider esplicito | solo contratto config; topologia fuori scope |

## Disegno del percorso canonico

### Source of truth

Creare una specifica `docs/infrastructure/environments.md` che definisca:

- profili supportati;
- variabili obbligatorie e valori locali non sensibili;
- hostname interni Compose e URL esposti all'host;
- responsabilita di bootstrap, reset e seed;
- differenza tra readiness del servizio e inizializzazione applicativa;
- comandi distruttivi chiaramente separati da quelli idempotenti.

### `.env.example` Compose-ready

Il file deve poter essere copiato in `.env` e produrre una configurazione Compose
valida senza modifiche obbligatorie:

- `APP_URL=http://127.0.0.1:8083`;
- `DB_CONNECTION=pgsql` e `DB_HOST=postgres`;
- credenziali locali di sviluppo dichiarate e non riutilizzabili in produzione;
- `SCOUT_DRIVER=meilisearch` e `MEILISEARCH_URL=http://meilisearch:7700`;
- chiave Meilisearch locale coerente con Compose;
- `MAIL_MAILER=smtp`, `MAIL_HOST=mailpit`, `MAIL_PORT=1025`;
- `QUEUE_CONNECTION=sync` finche manca un worker;
- locale/timezone coerenti con la documentazione.

Le configurazioni sensibili di produzione non devono ricevere fallback locali
silenziosi.

### Comandi operativi

Introdurre due entrypoint distinti:

1. un comando di bootstrap idempotente, ad esempio `./scripts/bootstrap.sh`;
2. un comando diagnostico read-only, ad esempio `./scripts/doctor.sh`.

Il bootstrap deve:

1. creare `.env` da `.env.example` solo se assente;
2. validare i prerequisiti host;
3. costruire e avviare i servizi;
4. attendere health/readiness con timeout;
5. installare Composer e npm nei volumi dedicati;
6. generare `APP_KEY` solo se vuota;
7. pulire le cache Laravel incompatibili;
8. eseguire migrazioni non distruttive;
9. compilare gli asset;
10. applicare le impostazioni Scout;
11. eseguire seed/import solo quando richiesto esplicitamente;
12. terminare con una diagnostica sintetica e actionable.

Il doctor non modifica stato e verifica almeno:

- variabili richieste e assenza di placeholder invalidi;
- `docker compose config`;
- stato/health dei container;
- autoload e bootstrap Artisan;
- connessione PostgreSQL;
- raggiungibilita Meilisearch e Mailpit;
- migrazioni pendenti;
- asset Vite presenti e assenza di `public/hot` stale;
- risposta HTTP dell'app.

## Piano di implementazione

### Fase 0 - Decision record e specifica ambienti

1. Confermare runtime locale canonico, queue transitoria, comportamento seed e
   inizializzazione Scout.
2. Creare `docs/infrastructure/environments.md`.
3. Aggiornare `docs/project/technical-decisions.md` distinguendo runtime locale
   Docker e test rapidi SQLite.
4. Definire variabili obbligatorie, opzionali e vietate per ogni profilo.

Acceptance:

- ogni profilo ha uno scopo e una tecnologia espliciti;
- SQLite non viene piu descritto ambiguamente come runtime locale equivalente;
- nessuna decisione su reset distruttivi resta implicita.

### Fase 1 - Bootstrap Laravel deterministico

1. Rimuovere da `bootstrap/providers.php` la registrazione obsoleta di
   `VoltServiceProvider`.
2. Verificare che tutte le route `pages::` siano risolte tramite
   `config/livewire.php`.
3. Aggiungere un test/bootstrap check che fallisca se un provider registrato non
   esiste.
4. Verificare `composer install` con script abilitati e cache vuote.

Acceptance:

- tutti i provider registrati sono autoloadable;
- `php artisan about` e `php artisan route:list` funzionano senza cache pregresse;
- le pagine Livewire single-file continuano a risolversi.

### Fase 2 - Contratto `.env` e Compose

1. Rendere `.env.example` coerente con il profilo locale Docker.
2. Allineare default applicativi sicuri in `config/database.php` e
   `config/scout.php`, evitando fallback Algolia accidentali.
3. Aggiungere espansioni Compose con errore per variabili realmente obbligatorie
   e default solo per valori locali non sensibili.
4. Aggiungere healthcheck a Meilisearch e dipendenza `service_healthy` per l'app.
5. Valutare healthcheck applicativo separato dalla semplice apertura di PHP-FPM.

Acceptance:

- `cp .env.example .env && docker compose config` non emette warning;
- PostgreSQL riceve database, utente e password locali validi;
- Laravel usa hostname interni Compose, mentre browser e callback usano URL host;
- Scout non tenta mai Algolia nel profilo locale.

### Fase 3 - Bootstrap e doctor idempotenti

1. Implementare gli script con shell strict mode, messaggi brevi e timeout.
2. Rendere ogni passaggio ripetibile senza rigenerare chiavi o cancellare dati.
3. Separare flag `--seed` e un eventuale reset distruttivo in un comando diverso.
4. Gestire volumi dipendenze vuoti e installazioni parziali.
5. Esporre alias Composer o documentazione senza duplicare la logica dello
   script shell.

Acceptance:

- prima esecuzione completa un checkout pulito;
- seconda esecuzione non distrugge DB, chiavi, file o volumi;
- un errore indica componente, causa e comando di remediation;
- nessun passaggio richiede comandi manuali non documentati.

### Fase 4 - Readiness applicativa e inizializzazione search

1. Attendere PostgreSQL e Meilisearch tramite healthcheck reali.
2. Eseguire `scout:sync-index-settings` dopo che Meilisearch e pronto.
3. Importare dati solo quando il bootstrap ha eseguito seed o quando richiesto.
4. Verificare una query search minima nel profilo integration.
5. Documentare che freshness, rebuild e recovery dell'indice restano nel task
   Meilisearch dedicato.

Acceptance:

- il bootstrap non presenta l'app come pronta prima delle dipendenze essenziali;
- una installazione seedata restituisce risultati search senza passaggi nascosti;
- una installazione vuota non fallisce per assenza di record da indicizzare.

### Fase 5 - Fresh-checkout smoke e drift gate

1. Aggiungere uno smoke test eseguibile in worktree/clone temporaneo con nome
   progetto Compose e porte isolate.
2. Verificare bootstrap, migrazioni, route, HTTP, DB e Meilisearch partendo senza
   `.env`, `vendor`, `node_modules`, cache o volumi.
3. Aggiungere un gate leggero CI per provider/config e pianificare il job Docker
   completo senza confonderlo con la suite SQLite.
4. Aggiornare README, quickstart, runtime baseline e comandi frontend.
5. Rimuovere istruzioni Laravel starter-kit obsolete.

Acceptance:

- il test non dipende dallo stato del checkout principale;
- il README contiene un solo quickstart locale canonico;
- versioni e URL coincidono tra README, AGENTS e configurazione;
- drift su provider o variabili Compose obbligatorie produce un fallimento
  automatico.

## Strategia di test

### Test statici e unitari

- tutti i provider dichiarati risultano `class_exists` dopo autoload;
- `.env.example` contiene le chiavi richieste senza segreti reali;
- `docker compose config` e valido con il solo file esempio;
- shell lint sugli script di bootstrap/doctor;
- test dei flag e dei casi idempotenti dove possibile senza Docker.

### Test integration Docker

- checkout temporaneo senza artefatti generati;
- prima esecuzione bootstrap;
- seconda esecuzione bootstrap;
- `artisan about`, `migrate:status`, `route:list`;
- connessione PostgreSQL e health Meilisearch;
- sync settings e query Scout minima;
- risposta HTTP `200` su home e login;
- Mailpit raggiungibile dal container app.

### Regression suite

```bash
docker compose exec -T app composer lint
docker compose exec -T app composer analyse
docker compose exec -T app composer test
npm run frontend:test
./scripts/security/run.sh
```

Se il security gate resta bloccato dagli advisory gia registrati, il risultato va
riportato separatamente dal bootstrap smoke e non mascherato.

## Rollout

1. decision record e provider stale;
2. `.env.example` e validazione Compose;
3. healthcheck e doctor;
4. bootstrap idempotente;
5. inizializzazione Scout;
6. fresh-checkout smoke;
7. allineamento documentale finale.

Ogni fase deve restare eseguibile sul branch senza richiedere la fase successiva.
In particolare, non introdurre un nuovo default queue `database` finche non
esiste un worker nel runtime.

## Definition of Done

- un checkout pulito raggiunge l'app con un unico comando documentato;
- una seconda esecuzione e idempotente e non distruttiva;
- `.env.example` e Compose-ready e non contiene segreti reali;
- tutti i provider registrati esistono;
- PostgreSQL, Meilisearch e Mailpit sono configurati senza passaggi impliciti;
- test rapido SQLite e smoke integration PostgreSQL sono distinti e nominati;
- README, docs agentiche e runtime reale dichiarano le stesse versioni e URL;
- bootstrap, doctor, QA PHP, Playwright e security sono stati eseguiti;
- il rilievo P0 viene rimosso dalla flow map solo dopo fresh-checkout smoke verde.
