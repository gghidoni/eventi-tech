# Eventi Tech Livewire

Entry point agentico del repository.

## Regole non negoziabili

- Prima di proporre o integrare codice, consulta sempre le specifiche rilevanti in `docs/`.
- Se la specifica in `docs/` manca, non e chiara o e in conflitto, fermati e chiedi chiarimenti prima di implementare.
- In caso di conflitto tra assunzioni e documentazione, ha priorita la documentazione in `docs/`.
- Non sovrascrivere o revertire modifiche utente non richieste.

## Fast Start

- Stack: Laravel 12, Livewire 4, PHP 8.4, Tailwind 4, Vite 7, PostgreSQL, Meilisearch.
- App locale Docker-first: `http://127.0.0.1:8083`
- Container principali: `eventi-tech`, `eventi-tech-nginx`, `eventi-tech-postgres`, `eventi-tech-meilisearch`, `eventi-tech-mailpit`
- Frontend browser tooling: `Playwright` per navigazione e smoke test, `Lighthouse CI` per audit
- Source of truth funzionale e tecnica: `docs/`

## Percorso consigliato

1. Leggi [docs/agents/README.md](docs/agents/README.md)
2. Apri il quickstart in [docs/agents/quickstart.md](docs/agents/quickstart.md)
3. Se devi toccare codice, consulta le spec mirate in `docs/backend/*` o `docs/ui/*`
4. Se devi usare tool operativi, consulta [docs/agents/tools.md](docs/agents/tools.md)

## Entry point rapidi

- Workflow operativo: [docs/agents/workflow.md](docs/agents/workflow.md)
- Mappa repo e convenzioni: [docs/agents/repo-map.md](docs/agents/repo-map.md)
- Tooling agente: [docs/agents/tools.md](docs/agents/tools.md)
- Frontend testing e audit: [docs/ui/frontend-testing.md](docs/ui/frontend-testing.md)
