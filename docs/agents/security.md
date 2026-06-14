# Agent Security

## Obiettivo

Il baseline security del repo copre tre aree diverse:

- `composer audit` per vulnerabilita note nelle dipendenze PHP
- `npm audit` per vulnerabilita note nelle dipendenze frontend runtime
- `Semgrep` per static application security testing su codice e segreti

Questi controlli sono complementari a `Pint`, `Larastan` e ai test applicativi: non li sostituiscono.

## Scelte deliberate

### Perche non usiamo Enlightn

Per questo repo non e la scelta corretta:

- il pacchetto OSS `enlightn/enlightn` risulta abbandonato
- la compatibilita dichiarata si ferma a Laravel 11
- il progetto e su Laravel 13

Quindi il flusso security implementato qui usa strumenti attivamente supportati e compatibili con la stack corrente.

### Perche `npm audit --omit=dev`

Il gate di sicurezza frontend del repo e focalizzato sul runtime reale distribuito:

- le dipendenze di build e tooling (`vite`, `laravel-vite-plugin`, `tailwindcss`, `lighthouse`, `concurrently`) sono `devDependencies`
- il comando blocca vulnerabilita `high` o superiori solo sulle dipendenze runtime
- gli advisory sui tool di sviluppo restano visibili con `npm audit`, ma non bloccano il flusso standard se non impattano l'app distribuita

## Comandi

### Backend dependencies

```bash
docker compose exec -T app composer security:php
```

### Frontend runtime dependencies

```bash
docker compose exec -T app composer security:frontend
```

### SAST e secrets scan

```bash
./scripts/security/semgrep.sh
```

### Suite completa

```bash
./scripts/security/run.sh
```

## Semgrep

Il repo usa Semgrep via Docker con immagine pin-nata:

- immagine: `semgrep/semgrep:1.163.0`
- ruleset: `p/php` e `p/secrets`
- output JSON: `storage/testing/security/semgrep.json`

Gli artefatti generati, i vendor, gli asset buildati e la documentazione pubblicata del package locale sono esclusi tramite `.semgrepignore`.

## Quando eseguirlo

- sempre dopo aggiornamenti dipendenze
- sempre dopo modifiche a auth, middleware, upload, validation, query raw, redirect, HTTP client, file handling
- prima di push significativi su branch di lavoro che toccano area backend o config

Per modifiche puramente di refactor interno senza superficie security rilevante, il baseline minimo resta `composer lint`, `composer analyse` e `composer qa`.
