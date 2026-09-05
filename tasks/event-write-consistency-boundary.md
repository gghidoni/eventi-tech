# Task: Confine di consistenza per la scrittura degli eventi

## Obiettivo

Rendere creazione, modifica e attivazione di un evento un unico caso d'uso
applicativo, con una semantica esplicita per:

- transazione PostgreSQL;
- creazione o associazione dell'indirizzo;
- salvataggio di evento, tag e CFP;
- produzione e sostituzione dei file poster;
- email a proponente, amministratori e follower;
- aggiornamento dell'indice Meilisearch.

Il risultato non deve promettere atomicita tra sistemi che non condividono una
transazione. Deve invece garantire che:

1. le scritture relazionali siano atomiche;
2. i nuovi file siano compensati se il salvataggio DB fallisce;
3. un file gia referenziato non venga eliminato prima del commit sostitutivo;
4. email e search non partano prima del commit;
5. i side effect falliti restino osservabili e riprocessabili;
6. Livewire e Filament attraversino lo stesso confine applicativo.

## Perimetro

Incluso:

- creazione evento da dashboard Livewire;
- modifica evento da dashboard Livewire;
- creazione e modifica evento da Filament;
- transizione di stato che attiva un evento;
- poster desktop, mobile e thumbnail;
- tag e configurazione CFP/template;
- email generate dai casi d'uso sopra;
- sincronizzazione dell'evento nell'indice di ricerca;
- cleanup e riconciliazione degli artefatti del workflow.

Fuori scope:

- progettazione del worker di produzione, autoscaling e supervisione;
- retry policy globale, failed jobs e dashboard queue;
- modifica delle regole funzionali di CFP o moderazione;
- bonifica generale delle foreign key storiche;
- object storage di produzione e CDN;
- cancellazione completa dell'evento, finche non viene definita come caso d'uso
  separato.

Il task dedicato a queue e notifiche adottera l'outbox qui introdotto come
sorgente, sostituendo il dispatcher sincrono senza cambiare i producer.

## Specifiche consultate

- `docs/backend/actions.md`
- `docs/backend/uploads.md`
- `docs/backend/cfps.md`
- `docs/backend/models.md`
- `docs/backend/search.md`
- `docs/backend/authorization.md`
- `docs/project/architecture.md`
- `docs/project/technical-decisions.md`

## Evidenze confermate

### Creazione Livewire

`resources/views/pages/dashboard/events/⚡create.blade.php` coordina oggi il
workflow direttamente:

1. valida e autorizza la community;
2. crea l'address book con una transazione autonoma;
3. genera i tre file del poster;
4. chiama `CreateEvent`, che apre una nuova transazione per evento, CFP e tag;
5. invia nella stessa request la mail al proponente e le mail agli admin.

Se il punto 3 o 4 fallisce possono restare indirizzi o file orfani. Se una mail
fallisce dopo il commit, l'utente puo ricevere un errore anche se l'evento e gia
stato salvato.

### Modifica Livewire e Filament

- La pagina Livewire genera il nuovo poster prima di `UpdateEvent`.
- Filament salva gli eventi fuori da `CreateEvent` e `UpdateEvent`.
- In modifica Filament elimina i vecchi file prima che il nuovo salvataggio DB
  sia confermato: un errore puo lasciare nel DB path non piu esistenti.
- Non esiste quindi un confine applicativo comune tra i due adapter.

### Transazioni annidate e dipendenze

- `CreateAddressBook`, `CreateEvent`, `UpdateEvent` e `SaveEventCfp` possiedono
  ciascuna una propria `DB::transaction()`.
- `CreateEvent` e `UpdateEvent` istanziano direttamente `SaveEventCfp`, rendendo
  opaco il grafo delle dipendenze e difficile l'iniezione dei guasti nei test.
- Il coordinatore esterno non puo oggi governare rollback e compensazioni come
  un solo caso d'uso.

### Notifiche e search

- `Event::booted()` osserva la transizione ad `active` e dispatcha
  `NotifyCommunityFollowersOfApprovedEvent` dal lifecycle Eloquent.
