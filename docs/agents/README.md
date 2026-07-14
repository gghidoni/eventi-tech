# Agent Docs

Indice agentico del repository.

Questa sezione e pensata a livelli:

- entrypoint corti per orientarsi subito
- pagine intermedie per lavorare senza rumore
- spec tecniche gia esistenti in `docs/backend/*` e `docs/ui/*` per il dettaglio implementativo

## Ordine di lettura

1. [quickstart.md](./quickstart.md)
2. [workflow.md](./workflow.md)
3. [repo-map.md](./repo-map.md)
4. [tools.md](./tools.md)
5. [mcp-context7.md](./mcp-context7.md)
6. [security.md](./security.md)
7. [flow-audit/README.md](./flow-audit/README.md)

## Accesso al database locale

Quando un agente deve leggere schema o dati dal PostgreSQL locale, usa sempre il workflow MCP documentato in [mcp-postgres.md](./mcp-postgres.md).

Skill associata: `$eventi-tech-db`.

Non usare credenziali applicative o query dirette fuori procedura come fallback implicito.

## Accesso alle email locali

Quando un agente deve verificare se una mail e arrivata in Mailpit o capire che mail e, usa il workflow MCP documentato in [mcp-mailpit.md](./mcp-mailpit.md).

Skill associata: `$eventi-tech-mailpit`.

## Documentazione esterna via Context7

Quando serve documentazione aggiornata di librerie o framework, usa il server
MCP locale descritto in [mcp-context7.md](./mcp-context7.md).

## Audit completo dei flussi

Quando un agente deve testare tutti i flussi applicativi end to end, usa la mappa in [flow-audit/README.md](./flow-audit/README.md). Include baseline runtime, queue immediata con `QUEUE_CONNECTION=sync`, mappa UI/DB/email e goal operativo per Codex.

## Bootstrap e ambienti

Il runtime locale canonico, il doctor e la distinzione tra Docker e test SQLite
sono definiti in [../infrastructure/environments.md](../infrastructure/environments.md).

## Quando fermarsi

Se le spec di `docs/` non coprono il caso, oppure sono ambigue o in conflitto, non implementare per assunzione: fermati e chiarisci.

## Collegamenti alle spec tecniche

### Backend

- [../backend/routing.md](../backend/routing.md)
- [../backend/livewire.md](../backend/livewire.md)
- [../backend/actions.md](../backend/actions.md)
- [../backend/models.md](../backend/models.md)
- [../backend/cfps.md](../backend/cfps.md)
- [../backend/uploads.md](../backend/uploads.md)
- [../backend/search.md](../backend/search.md)
- [../backend/authorization.md](../backend/authorization.md)

### UI

- [../ui/design.md](../ui/design.md)
- [../ui/layout.md](../ui/layout.md)
- [../ui/forms.md](../ui/forms.md)
- [../ui/buttons-links.md](../ui/buttons-links.md)
- [../ui/cards-panels.md](../ui/cards-panels.md)
- [../ui/async-select.md](../ui/async-select.md)
- [../ui/date-picker.md](../ui/date-picker.md)
- [../ui/frontend-testing.md](../ui/frontend-testing.md)
