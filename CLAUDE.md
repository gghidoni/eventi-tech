# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Docker Environment

The project runs inside Docker. The PHP app container is `eventi-tech`. All commands except `git` must be executed inside the container via `docker exec`:

```bash
docker exec eventi-tech <command>
```

**Services:** app (`eventi-tech`, PHP 8.4-FPM), nginx (`eventi-tech-nginx`, port 8083), postgres (`eventi-tech-postgres`, port 5432), mailpit (`eventi-tech-mailpit`, port 8025), meilisearch (`eventi-tech-meilisearch`, port 7700).

## Commands

All commands below must be prefixed with `docker exec eventi-tech`:

```bash
# Full setup (dependencies, .env, key, migrations, npm)
docker exec eventi-tech composer setup

# Run all tests (Pest PHP, SQLite in-memory)
docker exec eventi-tech composer test

# Run a single test file
docker exec eventi-tech ./vendor/bin/pest tests/Unit/Actions/CreateEventTest.php

# Run a single test by name
docker exec eventi-tech ./vendor/bin/pest --filter="test_event_is_created_successfully"

# Static analysis (Larastan level 5)
docker exec eventi-tech ./vendor/bin/phpstan analyse

# Code formatting
docker exec eventi-tech ./vendor/bin/pint

# Import geographic data (cities, provinces, regions)
docker exec eventi-tech php artisan app:insert-city-province-region

# Index search models
docker exec eventi-tech php artisan scout:import "App\Models\Event"
```

## Architecture

### Livewire 4 Volt SFC Pages

Pages are **single-file components** (Volt SFC) at `resources/views/pages/` with `⚡` prefix. Each file contains PHP logic and Blade template in one file:

```php
<?php
new class extends Component {
    public function mount() { /* ... */ }
    public function rendering($view) {
        $view->layout('components.layouts.base', ['title' => '...']);
    }
    public function save(SomeAction $action) { /* ... */ }
}; ?>
<div class="page">
    <form wire:submit="save">...</form>
</div>
```

Routes bind directly to these SFC pages via `Route::livewire()`:
```php
Route::livewire('/', 'pages::dashboard.index')->name('dashboard.index');
```

### Action Classes

Business logic lives in `app/Actions/` as single-responsibility classes with an `execute()` method. They are injected into Livewire component methods via dependency injection:

```php
public function save(CreateEvent $action) {
    $data = $this->validate();
    $action->execute($data);
}
```

Auth-related actions (Fortify) are in `app/Actions/Fortify/`.

### Translations

Locale is `it` (Italian). Translation files are in `resources/lang/it/` (not `lang/`). Keys use dot notation grouped by file: `profile.fields.name`, `dashboard.events.fields.title`, `navigation.home`, etc.

### UI / CSS

Tailwind CSS v4 with `@theme` syntax in `resources/css/app.css`. Dark theme with glass-morphism design system:
- `.glass-card`, `.glass-panel`, `.glass-overlay` — backdrop-blur containers
- `.input-et`, `.select-et`, `.textarea-et` — form input styles
- Labels use: `class="block text-sm font-medium mb-1 text-gray-500"`
- Validation errors: `<span class="text-pink text-xs">{{ $message }}</span>`
- Submit buttons: cyan underline style with right arrow icon

### Component Communication

Components dispatch events to the global `<livewire:messages />` toast component:
```php
$this->dispatch('messageSent', message: '...', success: true);
```

### File Uploads

Uses `WithFileUploads` trait. Images are processed through action classes (`ProcessAvatar`, `ProcessPoster`, `ProcessLogo`) that crop/resize and convert to WebP via Intervention Image, stored in `storage/app/public/`.

### Search

Meilisearch via Laravel Scout. The `Event` model uses the `Searchable` trait. Scout driver is set to `null` in tests.

### Enums

PHP 8.1 backed enums in `app/Enums/`: `EventStatus` (Pending/Active/Terminate/Reject), `EventType` (InPerson/Online/Hybrid), `CommunityStatus` (Pending/Active/Rejected).

### Testing

Pest PHP with `RefreshDatabase` applied globally in `tests/Pest.php`. Tests use SQLite in-memory. Livewire components are tested with `Livewire::test()`.
