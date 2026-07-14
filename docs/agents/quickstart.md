# Agent Quickstart

## Stack

- Backend: Laravel 13, Livewire 4, PHP 8.4
- Frontend: Tailwind 4, Vite 8, Flatpickr
- Data: PostgreSQL 18, Meilisearch 1.44
- Test: Pest, Playwright, Lighthouse

## Ambiente locale

- URL principale: `http://127.0.0.1:8083`
- Vite dev server: `5173` solo quando attivo esplicitamente
- Mailpit: `http://127.0.0.1:8025`
- Meilisearch: `http://127.0.0.1:7700`
- `vendor/` e `node_modules/` vivono nel container tramite volumi Docker dedicati
- Bootstrap idempotente: `./scripts/bootstrap.sh`
- Diagnostica read-only: `./scripts/doctor.sh`
- Contratto completo: [../infrastructure/environments.md](../infrastructure/environments.md)

## Lettura DB locale via MCP

- Workflow canonico: [mcp-postgres.md](./mcp-postgres.md)
- Server consigliato: `eventi-tech-db`
- Trasporto: processo locale stdio custom, senza container MCP dedicato
- Credenziali: solo utente PostgreSQL dedicato read-only, mai utente applicativo Laravel
- Setup locale: `./scripts/agents/setup-mcp-postgres.sh`
- Test: `node scripts/agents/test-mcp-postgres.mjs`

## Lettura email locali via MCP

- Workflow canonico: [mcp-mailpit.md](./mcp-mailpit.md)
- Server consigliato: `eventi-tech-mailpit`
- Trasporto: processo locale stdio custom, senza container MCP dedicato
- API letta: Mailpit locale `http://127.0.0.1:8025`
- Test: `node scripts/agents/test-mcp-mailpit.mjs`

## Documentazione esterna via Context7

- Workflow canonico: [mcp-context7.md](./mcp-context7.md)
- Server: `context7`
- Trasporto: processo locale stdio avviato da `.codex/bin/context7-mcp.sh`
- Credenziale: `CONTEXT7_API_KEY` nell'ambiente o nel `.env` locale non versionato

## Comandi essenziali

```bash
./scripts/bootstrap.sh
./scripts/doctor.sh
docker compose exec -T app composer lint
docker compose exec -T app composer analyse
docker compose exec -T app composer test
docker compose exec -T app composer qa
docker compose exec -T app composer security
./scripts/security/run.sh
docker compose exec -T app ./vendor/bin/phpstan analyse
docker compose exec -T app ./vendor/bin/pint
npm run frontend:test
npm run frontend:audit
```

## Scelta rapida del percorso

- Devi capire il dominio o i pattern Laravel/Livewire: apri `docs/backend/*`
- Devi toccare UI o componenti Blade/Livewire: apri `docs/ui/*`
- Devi usare strumenti esterni o browser automation: apri [tools.md](./tools.md)
- Devi testare tutti i flussi applicativi end to end: apri [flow-audit/README.md](./flow-audit/README.md)
- Devi verificare dependency risk o sicurezza applicativa: apri [security.md](./security.md)
- Devi decidere come procedere prima di modificare codice: apri [workflow.md](./workflow.md)
