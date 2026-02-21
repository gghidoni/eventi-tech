# Feature: Preferiti Community + Mail su evento approvato

## Obiettivo
Consentire a un utente autenticato di:
1) aggiungere/rimuovere una o più **community** ai **preferiti** (cuoricino nella pagina pubblica community)
2) vedere l’elenco delle community preferite in dashboard, nella pagina **Preferiti** già esistente, con **selettore in alto** per switchare tra **Eventi** e **Community**
3) ricevere una **mail di notifica** quando una community preferita pubblica un nuovo evento e l’evento viene **approvato** (`status = active`)

## Success criteria (DoD)
- Nella pagina `communities.show` compare il cuore (solo se non owner).
- Click cuore:
  - guest → redirect login
  - user loggato ma non verificato → redirect verifica
  - user verificato → toggle + toast (pattern già usato per eventi)
- Dashboard `/dashboard/bookmarks`:
  - selettore/tab in alto: **Eventi** / **Community**
  - tab Community: lista paginata community preferite + rimozione dai preferiti (refresh live)
- Quando un evento passa a `active`:
  - parte un **Job in coda** che invia una mail a tutti gli utenti che hanno quella community tra i preferiti
- Test automatici verdi (aggiunti e già presenti, se impattati).

---

## Riferimenti (docs interne)
- Routing e pagine Livewire “pages::…”: `docs/backend/routing.md`
- Pattern Livewire (toast `messageSent`, refresh event-driven): `docs/backend/livewire.md`
- Pattern Actions e transazioni: `docs/backend/actions.md`
- Relazioni/accessor nei model: `docs/backend/models.md`
- UI cards/panels e stile generale: `docs/ui/cards-panels.md`, `docs/ui/layout.md`, `docs/ui/buttons-links.md`

## Riferimenti (codice esistente da riusare)
- Toggle preferiti eventi (action): `app/Actions/ToggleBookmark.php`
- Toggle preferiti eventi (trait Livewire, redirect + toast + dispatch refresh): `app/Livewire/Concerns/HasBookmarkToggle.php`
- Cuoricino evento (single): `resources/views/pages/events/⚡show.blade.php`
- Pagina preferiti eventi: `resources/views/pages/dashboard/⚡bookmarks.blade.php`
- Mini card evento (cuore + menu): `resources/views/livewire/event-mini-card.blade.php`
- Rotta dashboard preferiti: `routes/web.php` (`dashboard.bookmarks`)

---

## Decisioni (confermate)
- Preferiti in **un’unica pagina** `/dashboard/bookmarks` con **selettore** Eventi/Community.
- La mail massiva va inviata **solo** ai follower (utenti con community tra i preferiti).
- L’invio massivo avviene tramite **Job in coda** (in test gira comunque sync).

---

## Analisi schema pivot eventi (verifica obbligatoria)
Ho notato che la migration `database/migrations/2025_08_19_092311_create_event_user_table.php` sembra creare `unique()` su `user_id` e `event_id`, cosa che impedirebbe un vero many-to-many.
Tu però riesci ad aggiungere più eventi ai preferiti senza errori: quindi è obbligatorio verificare la realtà del DB.

### Check da fare (prima di toccare qualsiasi cosa)
1) Verificare vincoli/indici su `event_user` nel DB reale (Postgres docker) e in sqlite testing.
2) Eseguire `php artisan test` e in particolare:
   - `tests/Unit/Actions/ToggleBookmarkTest.php`
   - `tests/Unit/Models/UserTest.php` (bookmark multipli)

Se i vincoli risultano errati o i test falliscono:
- aggiungere una migration di fix “forward-only” (non modificare la migration storica):
  - rimuovere unique singoli
  - aggiungere unique composito `unique(['user_id','event_id'])`

---

## DB changes (preferiti community)
### 1) Nuova pivot table
- Migration: `create_community_user_table`
- Tabella: `community_user`
  - `user_id` FK → users
  - `community_id` FK → communities
  - `timestamps`
  - unique composito: `unique(['user_id','community_id'])`

---

## Backend changes

### 2) Relazioni Eloquent
Rif. pattern: `docs/backend/models.md`

- `app/Models/User.php`
  - aggiungere relazione many-to-many verso community preferite:
    - `favoriteCommunities(): BelongsToMany`
- `app/Models/Community.php`
  - aggiungere relazione inversa:
    - `favoritedByUsers(): BelongsToMany`
  - aggiungere accessor `is_mine` (come Event, ma su community) per nascondere il cuore all’owner
  - aggiungere cast `status` a `CommunityStatus::class` (coerente con enum già presente)

### 3) Action: toggle preferito community
Rif. pattern: `docs/backend/actions.md`

- Nuova action: `app/Actions/ToggleCommunityFavorite.php`
  - `execute(User $user, int $communityId): bool`
  - validare esistenza community (stesso approccio di `ToggleBookmark`)
  - `DB::transaction()` + `toggle($communityId)` + ritorno boolean “attached”

### 4) Trait Livewire: toggle cuore community
Rif. pattern: `docs/backend/livewire.md` (toast + refresh event-driven)

