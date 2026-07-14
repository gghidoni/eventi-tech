# Flow map

Questa mappa descrive cosa deve fare un agente durante un audit completo, cosa deve aspettarsi e come controllare che sia andato bene.

Per ogni flusso:

- usare Playwright per l'azione reale in UI;
- verificare HTTP status, assenza di errori JS critici, page error e request failed;
- verificare DB con MCP PostgreSQL;
- verificare email con MCP Mailpit quando il flusso deve inviare notifiche;
- usare `QUEUE_CONNECTION=sync`, quindi i job devono avere effetto immediato.

## 1. Navigazione pubblica e ricerca

### Home e lista eventi

Azione:

1. Aprire `/` da guest.
2. Verificare rendering desktop e mobile.
3. Usare ricerca testuale con stringa di almeno 3 caratteri.
4. Usare filtro location con risultati da `/find-location`.
5. Usare filtro tag se presente nel componente.
6. Aprire una card evento pubblica.

Atteso UI:

- la home si carica senza redirect;
- vengono mostrati eventi pubblici;
- la ricerca testuale filtra via Scout/Meilisearch quando la query supera 2 caratteri;
- gli eventi mostrati devono essere `active` e non conclusi;
- location online/in presenza/ibrida deve essere coerente con tipo e address book;
- le card devono mostrare CTA, stato preferito, tag e menu senza layout rotto.

Verifica DB:

- `events.status = 'active'`;
- `events.end_date >= now()` per la lista pubblica;
- eventuali tag devono essere presenti in pivot evento/tag;
- nessuna riga deve essere creata in `event_user` o `community_user` solo navigando.

Casi limite:

- query con meno di 3 caratteri non deve forzare Scout;
- location JSON non valido va testato come input ostile;
- evento `pending`, `reject` o `terminate` non deve comparire nella home;
- URL diretto `/events/{event}` per evento non active va provato e segnalato: nel codice attuale la show pubblica non filtra status.

### Dettaglio evento pubblico

Azione:

1. Aprire evento active senza login.
2. Aprire evento owner con utente owner.
3. Aprire evento non owner con utente verificato.
4. Aprire evento pending/reject/terminate con URL noto.

Atteso UI:

- guest vede descrizione, date, community, luogo o stato online, tag e link pubblici;
- owner vede azioni di edit e candidature dove disponibili;
- non owner vede bookmark se autenticato e non owner;
- guest che prova bookmark viene portato al login;
- CTA ticket usa `tickets_url` quando presente;
- CTA calendario esiste ma attualmente punta a `/`, da segnalare come rischio/funzionalita incompleta;
- CFP esterna published mostra link esterno;
- CFP interna published mostra link apply solo se `isOpen()` e l'utente puo candidarsi.

Verifica DB:

- apertura dettaglio non deve creare mutazioni;
- bookmark crea/rimuove solo pivot `event_user`.

Casi limite:

- evento con CFP `draft` o `archived` non deve mostrare CTA pubblica;
- CFP interna published ma chiusa deve mostrare CTA disabilitata o non cliccabile;
- CFP esterna published oggi non controlla finestra temporale nella CTA: verificare e segnalare se resta visibile fuori periodo.

### Dettaglio community pubblico

Azione:

1. Aprire `/communities/{community}` da guest.
2. Aprire da owner.
3. Aprire da utente verificato non owner.
4. Aprire community pending con URL noto.

Atteso UI:

- guest vede dati pubblici, social, eventi community;
- owner non deve poter aggiungere la propria community ai preferiti;
- non owner verificato puo togglare preferito;
- guest che prova preferito viene portato al login;
- unverified viene portato alla verifica email.

Verifica DB:

- toggle preferito crea/rimuove pivot `community_user`;
- navigazione non crea mutazioni.

Casi limite:

- la pagina community pubblica oggi non filtra esplicitamente lo status community;
- la lista eventi community oggi usa gli eventi della community senza filtro status evidente. Testare pending/reject/terminate e segnalare eventuale esposizione.

## 2. Autenticazione e account

### Registrazione e verifica email

Azione:

