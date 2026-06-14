#!/usr/bin/env node
import { spawn } from 'node:child_process';
import { existsSync, readFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const repoRoot = resolve(__dirname, '../..');
const urlFile = resolve(repoRoot, '.codex/mcp-postgres.url');
const serverName = 'eventi-tech-db';
const serverVersion = '1.0.0';
const maxLimit = 200;
const defaultLimit = 50;
const maxCellChars = 500;

const instructions = [
  'Local PostgreSQL MCP for Eventi Tech. Use only for local read-only inspection.',
  'Prefer db_schema, db_tables, db_describe_table, and db_sample before db_query.',
  'Keep queries targeted, use limits, avoid sensitive columns unless required, and report when conclusions come from DB state.',
].join(' ');

let inputBuffer = Buffer.alloc(0);
let psqlProcess = null;
let psqlCurrentQuery = null;
let psqlStdoutBuffer = '';
let psqlStderrBuffer = '';
let psqlReadyPromise = null;
let queryChain = Promise.resolve();
let nextQueryId = 1;
let lastQueryStats = null;

process.stdin.on('data', (chunk) => {
  inputBuffer = Buffer.concat([inputBuffer, chunk]);
  readMessages();
});

process.stdin.on('end', () => process.exit(0));

function readMessages() {
  while (true) {
    const firstNewline = inputBuffer.indexOf('\n');
    const headerEnd = inputBuffer.indexOf('\r\n\r\n');

    if (firstNewline !== -1 && (headerEnd === -1 || firstNewline < headerEnd)) {
      const line = inputBuffer.slice(0, firstNewline).toString('utf8').trim();
      inputBuffer = inputBuffer.slice(firstNewline + 1);

      if (line !== '') {
        void handleMessage(JSON.parse(line));
      }

      continue;
    }

    if (headerEnd === -1) return;

    const header = inputBuffer.slice(0, headerEnd).toString('utf8');
    const match = header.match(/Content-Length:\s*(\d+)/i);
    if (!match) {
      inputBuffer = Buffer.alloc(0);
      return;
    }

    const length = Number(match[1]);
    const bodyStart = headerEnd + 4;
    const bodyEnd = bodyStart + length;
    if (inputBuffer.length < bodyEnd) return;

    const body = inputBuffer.slice(bodyStart, bodyEnd).toString('utf8');
    inputBuffer = inputBuffer.slice(bodyEnd);

    void handleMessage(JSON.parse(body));
  }
}

async function handleMessage(message) {
  if (!Object.prototype.hasOwnProperty.call(message, 'id')) {
    return;
  }

  try {
    const result = await dispatch(message.method, message.params ?? {});
    send({ jsonrpc: '2.0', id: message.id, result });
  } catch (error) {
    send({
      jsonrpc: '2.0',
      id: message.id,
      error: {
        code: -32000,
        message: error instanceof Error ? error.message : String(error),
      },
    });
  }
}

async function dispatch(method, params) {
  switch (method) {
    case 'initialize':
      void ensurePsql().catch(() => {});

      return {
        protocolVersion: params.protocolVersion ?? '2024-11-05',
        capabilities: {
          tools: {},
          resources: {},
        },
        serverInfo: {
          name: serverName,
          version: serverVersion,
        },
        instructions,
      };

    case 'tools/list':
      return { tools: toolDefinitions() };

    case 'tools/call':
      return callTool(params.name, params.arguments ?? {});

    case 'resources/list':
      return { resources: [] };

    case 'prompts/list':
      return { prompts: [] };

    case 'ping':
      return {};

    default:
      throw new Error(`Unsupported MCP method: ${method}`);
  }
}

async function callTool(name, args) {
  const startedAt = performance.now();
  lastQueryStats = null;
  let value;

  switch (name) {
    case 'db_schema':
      value = await dbSchema();
      break;

    case 'db_tables':
      value = await dbTables();
      break;

    case 'db_describe_table':
      value = await dbDescribeTable(requireIdentifier(args.table, 'table'));
      break;

    case 'db_sample':
      value = await dbSample(
        requireIdentifier(args.table, 'table'),
        normalizeLimit(args.limit, 20),
      );
      break;

    case 'db_query':
      value = await dbQuery(
        requireSql(args.sql),
        normalizeLimit(args.limit, defaultLimit),
      );
      break;

    case 'db_explain':
      value = await dbExplain(requireSql(args.sql));
      break;

    default:
      throw new Error(`Unknown tool: ${name}`);
  }

  return jsonContent(withToolMeta(value, name, startedAt));
}

function toolDefinitions() {
  return [
    {
      name: 'db_schema',
      description: 'Return a compact JSON schema for all readable non-system PostgreSQL tables: schemas, tables, columns, primary keys, foreign keys, and indexes.',
      inputSchema: { type: 'object', additionalProperties: false, properties: {} },
    },
    {
      name: 'db_tables',
      description: 'List all readable non-system tables with schema name, table name, estimated row count, and table kind.',
      inputSchema: { type: 'object', additionalProperties: false, properties: {} },
    },
    {
      name: 'db_describe_table',
      description: 'Describe one table with columns, constraints, foreign keys, and indexes. Use before querying a table.',
      inputSchema: {
        type: 'object',
        additionalProperties: false,
        required: ['table'],
        properties: {
          table: { type: 'string', description: 'Table name as table or schema.table.' },
        },
      },
    },
    {
      name: 'db_sample',
      description: 'Return a small sample from one table. Values are truncated for token efficiency.',
      inputSchema: {
        type: 'object',
        additionalProperties: false,
        required: ['table'],
        properties: {
          table: { type: 'string', description: 'Table name as table or schema.table.' },
          limit: { type: 'integer', minimum: 1, maximum: maxLimit, default: 20 },
        },
      },
    },
    {
      name: 'db_query',
      description: 'Run one read-only SELECT/WITH/VALUES query with an enforced outer LIMIT. Values are truncated for token efficiency.',
      inputSchema: {
        type: 'object',
        additionalProperties: false,
        required: ['sql'],
        properties: {
          sql: { type: 'string', description: 'Single read-only SELECT, WITH, or VALUES query.' },
          limit: { type: 'integer', minimum: 1, maximum: maxLimit, default: defaultLimit },
        },
      },
    },
    {
      name: 'db_explain',
      description: 'Run EXPLAIN FORMAT JSON for one read-only SELECT/WITH/VALUES query without returning table data.',
      inputSchema: {
        type: 'object',
        additionalProperties: false,
        required: ['sql'],
        properties: {
          sql: { type: 'string', description: 'Single read-only SELECT, WITH, or VALUES query.' },
        },
      },
    },
  ];
}

async function dbSchema() {
  const sql = `
WITH user_tables AS (
  SELECT
    n.nspname AS schema_name,
    c.relname AS table_name,
    c.relkind,
    c.reltuples::bigint AS estimated_rows,
    c.oid AS table_oid
  FROM pg_class c
  JOIN pg_namespace n ON n.oid = c.relnamespace
  WHERE c.relkind IN ('r', 'p', 'v', 'm', 'f')
    AND n.nspname NOT IN ('pg_catalog', 'information_schema')
    AND n.nspname NOT LIKE 'pg_toast%'
),
columns AS (
  SELECT
    table_schema,
    table_name,
    jsonb_agg(jsonb_build_object(
      'n', column_name,
      't', data_type,
      'dbt', udt_name,
      'null', is_nullable = 'YES',
      'def', column_default
    ) ORDER BY ordinal_position) AS cols
  FROM information_schema.columns
  WHERE table_schema NOT IN ('pg_catalog', 'information_schema')
  GROUP BY table_schema, table_name
),
primary_keys AS (
  SELECT
    tc.table_schema,
    tc.table_name,
    jsonb_agg(kcu.column_name ORDER BY kcu.ordinal_position) AS pk
  FROM information_schema.table_constraints tc
  JOIN information_schema.key_column_usage kcu
    ON kcu.constraint_schema = tc.constraint_schema
   AND kcu.constraint_name = tc.constraint_name
  WHERE tc.constraint_type = 'PRIMARY KEY'
  GROUP BY tc.table_schema, tc.table_name
),
foreign_keys AS (
  SELECT
    tc.table_schema,
    tc.table_name,
    jsonb_agg(jsonb_build_object(
      'cols', fk.cols,
      'ref', ccu.table_schema || '.' || ccu.table_name,
      'ref_cols', fk.ref_cols
    ) ORDER BY tc.constraint_name) AS fks
  FROM information_schema.table_constraints tc
  JOIN information_schema.constraint_column_usage ccu
    ON ccu.constraint_schema = tc.constraint_schema
   AND ccu.constraint_name = tc.constraint_name
  JOIN LATERAL (
    SELECT
      jsonb_agg(kcu.column_name ORDER BY kcu.ordinal_position) AS cols,
      jsonb_agg(ccu.column_name ORDER BY kcu.ordinal_position) AS ref_cols
    FROM information_schema.key_column_usage kcu
    WHERE kcu.constraint_schema = tc.constraint_schema
      AND kcu.constraint_name = tc.constraint_name
  ) fk ON true
  WHERE tc.constraint_type = 'FOREIGN KEY'
  GROUP BY tc.table_schema, tc.table_name
),
indexes AS (
  SELECT
    schemaname AS table_schema,
    tablename AS table_name,
    jsonb_agg(jsonb_build_object('n', indexname, 'def', indexdef) ORDER BY indexname) AS idx
  FROM pg_indexes
  WHERE schemaname NOT IN ('pg_catalog', 'information_schema')
  GROUP BY schemaname, tablename
)
SELECT coalesce(jsonb_agg(jsonb_build_object(
  's', t.schema_name,
  't', t.table_name,
  'kind', t.relkind,
  'rows', t.estimated_rows,
  'pk', coalesce(pk.pk, '[]'::jsonb),
  'cols', coalesce(c.cols, '[]'::jsonb),
  'fks', coalesce(fk.fks, '[]'::jsonb),
  'idx', coalesce(i.idx, '[]'::jsonb)
) ORDER BY t.schema_name, t.table_name), '[]'::jsonb)
FROM user_tables t
LEFT JOIN columns c ON c.table_schema = t.schema_name AND c.table_name = t.table_name
LEFT JOIN primary_keys pk ON pk.table_schema = t.schema_name AND pk.table_name = t.table_name
LEFT JOIN foreign_keys fk ON fk.table_schema = t.schema_name AND fk.table_name = t.table_name
LEFT JOIN indexes i ON i.table_schema = t.schema_name AND i.table_name = t.table_name;
`;

  return {
    schema: 'all',
    tables: await runJson(sql),
  };
}

async function dbTables() {
  const sql = `
SELECT coalesce(jsonb_agg(jsonb_build_object(
  'schema', n.nspname,
  'table', c.relname,
  'kind', c.relkind,
  'estimated_rows', c.reltuples::bigint
) ORDER BY n.nspname, c.relname), '[]'::jsonb)
FROM pg_class c
JOIN pg_namespace n ON n.oid = c.relnamespace
WHERE c.relkind IN ('r', 'p', 'v', 'm', 'f')
  AND n.nspname NOT IN ('pg_catalog', 'information_schema')
  AND n.nspname NOT LIKE 'pg_toast%';
`;

  return { tables: await runJson(sql) };
}

async function dbDescribeTable(table) {
  const { schema, name } = splitTableName(table);
  const sql = `
WITH target AS (
  SELECT ${sqlLiteral(schema)}::name AS schema_name, ${sqlLiteral(name)}::name AS table_name
),
columns AS (
  SELECT coalesce(jsonb_agg(jsonb_build_object(
    'name', column_name,
    'type', data_type,
    'db_type', udt_name,
    'nullable', is_nullable = 'YES',
    'default', column_default,
    'position', ordinal_position
  ) ORDER BY ordinal_position), '[]'::jsonb) AS data
  FROM information_schema.columns c
  JOIN target t ON t.schema_name = c.table_schema AND t.table_name = c.table_name
),
constraints AS (
  SELECT coalesce(jsonb_agg(jsonb_build_object(
    'name', tc.constraint_name,
    'type', tc.constraint_type,
    'columns', cols.cols
  ) ORDER BY tc.constraint_type, tc.constraint_name), '[]'::jsonb) AS data
  FROM information_schema.table_constraints tc
  JOIN target t ON t.schema_name = tc.table_schema AND t.table_name = tc.table_name
  LEFT JOIN LATERAL (
    SELECT jsonb_agg(kcu.column_name ORDER BY kcu.ordinal_position) AS cols
    FROM information_schema.key_column_usage kcu
    WHERE kcu.constraint_schema = tc.constraint_schema
      AND kcu.constraint_name = tc.constraint_name
  ) cols ON true
),
foreign_keys AS (
  SELECT coalesce(jsonb_agg(jsonb_build_object(
    'name', tc.constraint_name,
    'columns', fk.cols,
    'references', ccu.table_schema || '.' || ccu.table_name,
    'reference_columns', fk.ref_cols
  ) ORDER BY tc.constraint_name), '[]'::jsonb) AS data
  FROM information_schema.table_constraints tc
  JOIN target t ON t.schema_name = tc.table_schema AND t.table_name = tc.table_name
  JOIN information_schema.constraint_column_usage ccu
    ON ccu.constraint_schema = tc.constraint_schema
   AND ccu.constraint_name = tc.constraint_name
  JOIN LATERAL (
    SELECT
      jsonb_agg(kcu.column_name ORDER BY kcu.ordinal_position) AS cols,
      jsonb_agg(ccu.column_name ORDER BY kcu.ordinal_position) AS ref_cols
    FROM information_schema.key_column_usage kcu
    WHERE kcu.constraint_schema = tc.constraint_schema
      AND kcu.constraint_name = tc.constraint_name
  ) fk ON true
  WHERE tc.constraint_type = 'FOREIGN KEY'
),
indexes AS (
  SELECT coalesce(jsonb_agg(jsonb_build_object(
    'name', indexname,
    'definition', indexdef
  ) ORDER BY indexname), '[]'::jsonb) AS data
  FROM pg_indexes i
  JOIN target t ON t.schema_name = i.schemaname AND t.table_name = i.tablename
)
SELECT jsonb_build_object(
  'table', (SELECT schema_name || '.' || table_name FROM target),
  'columns', (SELECT data FROM columns),
  'constraints', (SELECT data FROM constraints),
  'foreign_keys', (SELECT data FROM foreign_keys),
  'indexes', (SELECT data FROM indexes)
);
`;

  const description = await runJson(sql);
  if (description.columns.length === 0) {
    throw new Error(`Table not found or not readable: ${table}`);
  }

  return description;
}

async function dbSample(table, limit) {
  const { schema, name } = splitTableName(table);
  const sql = `SELECT * FROM ${quoteIdent(schema)}.${quoteIdent(name)}`;
  return dbQuery(sql, limit);
}

async function dbQuery(sql, limit) {
  const normalized = normalizeReadOnlySql(sql);
  const fetchLimit = limit + 1;
  const wrapped = `
WITH _mcp_source AS MATERIALIZED (
${normalized}
)
SELECT coalesce(jsonb_agg(row_to_json(_mcp_rows)), '[]'::jsonb)
FROM (
  SELECT *
  FROM _mcp_source
  LIMIT ${fetchLimit}
) _mcp_rows;
`;

  const rows = truncateValues(await runJson(wrapped));

  return {
    limit,
    returned: Math.min(rows.length, limit),
    truncated: rows.length > limit,
    rows: rows.slice(0, limit),
  };
}

async function dbExplain(sql) {
  const normalized = normalizeReadOnlySql(sql);
  const plan = await runJson(`EXPLAIN (FORMAT JSON) ${normalized}`);

  return { plan };
}

function normalizeReadOnlySql(sql) {
  const trimmed = sql.trim().replace(/;+$/g, '').trim();
  const scrubbed = scrubSqlForValidation(trimmed);

  if (/^\s*\\/m.test(scrubbed)) {
    throw new Error('psql meta commands are not allowed.');
  }

  if (scrubbed.includes(';')) {
    throw new Error('Only one SQL statement is allowed.');
  }

  if (!/^(select|with|values)\b/i.test(scrubbed.trim())) {
    throw new Error('Only SELECT, WITH, and VALUES queries are allowed.');
  }

  if (/\b(insert|update|delete|merge|create|alter|drop|truncate|grant|revoke|copy|call|do|execute|vacuum|analyze|refresh|reindex|listen|notify)\b/i.test(scrubbed)) {
    throw new Error('Query contains a forbidden write/admin keyword.');
  }

  if (/\bfor\s+(update|no\s+key\s+update|share|key\s+share)\b/i.test(scrubbed)) {
    throw new Error('Locking clauses are not allowed.');
  }

  return trimmed;
}

function scrubSqlForValidation(sql) {
  let result = '';
  let index = 0;

  while (index < sql.length) {
    const char = sql[index];
    const next = sql[index + 1];

    if (char === '-' && next === '-') {
      while (index < sql.length && sql[index] !== '\n') index++;
      result += ' ';
      continue;
    }

    if (char === '/' && next === '*') {
      index += 2;
      while (index < sql.length && !(sql[index] === '*' && sql[index + 1] === '/')) index++;
      index += 2;
      result += ' ';
      continue;
    }

    if (char === "'") {
      index++;
      while (index < sql.length) {
        if (sql[index] === "'" && sql[index + 1] === "'") {
          index += 2;
          continue;
        }
        if (sql[index] === "'") {
          index++;
          break;
        }
        index++;
      }
      result += "''";
      continue;
    }

    if (char === '"') {
      index++;
      while (index < sql.length) {
        if (sql[index] === '"' && sql[index + 1] === '"') {
          index += 2;
          continue;
        }
        if (sql[index] === '"') {
          index++;
          break;
        }
        index++;
      }
      result += '""';
      continue;
    }

    result += char;
    index++;
  }

  return result;
}

function normalizeLimit(value, fallback) {
  const parsed = Number.parseInt(value ?? fallback, 10);
  if (!Number.isInteger(parsed) || parsed < 1) {
    throw new Error('limit must be a positive integer.');
  }

  return Math.min(parsed, maxLimit);
}

function requireSql(value) {
  if (typeof value !== 'string' || value.trim() === '') {
    throw new Error('sql is required.');
  }

  return value;
}

function requireIdentifier(value, label) {
  if (typeof value !== 'string' || value.trim() === '') {
    throw new Error(`${label} is required.`);
  }

  return value.trim();
}

function splitTableName(table) {
  const parts = table.split('.');
  if (parts.length > 2) {
    throw new Error('Table must be formatted as table or schema.table.');
  }

  const schema = parts.length === 2 ? parts[0] : 'public';
  const name = parts.length === 2 ? parts[1] : parts[0];

  if (!isIdentifier(schema) || !isIdentifier(name)) {
    throw new Error('Table identifiers may contain only letters, numbers, and underscores, and may not start with a number.');
  }

  return { schema, name };
}

function isIdentifier(value) {
  return /^[A-Za-z_][A-Za-z0-9_]*$/.test(value);
}

function quoteIdent(value) {
  return `"${value.replaceAll('"', '""')}"`;
}

function sqlLiteral(value) {
  return `'${value.replaceAll("'", "''")}'`;
}

function truncateValues(value) {
  if (Array.isArray(value)) return value.map((item) => truncateValues(item));
  if (value && typeof value === 'object') {
    return Object.fromEntries(Object.entries(value).map(([key, item]) => [key, truncateValues(item)]));
  }
  if (typeof value === 'string' && value.length > maxCellChars) {
    return `${value.slice(0, maxCellChars)}... [truncated ${value.length - maxCellChars} chars]`;
  }

  return value;
}

async function runJson(sql) {
  const output = await runPsql(sql);
  const compact = output.trim();
  if (compact === '') return null;

  return JSON.parse(compact);
}

function runPsql(sql) {
  queryChain = queryChain.then(() => runPsqlSingle(sql), () => runPsqlSingle(sql));

  return queryChain;
}

function runPsqlSingle(sql) {
  const command = `${sql.trim().replace(/;+$/g, '')};`;
  const sentinel = `__MCP_QUERY_DONE_${process.pid}_${nextQueryId++}__`;
  const hadReadySession = Boolean(psqlReadyPromise);
  const startedAt = performance.now();

  return new Promise((resolvePromise, reject) => {
    ensurePsql()
      .then((child) => {
        let timeout = null;

        psqlCurrentQuery = {
          sentinel,
          stdout: '',
          stderrStart: psqlStderrBuffer.length,
          resolve: (value) => {
            clearTimeout(timeout);
            psqlCurrentQuery = null;
            lastQueryStats = {
              dbElapsedMs: Number((performance.now() - startedAt).toFixed(1)),
              psqlSession: hadReadySession ? 'reused' : 'started',
            };
            resolvePromise(value);
          },
          reject: (error) => {
            clearTimeout(timeout);
            psqlCurrentQuery = null;
            reject(error);
          },
        };

        timeout = setTimeout(() => {
          const error = new Error('PostgreSQL MCP query timed out.');
          stopPsql();
          reject(error);
        }, 10_000);

        child.stdin.write(`${command}\n\\echo ${sentinel}\n`);
      })
      .catch(reject);
  });
}

function ensurePsql() {
  if (psqlReadyPromise) {
    return psqlReadyPromise;
  }

  const databaseUrl = getDatabaseUrl();

  psqlReadyPromise = new Promise((resolvePromise, reject) => {
    const child = spawn('docker', [
      'compose',
      'exec',
      '-T',
      'postgres',
      'psql',
      databaseUrl,
      '-X',
      '-q',
      '-t',
      '-A',
      '-P',
      'pager=off',
    ], {
      cwd: repoRoot,
      stdio: ['pipe', 'pipe', 'pipe'],
    });

    let startupError = '';
    let settled = false;

    child.stdout.setEncoding('utf8');
    child.stderr.setEncoding('utf8');
    child.stdout.on('data', handlePsqlStdout);
    child.stderr.on('data', (chunk) => {
      psqlStderrBuffer += chunk;
      startupError += chunk;
    });
    child.on('error', (error) => {
      psqlProcess = null;
      psqlReadyPromise = null;
      if (!settled) {
        settled = true;
        reject(error);
      }
      if (psqlCurrentQuery) {
        psqlCurrentQuery.reject(error);
      }
    });
    child.on('close', (code) => {
      psqlProcess = null;
      if (!settled) {
        settled = true;
        reject(new Error(startupError.trim() || `psql exited with code ${code}`));
      }
      if (psqlCurrentQuery) {
        psqlCurrentQuery.reject(new Error(psqlStderrBuffer.trim() || `psql exited with code ${code}`));
      }
    });

    psqlProcess = child;
    child.stdin.write("SET default_transaction_read_only = on;\nSET statement_timeout = '5s';\n\\echo __MCP_PSQL_READY__\n");

    const startupTimeout = setTimeout(() => {
      if (settled) return;
      settled = true;
      stopPsql();
      reject(new Error('Timed out while starting persistent psql process.'));
    }, 10_000);

    const readyCheck = setInterval(() => {
      if (!psqlStdoutBuffer.includes('__MCP_PSQL_READY__')) return;

      clearInterval(readyCheck);
      clearTimeout(startupTimeout);
      psqlStdoutBuffer = psqlStdoutBuffer.replace('__MCP_PSQL_READY__\n', '').replace('__MCP_PSQL_READY__', '');
      settled = true;
      resolvePromise(child);
    }, 10);
  });

  return psqlReadyPromise;
}

function handlePsqlStdout(chunk) {
  psqlStdoutBuffer += chunk;

  if (!psqlCurrentQuery) return;

  const markerIndex = psqlStdoutBuffer.indexOf(psqlCurrentQuery.sentinel);
  if (markerIndex === -1) return;

  const output = psqlStdoutBuffer.slice(0, markerIndex);
  const afterMarker = psqlStdoutBuffer.slice(markerIndex + psqlCurrentQuery.sentinel.length);
  psqlStdoutBuffer = afterMarker.replace(/^\r?\n/, '');

  const stderr = psqlStderrBuffer.slice(psqlCurrentQuery.stderrStart).trim();
  if (stderr !== '') {
    psqlCurrentQuery.reject(new Error(stderr));
    return;
  }

  psqlCurrentQuery.resolve(output);
}

function stopPsql() {
  if (!psqlProcess) return;

  const child = psqlProcess;
  psqlProcess = null;
  psqlReadyPromise = null;
  child.kill('SIGTERM');
}

process.on('exit', stopPsql);

function getDatabaseUrl() {
  if (process.env.EVENTI_TECH_MCP_DATABASE_URL) {
    return process.env.EVENTI_TECH_MCP_DATABASE_URL;
  }

  if (!existsSync(urlFile)) {
    throw new Error(`Missing ${urlFile}. Run ./scripts/agents/setup-mcp-postgres.sh`);
  }

  return readFileSync(urlFile, 'utf8').trim();
}

function jsonContent(value) {
  return {
    content: [
      {
        type: 'text',
        text: JSON.stringify(value),
      },
    ],
  };
}

function withToolMeta(value, toolName, startedAt) {
  const meta = {
    tool: toolName,
    serverElapsedMs: Number((performance.now() - startedAt).toFixed(1)),
    db: lastQueryStats,
  };

  if (value && typeof value === 'object' && !Array.isArray(value)) {
    return {
      ...value,
      _mcp: meta,
    };
  }

  return {
    value,
    _mcp: meta,
  };
}

function send(message) {
  const body = JSON.stringify(message);
  process.stdout.write(`${body}\n`);
}
