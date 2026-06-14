# Project Architecture

## Vista rapida

L'applicazione e una Laravel app monolitica con UI reattiva costruita principalmente con Livewire.

Questo significa:

- backend, rendering e orchestration stanno nello stesso progetto
- il browser interagisce con componenti Livewire invece che con una SPA separata
- il confine tra UI e business logic resta vicino al dominio Laravel

## Blocchi principali

### Pagine pubbliche

- home con ricerca eventi
- dettaglio evento
- dettaglio community

Le pagine pubbliche privilegiano lettura, ricerca e conversione verso login o approfondimento.

### Area autenticata

- dashboard
- bookmarks
- profilo
- gestione community
- creazione e modifica eventi

Qui la UI e piu operativa e usa intensamente Livewire per form, feedback e refresh locali.

### Backoffice

- pannello admin Filament su `/admin`

Il backoffice e separato dal flusso principale utente, ma vive nello stesso monolite.

## Pattern applicativi

### Routing

Le pagine principali usano `Route::livewire()` con namespace `pages::...`.

Approfondimento: `docs/backend/routing.md`

### Pages Livewire single-file

Molte pagine sono file Blade con classe anonima integrata e markup nello stesso file.

Questo riduce dispersione quando la logica e locale alla pagina.

Approfondimento: `docs/backend/livewire.md`

### Actions per logica di business

La logica non banale viene spostata in `app/Actions/*`, spesso con metodo `execute()`.

Questo mantiene piu leggere le pagine Livewire e rende piu chiaro dove stanno:

- transazioni
- upload processing
- create/update di entita
- toggle e side effect

Approfondimento: `docs/backend/actions.md`

### Eloquent come centro del dominio

I model espongono:

- relazioni
- cast enum
- accessor utili alla UI
- URL helper

Approfondimento: `docs/backend/models.md`

## Flussi dati rilevanti

### Ricerca eventi

1. l'utente scrive una query o seleziona una localita
2. Livewire aggiorna il componente di ricerca
3. Scout/Meilisearch produce gli ID candidati
4. Eloquent filtra e arricchisce i record reali
5. la UI renderizza card e paginazione

Approfondimento: `docs/backend/search.md`

### Publishing eventi

1. owner o organizer compila la form
2. la pagina Livewire valida e orchestra
3. le Action gestiscono create/update e upload immagini
4. vengono sincronizzati i tag e gli altri riferimenti
5. la UI mostra redirect o feedback

### Feedback UI

Il feedback lato utente passa spesso per:

- flash session
- evento `messageSent`
- aggiornamenti Livewire mirati

## Infrastruttura locale

### Runtime applicativo

- PHP-FPM in container `eventi-tech`
- Nginx esposto su `8083`
- PostgreSQL, Meilisearch e Mailpit in container dedicati

### Frontend tooling

- Vite per build e dev server
- Playwright per browser automation
- Lighthouse per audit qualitativi

Questi ultimi vivono come tooling di progetto e non come pezzi strutturali del runtime applicativo.
