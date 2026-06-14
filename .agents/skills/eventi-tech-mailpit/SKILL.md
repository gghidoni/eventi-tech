---
name: eventi-tech-mailpit
description: Read and inspect the Eventi Tech local Mailpit inbox through the repo MCP server `eventi-tech-mailpit`. Use when Codex needs to verify whether an email arrived, identify what email was sent, inspect recipients/subject/body previews, or extract verification/reset links from local Mailpit during auth, CFP, notification, or E2E workflows.
---

# Eventi Tech Mailpit

Use the local MCP server `eventi-tech-mailpit` to read Mailpit messages. The server is read-only by design.

The server avoids Mailpit's message-summary endpoint because that endpoint marks messages as read. It uses mailbox lists and raw message source for details, identification, and link extraction.

## Workflow

1. Read `docs/agents/mcp-mailpit.md` if the task needs setup, testing, or safety details.
2. Prefer MCP tools over browser UI or curl for Mailpit reads.
3. To check if a user received an email, call `mailpit_latest_for` with the recipient email.
4. To understand what email it is, call `mailpit_identify`.
5. To recover verification or reset links, call `mailpit_extract_links`.
6. Use `mailpit_get` with `include_body: true` only when a body preview is needed.

## Guardrails

- Do not send, delete, tag, release, or mutate Mailpit messages.
- Do not use this for production or real email providers.
- Keep output compact: prefer metadata and link extraction before body previews.
- State whether the conclusion comes from Mailpit, code, or documentation.
