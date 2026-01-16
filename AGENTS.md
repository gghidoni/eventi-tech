# AGENTS.md - Development Guidelines for Eventi Tech Livewire

This file contains essential information for agentic coding agents working in this Laravel + Livewire event management platform.

## Project Overview

**Type**: Laravel 12 + Livewire 3 event management system
**Stack**: PHP 8.4+, SQLite/PostgreSQL, Tailwind CSS 4.x, Vite 7.x, Meilisearch, Redis
**Purpose**: Tech community event management with geolocation search, bookmarks, notifications, and user management

## Essential Commands

### Development
```bash
composer setup          # Full project setup (install, migrate, build)
composer dev            # Start dev server with queue, logs, and Vite
php artisan serve       # Laravel only (port 8000)
npm run dev            # Vite dev server (port 5173)
```

### Testing
```bash
composer test           # Run all tests with config clear
./vendor/bin/pest       # Run all tests
./vendor/bin/pest --filter "TestName"  # Run specific test
./vendor/bin/pest tests/Feature/Auth/AuthenticationTest.php  # Single file
```

### Code Quality
```bash
vendor/bin/pint         # Format code (Laravel Pint)
vendor/bin/phpstan      # Static analysis (Larastan)
```

### Database
```bash
php artisan migrate     # Run migrations
php artisan migrate:fresh --seed  # Fresh DB with seeders
php artisan tinker      # REPL for debugging
```

## Code Style Guidelines

