#!/usr/bin/env bash
set -euo pipefail

script_dir="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
repo_root="$(cd "$script_dir/../.." && pwd)"

trim() {
  local value="$1"

  value="${value#"${value%%[![:space:]]*}"}"
  value="${value%"${value##*[![:space:]]}"}"

  printf '%s' "$value"
}

strip_quotes() {
  local value="$1"

  if [[ "$value" == \"*\" && "$value" == *\" ]]; then
    value="${value:1:${#value}-2}"
  elif [[ "$value" == \'*\' && "$value" == *\' ]]; then
    value="${value:1:${#value}-2}"
  fi

  printf '%s' "$value"
}

load_context7_key_from_env_file() {
  local env_file="$repo_root/.env"
  local line value

  [[ -f "$env_file" ]] || return 0

  while IFS= read -r line || [[ -n "$line" ]]; do
    line="${line%$'\r'}"

    [[ "$line" =~ ^[[:space:]]*$ ]] && continue
    [[ "$line" =~ ^[[:space:]]*# ]] && continue

    if [[ "$line" =~ ^[[:space:]]*(export[[:space:]]+)?CONTEXT7_API_KEY[[:space:]]*=[[:space:]]*(.*)$ ]]; then
      value="${BASH_REMATCH[2]}"
      value="$(trim "$value")"
      value="$(strip_quotes "$value")"

      if [[ -n "$value" ]]; then
        export CONTEXT7_API_KEY="$value"
      fi

      return 0
    fi
  done < "$env_file"
}

if [[ -z "${CONTEXT7_API_KEY:-}" ]]; then
  load_context7_key_from_env_file
fi

if [[ -z "${CONTEXT7_API_KEY:-}" ]]; then
  cat >&2 <<'EOF'
Context7 MCP requires CONTEXT7_API_KEY.

Set it in your shell environment, for example:
  export CONTEXT7_API_KEY="ctx7sk-..."

Or add this line to the local, uncommitted .env file:
  CONTEXT7_API_KEY=ctx7sk-...
EOF
  exit 1
fi

exec npx -y @upstash/context7-mcp --api-key "$CONTEXT7_API_KEY"
