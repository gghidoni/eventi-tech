#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
SMOKE_ID="${SMOKE_ID:-$$}"

smoke_base="${SMOKE_BASE:-${ROOT_DIR}/storage/app}"

SMOKE_ROOT="${smoke_base}/eventi-tech-bootstrap-smoke-${SMOKE_ID}"
PORT_OFFSET=$((SMOKE_ID % 1000))

cleanup() {
  if [[ -d "$SMOKE_ROOT" ]]; then
    (
      cd "$SMOKE_ROOT"
      docker compose down --volumes --remove-orphans >/dev/null 2>&1 || true
    )
    rm -rf "$SMOKE_ROOT"
  fi
}

trap cleanup EXIT

[[ ! -e "$SMOKE_ROOT" ]] || {
  printf '[fresh-smoke] ERROR: temporary path already exists: %s\n' "$SMOKE_ROOT" >&2
  exit 1
}

mkdir -p "$SMOKE_ROOT"
SMOKE_ROOT="$(cd "$SMOKE_ROOT" && pwd -P)"

cd "$ROOT_DIR"
git ls-files -co --exclude-standard -z \
  | tar --null -T - -cf - \
  | tar -xf - -C "$SMOKE_ROOT"

cd "$SMOKE_ROOT"

export COMPOSE_PROJECT_NAME="eventi-tech-smoke-${SMOKE_ID}"
export APP_CONTAINER_NAME="eventi-tech-smoke-${SMOKE_ID}-app"
export NGINX_CONTAINER_NAME="eventi-tech-smoke-${SMOKE_ID}-nginx"
export POSTGRES_CONTAINER_NAME="eventi-tech-smoke-${SMOKE_ID}-postgres"
export MEILISEARCH_CONTAINER_NAME="eventi-tech-smoke-${SMOKE_ID}-meilisearch"
export MAILPIT_CONTAINER_NAME="eventi-tech-smoke-${SMOKE_ID}-mailpit"

export FORWARD_APP_PORT=$((18000 + PORT_OFFSET))
export FORWARD_APP_FPM_PORT=$((19000 + PORT_OFFSET))
export FORWARD_VITE_PORT=$((20000 + PORT_OFFSET))
export FORWARD_DB_PORT=$((21000 + PORT_OFFSET))
export FORWARD_MEILISEARCH_PORT=$((22000 + PORT_OFFSET))
export FORWARD_MAILPIT_SMTP_PORT=$((23000 + PORT_OFFSET))
export FORWARD_MAILPIT_DASHBOARD_PORT=$((24000 + PORT_OFFSET))

export DOCTOR_APP_URL="http://127.0.0.1:${FORWARD_APP_PORT}"
export DOCTOR_MEILISEARCH_URL="http://127.0.0.1:${FORWARD_MEILISEARCH_PORT}"
export DOCTOR_MAILPIT_URL="http://127.0.0.1:${FORWARD_MAILPIT_DASHBOARD_PORT}"

printf '[fresh-smoke] First bootstrap in %s\n' "$SMOKE_ROOT"
./scripts/bootstrap.sh --seed

printf '[fresh-smoke] Verifying seeded search through Laravel Scout.\n'
docker compose exec -T app php artisan tinker \
  --execute='throw_unless(App\Models\Event::search("")->take(1)->get()->isNotEmpty());'

printf '[fresh-smoke] Second bootstrap must be non-destructive and idempotent.\n'
./scripts/bootstrap.sh

printf '[fresh-smoke] Fresh-checkout smoke passed.\n'
