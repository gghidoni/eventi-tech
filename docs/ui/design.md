# UI: Design Guide per Agenti

## Scopo

Questa guida e il riferimento pratico per creare o modificare pagine, componenti e stili UI in Eventi Tech.

Usala prima di toccare:

- viste Blade o Livewire in `resources/views`
- classi CSS in `resources/css`
- asset visuali in `public/icons`, `public/images` o storage pubblico
- test Playwright o audit Lighthouse collegati alla UI

Questa guida non sostituisce le spec UI mirate: le collega e le rende operative. Se una regola qui e in conflitto con una spec piu specifica in `docs/ui/*`, ha priorita la spec specifica.

## Prima Di Implementare

1. Leggi questa guida.
2. Leggi la spec mirata:
   - layout, font, colori: `docs/ui/layout.md`
   - form: `docs/ui/forms.md`
   - bottoni e link: `docs/ui/buttons-links.md`
   - card e panel: `docs/ui/cards-panels.md`
   - async select: `docs/ui/async-select.md`
   - date picker: `docs/ui/date-picker.md`
   - test frontend: `docs/ui/frontend-testing.md`
3. Ispeziona almeno un esempio reale gia presente nel repo.
4. Se il caso non e coperto, fermati e chiedi chiarimenti prima di inventare un nuovo linguaggio visivo.

## Identita Del Prodotto

Eventi Tech e una piattaforma Laravel-first per scoprire eventi tech, pubblicare iniziative e gestire community.

La UI deve sembrare:

- tecnica, diretta e leggibile
- veloce da scansionare su mobile
- coerente tra discovery pubblica e dashboard operativa
- reattiva senza sembrare una SPA complessa

La UI non deve sembrare:

- una landing page marketing generica
- un social network
- un ticketing system completo
- una dashboard SaaS enterprise con troppi livelli visuali

## Principi Visivi

- Dark-first: il layout base monta sempre `class="dark"` sull'elemento `html`.
- Atmosfera unica: il body usa `--app-atmosphere-image`; navbar e overlay la riusano con `glass-nav` e `glass-overlay`.
- Monospace come default: il body forza `JetBrains Mono`.
- Brand font selettivo: usa `.font-anta` per titoli, label evento/community, badge e microcopy visualmente distintivo.
- Accenti limitati: usa cyan per azioni/link/stati informativi, pink per errori, attivo, bookmark e dettagli caldi, green per successi.
- Componenti compatti: l'app privilegia densita leggibile, non spaziature da landing page.
- Form standardizzati: usa `input-et`, `textarea-et`, `select-et`; non creare varianti locali senza motivo.
- Navigazione interna Livewire: usa `wire:navigate` per link interni.
- Iconografia esistente: prima cerca un'icona in `public/icons`; aggiungi nuove icone solo se coerenti per stile, colore e peso.

## Token E Colori

La source of truth dei token e `resources/css/app.css`.

| Ruolo | Token/classe | Valore attuale | Uso |
| --- | --- | --- | --- |
| Background | `--color-background` | `#2b2b2b` | base pagina |
| Testo neutro | `--color-white` / `text-gray-300` | `#c1c9cf` | testo principale |
| Cyan | `--color-cyan` / `text-cyan` | `#72b7c0` | link, icone info, azioni secondarie |
| Pink | `--color-pink` / `text-pink` | `#f19cba` | errori, active state, bookmark, dettagli |
| Green | `--color-green` | `#72c0a0` | successi |
| Accent | `bg-accent text-background` | dipende da dark theme | CTA piene auth |

Regole:

- Non introdurre palette parallele in singole view.
- Evita gradienti custom locali: l'atmosfera e gia nel body.
- Per stati disabilitati usa `disabled:opacity-50`.
- Per bordi neutri usa `border-gray-600` o border rgba gia presente nei componenti glass.
- Per testi secondari usa `text-gray-500`, `opacity-70` o `opacity-80` secondo gli esempi esistenti.

## Tipografia

Font disponibili:

- `JetBrains Mono`: font principale, gia applicato al body.
- `Anta`: utility `.font-anta`, caricata localmente.

Uso consigliato:

- Titoli pagina: `text-2xl` o `text-3xl`; aggiungi `.font-anta` solo se serve enfasi brand.
- Titoli evento/card: `.font-anta`, spesso con `font-bold`, `line-clamp-2`, `leading-[18px]`.
- Label compatte, badge, data/luogo: `.font-anta text-xs` o `.font-anta text-sm`.
- Form label e testo operativo: resta sul font body, salvo casi gia analoghi.

Evita:

- nuovi font
- tracking negativo
- scale tipografiche molto grandi dentro dashboard, card, form o menu
- testi lunghi dentro bottoni compatti senza `whitespace`, truncation o layout stabile

## Layout Base

Il layout principale e `resources/views/components/layouts/base.blade.php`.

Pattern da rispettare:

- Navbar fissa in alto con `glass-nav`, altezza pratica `72px`.
- Contenuto principale sotto navbar con `pt-[72px]`.
- Container pagina standard: usa `.page` quando possibile.
- `.page` applica `container mx-auto py-8 px-6 flex flex-col`.
- Titolo pagina standard: `.page-title`, ma molte view esistenti usano direttamente `text-2xl` o `text-3xl`.

Quando crei una pagina:

- Parti da `<div class="page">` per dashboard e pagine pubbliche semplici.
- Mantieni `px-6` come padding mobile base.
- Evita sezioni flottanti tipo card decorative attorno all'intera pagina.
- Usa griglie solo quando servono davvero; su mobile l'app e principalmente single-column.

## Pagine Pubbliche

Esempi:

- `resources/views/index.blade.php`
- `resources/views/livewire/events-search.blade.php`
- `resources/views/livewire/event-mini-card.blade.php`
- `resources/views/pages/events/⚡show.blade.php`
- `resources/views/pages/communities/⚡show.blade.php`

Pattern:

- La home mette subito ricerca e lista eventi, non una hero marketing.
- Le card evento pubbliche usano `glass-card`.
- Le detail page danno priorita a titolo, community, luogo, data, tag, CTA e poster.
- Poster e loghi sono contenuto, non decorazione: devono essere visibili, nitidi e con `object-cover`/`object-contain` coerente.
- I tag possono usare colori dinamici dal DB con inline style, come gia documentato nei commenti delle view.

Azioni pubbliche:

- Bookmark: icone `heart-pink-empty.svg` / `heart-pink-fill.svg`.
- Luogo: `location-cyan.svg` nelle card, `location-pink.svg` nel dettaglio evento.
- Date: `calendar-cyan.svg` o `clock-pink.svg` in base al contesto.
- CTA esterne: bottoni outline compatti con bordo bianco, icona e testo uppercase `.font-anta text-xs`.

## Dashboard E Area Operativa

Esempi:

- `resources/views/pages/dashboard/⚡index.blade.php`
- `resources/views/pages/dashboard/⚡profile.blade.php`
- `resources/views/pages/dashboard/communities/⚡create.blade.php`
- `resources/views/pages/dashboard/events/⚡create.blade.php`
- `resources/views/livewire/dashboard/communities/card.blade.php`

Pattern:

- La dashboard e piu sobria delle pagine pubbliche.
- Le card metriche usano bordo semplice: `border rounded-sm border-gray-600 p-3 h-32`.
- I link operativi sono spesso testo cyan, underline e icona.
- Le liste di gestione sono compatte, con avatar/logo piccolo e menu kebab.
- Evita glass-card per ogni pannello dashboard: usa glass solo se il pattern esistente lo richiede.

Regola pratica:

- Discovery pubblica: piu atmosfera, card glass, poster, tag.
- Gestione autenticata: piu densita, bordi semplici, form standard, meno decorazione.

## Card E Panel

Usa:

- `.glass-card` per card principali pubbliche, specialmente eventi.
- `.glass-panel` per dropdown, menu kebab e overlay contestuali.
- `border border-gray-600 rounded-sm` per card dashboard semplici.

Non fare:

- card dentro card
- intere pagine incapsulate in card decorative
- nuovi effetti glass locali copiati a mano
- radius casuali: base `rounded-md` o `8px`; `glass-panel` usa gia `12px` per dropdown sovrapposti

Pattern menu:

- trigger con `kebab-white.svg`
- stato booleano `menuOpen`
- `wire:click.outside="closeMenu"`
- panel `absolute right-0 ... w-48 glass-panel z-10`
- item `block px-4 py-2 text-xs text-gray-200 hover:bg-white/10 hover:text-white cursor-pointer`