1. Registrare un nuovo utente con email unica.
2. Verificare redirect a `/thanks-register`.
3. Provare accesso a `/dashboard`.
4. Leggere email di verifica da Mailpit via MCP.
5. Aprire link firmato di verifica.
6. Tornare a `/dashboard`.

Atteso UI:

- registrazione valida crea sessione e mostra pagina di ringraziamento;
- dashboard e route protette `verified` rimandano a `/email/verify`;
- link di verifica porta a dashboard con email verificata;
- resend da `/email/verify` invia una nuova email.

Verifica DB:

- `users.email_verified_at` inizialmente `null`, poi valorizzato;
- password salvata hashata;
- nessuna community/evento creato automaticamente.

Verifica email:

- email di verifica al nuovo utente;
- nuova email dopo resend;
- link estratto da Mailpit deve contenere route firmata `/email/verify/{id}/{hash}`.

Casi limite:

- email gia usata;
- password non confermata;
- campi obbligatori vuoti;
- link verifica riusato dopo verifica: deve rimanere idempotente o portare a dashboard.

### Login, logout e rate limit

Azione:

1. Login valido con Andrea.
2. Logout.
3. Login non valido ripetuto oltre soglia.
4. Login con Marco non verificato.

Atteso UI:

- login valido porta alla intended URL o dashboard;
- logout invalida sessione e torna pubblico;
- credenziali errate mostrano errore;
- dopo troppi tentativi interviene rate limiter;
- utente non verificato puo autenticarsi ma viene bloccato sulle route `verified`.

Verifica DB:

- nessuna mutazione funzionale tranne eventuali sessioni;
- `email_verified_at` di Marco resta `null`.

### Password reset

Azione:

1. Aprire `/forgot-password`.
2. Richiedere reset per email esistente.
3. Estrarre link da Mailpit.
4. Impostare nuova password.
5. Login con nuova password.
6. Verificare che vecchia password non funzioni.

Atteso UI:

- richiesta mostra stato di invio;
- link reset apre form con token;
- password aggiornata consente login;
- token consumato o non piu valido dopo uso.

Verifica DB:

- `password_reset_tokens` riceve token dopo richiesta;
- dopo reset la password hash dell'utente cambia;
- token viene rimosso o reso inutilizzabile secondo Fortify.

Verifica email:

- email reset al destinatario corretto;
- link contiene token e email.

### Profilo utente

Azione:

1. Login verificato.
2. Aprire `/dashboard/profile`.
3. Aggiornare nome.
4. Caricare avatar valido.
5. Cambiare password con current password corretta.
6. Provare password change con current password errata.

Atteso UI:

- nome aggiornato con messaggio di successo;
- avatar visibile dopo processing;
- cambio password valido mostra successo;
- current password errata mostra errore;
- la UI profilo corrente non espone cambio email.

Verifica DB e storage:

- `users.name` aggiornato;
- `users.avatar` valorizzato con path public avatars;
- file avatar generato in formato/processamento previsto;
- password hash cambia solo nel caso valido.

## 3. Preferiti e bookmark

### Bookmark evento

Azione:

1. Guest clicca bookmark su evento.
2. Utente non verificato clicca bookmark.
3. Utente verificato non owner aggiunge bookmark.
4. Lo stesso utente rimuove bookmark.
5. Aprire `/dashboard/bookmarks`.

Atteso UI:

- guest viene mandato al login;
- unverified viene mandato alla verifica email;
- verified vede cuore/stato aggiornato e toast;
- dashboard bookmarks riflette attach/detach;
- owner non deve vedere azione bookmark sul proprio evento.

Verifica DB:

- attach crea riga `event_user` con `event_id` e `user_id`;
- detach elimina la riga;
- non ci sono duplicati per stesso utente/evento.

### Favorite community

Azione:

1. Utente verificato non owner aggiunge community ai preferiti.
2. Rimuove la stessa community.
3. Aggiunge preferito e lascia attivo per test notifica evento approvato.

Atteso UI:

- stato preferito cambia senza reload incoerenti;
- dashboard bookmarks tab community mostra community favorita.

Verifica DB:

- attach/detach su `community_user`;
- nessun duplicato.

Verifica job/email:

- quando un evento della community favorita passa ad `active`, con queue sync l'email ai follower deve arrivare subito.

## 4. Community organizer