- Il job invia mail nel proprio `handle()`.
- Le connessioni queue e Scout hanno `after_commit=false`.
- La configurazione locale usa temporaneamente queue `sync`, quindi dispatch e
  invio possono ancora avvenire nella request.

### Copertura test

I test di `CreateEvent` e `UpdateEvent` coprono soprattutto i percorsi di
successo. Mancano test di failure injection per:

- scrittura parziale delle varianti poster;
- rollback dopo la creazione dell'indirizzo;
- fallimento di CFP o tag sync;
- errore mail o Meilisearch dopo il commit;
- sostituzione poster fallita;
- comportamento equivalente tra Livewire e Filament.

## Verifica PostgreSQL

Il server MCP read-only del repository ha superato il diagnostico completo:
connessione, introspezione, query read-only e blocco delle scritture sono
operativi. Il connettore integrato della sessione e rimasto su uno stato stale e
non ha restituito le singole descrizioni delle tabelle.

Prima di introdurre la migration outbox, la Fase 0 deve quindi ripetere via MCP:

- descrizione di `events`, `address_books`, `event_tag`, `cfps`,
  `cfp_templates` e `cfp_template_fields`;
- conteggio di riferimenti orfani rilevanti;
- verifica di indici e vincoli gia presenti nel database runtime.

Le migration confermano intanto che `cfps` e il dominio template recente hanno
vincoli espliciti, mentre diversi riferimenti storici di eventi, indirizzi e
pivot tag non li hanno. La correzione sistematica resta nel P1 dedicato
all'integrita referenziale e non deve essere inglobata qui.

## Decisioni architetturali proposte

### 1. Un application service per comando

Introdurre due entrypoint espliciti, indicativamente:

- `CreateEventWorkflow`;
- `UpdateEventWorkflow`.

Gli adapter continuano a occuparsi di input, validazione, autorizzazione e
feedback UI. Il workflow riceve un command tipizzato e coordina tutti i
collaboratori. Le Action interne diventano operation transaction-agnostic e
vengono iniettate, senza `new` nel codice applicativo.

Non e necessario introdurre repository generici o separare il monolite in
servizi: Eloquent resta il meccanismo di persistenza.

### 2. Una sola transazione relazionale esterna

La transazione posseduta dal workflow include:

- eventuale creazione dell'indirizzo;
- inserimento o aggiornamento dell'evento;
- sincronizzazione tag;
- salvataggio CFP e copy-on-write del template;
- registrazione dei messaggi outbox.

Nessuna email, chiamata Meilisearch o cancellazione di vecchi file deve avvenire
all'interno della transazione.

### 3. Compensazione esplicita dei file

Il poster viene scritto con nomi univoci prima della transazione e restituisce
un manifesto tipizzato con tutti i path prodotti.

- Se una variante fallisce, il processor cancella le varianti nuove gia create.
- Se la transazione fallisce, il workflow compensa cancellando tutti i nuovi
  file.
- In update, i vecchi file restano disponibili fino al commit che salva i nuovi
  path.
- Solo dopo il commit viene richiesto il cleanup idempotente dei vecchi path.
- Un cleanup fallito non invalida il comando gia committato: viene registrato e
  puo essere ripetuto.

Questa strategia privilegia temporanei file orfani rispetto a riferimenti DB
rotti, che avrebbero impatto immediato sugli utenti.

### 4. Outbox transazionale minimale

`DB::afterCommit()` da solo lascia una finestra in cui un crash puo perdere il
side effect. La soluzione proposta e una tabella outbox scritta nella stessa
transazione dell'evento.

Campi minimi:

- UUID;
- tipo messaggio;
- aggregate type e ID;
- chiave di deduplicazione univoca;
- payload versionato;
- data di disponibilita;
- tentativi e ultimo errore;
- data di completamento;
- timestamp.

Messaggi iniziali:

- evento creato: conferma al proponente;
- evento creato: notifica agli amministratori;
- evento attivato: notifica ai follower della community;
- evento salvato: upsert nell'indice;
- cleanup del poster sostituito.

Dopo il commit un dispatcher prova a consumare i record prodotti dal comando.
Nel runtime locale corrente puo farlo in modo sincrono, ma un errore non deve
trasformare un commit riuscito in errore di salvataggio: il record resta pending.
Il successivo task queue sostituira il trigger sincrono con un worker.