## Form

Spec primaria: `docs/ui/forms.md`.

Classi standard:

- input testo/password/email: `input-et`
- textarea: `textarea-et`
- select: `input-et select-et`

Pattern errori:

```blade
@error('campo')
    <span class="text-pink text-xs">{{ $message }}</span>
@enderror
```

Pattern submit dashboard:

```blade
<button type="submit" class="flex items-center space-x-2 text-cyan underline mt-6">
```

Pattern submit auth:

```blade
<button class="w-full flex items-center justify-center bg-accent text-background px-4 py-2 rounded-md hover:bg-accent/90 disabled:opacity-50">
```

Regole:

- Non creare componenti input nuovi se bastano le classi standard.
- Mantieni altezza input `40px` quando possibile.
- Per icone dentro input usa wrapper `relative` e icona assoluta a destra o sinistra.
- Per checkbox usa stile compatto e dark-aware, come `rounded border-gray-600 bg-transparent`.
- Per URL opzionali, controlla i pattern backend: spesso `''` viene normalizzato a `null`.

## Upload E Anteprime

Pattern:

- label con `input-et ... cursor-pointer`
- input file reale `class="hidden"`
- anteprima con immagine esistente, temporaryUrl oppure placeholder dashed
- stato upload con `wire:loading wire:target="campo"` in `text-xs text-cyan`

Dimensioni ricorrenti:

- avatar/logo: `size-16 rounded-full object-cover border border-gray-600`
- poster evento: `h-40 rounded-sm object-cover border border-gray-600`
- placeholder: `border border-dashed border-gray-600 text-[10px] text-gray-500 text-center`

## Async Select

Spec primaria: `docs/ui/async-select.md`.

Regole:

- Usa i wrapper esistenti in `app/Livewire/Select/*` e `resources/views/livewire/select/*`.
- Usa endpoint con `route(...)`, non host Docker interni.
- Mantieni override dark/glass in `resources/css/app.css`.
- Non sostituire AsyncSelect con select custom locali per casi gia coperti.

Pattern:

- location: endpoint `route('find')`
- tags: endpoint `route('find.tags')`, `multiple`, `max-selections`
- community: select single dedicata, senza search se coerente con view esistente

## Date E Time Picker

Spec primaria: `docs/ui/date-picker.md`.

Regole:

- Usa Flatpickr globale esposto da `resources/js/app.js`.
- Wrapper `wire:ignore` quando Flatpickr manipola il DOM.
- Sincronizza Livewire con `$wire.set('campo', dateStr)`.
- Per end date dipendente da start date, usa evento DOM custom come nelle form evento.
- Non usare input `datetime-local` nativi per nuove form evento se serve coerenza con il pattern esistente.

## Bottoni, Link E CTA

Spec primaria: `docs/ui/buttons-links.md`.

Tipi principali:

- Link operativo dashboard: `flex items-center space-x-2 text-cyan underline`.
- Bottone primario auth: `bg-accent text-background ... hover:bg-accent/90`.
- CTA outline pubblica: `flex h-10 w-full items-center justify-center space-x-2 rounded-md border border-white px-2 py-1`.
- Menu item: usa `<x-menu-item>` quando possibile.

Regole:

- Per azioni interne usa `wire:navigate` sui link.
- Per URL esterni usa `target="_blank" rel="noopener"`.
- Mantieni bottoni compatti, con icona dove esiste.
- Non introdurre varianti colorate non presenti nella palette.

## Navigazione

Pattern:

- Navbar nel layout base.
- Menu hamburger Livewire in `resources/views/livewire/hamburger-menu.blade.php`.
- Overlay full-screen mobile con `glass-overlay`.
- Voci menu con `<x-menu-item>`.
- Active state default: `text-pink font-bold`.

Regole:

- Le nuove route interne devono usare link con `wire:navigate` se sono nella navigazione applicativa.
- Mantieni icone `*-white.svg` nei menu principali e `*-cyan.svg` nei link operativi.
- Non creare una seconda navigazione laterale se il flusso puo stare nel menu esistente.

## Feedback UI

Pattern esistente:

- Componente globale: `<livewire:messages />`.
- Evento Livewire: `messageSent`.
- Session flash: `success` / `error`.
- Toast fixed: `top-20 right-5`, sparisce dopo 4 secondi.

