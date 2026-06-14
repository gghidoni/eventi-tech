---
name: eventi-tech-db
description: Read and inspect the Eventi Tech local PostgreSQL database through the repo MCP server `eventi-tech-db`. Use when Codex needs DB-backed facts, schema, columns, relationships, seed/runtime data, row samples, query plans, or verification of persisted effects before making database-dependent decisions in this repository.
---

# Eventi Tech DB

Use the local MCP server `eventi-tech-db` for PostgreSQL reads in this repo. The server is read-only by design and backed by the `mcp_readonly` database role.

The server keeps one persistent `psql` session for speed. Expect the first DB tool call to pay warm-up cost and later calls to be much faster.

Tool responses include `_mcp` timing metadata. Use it to distinguish server/DB time from Codex client roundtrip time when diagnosing latency.

## Workflow

1. Read `docs/agents/mcp-postgres.md` if the task needs setup, testing, or safety details.
2. Prefer MCP tools over shell SQL for DB reads.
3. Use the smallest tool that answers the question:
   - `db_tables` to discover available tables quickly.
   - `db_describe_table` before querying a table.
   - `db_sample` for a small value preview.
   - `db_query` only for targeted read-only questions.
   - `db_explain` when query shape or performance matters.
   - `db_schema` only when global relationships are needed.
4. Keep reads token-efficient: select only needed columns, use filters, and pass explicit small limits.
5. State whether conclusions come from DB state, code, or documentation.

## Guardrails

- Do not use application DB credentials as fallback.
- Do not query production, staging, or shared environments.
- Do not request broad dumps when schema, describe, or sample is enough.
- Avoid sensitive columns unless required by the user task.
- If MCP is unavailable, run `node scripts/agents/test-mcp-postgres.mjs` to diagnose before switching approach.

## Local Setup

If the server is not configured, use the repo workflow:

```bash
./scripts/agents/setup-mcp-postgres.sh
```

Then restart Codex or open a new session so `.codex/config.toml` is reloaded.
