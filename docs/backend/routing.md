# Backend: Routing e Livewire “pages::...”

## Convenzione rotte pagine

Le pagine principali sono Livewire pages risolte con la sintassi `pages::...`:

- Event show: `pages::events.show`
- Community show: `pages::communities.show`
- Dashboard: `pages::dashboard.*`
- Auth: `pages::auth.*`

Fonte:

- `routes/web.php`

## Dove sono i file

Le pagine `pages::...` corrispondono a Blade “Volt-like” in:

- `resources/views/pages/**/⚡*.blade.php`

Dentro questi file c’e:

- un `new class extends Component { ... }`
- seguito dalla view Blade/HTML

Esempio:

- `resources/views/pages/auth/⚡login.blade.php`