La semantica e **at-least-once**. DB e outbox impediscono di produrre due volte
lo stesso messaggio logico; per email e provider esterni non si dichiara
exactly-once, che non e garantibile senza supporto idempotente del destinatario.

### 5. Side effect fuori dal model

Rimuovere il dispatch dal `booted()` di `Event`. La transizione di stato viene
rilevata nel workflow e produce il relativo messaggio outbox. In questo modo:

- il comportamento e visibile nel caso d'uso;
- non parte durante seed, factory o aggiornamenti incidentali;
- e testabile senza dipendere implicitamente dagli event Eloquent;
- Livewire e Filament producono lo stesso risultato.

### 6. Search a consistenza eventuale dichiarata

Le scritture dell'evento gestite dal workflow non devono attivare Scout prima
del commit. L'upsert Meilisearch viene eseguito dal consumer outbox usando lo
stato corrente PostgreSQL dell'evento, non una copia completa potenzialmente
stale nel payload.

Se Meilisearch e indisponibile:

- il salvataggio DB resta riuscito;
- il messaggio resta pending con errore osservabile;
- la ricerca puo essere temporaneamente stale;
- il replay riallinea l'indice senza ricreare l'evento.

Le policy generali di rebuild e freshness restano nel rilievo dedicato al
lifecycle Meilisearch.

## Invarianti del workflow

| Invariante | Garanzia |
| --- | --- |
| Evento, tag e CFP | Commit o rollback insieme |
| Nuovo indirizzo creato dal comando | Non sopravvive al rollback del comando |
| Nuovo poster | Tutte le varianti esistono oppure nessun nuovo path viene salvato |
| Poster precedente | Non viene eliminato prima del commit sostitutivo |
| Email | Mai inviata prima del commit |
| Attivazione | Produce una sola occorrenza logica per transizione |
| Search | Mai aggiornata con uno stato DB non committato |
| Errore side effect | Persistito e riprocessabile, senza mentire sull'esito DB |
| Adapter | Livewire e Filament invocano lo stesso caso d'uso |

## Matrice dei guasti

| Punto di errore | Stato DB atteso | Stato file atteso | Outbox/feedback |
| --- | --- | --- | --- |
| Prima variante poster | Invariato | Nessun nuovo file | Errore di salvataggio |
| Seconda/terza variante poster | Invariato | Nuove varianti compensate | Errore di salvataggio |
| Creazione indirizzo o evento | Rollback completo | Nuovo poster compensato | Nessun messaggio prodotto |
| CFP/template o tag | Rollback completo | Nuovo poster compensato | Nessun messaggio prodotto |
| Commit PostgreSQL | Commit completo | Nuovo poster mantenuto | Messaggi pending persistiti |
| Invio email dopo commit | Commit completo | Invariato | Messaggio pending/failed; UI conferma il salvataggio |
| Upsert Meilisearch | Commit completo | Invariato | Search pending/failed; UI conferma il salvataggio |
| Cleanup vecchio poster | Commit completo con nuovi path | Vecchi file temporaneamente orfani | Cleanup pending/failed |
| Retry della request dopo esito ambiguo | Nessun duplicato logico se riusa la command key | Cleanup idempotente | Deduplica outbox |

## Piano di implementazione

### Fase 0 - Chiudere specifica e baseline runtime

1. Formalizzare in `docs/backend/event-write-workflow.md` confine, invarianti,
   consistenza eventuale e ownership degli adapter.
2. Registrare l'adozione dell'outbox e la semantica at-least-once in
   `docs/project/technical-decisions.md`.
3. Ripetere l'introspezione PostgreSQL via MCP e salvare solo evidenze aggregate,
   senza dati sensibili.
4. Eseguire i test esistenti per creare una baseline prima dei refactor.
5. Aggiungere test di caratterizzazione per creazione, update e attivazione
   correnti, senza modificare ancora la produzione.

Acceptance:

- nessuna regola funzionale resta affidata soltanto al codice storico;
- schema runtime e migration sono confrontati;
- i test distinguono successo DB da successo dei side effect.

