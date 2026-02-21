# Backend: Actions (Business Logic)

## Perche ci sono

La logica “di business” e spesso in `app/Actions/*` con un metodo `execute(...)`.

Queste Actions vengono:

- iniettate nei metodi Livewire (type-hint in signature)
- usate per racchiudere transazioni, upload processing, update, ecc.

## Pattern comune

- Classe `XxxAction`
- `execute(array $data): Model` oppure `execute(UploadedFile $file): ...`
- `DB::transaction(...)` quando serve consistenza

Esempi:

- Creazione evento + sync tag: `app/Actions/CreateEvent.php`
- Creazione community: `app/Actions/CreateCommunity.php`
- Toggle bookmark: `app/Actions/ToggleBookmark.php`

## Uso da Livewire

Esempio (in pagina):

- `save(CreateEvent $createEventAction, ProcessPoster $processPosterAction, ...)`

Fonte:

- `resources/views/pages/dashboard/events/⚡create.blade.php`

## Gestione errori

Pattern visto nel repo:

- `try/catch` e log `Log::error(...)`
- feedback UI:
  - redirect con `->with('success'|'error', ...)`
  - oppure `dispatch('messageSent', ...)`

Esempi:

- Community create: redirect con flash (`resources/views/pages/dashboard/communities/⚡create.blade.php`)
- Bookmark toggle: toast (`app/Livewire/Concerns/HasBookmarkToggle.php`)

