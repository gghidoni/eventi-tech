# Backend: Pattern Livewire (Componenti, Pages, Eventi)

## Due stili presenti nel repo

### 1) Livewire “pages” inline (Volt-like)

Caratteristiche:

- Classe anonima `new class extends Component`
- Logica e markup nello stesso file Blade
- Layout impostato con hook `rendering($view)`

Esempi:

- Login: `resources/views/pages/auth/⚡login.blade.php`
- Create event: `resources/views/pages/dashboard/events/⚡create.blade.php`
- Profile: `resources/views/pages/dashboard/⚡profile.blade.php`

### 2) Componenti Livewire classici

Caratteristiche:

- Classe in `app/Livewire/...`
- View in `resources/views/livewire/...`

Esempi:

- Search eventi: `app/Livewire/EventsSearch.php` + `resources/views/livewire/events-search.blade.php`
- Event mini card: `resources/views/livewire/event-mini-card.blade.php` (classe inline) montata come `<livewire:event-mini-card ... />`

## Eventi Livewire e comunicazione UI

### Toast/flash (messages)

- I componenti dispatchano `messageSent` con payload `message` e `success`.
- Il listener e `resources/views/livewire/messages.blade.php` (`#[On('messageSent')]`).

Esempi:

- Bookmark toggle: `app/Livewire/Concerns/HasBookmarkToggle.php`
- Create event error: `resources/views/pages/dashboard/events/⚡create.blade.php`

### Refresh tra componenti (bookmarkUpdated)

- Dopo “unbookmark” viene dispatchato `bookmarkUpdated`.
- La pagina bookmarks ascolta e si aggiorna.

Fonte:

- `app/Livewire/Concerns/HasBookmarkToggle.php`
- `resources/views/pages/dashboard/⚡bookmarks.blade.php`

## Pagination custom

- In Blade: `{{ $events->links('livewire.custom-pagination') }}`
- View: `resources/views/livewire/custom-pagination.blade.php`

