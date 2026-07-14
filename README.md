# Eventi Tech

A modern platform for discovering and managing tech events and communities in Italy.

## Overview

Eventi Tech is a web application designed to connect tech enthusiasts with events and communities in their area. Whether you're an organizer looking to promote your meetups, conferences, or workshops, or a developer searching for the next interesting event to attend, this platform provides the tools you need.

Built with Laravel 13 and Livewire 4, the application offers a reactive, modern user experience without the complexity of a separate JavaScript framework.

For repository-oriented documentation, start from:

- `docs/project/README.md` for product intent, architecture, and technical decisions
- `docs/agents/README.md` for agent workflow and operational tooling

## Features

- **Event Discovery** - Browse and search tech events with filters for location and keywords
- **Community Management** - Create and manage your tech community profile
- **Event Creation** - Publish events with details, posters, and ticket links
- **Bookmarking System** - Save interesting events to your personal list
- **User Dashboard** - Manage your communities, events, and bookmarks in one place
- **Full-text Search** - Fast, typo-tolerant search powered by Meilisearch
- **Responsive Design** - Optimized experience across desktop and mobile devices

## Tech Stack

| Category | Technology |
|----------|------------|
| Backend | Laravel 13, PHP 8.4 |
| Frontend | Livewire 4, Tailwind CSS 4, Vite 8 |
| Database | PostgreSQL 18 (Docker); SQLite only for fast tests |
| Search | Meilisearch + Laravel Scout |
| Auth | Laravel Fortify |
| Testing | Pest PHP 4 |
| Static Analysis | Larastan (level 5) |
| Code Style | Laravel Pint |

## Quick Start

### Prerequisites

- Docker with Compose v2
- `curl`

### Canonical Docker setup

```bash
# Idempotent, non-destructive bootstrap
./scripts/bootstrap.sh

# Optional demo data and search import on an empty database
./scripts/bootstrap.sh --seed

# Read-only environment diagnostics
./scripts/doctor.sh
```

**Available services:**

| Service | URL |
|---------|-----|
| Application | http://127.0.0.1:8083 |
| Mailpit (Email) | http://127.0.0.1:8025 |
| Meilisearch | http://127.0.0.1:7700 |
| PostgreSQL | 127.0.0.1:5432 |

`vendor/` and `node_modules/` live in Docker volumes managed by the `app`
service. SQLite is deliberately limited to the fast Pest profile. See
`docs/infrastructure/environments.md` for the complete contract.

## Project Structure

```
app/
├── Actions/           # Single-responsibility action classes
├── Enums/             # PHP 8.1+ Enums (EventType, EventStatus, CommunityStatus)
├── Livewire/          # Livewire components
├── Models/            # Eloquent models
│   └── AddressBook/   # Geographic models (Region, Province, City)
database/
├── factories/         # Model factories for testing
├── migrations/        # Database migrations
├── seeders/           # Database seeders
resources/views/
├── livewire/          # Livewire component views
├── pages/             # Volt pages
tests/
├── Feature/           # Feature/integration tests
└── Unit/              # Unit tests (Actions, Models)
```

## Documentation Map

- `docs/project/*` explains what the project is, why it exists, and how it is structured
- `docs/backend/*` documents backend patterns already present in code
- `docs/ui/*` documents UI patterns and frontend tooling
- `docs/agents/*` documents how an agent should work in this repository

## Development

### Running Tests

```bash
# Run all tests
docker compose exec -T app composer test

# Or directly with Pest
docker compose exec -T app ./vendor/bin/pest

# Run specific test suite
docker compose exec -T app ./vendor/bin/pest --testsuite=Unit
docker compose exec -T app ./vendor/bin/pest --testsuite=Feature
```

### Code Quality

```bash
# Static analysis with PHPStan (level 5)
docker compose exec -T app composer analyse

# Code formatting with Pint
docker compose exec -T app composer lint
```

### Useful Commands

```bash
# Re-run the idempotent environment bootstrap
./scripts/bootstrap.sh

# Import Italian cities, provinces, and regions
docker compose exec -T app php artisan app:insert-city-province-region

# Index models for Meilisearch
docker compose exec -T app php artisan scout:import "App\Models\Event"
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
