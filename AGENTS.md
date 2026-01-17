# Eventi Tech Livewire

Applicazione Laravel 12 + Livewire 4 per la gestione di eventi tech e community.

## Stack Tecnologico

- **Backend:** Laravel 12.x, Livewire 4.x, PHP 8.4
- **Frontend:** Vite 7.x, Tailwind CSS 4.x, Flatpickr
- **Database:** PostgreSQL 15 (Docker) / SQLite (locale)
- **Search:** Meilisearch + Laravel Scout
- **Testing:** Pest PHP 4.x
- **Static Analysis:** Larastan level 5
- **Code Style:** Laravel Pint

## Struttura Progetto

- `app/Actions/` - Classi action single-responsibility
- `app/Enums/` - PHP 8.1+ Enums (EventType, EventStatus, CommunityStatus)
- `app/Livewire/` - Componenti Livewire
- `app/Models/` - Eloquent models
- `app/Models/AddressBook/` - Models geografici (Region, Province, City)
- `database/migrations/` - Migrazioni database
- `database/seeders/` - Seeders con immagini placeholder
- `resources/views/livewire/` - Viste componenti Livewire
- `resources/views/pages/` - Pagine Volt (prefisso lightning bolt)

## Convenzioni di Codice

- Usa PHP 8.4 features (constructor promotion, typed properties, enums)
- Segui Laravel Pint preset con regole custom in `pint.json`
- Mantieni PHPStan level 5 senza errori
- Classi ordinate secondo `ordered_class_elements` in pint.json
- Usa strict comparison (`===`)
- Import globali per classi, costanti e funzioni

## Comandi Utili

```bash
composer dev          # Avvia ambiente sviluppo
composer test         # Esegue test Pest
vendor/bin/pint       # Formatta codice
vendor/bin/phpstan analyse  # Analisi statica
docker-compose up -d  # Avvia Docker
```

## Docker

Servizi disponibili:
- App: http://localhost:8083
- Mailpit: http://localhost:8025
- Meilisearch: http://localhost:7700
- PostgreSQL: localhost:5432

## Pattern Livewire

- Componenti in `app/Livewire/`
- Viste in `resources/views/livewire/`
- Usa Form Objects per validazione complessa
- Usa Actions per logica di business
