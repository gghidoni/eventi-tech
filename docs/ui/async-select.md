# UI: Async Select (Livewire)

## Cosa c’e

Il progetto usa `DrPshtiwan/LivewireAsyncSelect` (AsyncSelect) con viste custom in:

- `resources/views/livewire/select/*.blade.php`

E componenti Livewire wrapper in:

- `app/Livewire/Select/*.php`

## Stili e JS

- In head: `@asyncSelectStyles` e script `js/async-select.js`
  - fonte: `resources/views/partials/head.blade.php`
- Override dark/glass in CSS:
  - fonte: `resources/css/app.css` (sezione “Async Select: dark/glass overrides”)

## Endpoint (API) usati dai select

Rotte:

- `GET /find-location` -> `AddressBookController::findLocation`
- `GET /find-tags` -> `TagController::findTags`

Fonte: `routes/web.php`.

Implementazioni:

- `app/Http/Controllers/AddressBookController.php`
- `app/Http/Controllers/TagController.php`

## Pattern d’uso nelle pagine

Esempi reali:

- Select location (city):
  - `resources/views/pages/dashboard/events/⚡create.blade.php`
  - passa `:endpoint="route('find')"` e `:extra-params="['type' => 'city']"`
- Select tags (multiple):
  - `resources/views/pages/dashboard/events/⚡create.blade.php`
  - passa `:endpoint="route('find.tags')"`, `:multiple="true"`, `:max-selections="4"`

## Best practice endpoint

- usa `route(...)` o URL host-relative, non hostname interni Docker come `http://nginx/...`
- questo evita rotture nei browser eseguiti fuori rete Docker, nei test E2E e negli audit Lighthouse
- Select community (single, no search):
  - `resources/views/pages/dashboard/events/⚡create.blade.php`

## Nota su “value”

Per location, il `value` e un JSON stringificato (type/id/name).

- Fonte: `app/Http/Controllers/AddressBookController.php`
- Parsing lato Livewire:
  - `app/Livewire/EventsSearch.php` (decodifica `location` e applica filtro)
  - `resources/views/pages/dashboard/events/⚡create.blade.php` (decodifica `city`)