- Nuovo trait: `app/Livewire/Concerns/HasCommunityFavoriteToggle.php`
  - `public bool $isCommunityFavorited = false;`
  - `toggleCommunityFavorite(ToggleCommunityFavorite $action)`
    - guest → redirect `/login`
    - non verificato → redirect `verification.notice`
    - try/catch → dispatch `messageSent` (success/fail)
    - quando rimuove → dispatch `communityFavoriteUpdated` (refresh lista)
  - `initializeCommunityFavoriteState(Community $community): void`

Nota: rispettare convenzione progetto: commenti in italiano.

---

## UI changes

### 5) Cuoricino nella single page community
Rif. pattern: `resources/views/pages/events/⚡show.blade.php`

- File: `resources/views/pages/communities/⚡show.blade.php`
  - usare `HasCommunityFavoriteToggle`
  - in `mount()` inizializzare stato
  - aggiungere cuore (fill/empty) con `wire:click="toggleCommunityFavorite"`
  - mostrare cuore solo se `!$community->is_mine`

### 6) Dashboard: pagina preferiti con selettore Eventi/Community
Rif. pattern: `docs/backend/routing.md`, `docs/backend/livewire.md`

- File: `resources/views/pages/dashboard/⚡bookmarks.blade.php`
  - aggiungere stato tab (es. `public string $tab = 'events'`)
  - tab preso da querystring `?tab=events|communities` (whitelist)
  - UI: due tab/bottoni in alto (stile coerente con docs UI)
  - dati:
    - `events`: `auth()->user()->bookmarks()->latest()->paginate(8)`
    - `communities`: `auth()->user()->favoriteCommunities()->latest()->paginate(8)`
  - listeners:
    - mantenere `bookmarkUpdated`
    - aggiungere `communityFavoriteUpdated`

### 7) Lista community preferite (mini card)
Rif. pattern: `resources/views/livewire/event-mini-card.blade.php`

- Nuovo componente: `resources/views/livewire/community-mini-card.blade.php`
  - mostra logo, nome, link “Apri”
  - cuore cliccabile per toggle (rimozione rapida)
  - usa `HasCommunityFavoriteToggle` e init stato in `mount()`

### 8) Dashboard index: card “community preferite”
Rif. pattern esistente: `resources/views/pages/dashboard/⚡index.blade.php`
- sostituire placeholder “TODO community seguite”
- contatore: `favoriteCommunities()->count()`
- link a `/dashboard/bookmarks?tab=communities`

---

## Email: notifica massiva su evento approvato

### 9) Nuova Mailable
- `app/Mail/CommunityFavoriteEventApproved.php`
  - subject: “Nuovo evento pubblicato da {community}”
  - view: `resources/views/emails/events/community-favorite-approved.blade.php`
  - include: titolo evento, nome community, link evento, link preferiti

### 10) Job in coda (invio a chunk)
- `app/Jobs/NotifyCommunityFollowersOfApprovedEvent.php`
  - input: `event_id`
  - caricare `Event` + `community`
  - query utenti follower: utenti che hanno `favoriteCommunities` contenente `community_id`
  - inviare mail per utente con `Mail::to()->send()` *dentro il job* (il job è già asincrono)
  - usare `chunkById()` per scalare

### 11) Trigger: quando evento diventa `active`
Requisito: “Quando la community pubblica un nuovo evento, quando viene approvato…”
Quindi trigger = transizione `status: pending → active` (o comunque `!= active → active`).

Passi (senza inventare):
1) Cercare nel repo **dove oggi viene gestita l’approvazione evento** e la mail all’owner (tu dici che esiste già).
2) Agganciare lì il dispatch del Job follower, senza toccare la mail owner.
3) Se non esiste un punto unico nel codice applicativo, implementare un hook affidabile:
   - opzione fallback: `Event::booted()` su `updated` con `wasChanged('status')` e `status === active`
   - dispatch del Job una sola volta per transizione

Nota: evitare doppi invii (idempotenza minima), es:
- registrare invio in tabella dedicata o usare un lock (se necessario). Scelta da fare in implementazione in base a come viene aggiornato lo status (una volta sola vs più edit).

---

## Traduzioni
- `resources/lang/it/dashboard.php`: testi tab preferiti + empty state community
- `resources/lang/it/communities.php`: azioni/messaggi preferiti community (coerenti con `resources/lang/it/events.php`)

---

## Test plan
Rif. stack: Pest/PHPUnit già presente in `tests/`

1) `ToggleCommunityFavorite` (unit):
- aggiunge preferito
- rimuove preferito
- user può preferire più community

2) Relazioni model (unit):
- user ↔ favorite communities many-to-many

3) Notifica mail su approvazione (feature/unit con Mail fake):
- 2 follower ricevono mail quando evento passa a `active`
- utenti non follower non ricevono
- (opzionale) confermare che owner mail non viene alterata

4) Se necessario (solo dopo verifica pivot `event_user`):
- aggiungere migration di fix e far passare i test esistenti bookmark eventi

---

## Note implementative (convenzioni progetto)
- Soluzioni minimali e leggibili, senza over-engineering.
- Commenti in italiano.
- Riusare pattern già presenti: Actions + Traits Livewire + dispatch eventi per refresh.