### Fase 1 - Command e orchestratori applicativi

1. Definire command immutabili per create e update, con tipi espliciti per
   community, dati evento, address, tag, CFP e poster.
2. Introdurre `CreateEventWorkflow` e `UpdateEventWorkflow` via container.
3. Spostare il mapping dai componenti verso factory dedicate senza spostare la
   validazione HTTP nel dominio.
4. Iniettare le operation CFP/address invece di istanziarle direttamente.
5. Restituire un result tipizzato con evento salvato e side effect schedulati.

Acceptance:

- ogni adapter effettua una sola chiamata mutante;
- il workflow non dipende da Livewire o Filament;
- tutti i collaboratori possono essere sostituiti nei test.

### Fase 2 - Transazione relazionale unica

1. Spostare la ownership di `DB::transaction()` nel workflow.
2. Rendere transaction-agnostic le operation interne.
3. Portare address, evento, tag e CFP nello stesso callback transazionale.
4. Conservare le regole copy-on-write dei template documentate.
5. Rilevare la transizione `non-active -> active` prima di perdere lo stato
   originale.
6. Inserire i messaggi outbox nella stessa transazione.

Acceptance:

- un'eccezione in qualsiasi operation relazionale non lascia scritture parziali;
- nessun side effect esterno parte nel callback DB;
- i rollback sono provati su PostgreSQL, non soltanto SQLite.

### Fase 3 - Poster compensabile

1. Fare restituire a `ProcessPoster` un manifesto dei tre artefatti.
2. Rendere atomica dal punto di vista applicativo la produzione delle varianti.
3. Aggiungere una compensation idempotente dei nuovi file.
4. In update, acquisire i vecchi path senza cancellarli.
5. Produrre dopo il commit un messaggio di cleanup per i soli path sostituiti.
6. Aggiungere un comando di riconciliazione `--dry-run` per file mancanti e
   orfani, con cancellazione solo tramite opzione esplicita separata.

Acceptance:

- ogni failure point delle tre scritture ha un test con `Storage::fake()`;
- un rollback DB non lascia i nuovi file;
- un cleanup fallito non rompe i path correnti;
- path duplicati o gia assenti sono no-op sicuri.

### Fase 4 - Outbox e consumer sincrono

1. Creare migration, model e repository dell'outbox.
2. Applicare un vincolo univoco alla chiave di deduplicazione.
3. Versionare il payload e registrare handler espliciti per tipo.
4. Implementare claim, completamento, tentativi ed errore senza lock prolungati
   durante I/O esterno.
5. Avviare il drain dei messaggi del comando solo dopo il commit.
6. Non propagare alla UI l'errore del consumer come fallimento del commit.
7. Fornire un comando Artisan read/retry per operativita locale e recovery.

Acceptance:

- rollback evento implica rollback dei messaggi;
- commit evento e crash simulato lasciano messaggi recuperabili;
- due produzioni con la stessa dedupe key non duplicano l'occorrenza;
- un consumer fallito conserva diagnosi e puo essere rieseguito.

### Fase 5 - Email, attivazione e search

1. Spostare le mail di creazione in handler outbox.
2. Sostituire il model hook di `Event` con produzione esplicita del messaggio di
   attivazione.
3. Rendere il consumer follower indipendente dal model lifecycle.
4. Disabilitare il sync Scout automatico nel workflow e indicizzare dal
   consumer leggendo lo stato DB corrente.
5. Gestire come no-op un aggregate non piu indicizzabile o non trovato, secondo
   il tipo del messaggio.

Acceptance:

- nessuna mail o richiesta Meilisearch precede il commit;
- mail/search failure non produce un falso errore di salvataggio;
- replay search converge allo stato corrente;
- la transizione ad active non notifica due volte per lo stesso evento/versione.

### Fase 6 - Migrare Livewire e Filament

1. Collegare create/edit Livewire ai workflow.
2. Collegare create/edit e cambio stato Filament agli stessi workflow.
3. Mantenere ri-autorizzazione e policy agli ingressi gia definite dal task auth.
4. Uniformare feedback: successo DB separato da eventuale side effect pending.
5. Rimuovere i percorsi mutanti precedenti solo dopo i test di equivalenza.

