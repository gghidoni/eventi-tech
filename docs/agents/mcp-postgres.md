# MCP PostgreSQL Locale

## Scopo

Questa e la procedura canonica per leggere il database PostgreSQL locale tramite MCP.

Skill operativa associata: `$eventi-tech-db` in `.agents/skills/eventi-tech-db`.

Usala quando un agente deve:

- ispezionare schema, tabelle o colonne del DB locale
- leggere dati seedati o stato runtime locale
- verificare effetti applicativi persistiti
- confrontare codice Laravel/Eloquent con lo stato reale del database

Non usarla per:

- database di produzione, staging o ambienti condivisi
- modifiche dati o migrazioni
- operazioni amministrative distruttive
- aggirare test, factory o seeders quando quelli sono la fonte corretta

## Scelta tecnica

Usa il server MCP PostgreSQL locale custom del repo.

Non serve un container MCP dedicato. Il container necessario e solo PostgreSQL, gia definito da `docker-compose.yml` come servizio `postgres` e container `eventi-tech-postgres`.

Il server MCP configurato nel repo e `eventi-tech-db`, definito in `.codex/config.toml`.

Il trasporto stdio usa JSON-RPC delimitato da newline, come previsto dalla specifica MCP. Non usare framing `Content-Length`.

Componenti:

- `scripts/agents/mcp-postgres-server.mjs`: server MCP stdio custom, senza dipendenze npm esterne
- `scripts/agents/run-mcp-postgres.sh`: runner usato da Codex
- `scripts/agents/setup-mcp-postgres.sh`: setup ruolo PostgreSQL read-only e URL locale
- `scripts/agents/test-mcp-postgres.mjs`: smoke test protocollo MCP + tool DB

Il runner locale e:

```bash
./scripts/agents/run-mcp-postgres.sh
```

Se la variabile `EVENTI_TECH_MCP_DATABASE_URL` non e presente, legge l'URL da `.codex/mcp-postgres.url`.

Il server espone solo tool di lettura. La protezione principale sta comunque nel database tramite un utente dedicato solo lettura.

Per velocita, il server mantiene un processo `psql` persistente dentro il container PostgreSQL. La prima lettura puo pagare il warm-up Docker; le letture successive riusano la stessa sessione e sono molto piu rapide.

## Tool disponibili

- `db_schema`: schema compatto di tutte le tabelle leggibili non di sistema
- `db_tables`: elenco tabelle con schema, tipo e stima righe
- `db_describe_table`: dettaglio colonne, vincoli, foreign key e indici di una tabella
- `db_sample`: piccolo campione righe da una tabella con valori stringa troncati
- `db_query`: singola query `SELECT`, `WITH` o `VALUES`, con limite esterno forzato
- `db_explain`: `EXPLAIN (FORMAT JSON)` per query di lettura

Per risparmiare token, usa in ordine:

1. `db_tables` per orientarti.
2. `db_describe_table` sulla tabella interessata.
3. `db_sample` solo se servono esempi di valori.
4. `db_query` solo per domande mirate.
5. `db_schema` quando serve una vista globale delle relazioni.

## Prerequisiti

Avvia lo stack locale:

```bash
docker compose up -d
```

Verifica che PostgreSQL sia disponibile sul Mac tramite la porta esposta dal compose:

```bash
docker compose ps postgres
```

Il valore predefinito e `127.0.0.1:5432`, salvo override di `FORWARD_DB_PORT`.

## Setup rapido

Esegui:

```bash
./scripts/agents/setup-mcp-postgres.sh
```

Lo script:

- crea o aggiorna il ruolo PostgreSQL `mcp_readonly`
- assegna permessi solo lettura sul database locale
- forza `default_transaction_read_only`
- imposta `statement_timeout` a `5s`
- scrive l'URL locale in `.codex/mcp-postgres.url`

Il file `.codex/mcp-postgres.url` e ignorato da Git e non deve essere committato.

Puoi sovrascrivere i default con variabili locali:

```bash
MCP_POSTGRES_PASSWORD='password-locale' ./scripts/agents/setup-mcp-postgres.sh
```

## Utente read-only manuale

Non configurare MCP con l'utente applicativo Laravel.

Crea invece un utente locale dedicato, per esempio `mcp_readonly`, con password locale non committata.

Questa sezione serve solo se non vuoi usare `./scripts/agents/setup-mcp-postgres.sh`.

Entra in `psql` nel container PostgreSQL:

```bash
docker compose exec -T postgres sh -lc 'psql -U "$POSTGRES_USER" -d "$POSTGRES_DB"'
```

Esegui SQL equivalente:

```sql
CREATE ROLE mcp_readonly LOGIN PASSWORD 'local-password-not-committed';

GRANT CONNECT ON DATABASE eventi_tech_db TO mcp_readonly;
GRANT USAGE ON SCHEMA public TO mcp_readonly;
GRANT SELECT ON ALL TABLES IN SCHEMA public TO mcp_readonly;

ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT SELECT ON TABLES TO mcp_readonly;

ALTER ROLE mcp_readonly SET default_transaction_read_only = on;
ALTER ROLE mcp_readonly SET statement_timeout = '5s';
```

Se il database viene ricreato da zero, ricrea anche questo ruolo e i grant.

## Configurazione MCP

Preferisci configurazione locale dell'utente, fuori dal repository, quando contiene password.

La configurazione Codex e gia presente in `.codex/config.toml`:

```toml
[mcp_servers.eventi-tech-db]
command = "bash"
args = ["./scripts/agents/run-mcp-postgres.sh"]
startup_timeout_sec = 20
tool_timeout_sec = 30
default_tools_approval_mode = "auto"
```

Il runner usa, in ordine:

1. `EVENTI_TECH_MCP_DATABASE_URL`, se presente
2. `.codex/mcp-postgres.url`, generato dallo script di setup

Formato della variabile:

```bash
EVENTI_TECH_MCP_DATABASE_URL=postgresql://mcp_readonly:local-password-not-committed@127.0.0.1:5432/eventi_tech_db
```

Non committare password reali in `.codex/config.toml`, `.env`, documentazione o issue.

## Regole d'uso per agenti

Prima di leggere il DB:

1. Consulta questa pagina.
2. Verifica che lo stack Docker locale sia attivo.
3. Usa solo il server MCP `eventi-tech-db`.
4. Esegui query mirate e con `LIMIT` quando leggi tabelle applicative.
5. Evita di selezionare campi sensibili se non servono al task.
6. Riporta sempre se una conclusione deriva dal DB locale, dal codice o dalla documentazione.

Se il server MCP non e configurato o il ruolo read-only manca, non usare credenziali applicative come fallback implicito. Chiedi o prepara prima il setup read-only.

## Smoke test

Esegui:

```bash
node scripts/agents/test-mcp-postgres.mjs
```

Il test valida:

- handshake MCP stdio
- lista tool
- lettura elenco tabelle
- lettura schema globale
- descrizione tabella
- sample tabella
- query read-only come `mcp_readonly`
- `EXPLAIN`
- blocco query di scrittura
- blocco multi-statement
- blocco CTE con write
- recupero dopo errore SQL PostgreSQL
- latenze indicative dei tool
- metadati interni `_mcp` con tempo server/DB per distinguere overhead Codex da tempo query reale

Se il test fallisce per permessi Docker nel sandbox, rilancialo con permesso esplicito al Docker locale.

## Estensioni future

Valuta tool di dominio solo se riducono davvero rumore e token, per esempio `find_open_cfps` o `inspect_cfp_submissions`.

I tool di dominio devono restare read-only, usare query allowlistate e venire documentati qui prima dell'uso.
