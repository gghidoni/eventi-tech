#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
SEED=false

info() {
  printf '[bootstrap] %s\n' "$1"
}

fail() {
  printf '[bootstrap] ERROR: %s\n' "$1" >&2
  exit 1
}

usage() {
  cat <<'EOF'
Usage: ./scripts/bootstrap.sh [--seed]

  --seed  Populate demo data and import Event records into Meilisearch.
EOF
}

for argument in "$@"; do
  case "$argument" in
    --seed)
      SEED=true
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      usage >&2
      fail "Unknown argument: $argument"
      ;;
  esac
done

cd "$ROOT_DIR"

command -v docker >/dev/null 2>&1 || fail 'Docker is not installed or not available in PATH.'
docker compose version >/dev/null 2>&1 || fail 'Docker Compose v2 is not available.'

if [[ ! -f .env ]]; then
  info 'Creating .env from .env.example.'
  cp .env.example .env
fi

info 'Validating Docker Compose configuration.'
docker compose config --quiet

info 'Building the application image.'
docker compose build app

info 'Starting the local runtime and waiting for healthy dependencies.'
docker compose up -d

info 'Installing PHP dependencies in the Docker volume.'
docker compose exec -T app composer install --no-interaction --prefer-dist

info 'Installing frontend dependencies in the Docker volume.'
docker compose exec -T app npm ci

if docker compose exec -T app sh -c "grep -Eq '^APP_KEY=[[:space:]]*$' .env"; then
  info 'Generating APP_KEY.'
  docker compose exec -T app php artisan key:generate --force --no-interaction
fi

info 'Clearing stale Laravel configuration.'
docker compose exec -T app php artisan config:clear --no-interaction

info 'Running non-destructive database migrations.'
docker compose exec -T app php artisan migrate --force --no-interaction

info 'Clearing Laravel caches after the database schema is ready.'
docker compose exec -T app php artisan optimize:clear --no-interaction

info 'Building production frontend assets.'
docker compose exec -T app npm run build

info 'Synchronizing Meilisearch index settings.'
docker compose exec -T app php artisan scout:sync-index-settings --no-interaction

if [[ "$SEED" == true ]]; then
  info 'Seeding demo data by explicit request.'
  docker compose exec -T app php artisan db:seed --force --no-interaction

  info 'Importing seeded events into Meilisearch.'
  docker compose exec -T app php artisan scout:import 'App\Models\Event' --no-interaction
fi

info 'Running the read-only environment doctor.'
./scripts/doctor.sh

info 'Bootstrap completed successfully.'
