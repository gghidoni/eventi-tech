# UI: Frontend Testing e Audit

## Obiettivo

Dotare il repo di una base unica per:

- navigare l'app in un browser reale
- verificare errori JavaScript lato client
- intercettare richieste fallite
- raccogliere trace, screenshot e video in caso di failure
- misurare performance, best practices, accessibility e SEO

## Scelta strumenti

### Playwright come runner principale

Playwright e la scelta primaria per questo repo perche:

- pilota un browser reale e copre bene interazioni Livewire
- offre auto-waiting e assertion web-first, piu stabile dei test DOM grezzi
- espone eventi utili per errori console, `pageerror` e `requestfailed`
- registra trace, screenshot e video senza tooling aggiuntivo

### Lighthouse CI come audit complementare

Lighthouse CI completa Playwright su aree che non conviene reinventare:

- performance
- accessibility
- best practices
- SEO

Nel repo e configurato in modalita `desktop` e salva i report in `storage/testing/lighthouse`.

### Chrome DevTools: utile, ma non come entrypoint principale

Chrome DevTools Protocol e il layer basso di ispezione del browser. E potente per profiling e debugging fine, ma nel repo conviene usarlo indirettamente tramite Playwright o Lighthouse.

Conclusione pratica:

- `Playwright` per navigazione, smoke test, errori client, HTML e flussi
- `Lighthouse CI` per punteggi e audit
- `Chrome DevTools` come supporto diagnostico, non come framework principale

## Integrazione con lo stack locale

### Modalita consigliata: app in Docker, test da host

Il repo espone l'app su `http://127.0.0.1:8083` tramite `nginx`.

Questo e il target predefinito sia di Playwright sia di Lighthouse:

- `PLAYWRIGHT_BASE_URL=http://127.0.0.1:8083`
- `LIGHTHOUSE_BASE_URL=http://127.0.0.1:8083`

Questa scelta mantiene:

- PHP, PostgreSQL, Meilisearch e Mailpit nei container esistenti
- browser runner separato dal container applicativo
- setup semplice per agenti e sviluppo locale

## Comandi

### Setup iniziale

```bash
docker-compose up -d
npm run build
npm run frontend:install
```

### Esecuzione test browser

```bash
npm run frontend:test
npm run frontend:test:headed
npm run frontend:test:ui
npm run frontend:test:debug
```

### Esecuzione audit

```bash
npm run frontend:audit
npm run frontend:check
```

## Best practice operative

### 1. Usa URL stabili e ambiente noto

- punta ai servizi Docker del repo tramite `localhost:8083`
- esegui `npm run build` prima degli audit, cosi il frontend usa asset compilati e ripetibili

### 2. Fallisci su errori browser veri

Nei test E2E vengono intercettati:

- `console error`
- `pageerror`
- `requestfailed`

Questa e la baseline minima per evitare regressioni silenziose lato Livewire, Alpine o asset.

### 3. Preferisci smoke test robusti

- verifica heading, form e landmark reali
- evita selettori troppo legati al CSS
- usa `getByRole`, `getByLabel`, `getByPlaceholder` quando possibile
- evita anche endpoint assoluti verso host Docker interni; nelle view Blade usa `route(...)`

### 4. Conserva artefatti di failure

- trace Playwright
- screenshot
- video
- report HTML Playwright
- report Lighthouse su filesystem

Sono piu utili del semplice log testuale quando l'agente deve diagnosticare una regressione UI.

### 5. Se serve un server non Docker, dichiaralo esplicitamente

`playwright.config.js` supporta un server avviato dal runner tramite `PLAYWRIGHT_WEB_SERVER_COMMAND`.

Esempio:

```bash
PLAYWRIGHT_BASE_URL=http://127.0.0.1:8000 \
PLAYWRIGHT_WEB_SERVER_COMMAND="php artisan serve --host=127.0.0.1 --port=8000" \
npm run frontend:test
```

Per questo repo resta comunque preferibile il target Docker su `8083`.

## File introdotti

- `playwright.config.js`
- `.lighthouserc.cjs`
- `tests/e2e/public-pages.spec.js`
- `tests/e2e/support/browserIssues.js`

## Nota pratica sul repo attuale

La smoke suite frontend e pensata anche per far emergere problemi infrastrutturali esistenti, non solo regressioni future. Se un test fallisce su una richiesta client, trattalo come segnale da investigare, non come rumore da mascherare.
