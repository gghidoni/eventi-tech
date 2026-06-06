# Agent Workflow

## Sequenza standard

1. Identifica l'area del task.
2. Leggi le spec rilevanti in `docs/`.
3. Ispeziona il codice reale coinvolto.
4. Solo dopo proponi o applica modifiche.
5. Verifica con test o controlli mirati.
6. Riporta outcome, limiti e follow-up reali.

## Regole pratiche

- Preferisci cambiare il minimo necessario.
- Mantieni allineati codice, config e documentazione se il comportamento cambia.
- Se introduci un tool o un workflow nuovo, documenta anche come va usato, non solo dove vive il codice.
- Se un test fallisce per ambiente o tooling, distinguilo chiaramente da un bug applicativo.
- Per ogni modifica PHP, considera `Pint` e `Larastan` parte della verifica finale obbligatoria, non opzionale.

## Quando usare Docker

- Per PHP, Artisan, Composer, Pest, PHPStan e Pint: preferisci `docker exec eventi-tech ...`
- Le dipendenze `vendor/` e `node_modules/` devono vivere nel container, non condivise con l'host
- Per Playwright e Lighthouse: preferisci esecuzione host-side contro `http://127.0.0.1:8083`
- Per asset Vite buildati: assicurati che l'app non stia puntando a `public/hot` se vuoi audit o smoke test stabili

## Verifica minima attesa

- Backend change: `docker exec eventi-tech composer lint` + `docker exec eventi-tech composer analyse`, poi test o comando mirato nel container
- UI/frontend change: almeno Playwright smoke o verifica browser equivalente
- Tooling/docs change: prova del comando o del flusso documentato

## Shortcut consigliato

- Per una verifica backend completa, preferisci `docker exec eventi-tech composer qa`
