# Backend: CFP

## Source of truth

Il dominio CFP vive in `cfps`. Il vecchio campo `events.cfp_url` e legacy e non va reintrodotto: anche le CFP esterne devono avere un record `cfps` con date e URL.

In questa fase del progetto il DB locale puo essere distrutto e rigenerato. Le migration CFP possono quindi essere riscritte invece di aggiungere migration compatibili incrementali.

## Modalita CFP

`cfps.mode` distingue:

- `external`: l'app mostra stato, date e countdown, poi rimanda a `external_url`
- `internal`: l'app raccogliera candidature interne tramite `cfp_submissions`

`cfps.status` distingue la pubblicazione editoriale:

- `draft`
- `published`
- `archived`

Lo stato open/closed si calcola da `opens_at` e `closes_at`.

## Template e campi

I template riutilizzabili sono in `cfp_templates` e appartengono a una community.

I campi del template sono in `cfp_template_fields`. Un CFP interno usa sempre i campi correnti del template collegato tramite `cfps.cfp_template_id`: il template e quindi "live" per le nuove candidature e per la review.

I template non hanno una gestione autonoma separata dal flusso evento. La modifica della configurazione CFP avviene dal form di creazione/modifica evento:

- se il template collegato non e usato da altre CFP, viene aggiornato in place
- se il template collegato e usato da almeno un'altra CFP, viene creato un nuovo template derivato con i campi modificati e solo la CFP corrente viene collegata alla copia
- se i campi non cambiano, la CFP resta collegata al template esistente
- se l'organizzatore crea campi custom senza template di partenza, viene creato automaticamente un nuovo template per quella configurazione

La creazione e modifica evento possono configurare una CFP interna senza uscire dal form evento:

- l'organizzatore puo selezionare un template della community
- se il template non viene modificato, la CFP viene collegata al template esistente
- se il template selezionato viene modificato inline, si applica la regola copy-on-write descritta sopra
- piu CFP possono usare lo stesso template; quando il template viene aggiornato in place, le nuove candidature delle CFP collegate usano i campi aggiornati

Le candidature puntano direttamente ai `cfp_template_fields`. In review si mostrano i campi correnti del template:

- se una risposta esiste, viene mostrata
- se una risposta manca perche il campo e stato aggiunto dopo l'invio della candidatura, viene mostrato vuoto con avviso
- i campi rimossi dal template non vengono mostrati; le eventuali risposte collegate possono essere eliminate tramite cascade

L'unico modo per configurare o modificare una CFP e il form di creazione/modifica evento. Non deve esistere una pagina dedicata "Gestisci CFP" per modificare la configurazione. Le pagine dedicate alle candidature servono solo a elencare e revisionare submission.

Tipi campo previsti:

- `text`
- `textarea`
- `select`
- `multiselect`
- `checkbox`
- `url`
- `email`
- `number`
- `date`

`options` e `validation` sono JSON opzionali.

## Candidature

Le candidature interne vivono in `cfp_submissions` e appartengono a:

- un CFP
- un utente autenticato

Un utente puo inviare piu candidature per lo stesso CFP.

`title` e `abstract` sono campi fissi della submission. Le risposte ai campi custom vivono in `cfp_submission_answers`, una riga per risposta, con `cfp_template_field_id` e `value` JSON.

Stati submission:

- `draft`
- `submitted`
- `under_review`
- `accepted`
- `rejected`
- `withdrawn`

La review blind non e prevista nello step corrente.

## Pagine candidature

Gli organizzatori hanno una pagina dashboard dedicata alle candidature ricevute:

- mostra un filtro evento con soli eventi dell'organizzatore che hanno candidature attive
- se arriva un parametro evento valido, quell'evento viene preselezionato
- sotto il filtro mostra card candidatura con speaker, titolo, stato e data invio
- il menu kebab della card evento deve linkare a questa pagina con evento preselezionato

Gli utenti autenticati hanno una pagina "Le mie candidature":

- mostra solo le candidature dell'utente corrente
- ogni card mostra evento, community, titolo proposta, stato e data invio

Per "candidature attive" si intendono le candidature con stato diverso da `draft` e `withdrawn`.

## Email

Le candidature CFP generano email transazionali:

- quando una candidatura viene inviata, l'organizzatore riceve una notifica
- quando una candidatura viene inviata, lo speaker riceve una conferma che la proposta e in revisione
- quando lo stato della candidatura cambia, lo speaker riceve una notifica con il nuovo stato