### Creazione community

Azione:

1. Login con utente verificato.
2. Aprire `/dashboard/communities/create`.
3. Inviare form vuoto.
4. Compilare nome, descrizione, website/social opzionali e logo.
5. Salvare.

Atteso UI:

- validazioni su name e description;
- URL social non validi rifiutati;
- logo non immagine o troppo grande rifiutato;
- salvataggio redirecta alla lista community con successo;
- nuova community nasce pending.

Verifica DB:

- riga `communities` con `user_id` corrente;
- `status = 'pending'`;
- `slug` generato da name;
- campi social salvati se validi.

Verifica storage:

- logo processato su disk `logos` in formato atteso.

Verifica email:

- `CreatedNewCommunity` al creator;
- `AdminNewCommunityNotification` a tutti gli utenti con `is_admin=true`;
- con queue/mail locale le email devono essere presenti subito in Mailpit.

### Lista e modifica community

Azione:

1. Aprire `/dashboard/communities`.
2. Modificare community propria.
3. Provare edit di community altrui.
4. Provare edit come utente non verificato.

Atteso UI:

- lista mostra solo community dell'utente;
- edit propria consente aggiornamento dati;
- edit altrui viene bloccato da `CommunityPolicy`;
- non verificato viene bloccato da `verified`.

Verifica DB:

- update modifica solo la community target;
- owner non cambia;
- status non dovrebbe essere modificato dal form organizer se non previsto dalla UI.

Casi limite:

- community pending con URL noto restituisce `404` anche all'owner;
- owner non verificato con community active seed non puo accedere alle dashboard routes fino a verifica.

## 5. Evento organizer

### Creazione evento

Azione:

1. Login con organizer verificato.
2. Aprire `/dashboard/events/create`.
3. Testare form vuoto.
4. Creare evento online.
5. Creare evento in presenza con city da `/find-location`.
6. Creare evento hybrid.
7. Usare massimo 4 tag e poi provare 5 tag.
8. Caricare poster valido e file non valido.
9. Creare evento con CFP esterna.
10. Creare evento con CFP interna.

Atteso UI:

- validazioni su titolo, descrizione, tipo, date e URL;
- per `online` non e richiesto address;
- per `in_person` e `hybrid` city/address sono richiesti;
- oltre 4 tag viene mostrato errore;
- evento creato nasce `pending`;
- redirect a lista eventi community con successo.

Verifica DB:

- `events.status = 'pending'`;
- `community_id` appartiene all'utente;
- `address_book_id` valorizzato solo quando richiesto dal tipo;
- pivot tag sincronizzata;
- poster path valorizzato se upload valido;
- record `cfps` creato quando configurato;
- `cfp_template_id` e fields coerenti per CFP interna.

Verifica storage:

- poster genera varianti desktop, mobile e thumb su disk `posters`.

Verifica email:

- `CreatedNewEvent` al creator;
- `AdminNewEventNotification` agli utenti `is_admin=true`.

Casi limite:

- la route create e protetta solo da `auth` e `verified`; il componente assume almeno una community (`communities[0]`). Testare utente verificato senza community e segnalare eventuale errore 500/UI;
- l'elenco community del form usa le community dell'utente, non solo active. Testare utente verificato con community pending.

### Modifica evento

Azione:

1. Aprire edit evento proprio.
2. Aggiornare titolo, descrizione, date, URL e tag.
3. Cambiare configurazione CFP.
4. Provare edit evento altrui.

Atteso UI:

- owner puo modificare;
- non owner riceve 403;
- salvataggio torna alla lista eventi con messaggio;
- rimozione CFP dal form elimina la CFP associata;
- modifica CFP interna rispetta regole template.

Verifica DB:

- `events` aggiornato solo sul record target;
- pivot tag sincronizzata;
- `cfps` aggiornato o eliminato;
- fields e template coerenti;
- status evento non deve cambiare dal form organizer se non previsto.

## 6. Approvazioni admin e Filament

### Accesso admin

Azione:

1. Aprire `/admin/login`.
2. Login con Gianni (`is_admin=true`, non verificato).
3. Logout o nuovo contesto.
4. Login con Andrea (`is_admin=false`, verificato).

Atteso osservato in local:

