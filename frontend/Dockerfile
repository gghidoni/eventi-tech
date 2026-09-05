# Usa un'immagine Node ufficiale
FROM node:22-alpine

# Usa Alpine package manager (apk) per installare bash
RUN apk update && apk add --no-cache bash

# Crea una directory di lavoro
WORKDIR /app

# Espone la porta 5173 (usata da Vite in dev)
EXPOSE 5173

# Imposta il comando di default (opzionale)
CMD [ "sh" ]
