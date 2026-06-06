# Eventi Tech

A modern platform for discovering and managing tech events and communities in Italy.

## Overview

Eventi Tech is a web application designed to connect tech enthusiasts with events and communities in their area. Whether you're an organizer looking to promote your meetups, conferences, or workshops, or a developer searching for the next interesting event to attend, this platform provides the tools you need.

Built with Laravel 12 and Livewire 4, the application offers a reactive, modern user experience without the complexity of a separate JavaScript framework.

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
| Backend | Laravel 12, PHP 8.4 |
| Frontend | Livewire 4, Tailwind CSS 4, Vite 7 |
| Database | PostgreSQL 15 (Docker) / SQLite (local) |
| Search | Meilisearch + Laravel Scout |
| Auth | Laravel Fortify |
| Testing | Pest PHP 4 |
| Static Analysis | Larastan (level 5) |
| Code Style | Laravel Pint |

## Quick Start

### Prerequisites

- PHP 8.4+
- Composer
- Node.js 18+
- Docker & Docker Compose (optional, for PostgreSQL/Meilisearch)

### Local Setup (SQLite)

```bash
# Clone the repository
git clone <repository-url>
cd eventi-tech-livewire

# Install dependencies and setup
composer setup

# Start development server
composer dev
```

The application will be available at `http://localhost:8000`.

### Docker Setup (PostgreSQL)

```bash
# Start all services
docker-compose up -d

# Install dependencies inside container-managed volumes
docker exec eventi-tech composer install
docker exec eventi-tech npm install

# Run migrations inside container
docker exec eventi-tech php artisan migrate

# Seed the database (optional)
docker exec eventi-tech php artisan db:seed
```

**Available services:**

| Service | URL |
|---------|-----|
| Application | http://localhost:8083 |
| Mailpit (Email) | http://localhost:8025 |
| Meilisearch | http://localhost:7700 |
| PostgreSQL | localhost:5432 |

For Docker-based development, `vendor/` and `node_modules/` are expected to live in Docker volumes managed by the `app` container.

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
composer test

# Or directly with Pest
./vendor/bin/pest

# Run specific test suite
./vendor/bin/pest --testsuite=Unit
./vendor/bin/pest --testsuite=Feature
```

### Code Quality

```bash
# Static analysis with PHPStan (level 5)
./vendor/bin/phpstan analyse

# Code formatting with Pint
./vendor/bin/pint
```

### Useful Commands

```bash
# Start development environment (server, queue, logs, vite)
composer dev

# Import Italian cities, provinces, and regions
php artisan app:insert-city-province-region

# Index models for Meilisearch
php artisan scout:import "App\Models\Event"
```

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
