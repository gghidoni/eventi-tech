# Runtime baseline

Questa baseline descrive lo stato da preparare prima di eseguire la mappa in [flow-map.md](flow-map.md).

## Ambiente locale

- URL app: `http://127.0.0.1:8083`
- Mailpit: `http://127.0.0.1:8025`
- Stack atteso: Laravel 13, Livewire 4, PHP 8.4, PostgreSQL 18, Meilisearch 1.44.
- Container principali: `eventi-tech`, `eventi-tech-nginx`, `eventi-tech-postgres`, `eventi-tech-meilisearch`, `eventi-tech-mailpit`.

Avvio minimo:

```bash
docker compose up -d
```

Reset canonico per un audit ripetibile:

```bash
docker compose exec -T app php artisan migrate:fresh --seed
docker compose exec -T app php artisan config:clear
```

Se il flusso di ricerca deve essere validato dopo un reset o dopo creazione massiva di eventi, reindicizzare Scout/Meilisearch:

```bash
docker compose exec -T app php artisan scout:import "App\\Models\\Event"
```

## Queue e job immediati

Durante il test completo la queue deve essere sincrona.

Controlli richiesti:

- `.env` deve avere `QUEUE_CONNECTION=sync`.
- Dopo eventuali modifiche a `.env`, eseguire `php artisan config:clear`.
- Dopo azioni che dispatchano job, verificare subito l'effetto senza aspettare worker esterni.
- La tabella `jobs` deve restare vuota per i job previsti in sync.

Flusso principale che dipende da questo:

- quando un evento passa ad `active`, il job `NotifyCommunityFollowersOfApprovedEvent` deve inviare subito le email `CommunityFavoriteEventApproved` agli utenti che hanno la community tra i preferiti.

## Strumenti obbligatori

Usa insieme questi strumenti, non uno solo.

- Playwright: navigazione reale, piu contesti/browser tab, ruoli diversi, console error, page error e request failed.
- MCP PostgreSQL: schema, campioni, query mirate e verifica di persistenza.
- MCP Mailpit: identificazione email, destinatari, subject, link di verifica/reset.
- Comandi Laravel dentro Docker per reset, seed, cache clear e QA finale.
- Lighthouse o `npm run frontend:audit` quando il flusso include controllo qualitativo frontend.

Per il DB seguire [docs/agents/mcp-postgres.md](../mcp-postgres.md). Il tool MCP deve usare credenziali read-only e non deve eseguire write.

Per le email seguire [docs/agents/mcp-mailpit.md](../mcp-mailpit.md). Il server MCP Mailpit e read-only: per una inbox pulita preferire il confronto per delta, oppure reinizializzare Mailpit con una procedura locale esplicita e documentata.

## Attori seed

Tutti gli utenti seed usano password `password`.

| Ruolo | Email | Stato | Note |
| --- | --- | --- | --- |
| Organizer verificato | `andrea.rossi@email.it` | email verificata, `is_admin=false` | owner community `Java Ancona`, community active |
| Speaker verificata | `anna.verdi@email.it` | email verificata, `is_admin=false` | candidata CFP nei seed |
| Organizer non verificato | `marco.bianchi@email.it` | email non verificata, `is_admin=false` | owner `Laravel Pordenone` active e `Wordpress Meetup Firenze` pending |
| Admin seed | `gianni.ghidoni@email.it` | email non verificata, `is_admin=true` | riceve notifiche admin, accesso dashboard app bloccato da `verified` |

Per test completi crea dati supplementari se il seed corrente non basta:

- utente verificato senza community, per testare route dashboard evento senza prerequisiti;
- utente verificato con sola community pending, per testare creazione evento da community non active;
- admin verificato, se serve distinguere tra admin app e admin Filament;
- follower di community prima di approvare un evento, per testare il job di notifica;
- eventi con CFP interna aperta, chiusa, futura, draft, published e archived;
- eventi pending, active, terminate e reject con URL noti, per testare visibilita pubblica diretta.

## Dati seed funzionali

Community seed canoniche:

| Community | Owner | Stato |
| --- | --- | --- |
| `Java Ancona` | Andrea | `active` |
| `Laravel Pordenone` | Marco | `active` |
| `Wordpress Meetup Firenze` | Marco | `pending` |

CFP seed canoniche:

- `Java & Spring Boot Workshop`: CFP interna `published` con template Java e candidature seed.
- `State Management in React con Redux`: CFP esterna `published`.
- Un evento con CFP esterna `draft`, che non deve mostrare CTA pubblica.
- Un evento con CFP esterna `archived`, che non deve mostrare CTA pubblica.

Template Java atteso:

- `level`: select required, opzioni `beginner`, `intermediate`, `advanced`;
- `format`: multiselect required, opzioni `talk`, `workshop`, `lightning`;
- `logistics`: textarea optional;
- `slides_url`: url optional;
- `first_time_speaker`: checkbox optional;
- `duration_minutes`: number required, min 10, max 120;
- `available_from`: date optional.

Template Laravel atteso:

- `track`: select required;
- `audience`: text required;
- `speaker_email`: email required.

## Note runtime osservate

Queste osservazioni sono importanti per interpretare i risultati.

- Nel runtime analizzato i container erano attivi e Mailpit era vuoto.
- Il DB locale puo essere sporco da audit Playwright precedenti. Per una verifica canonica eseguire sempre `migrate:fresh --seed`.
- Il tool MCP PostgreSQL diretto puo non vedere il socket Docker dal sandbox; in quel caso usare lo script repo MCP con Docker access e mantenere query read-only.
- In ambiente local, Filament consente l'accesso anche a utenti che non implementano `FilamentUser`. Il modello `User` non implementa `FilamentUser`; quindi un utente non admin valido puo entrare in `/admin`. Questo va testato e segnalato come comportamento di sicurezza, non assunto come requisito corretto.
- `is_admin` oggi e usato per notifiche admin e campi utente, ma non e il gate effettivo dell'accesso Filament in local.
- Le route pubbliche `events.show` e `communities.show` usano route model binding senza filtro status. Un evento pending/reject/terminate con URL noto puo risultare visibile anche se la home ricerca solo eventi active.

## Checklist prima di partire

1. Esegui reset e seed, salvo richiesta esplicita di testare DB sporco.
2. Verifica `.env` e config: `QUEUE_CONNECTION=sync`.
3. Apri Playwright con almeno tre contesti: guest, organizer verificato, speaker verificato.
4. Prepara un quarto contesto per admin/Filament e un quinto per utente non verificato.
5. Registra conteggi iniziali DB: users, communities, events, cfps, cfp_submissions, cfp_submission_answers, event_user, community_user, password_reset_tokens, jobs.
6. Registra conteggio iniziale Mailpit.
7. Esegui ogni flusso con verifica UI, DB ed email nello stesso passo, non solo alla fine.
