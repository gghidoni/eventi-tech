# UI: Date/Time Picker (Flatpickr)

## Cosa c’e

Flatpickr e caricato via Vite e reso globale:

- `resources/js/app.js` (assegna `window.flatpickr` e locale `Italian`)
- CSS Flatpickr importato in `resources/css/app.css`

## Pattern Alpine + Livewire

Nelle form eventi:

- wrapper `wire:ignore` (per evitare conflitti Livewire/DOM)
- `x-data` con `init()` che chiama `flatpickr($refs....)`
- `onChange` che fa `$wire.set('campo', dateStr)`

In piu:

- il campo “end” imposta `minDate` sulla base di `start`
- comunicazione start -> end via evento DOM custom (`$dispatch('start-date-changed', ...)`)

Esempio completo:

- `resources/views/pages/dashboard/events/⚡create.blade.php`

