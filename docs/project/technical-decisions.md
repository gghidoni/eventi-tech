# Technical Decisions

## Perche Laravel + Livewire

La scelta favorisce:

- velocita di sviluppo nel monolite
- flussi CRUD e form complessi vicini al backend
- minore overhead rispetto a una SPA separata

Tradeoff:

- meno flessibilita di una frontend architecture completamente separata
- maggiore attenzione necessaria a pattern Livewire, query e stato UI

## Perche Actions dedicate

La logica di business viene estratta da controller e pagine Livewire quando supera la semplice orchestrazione.

Vantaggi:

- responsabilita piu chiare
- testabilita migliore
- minor accoppiamento tra UI e dominio

Tradeoff:

- piu classi da mantenere
- rischio di frammentazione se usate senza criterio

## Perche Meilisearch via Scout

La ricerca eventi ha bisogno di essere:

- piu rapida del semplice `LIKE`
- tollerante agli errori
- integrabile con filtri applicativi

Per questo il repo usa Scout per ottenere candidati di ricerca e poi rifinisce i risultati con Eloquent.

Tradeoff:

- esiste un layer in piu da mantenere e indicizzare
- bisogna distinguere bene tra record indicizzati e query finali dell'app

## Perche PostgreSQL in Docker e SQLite nei test

PostgreSQL e il database dell'unico runtime locale supportato, avviato tramite
Docker Compose. SQLite e riservato alla suite Pest rapida ed ermetica.

Vantaggi:

- un solo contratto locale riproducibile
- test piu veloci e semplici

Tradeoff:

- alcune differenze tra engine vanno tenute a mente
- la suite SQLite non sostituisce lo smoke integration PostgreSQL

Approfondimento: `docs/infrastructure/environments.md`.

## Perche Playwright e Lighthouse

### Playwright

Scelto per:

- testare il browser reale
- rilevare errori JavaScript e request fallite
- coprire flussi utente veri

### Lighthouse

Scelto per:

- audit di performance
- accessibility
- best practices
- SEO

Decisione chiave:

- Playwright e il runner di automazione
- Lighthouse e il misuratore qualitativo
- Chrome DevTools non e il driver principale del repo

## Perche documentazione a livelli

Il repo contiene sia specifiche tecniche sia istruzioni operative per agenti.

Una documentazione piatta tende a diventare rumorosa. Per questo la struttura scelta e:

- entrypoint corti
- approfondimenti intermedi
- spec dettagliate linkate solo quando servono

Obiettivo:

- orientamento rapido
- meno duplicazione
- piu chiarezza su cosa leggere prima e cosa approfondire dopo