Acceptance:

- non esiste un percorso UI che persista eventi aggirando il workflow;
- Livewire e Filament producono le stesse invarianti;
- le pagine non coordinano direttamente storage, mail o Scout.

### Fase 7 - Osservabilita, recovery e rollout

1. Aggiungere log strutturati con command ID, event ID e outbox ID, senza payload
   personali completi.
2. Esporre metriche/comandi per pending, failed, eta massima e cleanup falliti.
3. Eseguire prima del rollout una riconciliazione dry-run tra path DB e storage.
4. Migrare prima schema e consumer, poi attivare i producer nei workflow.
5. Evitare dual-write dei side effect: ogni adapter usa o il vecchio flusso o il
   nuovo, mai entrambi.
6. Documentare rollback: disattivare i nuovi producer senza eliminare record
   outbox ancora necessari.

Acceptance:

- i side effect bloccati sono individuabili senza leggere log applicativi grezzi;
- il deploy e backward-compatible durante la finestra di rollout;
- esiste una procedura provata di replay e una di rollback.

## Strategia test

### Unit e component

- command mapping e validazione delle invarianti interne;
- processor poster con errore su ciascuna variante;
- dedupe key e versionamento payload;
- handler idempotenti per cleanup e search;
- rendering dei feedback Livewire/Filament.

### Integrazione PostgreSQL

- rollback di address/event/tag/CFP/outbox come unita;
- concorrenza sul vincolo di deduplicazione;
- claim e retry dei messaggi;
- transizione di stato e produzione dell'occorrenza di attivazione.

### Integrazione infrastrutturale

- Meilisearch indisponibile e successivo replay;
- Mail fake per failure deterministiche e Mailpit per almeno uno smoke reale;
- storage fake per compensazioni e filesystem locale per rename/delete reali.

### E2E

- creazione evento con email verificata;
- modifica con sostituzione poster;
- approvazione admin e notifica follower;
- salvataggio riuscito con search temporaneamente non disponibile;
- equivalenza essenziale tra dashboard e Filament.

## Verifiche obbligatorie finali

Poiche il task tocca PHP, auth boundary, upload, queue, search, migration e
configurazione sensibile:

```text
docker compose exec -T app composer lint
docker compose exec -T app composer analyse
docker compose exec -T app composer test
./scripts/security/run.sh
```

Se vengono toccati componenti o feedback UI:

```text
npm run frontend:test
```

Eseguire inoltre i test PostgreSQL/Meilisearch mirati introdotti dal task e uno
smoke Mailpit del percorso di creazione.

## Gate prima dell'implementazione

La documentazione corrente non definisce due aspetti necessari al codice:

1. **Indirizzo in modifica**: raccomandazione temporanea: preservare
   `address_book_id` negli update che non contengono un comando indirizzo e non
   cancellare record address esistenti, perche Filament puo riusarli. Il cambio
   online/offline e la modifica dell'indirizzo richiedono una regola funzionale
   esplicita.
2. **Affidabilita side effect**: raccomandazione: accettare semantica at-least-once
   con outbox, search eventuale e possibili duplicati email solo in caso di esito
   remoto ambiguo. L'alternativa best-effort con solo `afterCommit()` non soddisfa
   il requisito di recovery.

Queste due decisioni devono essere confermate e riportate nella nuova specifica
prima di iniziare la Fase 1.

## Definition of Done

- Livewire e Filament usano lo stesso application service per ogni mutazione
  evento in scope.
- Address, evento, tag, CFP e outbox condividono una transazione esterna.
- Il poster nuovo e compensato su fallimento; quello vecchio resta valido fino
  al commit.
- Email, follower notification, search e cleanup sono prodotti nel commit e
  consumati solo dopo.
- Nessun model lifecycle avvia side effect del workflow.
- Errori esterni sono osservabili, riprocessabili e non alterano l'esito DB
  comunicato all'utente.
- Matrice dei guasti coperta da test automatici proporzionati.
- Specifiche e decision record sono aggiornati.
- Tutti i gate obbligatori sono verdi; eventuali debiti preesistenti sono
  documentati separatamente e non mascherati.