- entrambi possono accedere alla dashboard Filament in ambiente local;
- questo e dovuto al fatto che `User` non implementa `FilamentUser` e il middleware Filament in local non blocca utenti non `FilamentUser`.

Verifica:

- screenshot o testo dashboard con nome utente;
- DB conferma `users.is_admin` diverso tra Gianni e Andrea.

Esito richiesto:

- segnalare come rischio auth/admin;
- non assumere che `is_admin=true` sia realmente il gate dell'admin panel finche non viene implementata policy/access control esplicita.

### Approvazione community

Azione:

1. Creare community pending da UI organizer.
2. Entrare in Filament `Communities`.
3. Aprire edit community.
4. Cambiare status in `active`.
5. Provare anche `reject`.

Atteso UI:

- resource admin consente edit dei campi community;
- lo status e un campo form testuale/select secondo resource corrente;
- dopo active, la community dovrebbe essere usabile come community approvata.

Verifica DB:

- `communities.status` passa a `active` o `reject`;
- owner invariato.

Verifica UI app:

- `has_active_community` dell'owner diventa vero dopo active;
- dashboard mostra link community/eventi;
- community rejected/pending non dovrebbe abilitare flussi che richiedono active, se il requisito lo prevede. Nel codice alcune route non filtrano active: segnalare eventuale mismatch.

### Approvazione evento

Azione:

1. Creare evento pending da organizer.
2. Fare preferire la community a un altro utente verificato.
3. Entrare in Filament `Events`.
4. Cambiare status evento in `active`.
5. Controllare home e detail.
6. Cambiare altro evento a `reject` o `terminate`.

Atteso UI:

- evento active compare nella ricerca/home se date valide;
- evento reject/terminate non compare in home;
- detail diretto va verificato per esposizione.

Verifica DB:

- `events.status` aggiornato;
- `jobs` resta vuota con queue sync;
- pivot `community_user` del follower presente prima dell'approvazione.

Verifica email:

- `CommunityFavoriteEventApproved` arriva subito agli utenti follower della community;
- non deve essere inviata a owner o utenti non follower, salvo requisito diverso.

### Altre risorse admin

Risorse da smoke-testare:

- `Users`: create/edit, `is_admin`, `email_verified_at`, avatar, password.
- `Tags`: create/edit/delete, icona Simple Icons, colori.
- `AddressBooks`, `Cities`, `Provinces`, `Regions`: CRUD base e relazioni.
- `Communities`: upload logo e status.
- `Events`: upload poster, status, community, address book.

Verifiche:

- CRUD crea, aggiorna e cancella la riga attesa;
- delete/bulk delete non rompe relazioni critiche;
- upload admin passa dai processor dedicati;
- il resource `Users` espone campi 2FA sensibili (`two_factor_secret`, `two_factor_recovery_codes`, `two_factor_confirmed_at`), gia indicati nella privacy spec come rischio.

## 7. CFP configurazione organizer

### CFP esterna

Azione:

1. Creare o editare evento.
2. Abilitare CFP.
3. Scegliere mode `external`.
4. Provare senza `external_url`.
5. Salvare con URL valido.
6. Testare status `draft`, `published`, `archived`.
7. Testare date opens/closes valide e closes prima di opens.

Atteso UI:

- URL esterno richiesto solo per mode external;
- date in formato `d-m-Y H:i`;
- closes deve essere dopo opens;
- published mostra CTA pubblica;
- draft/archived non mostrano CTA pubblica.

Verifica DB:

- `cfps.mode = 'external'`;
- `cfps.external_url` valorizzato;
- `cfps.cfp_template_id = null`;
- `cfps.status` coerente;
- `opens_at` e `closes_at` parsati correttamente.

Casi limite:

- verificare se CFP external published resta visibile fuori finestra temporale. Nel codice pubblico la CTA external guarda status e URL, non `isOpen()`.

### CFP interna e template

Azione:

1. Creare CFP interna usando template esistente.
2. Applicare template e salvare senza modifiche.
3. Modificare field di template non usato da altre CFP.
4. Modificare field di template gia usato da altra CFP.
5. Aggiungere e rimuovere field.
6. Testare tutti i tipi: text, textarea, select, multiselect, checkbox, url, email, number, date.