Regole:

- Per feedback globale, riusa `messageSent` o flash session.
- Per errori campo, usa `@error` vicino al campo.
- Non aggiungere sistemi toast paralleli.

## Icone E Immagini

Icone:

- Source preferita: `public/icons`.
- Nomi ricorrenti: `calendar-*`, `location-*`, `heart-*`, `users-*`, `plus-*`, `kebab-white`.
- Le icone sono spesso SVG colorate per variante (`cyan`, `pink`, `white`).

Immagini:

- Logo app: `public/images/logo.png`.
- Hamburger: `public/images/hamburger-menu.png` e `hamburger-menu-closed.png`.
- Poster/loghi/avatar arrivano da accessor model (`poster_img`, `poster_thumb_img`, `poster_mobile_img`, `logo_img`, `avatar_img`).

Regole:

- Non hardcodare path storage se esiste un accessor model.
- Usa `loading="lazy"` per immagini in liste.
- Usa `loading="eager"` solo per immagine principale above-the-fold.
- Mantieni alt testuale per immagini contenuto; icone decorative possono avere `alt=""`.

## Responsive

Baseline:

- Mobile-first.
- Container con `px-6`.
- Liste e form a colonna singola.
- Card evento compatte con poster piccolo e testo troncato.
- Griglie a due colonne solo per azioni o metriche dove gia funziona su mobile.

Controlli:

- Testi nei bottoni non devono uscire dal contenitore.
- Immagini devono avere dimensioni stabili (`w-*`, `h-*`, `size-*`, `aspect-*` quando serve).
- Menu e dropdown devono avere `z-10` o superiore in base al contesto.
- La navbar fissa non deve coprire heading o toast.

## Accessibilita Minima

- Usa `label for` e `id` coerenti nei form.
- Preferisci link veri per navigazione e button veri per azioni.
- Mantieni focus visibile: le classi input hanno gia `focus:ring`.
- Non affidare lo stato solo al colore se il testo puo chiarirlo.
- Per menu cliccabili con immagini, valuta `button` quando introduci nuovi trigger.
- Verifica con Playwright e Lighthouse quando la modifica tocca flussi o layout rilevanti.

## Copy E Traduzioni

Regole:

- Usa file lingua e `__()`/`@lang()` per testo utente stabile.
- Evita copy istruzionale visibile se l'interazione e ovvia.
- Mantieni microcopy breve, operativo e scansionabile.
- Coerenza lingua: UI utente principalmente italiana.
- Evita headline marketing generiche; il prodotto deve mostrare eventi e azioni reali.

## Quando Aggiungere Nuove Classi CSS

Aggiungi una classe in `resources/css/app.css` solo se:

- il pattern si ripete in piu view
- riduce duplicazione significativa
- rappresenta un token o componente comune
- non puo essere espresso chiaramente con Tailwind locale

Prima di aggiungere:

- cerca pattern simili con `rg`
- controlla `docs/ui/*`
- valuta se estendere `input-et`, `glass-card`, `glass-panel`, `.page` o `.page-title`

Non aggiungere classi per una sola eccezione estetica.

## Checklist Per Nuove UI

- Ho letto questa guida e la spec UI mirata.
- Ho confrontato almeno un esempio reale nel repo.
- Ho usato `.page` o il layout esistente dove possibile.
- Ho riusato palette, font e icone esistenti.
- Ho scelto card glass solo per contesti coerenti.
- Ho usato `input-et`, `textarea-et`, `select-et` nei form.
- Ho usato `wire:navigate` per link interni.
- Ho previsto errori, loading state e stati vuoti.
- Ho verificato mobile e desktop se il layout e nuovo.
- Ho aggiornato docs se ho introdotto un nuovo pattern riutilizzabile.

## Verifica Finale

Per modifiche UI o asset:

```bash
npm run frontend:test
```

Per audit qualitativi o modifiche con impatto su performance/accessibilita/SEO:

```bash
npm run frontend:audit
```

Per modifiche PHP collegate alla UI, segui anche la verifica obbligatoria del repo:

```bash
docker compose exec -T app composer lint
docker compose exec -T app composer analyse
```

Se la modifica non e puramente locale o cosmetica, esegui anche test backend mirati o `composer qa` nel container.
