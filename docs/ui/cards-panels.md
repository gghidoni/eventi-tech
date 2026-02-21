# UI: Card, Panel, Menu (Glass)

## Glass card

Per le card “principali” (es. eventi) si usa:

- `class="glass-card ..."`

Esempio:

- Event mini card: `resources/views/livewire/event-mini-card.blade.php`

## Glass panel (dropdown)

Per menu a tendina/overlay (kebab menu) si usa:

- `class="glass-panel ... absolute ... z-10"`
- spesso con `wire:click.outside="closeMenu"`

Esempi:

- Event mini card menu: `resources/views/livewire/event-mini-card.blade.php`
- Community card menu: `resources/views/livewire/dashboard/communities/card.blade.php`

## Card “semplici” (dashboard)

In dashboard ci sono card “border” senza glass:

- `border border-gray-600 ... p-3 ... h-32`

Esempio:

- Dashboard index: `resources/views/pages/dashboard/⚡index.blade.php`

