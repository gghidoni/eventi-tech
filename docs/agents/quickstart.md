# Agent Quickstart

## Stack

- Backend: Laravel 12, Livewire 4, PHP 8.4
- Frontend: Tailwind 4, Vite 7, Flatpickr
- Data: PostgreSQL 15, Meilisearch
- Test: Pest, Playwright, Lighthouse CI

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
docker exec eventi-tech composer test
docker exec eventi-tech ./vendor/bin/phpstan analyse
docker exec eventi-tech ./vendor/bin/pint
npm run frontend:test
npm run frontend:audit
```

## Scelta rapida del percorso

- Devi capire il dominio o i pattern Laravel/Livewire: apri `docs/backend/*`
- Devi toccare UI o componenti Blade/Livewire: apri `docs/ui/*`
- Devi usare strumenti esterni o browser automation: apri [tools.md](./tools.md)
- Devi decidere come procedere prima di modificare codice: apri [workflow.md](./workflow.md)
