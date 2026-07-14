# Infrastruttura: ambienti e bootstrap

## Scopo

Questa specifica e la source of truth per il bootstrap del repository e per i
profili ambiente supportati.

## Profili supportati

| Profilo | Database | Search | Queue | Mail | Scopo |
| --- | --- | --- | --- | --- | --- |
| `local` | PostgreSQL Compose | Meilisearch Compose | `sync` | Mailpit SMTP | sviluppo, browser ed E2E |
| `testing-fast` | SQLite `:memory:` | Scout `null` | `sync` | `array` | Pest rapido e CI |
| `testing-integration` | PostgreSQL | Meilisearch quando richiesto | `sync` | Mailpit/`array` | smoke infrastrutturale |
| `production` | configurazione esplicita | configurazione esplicita | configurazione esplicita | provider esplicito | contratto config; topologia fuori scope |

Docker e l'unico runtime locale canonico. SQLite non e un runtime locale
equivalente: e usato esclusivamente dalla suite test rapida configurata in
`phpunit.xml`.

## Bootstrap locale canonico

Prerequisiti host:

- Docker con Compose v2;
- `curl` per la diagnostica;
- Git e `tar` solo per il fresh-checkout smoke.

Prima installazione:

```bash
./scripts/bootstrap.sh
```

Con dati demo e import degli eventi in Meilisearch:

```bash
./scripts/bootstrap.sh --seed
```

Il comando base e idempotente e non distruttivo:

- crea `.env` solo se manca;
- non rigenera una `APP_KEY` esistente;
- usa `migrate`, mai `migrate:fresh`;
- non esegue seed senza `--seed`;
- reinstalla in modo deterministico le dipendenze dai lockfile;
- compila gli asset e sincronizza le impostazioni Scout.

I dati demo non costituiscono una migrazione idempotente. Il flag `--seed` va
usato su un database vuoto; per ricreare deliberatamente la baseline E2E usare
la procedura distruttiva documentata nella runtime baseline.

La fixture geografica `storage/app/private/comuni.json` e versionata: il seed
dei comuni non dipende da file gia presenti sulla macchina o da download
impliciti.

## Diagnostica read-only

```bash
./scripts/doctor.sh
```

Il doctor non modifica stato e verifica:

- contratto `.env` locale;
- interpolazione Compose;
- servizi running e health;
- provider Laravel registrati;
- bootstrap Artisan e connessione DB;
- assenza di migrazioni pending;
- endpoint Meilisearch e Mailpit;
- manifest Vite e assenza di `public/hot` stale;
- risposta HTTP dell'app.

## Variabili locali

`.env.example` deve poter essere copiato senza modifiche obbligatorie.

Valori interni ai container:

- `DB_HOST=postgres`;
- `MEILISEARCH_URL=http://meilisearch:7700`;
- `MAIL_HOST=mailpit`.

Valori usati dall'host:

- app: `http://127.0.0.1:8083`;
- Meilisearch: `http://127.0.0.1:7700`;
- Mailpit: `http://127.0.0.1:8025`;
- PostgreSQL: `127.0.0.1:5432`.

Le credenziali presenti nel file esempio sono esclusivamente locali e non
devono essere riutilizzate in produzione.

Le variabili `FORWARD_*` e `*_CONTAINER_NAME` consentono allo smoke test di
avviare uno stack isolato. Non cambiano gli hostname interni dei servizi.

## Queue locale

Il profilo locale usa temporaneamente `QUEUE_CONNECTION=sync` perche Compose non
include ancora un worker. Passare a `database` senza worker lascerebbe job non
processati.

Worker, retry, failed jobs e dispatch after-commit sono definiti dal rilievo
architetturale dedicato e non da questo bootstrap.

## Search iniziale

Il bootstrap esegue sempre:

```bash
php artisan scout:sync-index-settings
```

Con `--seed` esegue anche:

```bash
php artisan scout:import "App\Models\Event"
```

Rebuild, recovery e policy di freshness restano nel task dedicato al lifecycle
Meilisearch.

## Reset deliberatamente distruttivo

Solo per audit e dati locali sacrificabili:

```bash
docker compose exec -T app php artisan migrate:fresh --seed
docker compose exec -T app php artisan scout:import "App\Models\Event"
```

Questi comandi non fanno parte del bootstrap e non devono essere eseguiti
implicitamente.

## Fresh-checkout smoke

```bash
./scripts/testing/fresh-bootstrap-smoke.sh
```

Lo script copia i file versionabili in una directory temporanea, assegna nomi
container, progetto Compose e porte isolati, verifica una query Scout su dati
seedati, esegue due bootstrap consecutivi e infine elimina soltanto stack e
directory temporanei creati dallo smoke. La directory nasce sotto `storage/app`
per usare un path gia condiviso con Docker Desktop.

## Contratto produzione

La produzione non eredita i fallback locali. Devono essere espliciti almeno:

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL`, `APP_KEY`;
- connessione PostgreSQL e credenziali gestite come secret;
- driver queue con relativo worker;
- driver cache/session coerenti con la topologia;
- endpoint e chiave search se Scout e attivo;
- mailer e credenziali reali;
- storage persistente.

Deploy, TLS, secret manager, backup/restore, scheduler, osservabilita e disaster
recovery appartengono alla futura specifica di topologia production.
