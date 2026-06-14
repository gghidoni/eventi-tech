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

## Comandi essenziali

```bash
docker compose up -d
docker exec eventi-tech composer install
docker exec eventi-tech npm install
docker exec eventi-tech composer lint
docker exec eventi-tech composer analyse
docker exec eventi-tech composer test
docker exec eventi-tech composer qa
docker exec eventi-tech composer security
./scripts/security/run.sh
docker exec eventi-tech ./vendor/bin/phpstan analyse
docker exec eventi-tech ./vendor/bin/pint
npm run frontend:test
npm run frontend:audit
```

## Scelta rapida del percorso

- Devi capire il dominio o i pattern Laravel/Livewire: apri `docs/backend/*`
- Devi toccare UI o componenti Blade/Livewire: apri `docs/ui/*`
- Devi usare strumenti esterni o browser automation: apri [tools.md](./tools.md)
- Devi verificare dependency risk o sicurezza applicativa: apri [security.md](./security.md)
- Devi decidere come procedere prima di modificare codice: apri [workflow.md](./workflow.md)
