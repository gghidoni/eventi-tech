#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
url_file="${repo_root}/.codex/mcp-postgres.url"

db_name="${DB_DATABASE:-eventi_tech_db}"
db_host="${MCP_POSTGRES_HOST:-127.0.0.1}"
db_port="${FORWARD_DB_PORT:-5432}"
mcp_user="${MCP_POSTGRES_USER:-mcp_readonly}"
mcp_password="${MCP_POSTGRES_PASSWORD:-$(openssl rand -hex 32 | tr -d '\n')}"

if [[ ! "${db_name}" =~ ^[A-Za-z_][A-Za-z0-9_]*$ ]]; then
    echo "Invalid DB_DATABASE value for SQL identifier: ${db_name}" >&2
    exit 1
fi

if [[ ! "${mcp_user}" =~ ^[A-Za-z_][A-Za-z0-9_]*$ ]]; then
    echo "Invalid MCP_POSTGRES_USER value for SQL identifier: ${mcp_user}" >&2
    exit 1
fi

escaped_mcp_password="${mcp_password//\'/\'\'}"

mkdir -p "${repo_root}/.codex"

docker compose exec -T postgres sh -lc 'psql -v ON_ERROR_STOP=1 -U "$POSTGRES_USER" -d "$POSTGRES_DB"' <<SQL
DO \$\$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM pg_catalog.pg_roles
        WHERE rolname = '${mcp_user}'
    ) THEN
        CREATE ROLE ${mcp_user} LOGIN PASSWORD '${escaped_mcp_password}';
    ELSE
        ALTER ROLE ${mcp_user} WITH LOGIN PASSWORD '${escaped_mcp_password}';
    END IF;
END
\$\$;

GRANT CONNECT ON DATABASE ${db_name} TO ${mcp_user};
GRANT USAGE ON SCHEMA public TO ${mcp_user};
GRANT SELECT ON ALL TABLES IN SCHEMA public TO ${mcp_user};

ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT SELECT ON TABLES TO ${mcp_user};

ALTER ROLE ${mcp_user} SET default_transaction_read_only = on;
ALTER ROLE ${mcp_user} SET statement_timeout = '5s';
SQL

umask 077
printf 'postgresql://%s:%s@%s:%s/%s\n' \
    "${mcp_user}" \
    "${mcp_password}" \
    "${db_host}" \
    "${db_port}" \
    "${db_name}" > "${url_file}"

echo "MCP PostgreSQL read-only role configured."
echo "Local URL written to ${url_file}"