Atteso UI:

- mode internal nasconde URL esterno;
- selection template popola i field;
- label obbligatoria;
- select e multiselect richiedono opzioni;
- field required influenza validazione candidatura;
- number puo avere min/max in validation;
- salvataggio mantiene ordine e chiavi stabili.

Verifica DB:

- `cfps.mode = 'internal'`;
- `cfps.external_url = null`;
- `cfp_template_id` valorizzato;
- `cfp_template_fields` coerenti con campi UI;
- se template non e usato da altre CFP, viene aggiornato in place;
- se template e usato da altre CFP, viene creato template derivato/copy-on-write;
- field rimossi vengono cancellati e le risposte collegate possono essere eliminate per cascade.

Casi limite:

- rimozione field dopo candidature: detail organizer deve mostrare warning per risposte mancanti o non piu mappate quando applicabile;
- cambio tipo field dopo candidature puo rendere vecchie risposte incoerenti: verificare comportamento e segnalare.

## 8. Candidatura speaker CFP

### Accesso apply

Azione:

1. Guest apre `/events/{event}/cfp/apply`.
2. Utente non verificato apre la stessa route.
3. Speaker verificato apre CFP interna published aperta.
4. Speaker apre CFP internal draft/archived.
5. Speaker apre CFP internal chiusa o futura.
6. Speaker apre evento con CFP external.
7. Speaker apre evento senza CFP.

Atteso UI/HTTP:

- guest viene mandato al login;
- unverified viene mandato a verifica email;
- internal published aperta mostra form;
- draft/archived o senza CFP danno 404;
- internal chiusa/futura da route diretta da 403;
- external non ha form apply interno e deve dare 404 su route apply.

Verifica DB:

- nessuna candidatura creata solo aprendo form.

### Submit candidatura valida

Azione:

1. Login come Anna o nuovo speaker verificato.
2. Aprire CFP interna published aperta.
3. Compilare title e abstract.
4. Compilare tutti i field del template, includendo:
   - select con opzione valida;
   - multiselect con piu opzioni valide;
   - checkbox true/false;
   - URL valido;
   - email valida;
   - number nel range;
   - date valida.
5. Inviare.

Atteso UI:

- redirect a show evento;
- flash `Candidatura inviata.`;
- speaker puo vedere la candidatura in `/dashboard/cfp-submissions` se status attivo.

Verifica DB:

- riga `cfp_submissions` con:
  - `cfp_id` corretto;
  - `user_id` speaker;
  - `status = 'submitted'`;
  - `submitted_at` valorizzato;
  - title e abstract coerenti;
- una riga risposta per ogni field corrente in `cfp_submission_answers`;
- valori array/boolean/number/date serializzati in modo coerente con cast/model.

Verifica email:

- `CfpSubmissionReceived` all'organizer della community evento;
- `CfpSubmissionSubmitted` allo speaker;
- email presenti subito in Mailpit.

### Validazioni candidatura

Provare:

- title vuoto;
- abstract sotto 20 caratteri;
- required field vuoto;
- URL invalido;
- email invalida;
- number sotto min o sopra max;
- select con opzione non presente;
- multiselect con opzione non presente;
- date invalida;
- checkbox non valorizzato.

Atteso:

- nessuna riga submission o answer viene creata quando la validazione fallisce;
- errori mostrati vicino ai campi;
- Mailpit non riceve email.

### Multiple candidature

Azione:

1. Inviare due candidature valide dallo stesso speaker alla stessa CFP.

Atteso:

- il codice consente multiple submission per utente;
- lista speaker mostra entrambe se status non draft/withdrawn;
- organizer vede entrambe nei filtri.

Verifica DB:

- due righe `cfp_submissions` con stesso `user_id` e `cfp_id`, title diversi.

## 9. Revisione CFP organizer

### Lista candidature organizer

Azione:

1. Login come owner community dell'evento.
2. Aprire `/dashboard/communities/submissions`.
3. Usare filtro evento.
4. Usare filtro status.
5. Aprire con query `?event={id}` valida.
6. Provare evento non proprio nella query.

Atteso UI:

