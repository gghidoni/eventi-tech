# Docs di sviluppo (Eventi Tech Livewire)

Questa cartella contiene specifiche pratiche (basate sul codice esistente) per aiutare lo sviluppo di nuove feature nel progetto.

## Indice

### Agents

- `agents/README.md` Indice agentico a livelli
- `agents/quickstart.md` Entry point operativo rapido
- `agents/workflow.md` Sequenza di lavoro, verifiche e uso Docker/host
- `agents/repo-map.md` Mappa repository e pattern chiave
- `agents/tools.md` Istruzioni operative per Context7, Playwright, Lighthouse e tooling agente

### Project

- `project/README.md` Indice di alto livello del progetto
- `project/intent.md` Intento prodotto, utenti e use case
- `project/architecture.md` Struttura applicativa, flussi e confini tecnici
- `project/technical-decisions.md` Scelte tecniche principali e tradeoff

### UI

- `ui/design.md` Guida design agentica per creare pagine, componenti e stili coerenti
- `ui/layout.md` Layout base, font, colori, background, classi globali
- `ui/forms.md` Input/select/textarea, errori, upload file con anteprima
- `ui/buttons-links.md` Bottoni principali e “link button” (underline)
- `ui/cards-panels.md` Card, glassmorphism, menu a tendina
- `ui/async-select.md` Componenti select (DrPshtiwan livewire-async-select) e endpoint
- `ui/date-picker.md` Date/time picker con Flatpickr + Alpine + Livewire
- `ui/frontend-testing.md` Playwright + Lighthouse per smoke test, audit e debug frontend

### Backend

- `backend/routing.md` Mappa rotte e “pages::...” (Livewire pages)
- `backend/livewire.md` Pattern Livewire (Volt pages, componenti, eventi, layout)
- `backend/actions.md` Actions (`execute()`), transazioni, injection in Livewire
- `backend/models.md` Accessor, relazioni, URL helper, bookmark state
- `backend/cfps.md` Dominio CFP, template, campi custom e candidature
- `backend/uploads.md` Upload immagini e storage disk (`posters`, `logos`, `public/avatars`)
- `backend/search.md` Scout/Meilisearch e ricerca eventi
- `backend/privacy-cookie.md` Analisi cookie/privacy del repository e linee guida operative minime
- `backend/authorization.md` Matrice ruoli, visibilita pubblica, ownership dashboard e accesso Filament

### Infrastructure

- `infrastructure/environments.md` Profili ambiente, bootstrap, doctor e fresh-checkout smoke
