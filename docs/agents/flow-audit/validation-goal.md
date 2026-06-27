# Validation goal

Usa questo goal quando devi chiedere a Codex di eseguire il test completo dei flussi locali.

## Goal

Esegui un audit end to end completo dell'app Eventi Tech Livewire in locale, usando tutti gli strumenti disponibili: Playwright per la GUI, MCP PostgreSQL per verifiche DB, MCP Mailpit per email, Docker/artisan per reset e seed, e i comandi QA del repository. Devi testare tutti i flussi descritti in [flow-map.md](flow-map.md), partendo dalla baseline in [runtime-baseline.md](runtime-baseline.md).

Durante i test imposta i job immediati: `QUEUE_CONNECTION=sync`. Dopo ogni azione che dispatcha job o notifica, ci aspettiamo che DB ed email siano gia aggiornati senza worker asincroni.

## Istruzioni operative

1. Leggi prima:
   - `docs/agents/README.md`
   - `docs/agents/quickstart.md`
   - `docs/agents/tools.md`
   - `docs/agents/mcp-postgres.md`
   - `docs/agents/mcp-mailpit.md`
   - `docs/backend/*` rilevanti per auth, Livewire, actions, models, CFP, upload, search e privacy
   - `docs/ui/frontend-testing.md`
   - `docs/agents/flow-audit/runtime-baseline.md`
   - `docs/agents/flow-audit/flow-map.md`
2. Avvia o verifica Docker.
3. Ripristina un baseline ripetibile con `migrate:fresh --seed`, salvo diversa istruzione esplicita.
4. Verifica `.env` e config: `QUEUE_CONNECTION=sync`; poi `php artisan config:clear`.
5. Registra baseline DB e Mailpit prima di iniziare.
6. Crea seed supplementari solo se necessari a coprire i casi non presenti nel seed base.
7. Usa Playwright con contesti separati per guest, organizer verificato, speaker verificato, utente non verificato e admin/Filament.
8. Per flussi multiutente usa piu tab o piu contesti, per esempio follower community, organizer e admin nello stesso scenario di approvazione evento.
9. Dopo ogni mutazione UI verifica subito:
   - stato UI;
   - righe DB interessate;
   - email attese in Mailpit;
   - assenza di job pendenti quando queue sync e attesa.
10. Dove trovi un comportamento diverso dalle specifiche, non normalizzarlo: documentalo come bug, rischio o conflitto docs/codice.
11. Alla fine esegui i controlli richiesti da `AGENTS.md` in base a cosa hai toccato. Per audit UI completo includi almeno frontend test; se modifichi codice PHP esegui lint e analyse.

## Copertura minima obbligatoria

Devi coprire almeno:

- public home, search, location, tag, detail evento e detail community;
- registrazione, verifica email, resend, login, logout, rate limit, password reset;
- profilo, avatar e cambio password;
- bookmark evento e favorite community;
- create/edit community e notifiche;
- create/edit evento online, in presenza e hybrid, tag, address, poster e notifiche;
- approvazione community ed evento da Filament;
- notifica follower su evento approvato con queue sync;
- accesso Filament con admin e con non-admin, evidenziando il comportamento locale osservato;
- CFP esterna draft/published/archived;
- CFP interna con template, copy-on-write, tutti i field type e validazioni;
- submit candidatura speaker, email organizer/speaker e DB answers;
- lista candidature organizer, detail, cambio status, email status update;
- `Le mie candidature`;
- route e casi limite non autorizzati: guest, unverified, non owner, event/submission mismatch;
- endpoint `/find-location` e `/find-tags`;
- upload logo, poster e avatar;
- verifica finale DB/Mailpit/report.

## Output atteso

Produci un report finale con:

- scenario eseguito;
- risultato pass/fail;
- evidenza UI sintetica;
- query o verifica DB usata;
- email attese e trovate;
- bug, rischi o conflitti docs/codice;
- test automatizzati mancanti o da aggiungere;
- comandi QA eseguiti e risultato.

Il report deve distinguere:

- requisiti confermati dalle specifiche;
- comportamento confermato dal codice;
- comportamento osservato nel runtime;
- inferenze dell'agente.

## Stop conditions

Fermati e chiedi chiarimenti solo se:

- una specifica in `docs/` contraddice un'altra specifica e non c'e modo sicuro di decidere;
- un reset DB distruggerebbe dati che l'utente ha chiesto di preservare;
- Docker o i servizi locali non sono avviabili dopo tentativi ragionevoli;
- MCP DB o MCP Mailpit sono indisponibili e non puoi verificare in modo equivalente.