- lista mostra solo candidature di eventi owned dall'utente;
- default status selezionato `submitted`;
- filtri includono status attivi;
- candidature `draft` e `withdrawn` sono escluse dalla lista principale;
- query event valida prefiltra;
- query event non owned non espone dati.

Verifica DB:

- conteggi UI corrispondono a `cfp_submissions` filtrate per community owner e status.

### Detail candidatura e cambio stato

Azione:

1. Aprire detail `/dashboard/cfps/{cfp}/submissions/{submission}` come organizer owner.
2. Aprire lo stesso detail come utente non owner.
3. Aprire submission non appartenente alla CFP nella URL.
4. Cambiare status a `under_review`.
5. Salvare stesso status una seconda volta.
6. Cambiare status ad `accepted`.
7. Cambiare status a `rejected` o `withdrawn`.

Atteso UI/HTTP:

- owner vede title, abstract, speaker, risposte e select status;
- non owner riceve 403;
- submission non appartenente a CFP riceve 404;
- status cambia e mostra successo;
- salvare lo stesso status non deve inviare nuova email;
- status `withdrawn` puo essere selezionato dal detail anche se poi sparisce dalle liste attive.

Verifica DB:

- `cfp_submissions.status` aggiornato;
- `updated_at` cambia;
- answers non vengono modificate dal cambio stato.

Verifica email:

- `CfpSubmissionStatusUpdated` allo speaker solo quando lo status cambia davvero;
- nessuna email duplicata per salvataggio senza cambio.

Casi limite:

- se un field del template non ha answer, detail mostra warning per risposta mancante;
- se un answer non ha piu field associato dopo rimozione field, verificare se viene mostrata o persa per cascade e documentare.

## 10. Dashboard utente

### Dashboard principale

Azione:

1. Aprire `/dashboard` come Andrea.
2. Aprire come Anna.
3. Aprire come Marco non verificato.
4. Aprire come nuovo utente verificato senza community.

Atteso UI:

- Andrea vede metriche bookmark, favorite community, eventi pending/active delle sue community;
- link community/eventi/submissions/new event visibili quando `has_active_community` e true;
- Anna vede metriche ma non link organizer se non ha community active;
- Marco viene bloccato dalla verifica email;
- utente verificato senza community non deve causare errori sulla dashboard.

Verifica DB:

- conteggi corrispondono a pivot e status events:
  - bookmark eventi da `event_user`;
  - community favorite da `community_user`;
  - eventi pending/active owned via community.

### Le mie candidature

Azione:

1. Speaker con submission submitted/under_review/accepted/rejected apre `/dashboard/cfp-submissions`.
2. Speaker con solo draft/withdrawn apre la pagina o menu.

Atteso UI:

- mostra solo submission dell'utente corrente;
- esclude `draft` e `withdrawn`;
- menu mostra voce solo se ci sono submission non draft/non withdrawn;
- non ci sono azioni edit/withdraw nella UI corrente.

Verifica DB:

- righe filtrate da `cfp_submissions.user_id`;
- status coerenti con UI.

## 11. Endpoint ausiliari

### Location select

Azione:

1. Chiamare `/find-location?type=all&search=...`.
2. Chiamare `/find-location?type=city&search=...`.
3. Chiamare con `selected`.
4. Chiamare senza search.

Atteso:

- `type=all` ritorna comuni/province/regioni interleavati;
- `type=city` ritorna comuni con provincia/regione;
- `selected` risolve valore esistente;
- search vuota ritorna lista vuota;
- payload contiene `value`, `label`, `type` e metadati attesi.

Verifica DB:

- risultati coerenti con tabelle cities, provinces, regions.

### Tag select

Azione:

1. Chiamare `/find-tags?search=...`.
2. Chiamare con `selected[]`.
3. Testare paginazione.

Atteso:

- search vuota ritorna lista vuota;
- selected risolve tag esistenti;
- risultati includono metadati icon/color quando disponibili.

Verifica DB:

- risultati coerenti con tabella `tags`.

## 12. Email matrix

