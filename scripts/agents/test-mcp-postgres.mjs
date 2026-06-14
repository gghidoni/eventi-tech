#!/usr/bin/env node
import { spawn } from 'node:child_process';

const child = spawn('./scripts/agents/run-mcp-postgres.sh', [], {
  cwd: process.cwd(),
  stdio: ['pipe', 'pipe', 'pipe'],
});

let stdout = Buffer.alloc(0);
let stderr = '';
let nextId = 1;
const pending = new Map();
const timings = {};

child.stdout.on('data', (chunk) => {
  stdout = Buffer.concat([stdout, chunk]);
  parseMessages();
});

child.stderr.on('data', (chunk) => {
  stderr += chunk.toString('utf8');
});

function send(method, params = {}, timeoutMs = 30_000) {
  const id = nextId++;
  const body = JSON.stringify({ jsonrpc: '2.0', id, method, params });

  child.stdin.write(`${body}\n`);

  return new Promise((resolve, reject) => {
    const timer = setTimeout(() => {
      reject(new Error(`Timeout waiting for ${method}; stderr=${stderr}`));
    }, timeoutMs);

    pending.set(id, {
      method,
      resolve: (value) => {
        clearTimeout(timer);
        resolve(value);
      },
      reject: (error) => {
        clearTimeout(timer);
        reject(error);
      },
    });
  });
}

function notify(method, params = {}) {
  const body = JSON.stringify({ jsonrpc: '2.0', method, params });
  child.stdin.write(`${body}\n`);
}

function parseMessages() {
  while (true) {
    const newline = stdout.indexOf('\n');
    if (newline === -1) return;

    const line = stdout.slice(0, newline).toString('utf8').trim();
    stdout = stdout.slice(newline + 1);

    if (line === '') continue;

    const message = JSON.parse(line);

    if (!pending.has(message.id)) continue;

    const request = pending.get(message.id);
    pending.delete(message.id);

    if (message.error) {
      request.reject(new Error(`${request.method}: ${message.error.message}`));
      continue;
    }

    request.resolve(message.result);
  }
}

function parseToolResult(result) {
  return JSON.parse(result.content[0].text);
}

async function callTool(name, args = {}) {
  const startedAt = performance.now();

  try {
    return parseToolResult(await send('tools/call', { name, arguments: args }));
  } finally {
    timings[name] = Number((performance.now() - startedAt).toFixed(1));
  }
}

async function main() {
  const init = await send('initialize', {
    protocolVersion: '2024-11-05',
    capabilities: {},
    clientInfo: { name: 'eventi-tech-db-smoke', version: '1.0.0' },
  });

  notify('notifications/initialized');

  const tools = await send('tools/list');
  const toolNames = tools.tools.map((tool) => tool.name);
  const tables = await callTool('db_tables');
  const schema = await callTool('db_schema');

  if (tables.tables.length === 0) {
    throw new Error('db_tables returned no readable tables.');
  }

  const firstTable = tables.tables.find((table) => table.schema === 'public') ?? tables.tables[0];
  const tableName = `${firstTable.schema}.${firstTable.table}`;
  const description = await callTool('db_describe_table', { table: tableName });
  const sample = await callTool('db_sample', { table: tableName, limit: 2 });
  const query = await callTool('db_query', {
    sql: 'select current_database() as database, current_user as user_name',
    limit: 5,
  });
  const explain = await callTool('db_explain', {
    sql: 'select current_database() as database',
  });
  const stringKeyword = await callTool('db_query', {
    sql: "select 'update' as literal_value",
    limit: 1,
  });
  const queryAfterStringKeywordTiming = timings.db_query;

  let writeBlocked = false;
  let writeError = '';

  try {
    await callTool('db_query', {
      sql: 'create table mcp_write_check_from_smoke (id int)',
      limit: 1,
    });
  } catch (error) {
    writeBlocked = true;
    writeError = error.message;
  }

  let multiStatementBlocked = false;

  try {
    await callTool('db_query', {
      sql: 'select 1 as one; select 2 as two',
      limit: 1,
    });
  } catch {
    multiStatementBlocked = true;
  }

  let cteWriteBlocked = false;

  try {
    await callTool('db_query', {
      sql: "with inserted as (insert into users (name) values ('x') returning id) select id from inserted",
      limit: 1,
    });
  } catch {
    cteWriteBlocked = true;
  }

  if (!writeBlocked) {
    throw new Error('Write query was not blocked.');
  }

  if (!multiStatementBlocked) {
    throw new Error('Multi-statement query was not blocked.');
  }

  if (!cteWriteBlocked) {
    throw new Error('Write CTE query was not blocked.');
  }

  let sqlErrorRecovered = false;

  try {
    await callTool('db_query', {
      sql: 'select * from definitely_missing_mcp_table',
      limit: 1,
    });
  } catch {
    const recovery = await callTool('db_query', {
      sql: 'select 1 as ok_after_error',
      limit: 1,
    });
    sqlErrorRecovered = recovery.rows[0]?.ok_after_error === 1;
  }

  if (!sqlErrorRecovered) {
    throw new Error('Server did not recover after a PostgreSQL query error.');
  }

  const report = {
    server: init.serverInfo,
    tools: toolNames,
    tableCount: tables.tables.length,
    schemaTableCount: schema.tables.length,
    describedTable: description.table,
    describedColumnCount: description.columns.length,
    sampleReturned: sample.returned,
    queryRows: query.rows,
    stringKeywordRows: stringKeyword.rows,
    explainHasPlan: Array.isArray(explain.plan),
    internalMcpTimings: {
      db_tables: tables._mcp,
      db_query: query._mcp,
      db_explain: explain._mcp,
    },
    timingsMs: {
      db_tables: timings.db_tables,
      db_schema: timings.db_schema,
      db_describe_table: timings.db_describe_table,
      db_sample: timings.db_sample,
      db_query_after_connection_ready: queryAfterStringKeywordTiming,
      db_explain: timings.db_explain,
    },
    writeBlocked,
    writeError,
    multiStatementBlocked,
    cteWriteBlocked,
    sqlErrorRecovered,
  };

  console.log(JSON.stringify(report, null, 2));
}

main()
  .finally(() => {
    child.kill('SIGTERM');
  })
  .catch((error) => {
    console.error(error.stack ?? error.message);
    process.exit(1);
  });
