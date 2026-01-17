---
description: Esegue i test Pest
---
Esegui i test dentro il container Docker:

`docker exec eventi-tech composer test`

Se vengono passati argomenti (filter):
`docker exec eventi-tech vendor/bin/pest --filter $ARGUMENTS`

Mostra i risultati e suggerisci fix per eventuali fallimenti.
