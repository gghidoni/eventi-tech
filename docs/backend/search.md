# Backend: Search (Scout / Meilisearch)

## Event search

La ricerca testuale eventi usa Laravel Scout:

- `Event::search($this->query)->keys()->toArray()`

Poi filtra la query Eloquent con `whereIn('id', $searchIds)`.

Fonte:

- `app/Livewire/EventsSearch.php`

## Dati indicizzati

L’array per l’indicizzazione e definito in:

- `app/Models/Event.php` (`toSearchableArray()`)

Include:

- `title`, `description`
- `city_id`, `province_id`, `region_id` (da `address_book`)
- `start_date`, `end_date`

