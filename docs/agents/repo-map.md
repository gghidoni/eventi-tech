# Repo Map

## Aree principali

- `app/Actions/`: business logic con classi single-responsibility
- `app/Enums/`: enum applicative
- `app/Livewire/`: componenti Livewire classici
- `app/Models/`: model Eloquent
- `packages/`: fork locali di dipendenze applicative mantenute nel repo
- `resources/views/livewire/`: viste componenti
- `resources/views/pages/`: pagine Livewire single-file con prefisso `⚡`
- `database/seeders/`: dati seed, incluse credenziali utili per E2E
- `tests/`: Pest
- `tests/e2e/`: Playwright

## Pattern chiave

- Pages: `Route::livewire(..., 'pages::...')`
- Logica applicativa: action con `execute()`
- UI feedback: evento `messageSent` verso `<livewire:messages />`
- Search: Laravel Scout + Meilisearch

## Credenziali seed utili

- Utenti seedati con password `password` in `database/seeders/UserSeeder.php`
- Riutilizzabili per flussi autenticati Playwright, evitando account reali

## Dove approfondire

- Routing: [../backend/routing.md](../backend/routing.md)
- Livewire: [../backend/livewire.md](../backend/livewire.md)
- Models: [../backend/models.md](../backend/models.md)
- UI base: [../ui/layout.md](../ui/layout.md)
- Frontend testing: [../ui/frontend-testing.md](../ui/frontend-testing.md)
