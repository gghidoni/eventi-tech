# AGENTS.md - Development Guidelines for Eventi Tech Livewire

This file contains essential information for agentic coding agents working in this Laravel + Livewire event management platform.

## Project Overview

**Type**: Laravel 12 + Livewire 3 event management system  
**Stack**: PHP 8.2+, SQLite/PostgreSQL, Tailwind CSS 4.x, Vite 7.x  
**Purpose**: Tech community event management with search, user management, and notifications

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
- Follow existing patterns in `resources/views/volt/`
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

1. **Setup**: Run `composer setup` for new environments
2. **Development**: Use `composer dev` for full stack
3. **Testing**: Run tests before committing
4. **Code Quality**: Run `pint` and `phpstan` before PRs
5. **Deployment**: Use `npm run build` for production assets

## Common Patterns

### Action Class Example
```php
class CreateEvent
{
    public function handle(array $data): Event
    {
        return DB::transaction(function () use ($data) {
            return Event::create($data);
        });
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
        return Event::where('name', 'like', "%{$this->search}%")
            ->limit(10)
            ->get();
    }
    
    public function render(): View
    {
        return view('livewire.events-search');
    }
}
```

## Environment Configuration

- **Database**: SQLite for local, PostgreSQL for production
- **Search**: Meilisearch for full-text search
- **Queue**: Redis for background jobs
- **Email**: Mailpit for local testing

## CI/CD Requirements

- **PHP**: 8.4 in CI
- **Node**: 22 for frontend builds
- **Tests**: Must pass before merge
- **Linting**: Pint and PHPStan must pass
- **Coverage**: Maintain test coverage

## Debugging Tools

- **Laravel Telescope**: Available for debugging
- **Laravel Pail**: Real-time log viewing (`php artisan pail`)
- **Tinker**: Interactive debugging (`php artisan tinker`)
- **Browser DevTools**: For frontend debugging

Remember: This is a modern Laravel application following best practices. Always test thoroughly and maintain code quality standards.