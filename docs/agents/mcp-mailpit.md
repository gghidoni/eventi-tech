# MCP Mailpit Locale

## Scopo

Questa e la procedura canonica per leggere Mailpit locale tramite MCP.

Skill associata: `$eventi-tech-mailpit` in `.agents/skills/eventi-tech-mailpit`.

Usala quando un agente deve capire:

- se una mail e arrivata
- a chi e stata inviata
- che tipo di mail e
- quale subject/body/link contiene
- se un flusso auth, password reset, verifica email o CFP ha prodotto email

Non usarla per:

- inviare email
- cancellare messaggi
- modificare tag o stato
- leggere provider email reali

## Scelta tecnica

Usa il server MCP Mailpit locale custom del repo.

Il server MCP configurato nel repo e `eventi-tech-mailpit`, definito in `.codex/config.toml`.

Componenti:

- `scripts/agents/mcp-mailpit-server.mjs`: server MCP stdio custom read-only
- `scripts/agents/run-mcp-mailpit.sh`: runner usato da Codex
- `scripts/agents/test-mcp-mailpit.mjs`: smoke test protocollo MCP + API Mailpit

Il server legge l'API HTTP locale di Mailpit:

```text
http://127.0.0.1:8025
```

Puoi sovrascriverla con:

```bash
MAILPIT_BASE_URL=http://127.0.0.1:8025
```

Nota: Mailpit marca come letto il messaggio quando si usa `GET /api/v1/message/{ID}`. Il server MCP evita quell'endpoint nei tool ordinari e usa lista messaggi + sorgente raw per dettagli, identificazione e link.

## Tool disponibili

- `mailpit_info`: informazioni runtime e contatori Mailpit
- `mailpit_list`: lista email recenti con metadati compatti
- `mailpit_search`: ricerca usando la sintassi Mailpit
- `mailpit_latest_for`: ultima email recente per destinatario
- `mailpit_get`: dettaglio compatto di una email
- `mailpit_identify`: classifica il tipo di email
- `mailpit_extract_links`: estrae link dal body

## Workflow consigliato

Per rispondere "e arrivata la mail?":

1. Usa `mailpit_latest_for` con email destinatario.
2. Se serve restringere, passa `subject_contains`.
3. Usa `mailpit_identify` sul messaggio trovato.
4. Usa `mailpit_extract_links` se devi recuperare link verifica/reset.

Per capire "che mail e?":

1. Usa `mailpit_list` con limit basso.
2. Usa `mailpit_identify` sull'id rilevante.
3. Usa `mailpit_get` con `include_body: true` solo se serve preview corpo.

## Prerequisiti

Avvia lo stack locale:

```bash
docker compose up -d
```

Verifica Mailpit:

```bash
curl -I http://127.0.0.1:8025/
```

## Smoke test

Esegui:

```bash
node scripts/agents/test-mcp-mailpit.mjs
```

Il test valida:

- handshake MCP stdio
- lista tool
- API Mailpit raggiungibile
- lista messaggi recenti
- identificazione ultimo messaggio, se presente
- coerenza tra `kind` di `mailpit_list` e `mailpit_identify` sui messaggi riconosciuti
- estrazione link utilizzabili da body quoted-printable
- contatore `Unread` invariato dopo le letture

## Fonti API

Mailpit espone una REST API v1 per accesso, ricerca ed eliminazione messaggi. In questo repo usiamo solo endpoint di lettura.