### PHP Standards
- **Strict Types**: Always use `declare(strict_types=1);`
- **Type Hints**: Required for all parameters and return types
- **PSR-4**: Follow Laravel namespace structure
- **Formatting**: Use `vendor/bin/pint` (Laravel's opinionated style)

### Naming Conventions
- **Models**: PascalCase (Event, Community, User)
- **Actions**: PascalCase with verb-noun pattern (CreateEvent, UpdateCommunity)
- **Enums**: PascalCase (EventStatus, CommunityStatus, EventType)
- **Livewire Components**: PascalCase (EventsSearch, Communities)
- **Methods**: camelCase with descriptive names
- **Variables**: camelCase, meaningful names
- **Constants**: UPPER_SNAKE_CASE

### Architecture Patterns
- **Action Classes**: Business logic in dedicated Action classes
- **Enums**: Use for status fields and constants instead of magic strings
- **Form Requests**: Validation in separate Request classes
- **Transactions**: Use database transactions for write operations
- **Relationships**: Proper return type declarations on model relationships

### Import Organization
```php
// 1. External libraries
use Illuminate\Support\Facades\DB;
use Livewire\Component;

// 2. Application classes (Models, Actions, Enums)
use App\Models\Event;
use App\Actions\CreateEvent;
use App\Enums\EventStatus;

// 3. Same namespace (relative imports)
use function view;
```

## Livewire & Volt Guidelines

### Component Structure
- **Properties**: Public for reactive data, protected/private for internal
- **Rules**: Use `#[Rule]` attributes for validation
- **Computed**: Use `#[Computed]` for derived properties
- **Listeners**: Use `#[Listen]` attributes for event handling

### Volt Components
- Use Blade syntax for simple components
- Follow existing patterns in `resources/views/livewire/` (not `volt/`)
- Keep logic minimal, move complex operations to Actions

### Best Practices
- Avoid heavy computations in render methods
- Use lazy loading for large datasets
- Implement proper loading states
- Validate all user inputs

## Testing Guidelines

### Test Structure
```php
// Feature tests
test('users can view events', function () {
    // Arrange
    $event = Event::factory()->create();
    
    // Act
    $response = $this->get('/events');
    
    // Assert
    $response->assertOk();
    $response->assertSee($event->name);
});
```

### Testing Patterns
- **Database**: Use RefreshDatabase trait for clean state
- **Factories**: Use model factories for test data
- **Assertions**: Use Laravel's assertion methods
- **Livewire**: Test components with `Livewire::test()`

## Frontend Guidelines

### Tailwind CSS
- **Utilities**: Prefer utility classes over custom CSS
- **Components**: Use existing `.input-et`, `.select-et`, `.textarea-et`
- **Dark Mode**: Support with `dark:` variants
- **Responsive**: Mobile-first approach

### JavaScript
- **Alpine.js**: Use for client-side interactivity
- **Vite**: Module bundling with HMR
- **Axios**: HTTP client for API calls

## Security Best Practices

- **Authentication**: Use Laravel Fortify flows
- **Authorization**: Implement proper middleware and policies
- **Validation**: Always validate user inputs
- **Mass Assignment**: Use `$fillable` on models
- **CSRF**: Laravel handles automatically
- **SQL Injection**: Use Eloquent/parameterized queries

## Database Guidelines

### Migrations
- **Naming**: Descriptive, snake_case
- **Foreign Keys**: Proper constraints with cascades
- **Indexes**: Add for frequently queried columns
- **Soft Deletes**: Use where appropriate

### Models
- **Relationships**: Define all relationships
- **Casts**: Use for proper type conversion
- **Scopes**: Use for common queries
- **Accessors/Mutators**: Use for computed properties

## Development Workflow

### Core Development Principles
- **Step-by-Step Implementation**: Propose structure first, implement gradually with user approval
- **User Confirmation Required**: Always ask before commits, pushes, and database operations
- **Quality Gates**: Test and validate before committing changes
- **Safety First**: Database operations and destructive actions require explicit confirmation

### Local Development (Docker)
1. **Setup**: Run `docker-compose up -d` to start all services
2. **Database**: ⚠️ **Ask user confirmation** before `docker exec eventi-tech php artisan migrate` and seeding
3. **Development**: Use `docker exec eventi-tech composer dev` for full stack or individual services
4. **Testing**: Run `docker exec eventi-tech ./vendor/bin/pest` before committing (user approval required)
5. **Code Quality**: Run `docker exec eventi-tech vendor/bin/pint` and `docker exec eventi-tech vendor/bin/phpstan` before PRs
6. **Deployment**: Use `docker exec eventi-tech npm run build` for production assets

### Host Development (without Docker)
1. **Setup**: Run `composer setup` for new environments
2. **Database**: ⚠️ **Ask user confirmation** before migrations and seeding
3. **Development**: Use `composer dev` for full stack
4. **Testing**: Run tests before committing (user approval required)
5. **Code Quality**: Run `pint` and `phpstan` before PRs
6. **Deployment**: Use `npm run build` for production assets

## Common Patterns

### Action Class Example
```php
class CreateEvent
{
    public function handle(array $data): Event
    {
        return DB::transaction(function () use ($data) {
            $event = Event::create($data);
            $event->searchable(); // Index for Meilisearch
            return $event;
        });
    }
}
```

### Notification Example
```php
class CreatedNewEvent extends Mailable
{
    public function __construct(
        public Event $event,
        public User $organizer
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Event Created',
            from: config('mail.from.address'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.events.created',
        );
    }
}
```

### Scout Search Integration
```php
class Event extends Model
{
    use Searchable;

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'tags' => $this->tags->pluck('name')->toArray(),
            'location' => $this->addressBook?->full_address,
        ];
    }
}
```

### Livewire Component Example
```php
class EventsSearch extends Component
{
    public string $search = '';

    #[Computed]
    public function results(): Collection
    {
        return Event::search($this->search) // Meilisearch integration
            ->where('status', EventStatus::Published)
            ->with('addressBook')
            ->paginate(20);
    }

    public function render(): View
    {
        return view('livewire.events-search');
    }
}
```

### AddressBook Models (Geolocation)
- **Region/Province/City**: Hierarchical location models for Italian addresses
- **AddressBook**: Links events to specific locations for geolocation-based filtering
- **Usage**: Events have `addressBook()` relationship for location data

## Environment Configuration

- **Database**: SQLite for local development, PostgreSQL in Docker/production
- **Search**: Meilisearch for full-text search with Scout integration
- **Queue**: Redis for background jobs
- **Email**: Mailpit for local testing (SMTP port 1025, Web UI port 8025)
- **Image Processing**: Intervention Image for poster/logo handling
- **Async Select**: Livewire Async Select for dynamic dropdowns

### Docker Services
- **app** (eventi-tech): PHP-FPM container on port 9000
- **nginx**: Web server on port 8083
- **postgres**: Database on port 5432
- **meilisearch**: Search engine on port 7700
- **mailpit**: Email testing on ports 1025 (SMTP) and 8025 (Web UI)

## CI/CD Requirements

- **PHP**: 8.4 in CI
- **Node**: 22 for frontend builds
- **Tests**: Must pass before merge (user confirmation required for test implementation)
- **Linting**: Pint and PHPStan must pass
- **Coverage**: Maintain test coverage
- **Database**: Migrations and seeds require explicit approval
- **Commits**: Only create commits when explicitly requested by user
- **Pushes**: Only push when explicitly requested by user

## Docker Commands

### Container Execution
```bash
# Start all services
docker-compose up -d

# Execute commands inside app container
docker exec eventi-tech <command>

# Examples
docker exec eventi-tech php artisan migrate
docker exec eventi-tech composer install
docker exec eventi-tech vendor/bin/pint
docker exec eventi-tech ./vendor/bin/pest
docker exec eventi-tech npm run build

# View logs
docker logs eventi-tech -f
docker logs eventi-tech-meilisearch -f
```

### Service URLs
- **App**: http://localhost:8083
- **Meilisearch**: http://localhost:7700
- **Mailpit**: http://localhost:8025
- **Vite (dev)**: http://localhost:5173

## Development Safety Protocols

### User Confirmation Requirements
- **Database Operations**: Always ask before migrations, seeds, or destructive DB changes
- **Git Operations**: Never commit or push without explicit user approval
- **Complex Changes**: Break down into steps and get approval for each phase
- **Destructive Actions**: Warn and confirm before force pushes, branch deletions, or data loss

### Workflow Guidelines
- **Step-by-Step**: Propose general structure first, then implement specific parts
- **Quality Gates**: Test and validate each component before proceeding
- **Documentation**: Update AGENTS.md for any workflow or architectural changes
- **Backup**: Ensure critical data is backed up before major changes

## Debugging Tools

- **Laravel Telescope**: Available for debugging
- **Laravel Pail**: Real-time log viewing (`docker exec eventi-tech php artisan pail`)
- **Tinker**: Interactive debugging (`docker exec eventi-tech php artisan tinker`)
- **Browser DevTools**: For frontend debugging
- **Docker Logs**: `docker logs <service-name>` for container debugging

Remember: This is a modern Laravel application following best practices. Always test thoroughly and maintain code quality standards.