| Flusso | Trigger | Destinatario atteso | Verifica Mailpit |
| --- | --- | --- | --- |
| Registrazione | nuovo utente | utente registrato | email verifica con link firmato |
| Resend verifica | `/email/verify` resend | utente autenticato | nuova email verifica |
| Password reset | forgot password | utente richiesto | link reset con token |
| Community creata | create community | creator | notifica creazione community |
| Community creata | create community | tutti `is_admin=true` | notifica admin nuova community |
| Evento creato | create event | creator | notifica creazione evento |
| Evento creato | create event | tutti `is_admin=true` | notifica admin nuovo evento |
| Evento approvato | status passa active | follower community | `CommunityFavoriteEventApproved`, immediata con queue sync |
| CFP submission | speaker invia candidatura | organizer | `CfpSubmissionReceived` |
| CFP submission | speaker invia candidatura | speaker | `CfpSubmissionSubmitted` |
| Cambio status CFP | organizer cambia status | speaker | `CfpSubmissionStatusUpdated`, solo se status cambia |

Per ogni email controllare:

- incremento del conteggio Mailpit rispetto al baseline;
- destinatario;
- subject o tipo notifica;
- link principali quando presenti;
- assenza di duplicati quando l'azione non cambia stato.

## 13. DB verification matrix

| Area | Tabelle principali | Cosa verificare |
| --- | --- | --- |
| Auth | `users`, `password_reset_tokens` | verify email, password hash, token reset |
| Community | `communities`, `community_user` | owner, status, slug, favorite pivot |
| Eventi | `events`, `event_user`, tag pivot, `address_books` | status, owner via community, bookmark, address, tag |
| CFP | `cfps`, `cfp_templates`, `cfp_template_fields` | mode, status, dates, template reuse/copy-on-write |
| Submission | `cfp_submissions`, `cfp_submission_answers` | status, submitted_at, answers per field |
| Upload | path su `users`, `communities`, `events` | file processati e path salvati |
| Queue | `jobs` | vuota con `QUEUE_CONNECTION=sync` |
| Search | `events` + indice Meilisearch | home mostra solo active non conclusi |

## 14. Rischi e comportamenti da segnalare

Questi punti devono entrare nel report finale dell'audit se confermati:

- CTA external CFP published non sembra controllare open/close date;
- link calendario evento punta a `/`;
- UI profilo non espone cambio email;
- route Fortify 2FA esistono ma non c'e flusso UI dedicato;
- resource admin Users espone campi 2FA sensibili.
- il security gate segnala advisory runtime gia presenti nel lockfile: quattro
  advisory Composer medium (`guzzlehttp/guzzle`, `guzzlehttp/psr7`,
  `phpseclib/phpseclib`) e uno npm high (`form-data`).

### Rilievi architetturali aperti

I punti seguenti derivano dalla review statica di repository e documentazione.
Sono problemi architetturali o infrastrutturali da risolvere, non singoli bug UI.
La priorita indica l'ordine consigliato di approfondimento:

- `P0`: rischio immediato per sicurezza o avvio affidabile dell'applicazione;
- `P1`: rischio elevato per consistenza, affidabilita operativa o qualita delle release;
- `P2`: rischio progressivo per manutenibilita, scalabilita o allineamento documentale.

