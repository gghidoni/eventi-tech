# UI: Layout, Font, Colori

## Obiettivo

Capire “da dove parte” la UI: layout, font, palette e classi globali gia presenti.

## Layout base

- Il layout principale e `resources/views/components/layouts/base.blade.php`.
- Include `resources/views/partials/head.blade.php` e monta:
  - `<livewire:hamburger-menu />` nella navbar
  - `<livewire:messages />` per toast/flash

## Dark mode

- L’HTML ha `class="dark"` nel layout base.
- In CSS esiste un override `@layer theme` per `.dark` (vedi `resources/css/app.css`).

## Font

- Font caricati localmente in `resources/css/fonts.css`.
- Font utility custom:
  - `.font-anta` (usata per titoli/label “brand”)
- Nota: nel `body` viene forzato `JetBrains Mono` come font principale (vedi `@layer base` in `resources/css/app.css`).

## Palette e theme tokens

In `resources/css/app.css` sono definiti:

- `@theme` con CSS variables (es. `--color-background`, `--color-pink`, `--color-cyan`, `--color-green`).
- Variabili UI (`--ui-primary`, `--ui-secondary`, ecc.) in `:root`.
- Background “atmosphere” via `--app-atmosphere-image` applicato al `body`.

## Classi component “globali”

Definite in `resources/css/app.css`:

- `.glass-card` (card effetto vetro)
- `.glass-panel` (dropdown/pannelli sovrapposti)
- `.glass-nav` (navbar con stessa atmosfera del body)
- `.glass-overlay` (overlay menu mobile)
- `.page` (container pagina standard)
- `.page-title` (titolo pagina standard)

## Esempi reali nel repo

- Navbar + layout: `resources/views/components/layouts/base.blade.php`
- Menu mobile con overlay: `resources/views/livewire/hamburger-menu.blade.php`
- Card vetro: `resources/views/livewire/event-mini-card.blade.php`

