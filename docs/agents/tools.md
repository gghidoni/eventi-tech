# Agent Tools

## Principio

Usa il tool piu alto livello che risolve il problema in modo affidabile. Evita di usare strumenti diagnostici low-level come prima scelta se esiste gia un workflow stabile nel repo.

## Context7

Usalo quando serve documentazione aggiornata di librerie o framework.

### Best practice

1. Risolvi prima il library ID corretto.
2. Interroga solo la libreria rilevante al task.
3. Preferisci documentazione ufficiale o primaria.
4. Non usare Context7 per inferire il comportamento del codice locale: per quello leggi il repo.
5. Se la doc esterna e in conflitto con `docs/` o con il codice reale del progetto, segnala il conflitto e verifica prima di implementare.

## MCP PostgreSQL

Per leggere schema o dati del PostgreSQL locale, usa il workflow dedicato in [mcp-postgres.md](./mcp-postgres.md).

Regole operative:

- server MCP canonico: `eventi-tech-db`
- server custom locale in `scripts/agents/mcp-postgres-server.mjs`
- avvio tramite `.codex/config.toml` e `./scripts/agents/run-mcp-postgres.sh`, senza container MCP dedicato e senza pacchetti npm MCP generici
- connessione al PostgreSQL Docker esposto su `127.0.0.1:${FORWARD_DB_PORT:-5432}`
- utente DB dedicato read-only, non utente Laravel
- preferisci `db_tables`, `db_describe_table` e `db_sample` prima di `db_query`
- query mirate, con limite esplicito quando leggi tabelle applicative
- nessuna password reale in file versionati

## MCP Mailpit

Per verificare email locali catturate da Mailpit, usa il workflow dedicato in [mcp-mailpit.md](./mcp-mailpit.md).

Regole operative:

- server MCP canonico: `eventi-tech-mailpit`
- server custom locale in `scripts/agents/mcp-mailpit-server.mjs`
- avvio tramite `.codex/config.toml` e `./scripts/agents/run-mcp-mailpit.sh`
- solo endpoint API Mailpit di lettura
- evita l'endpoint summary Mailpit che marca i messaggi come letti
- preferisci `mailpit_latest_for`, `mailpit_identify` e `mailpit_extract_links`
- usa preview body solo quando serve davvero

## QA Backend

Per modifiche PHP, il baseline del repo e:

- `docker exec eventi-tech composer lint`
- `docker exec eventi-tech composer analyse`

Per una passata completa:

```bash
docker exec eventi-tech composer qa
```

`composer qa` esegue in sequenza:

- Pint in modalita test
- Larastan / PHPStan
- suite test backend

## QA Security

Per verifiche di sicurezza del repo, il baseline e:

```bash
./scripts/security/run.sh
```

La suite esegue in sequenza:

- `composer audit` sulle dipendenze PHP runtime
- `npm audit --omit=dev` sulle dipendenze frontend runtime
- `Semgrep` su codice PHP e ricerca segreti

Approfondimento: [security.md](./security.md)

### Casi tipici

- Laravel / Livewire / Tailwind / Playwright / Lighthouse
- API o opzioni di libreria non stabili nel tempo
- best practice operative di tool di terze parti

## Playwright

E il runner principale per browser automation nel repo.

### Usalo per

- navigare l'app
- verificare errori JS client-side
- intercettare request fallite
- validare flussi utente reali
- raccogliere trace, screenshot e video

### Regole pratiche

- esegui contro `http://127.0.0.1:8083`
- avvia prima `docker compose up -d`
- usa utenti seedati per flussi autenticati
- non dipendere da una sessione Chrome personale gia aperta
- se fallisce il launch browser, separa subito problema ambiente da bug applicativo

### Comandi

```bash
npm run frontend:install
npm run frontend:test
npm run frontend:test:headed
npm run frontend:test:debug
```

Approfondimento: [../ui/frontend-testing.md](../ui/frontend-testing.md)

## Lighthouse

E il tool di audit per performance, accessibility, best practices e SEO.

### Usalo per

- raccogliere score di categoria
- produrre report HTML/JSON persistiti
- rilevare regressioni qualitative del frontend

### Regole pratiche

- esegui contro l'app Docker locale
- usa asset buildati stabili
- tratta i warning come backlog tecnico, non tutti come blocker immediati
- conserva i report in `storage/testing/lighthouse`

### Comando

```bash
npm run frontend:audit
```

## Chrome DevTools

Non e il driver principale del repo.

Usalo solo come supporto diagnostico o quando serve un attach a una sessione browser reale. Per test ripetibili, preferisci Playwright.
