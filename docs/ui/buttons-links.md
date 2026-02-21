# UI: Bottoni e Link

## “Link button” (sottolineato)

In dashboard e form si usa spesso un bottone minimale “testo + icona”:

- classi tipiche: `flex items-center space-x-2 text-cyan underline`

Esempi:

- Profile save: `resources/views/pages/dashboard/⚡profile.blade.php`
- Create community: `resources/views/pages/dashboard/communities/⚡create.blade.php`
- Create event: `resources/views/pages/dashboard/events/⚡create.blade.php`

## Bottone primario pieno

Nel login esiste un bottone “pieno” con accento:

- `bg-accent text-background ... hover:bg-accent/90 disabled:opacity-50`

Esempio:

- Login submit: `resources/views/pages/auth/⚡login.blade.php`

## Navigazione Livewire

Nei link di navigazione interna viene usato `wire:navigate`:

- Menu item: `resources/views/components/menu-item.blade.php`
- Card eventi/links: varie viste (es. `resources/views/livewire/event-mini-card.blade.php`)

