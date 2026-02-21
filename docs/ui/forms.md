# UI: Form (Input, Select, Textarea, Errori, Upload)

## Classi standard

In questo progetto gli input non sono (ancora) componenti Blade dedicati: si usa un set di classi CSS.

- Input testo/password/email: `class="input-et"`
- Textarea: `class="textarea-et"`
- Select: `class="input-et select-et"`

Fonte: `resources/css/app.css`.

## Errori di validazione

Pattern comune:

- `@error('campo') <span class="text-pink text-xs">{{ $message }}</span> @enderror`

Esempi:

- Login: `resources/views/pages/auth/⚡login.blade.php`
- Create community: `resources/views/pages/dashboard/communities/⚡create.blade.php`
- Create event: `resources/views/pages/dashboard/events/⚡create.blade.php`

## Upload file con anteprima (Livewire)

Pattern usato per avatar/logo/poster:

- `<label class="input-et ... cursor-pointer">` come bottone custom
- `<input type="file" class="hidden" wire:model="...">`
- Anteprima:
  - se file selezionato: `{{ $file->temporaryUrl() }}`
  - altrimenti: placeholder o immagine esistente
- Stato upload: `wire:loading wire:target="campo"`

Esempi:

- Logo community: `resources/views/pages/dashboard/communities/⚡create.blade.php`
- Avatar profilo: `resources/views/pages/dashboard/⚡profile.blade.php`
- Poster evento: `resources/views/pages/dashboard/events/⚡create.blade.php`

## Note pratiche

- Per i campi “opzionali URL” nel backend spesso viene normalizzato `''` -> `null` prima di salvare (vedi `resources/views/pages/dashboard/events/⚡create.blade.php`).