| Priorita | Problema | Evidenza statica | Obiettivo architetturale |
| --- | --- | --- | --- |
| P0 | Autorizzazione e visibilita non hanno un confine uniforme (implementato, chiusura formale sospesa) | La mitigazione e implementata tramite Policy, gate Filament, scope pubblici e ri-autorizzazione Livewire; test PHP ed E2E sono verdi. Il rilievo resta elencato finche il security gate complessivo non torna verde, come richiesto dalla Definition of Done del task. | Risolvere gli advisory di dipendenza registrati sopra, rieseguire `./scripts/security/run.sh` e quindi rimuovere formalmente questo rilievo. |
| P0 | Il bootstrap e il contratto ambiente non sono riproducibili da checkout pulito | `bootstrap/providers.php` registra `App\Providers\VoltServiceProvider`, ma la classe non e presente nel repository. Le docs dichiarano Docker/PostgreSQL/Meilisearch come percorso canonico, mentre `.env.example` usa SQLite e non dichiara la configurazione Scout/Meilisearch necessaria. | Rendere esplicito e verificabile un unico contratto di bootstrap/configurazione per locale, test e produzione. |
| P1 | Il confine transazionale non include DB, file, email e indice di ricerca | Nei flussi evento indirizzi e immagini vengono creati prima della transazione che salva l'evento; le email partono dopo il commit ma nella stessa request. Scout ha `after_commit=false`. Errori intermedi possono produrre dati, file o feedback utente incoerenti. | Definire un application service che coordini la transazione DB e side effect after-commit, con compensazione o cleanup per i file e operazioni idempotenti. |
| P1 | Queue e notifiche non hanno una semantica operativa unica | `QUEUE_CONNECTION` usa `database` come default, ma `docker-compose.yml` non definisce un worker. Alcune mail sono sincrone, altre passano da job; `Event::booted()` dispatcha il job dalla lifecycle del model e le connessioni queue hanno `after_commit=false`. | Stabilire worker, retry, failed job, idempotenza e dispatch after-commit come parte del runtime, separando i side effect dai model. |
| P1 | L'integrita referenziale del database e applicata in modo disomogeneo | Diverse migration storiche usano `foreignIdFor()` senza `constrained()` per eventi, community, address book e gerarchia geografica. La pivot `event_tag` non ha FK o vincolo univoco composto, mentre il dominio CFP recente usa vincoli espliciti. | Portare le invarianti strutturali nel database con FK, strategie on-delete e unicita coerenti, verificando prima i dati esistenti su PostgreSQL. |
| P1 | La pipeline standard non verifica l'architettura runtime reale | `phpunit.xml` usa SQLite in-memory, queue sync e Scout null. La CI principale non esegue Larastan, security scan, Playwright, PostgreSQL/Meilisearch integration test o i test del package locale. Il workflow lint esegue Pint in modalita modificante invece di usarlo come gate read-only. | Aggiungere livelli CI distinti: feedback rapido SQLite, integrazione PostgreSQL/Meilisearch e gate di qualita/security/frontend. |
| P1 | Manca una topologia operativa di produzione | La documentazione descrive solo l'ambiente locale. Non sono definiti deploy, migration/rollback, worker, scheduler, object storage, backup/restore, TLS, secret management, readiness, metriche, alerting o disaster recovery. Sessioni, cache e queue convergono di default sul database applicativo. | Documentare e validare una topologia production minima con responsabilita, persistenza, recovery e osservabilita esplicite. |
| P2 | I confini del monolite sono convenzioni, non moduli applicativi enforceable | Pagine Livewire estese coordinano validazione, mapping, upload, persistenza, email ed errori. Alcune Action istanziano direttamente altre Action; i model espongono URL, dipendono da `auth()` e generano job. | Consolidare il monolite per moduli di dominio e mantenere Livewire come adapter UI, senza introdurre microservizi. |
| P2 | Il ciclo di vita di Meilisearch non e definito | L'import dopo reset e manuale, non esistono una policy di rebuild/freshness o un fallback documentato. Il servizio Compose non ha healthcheck e l'app dipende solo dal suo stato `started`. | Definire inizializzazione, reindicizzazione, health/readiness, gestione degli errori e aspettative di consistenza tra PostgreSQL e indice. |
| P2 | Documentazione e configurazione mostrano drift | Il README indica Laravel 12 e Vite 7, mentre Composer, package.json e docs agentiche indicano Laravel 13 e Vite 8. Le note di audit descrivono rischi importanti, ma mancano decisioni formali su autorizzazione, consistenza, deployment e requisiti non funzionali. | Allineare gli entry point e trasformare le invarianti architetturali in specifiche verificabili e decision record. |

Piano del primo rilievo P0: [`tasks/authorization-visibility-boundary.md`](../../../tasks/authorization-visibility-boundary.md).

### Verifica e aggiornamento dei rilievi

Per ogni punto architetturale:

1. confermare l'evidenza nel runtime Docker e nel PostgreSQL locale quando applicabile;
2. collegare eventuali bug concreti emersi durante la flow audit;
3. aprire un task di implementazione separato con acceptance criteria e verifiche richieste;
4. mantenere il punto in questa sezione finche l'obiettivo architetturale non e verificato;
5. quando risolto, rimuoverlo da questa lista e registrare la decisione stabile in `docs/project/technical-decisions.md` o nella spec tecnica pertinente.
