# Full flow audit

Questa cartella e la mappa operativa per un agente che deve validare in locale tutti i flussi applicativi di Eventi Tech Livewire con browser, database, inbox email e controlli finali.

Non e un test report: e la traccia di cosa testare, cosa aspettarsi e come verificare che ogni passaggio abbia prodotto lo stato corretto.

## Ordine di lettura

1. [runtime-baseline.md](runtime-baseline.md): ambiente locale, seed, ruoli, strumenti e note critiche.
2. [flow-map.md](flow-map.md): mappa dettagliata dei flussi UI, DB, email e casi limite.
3. [validation-goal.md](validation-goal.md): goal da dare a Codex per eseguire il test completo end to end.

## Principi vincolanti

- Prima di testare o cambiare codice, rileggi le specifiche rilevanti in `docs/`.
- Se una specifica manca, e ambigua o confligge con il codice, fermati e segnala il conflitto.
- Usa Playwright per la GUI, MCP PostgreSQL per verifiche DB e MCP Mailpit per email.
- Durante i test i job devono essere immediati: usa `QUEUE_CONNECTION=sync` e aspettati che gli effetti dei job siano gia visibili dopo l'azione UI.
- In locale e consentito ripristinare database, seed e dati generati, ma non sovrascrivere modifiche utente non richieste.

## Ambito

La mappa copre:

- navigazione pubblica, ricerca, dettagli evento e community;
- registrazione, login, verifica email, password reset, profilo e logout;
- preferiti evento e community;
- creazione e modifica community;
- creazione, modifica e approvazione eventi;
- configurazione CFP esterne e interne;
- candidatura speaker, revisione organizer e cambio stato;
- risorse admin Filament e approvazioni;
- upload, email, queue, job, DB e casi di sicurezza osservati.

## Fonti usate per costruire la mappa

- Specifiche in `docs/agents/*`, `docs/backend/*` e `docs/ui/frontend-testing.md`.
- Route Laravel e componenti Livewire in `routes/`, `app/Livewire/`, `app/Actions/`, `app/Models/`, `app/Filament/Resources/`.
- Seeder in `database/seeders/`.
- Runtime locale con container Docker attivi.
- MCP Mailpit.
- MCP PostgreSQL tramite server repo avviato da script locale, perche il tool DB diretto nel sandbox non aveva accesso al socket Docker.
- Playwright su `/admin/login`, con verifica reale dell'accesso Filament in ambiente locale.
