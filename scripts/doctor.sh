#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

info() {
  printf '[doctor] %s\n' "$1"
}

fail() {
  printf '[doctor] ERROR: %s\n' "$1" >&2
  exit 1
}

read_env() {
  local key="$1"
  local line value

  line="$(grep -E "^[[:space:]]*${key}=" .env | tail -n 1 || true)"
  value="${line#*=}"
  value="${value%$'\r'}"

  if [[ "$value" == \"*\" && "$value" == *\" ]]; then
    value="${value:1:${#value}-2}"
  elif [[ "$value" == \'*\' && "$value" == *\' ]]; then
    value="${value:1:${#value}-2}"
  fi

  printf '%s' "$value"
}

require_env() {
  local key="$1"

  [[ -n "$(read_env "$key")" ]] || fail "$key is missing or empty in .env."
}

expect_env() {
  local key="$1"
  local expected="$2"
  local actual

  actual="$(read_env "$key")"
  [[ "$actual" == "$expected" ]] || fail "$key must be '$expected' for the canonical local runtime (found '$actual')."
}

require_command() {
  command -v "$1" >/dev/null 2>&1 || fail "$1 is required but not available in PATH."
}

require_running_service() {
  local service="$1"

  docker compose ps --status running --services | grep -qx "$service" \
    || fail "Docker Compose service '$service' is not running."
}

require_healthy_service() {
  local service="$1"
  local container_id health

  container_id="$(docker compose ps -q "$service")"
  [[ -n "$container_id" ]] || fail "Docker Compose service '$service' has no container."

  health="$(docker inspect --format '{{if .State.Health}}{{.State.Health.Status}}{{else}}none{{end}}' "$container_id")"
  [[ "$health" == 'healthy' ]] || fail "Docker Compose service '$service' is not healthy (status: $health)."
}

cd "$ROOT_DIR"

require_command docker
require_command curl
docker compose version >/dev/null 2>&1 || fail 'Docker Compose v2 is not available.'
[[ -f .env ]] || fail '.env is missing. Run ./scripts/bootstrap.sh.'

info 'Validating the canonical local environment contract.'
expect_env APP_ENV local
expect_env DB_CONNECTION pgsql
expect_env DB_HOST postgres
expect_env SCOUT_DRIVER meilisearch
expect_env MEILISEARCH_URL http://meilisearch:7700
expect_env MAIL_MAILER smtp
expect_env MAIL_HOST mailpit
expect_env QUEUE_CONNECTION sync
require_env APP_KEY
require_env DB_DATABASE
require_env DB_USERNAME
require_env DB_PASSWORD
require_env MEILI_MASTER_KEY

info 'Validating Docker Compose interpolation.'
docker compose config --quiet

for service in postgres meilisearch mailpit app nginx; do
  require_running_service "$service"
done

for service in postgres meilisearch mailpit; do
  require_healthy_service "$service"
done

info 'Checking provider autoload and Laravel bootstrap.'
docker compose exec -T app php -r '
require "vendor/autoload.php";
$providers = require "bootstrap/providers.php";
$missing = array_values(array_filter($providers, static fn (string $provider): bool => !class_exists($provider)));
if ($missing !== []) {
    fwrite(STDERR, "Missing providers: ".implode(", ", $missing).PHP_EOL);
    exit(1);
}
'
docker compose exec -T app php artisan about --only=environment --no-ansi >/dev/null

info 'Checking PostgreSQL connectivity and migration state.'
migration_status="$(docker compose exec -T app php artisan migrate:status --no-ansi)"
if grep -q 'Pending' <<<"$migration_status"; then
  fail 'Database migrations are pending. Run ./scripts/bootstrap.sh.'
fi

meilisearch_port="${FORWARD_MEILISEARCH_PORT:-$(read_env FORWARD_MEILISEARCH_PORT)}"
mailpit_port="${FORWARD_MAILPIT_DASHBOARD_PORT:-$(read_env FORWARD_MAILPIT_DASHBOARD_PORT)}"
app_port="${FORWARD_APP_PORT:-$(read_env FORWARD_APP_PORT)}"

meilisearch_url="${DOCTOR_MEILISEARCH_URL:-http://127.0.0.1:${meilisearch_port:-7700}}"
mailpit_url="${DOCTOR_MAILPIT_URL:-http://127.0.0.1:${mailpit_port:-8025}}"
app_url="${DOCTOR_APP_URL:-http://127.0.0.1:${app_port:-8083}}"

info 'Checking Meilisearch and Mailpit host endpoints.'
curl --fail --silent --show-error "$meilisearch_url/health" >/dev/null
curl --fail --silent --show-error "$mailpit_url/api/v1/info" >/dev/null

info 'Checking built assets and stale Vite state.'
[[ -f public/build/manifest.json ]] || fail 'public/build/manifest.json is missing. Run ./scripts/bootstrap.sh.'
[[ ! -f public/hot ]] || fail 'public/hot is stale; remove it or rebuild assets.'

info 'Checking the application HTTP endpoint.'
curl --fail --silent --show-error "$app_url" >/dev/null

info 'All checks passed.'
