#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
url_file="${repo_root}/.codex/mcp-postgres.url"

if [[ -z "${EVENTI_TECH_MCP_DATABASE_URL:-}" && ! -f "${url_file}" ]]; then
    echo "Missing EVENTI_TECH_MCP_DATABASE_URL and ${url_file}" >&2
    echo "Run: ./scripts/agents/setup-mcp-postgres.sh" >&2
    exit 1
fi

exec node "${repo_root}/scripts/agents/mcp-postgres-server.mjs"
