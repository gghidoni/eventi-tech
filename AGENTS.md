# Eventi Tech Livewire

Entry point agentico del repository.

## Regole non negoziabili

- Prima di proporre o integrare codice, consulta sempre le specifiche rilevanti in `docs/`.
- Se la specifica in `docs/` manca, non e chiara o e in conflitto, fermati e chiedi chiarimenti prima di implementare.
- In caso di conflitto tra assunzioni e documentazione, ha priorita la documentazione in `docs/`.
- Non sovrascrivere o revertire modifiche utente non richieste.

## Fast Start

- Stack: Laravel 13, Livewire 4, PHP 8.4, Tailwind 4, Vite 8, PostgreSQL 18, Meilisearch 1.44.
- App locale Docker-first: `http://127.0.0.1:8083`
- Container principali: `eventi-tech`, `eventi-tech-nginx`, `eventi-tech-postgres`, `eventi-tech-meilisearch`, `eventi-tech-mailpit`
- Frontend browser tooling: `Playwright` per navigazione e smoke test, `Lighthouse` per audit
- Source of truth funzionale e tecnica: `docs/`

## Verifica finale obbligatoria

- Se tocchi codice PHP, esegui sempre alla fine `docker compose exec -T app composer lint` e `docker compose exec -T app composer analyse`.
- Se la modifica non e puramente locale o cosmetica, esegui anche `docker compose exec -T app composer test` oppure `docker compose exec -T app composer qa`.
- Se tocchi UI o asset, verifica anche il frontend con `npm run frontend:test` e usa `npm run frontend:audit` quando serve audit qualitativo.
- Se tocchi dipendenze, auth, upload, middleware, query raw o config sensibile, esegui anche `./scripts/security/run.sh`.

## Percorso consigliato

1. Leggi [docs/agents/README.md](docs/agents/README.md)
2. Apri il quickstart in [docs/agents/quickstart.md](docs/agents/quickstart.md)
3. Se devi toccare codice, consulta le spec mirate in `docs/backend/*` o `docs/ui/*`
4. Se devi usare tool operativi, consulta [docs/agents/tools.md](docs/agents/tools.md)

## Entry point rapidi

- Workflow operativo: [docs/agents/workflow.md](docs/agents/workflow.md)
- Mappa repo e convenzioni: [docs/agents/repo-map.md](docs/agents/repo-map.md)
- Tooling agente: [docs/agents/tools.md](docs/agents/tools.md)
- Security workflow: [docs/agents/security.md](docs/agents/security.md)
- Frontend testing e audit: [docs/ui/frontend-testing.md](docs/ui/frontend-testing.md)
