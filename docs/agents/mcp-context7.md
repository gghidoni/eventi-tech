# Context7 MCP

Il repository avvia Context7 come server MCP locale `stdio` tramite
`.codex/bin/context7-mcp.sh`.

Questo setup evita lo stato OAuth del server remoto, non versiona credenziali e
resta ripetibile per ogni clone del repository.

## Prerequisiti

- Node.js e npm disponibili sulla macchina host.
- Una API key personale creata nel dashboard Context7.

Al primo avvio `npx` puo scaricare `@upstash/context7-mcp` nella cache npm
locale.

## Configurazione della chiave

Opzione consigliata, valida per le shell locali:

```bash
export CONTEXT7_API_KEY="ctx7sk-..."
```

Per renderla persistente, aggiungere l'export al proprio file di configurazione
shell e riavviare Codex.

In alternativa, aggiungere la chiave al `.env` locale non versionato:

```dotenv
CONTEXT7_API_KEY=ctx7sk-...
```

Il wrapper legge esclusivamente `CONTEXT7_API_KEY`; non esegue `source .env`.

## Verifica

Controllare senza stampare la chiave:

```bash
echo ${CONTEXT7_API_KEY:+present}
```

Riavviare Codex dopo la configurazione: i server MCP vengono inizializzati
all'avvio della sessione. Verificare poi Context7 risolvendo una libreria comune,
per esempio Laravel.

## Troubleshooting

- `Context7 MCP requires CONTEXT7_API_KEY`: la chiave non e disponibile ne
  nell'ambiente del processo Codex ne nel `.env` locale.
- `Invalid API key`: la chiave e errata, revocata o scaduta; generarne una nuova
  dal dashboard Context7.
- `Invalid or expired OAuth token`: la sessione corrente usa ancora la vecchia
  configurazione HTTP/OAuth; chiudere e riaprire Codex dal repository.
- Timeout al primo avvio: verificare la connettivita npm e riprovare dopo che
  `npx` ha popolato la cache locale.
