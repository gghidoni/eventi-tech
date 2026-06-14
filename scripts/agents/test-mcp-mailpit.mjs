#!/usr/bin/env node
import { spawn } from 'node:child_process';

const child = spawn('./scripts/agents/run-mcp-mailpit.sh', [], {
  cwd: process.cwd(),
  stdio: ['pipe', 'pipe', 'pipe'],
});

let stdout = Buffer.alloc(0);
let stderr = '';
let nextId = 1;
const pending = new Map();

child.stdout.on('data', (chunk) => {
  stdout = Buffer.concat([stdout, chunk]);
  parseMessages();
});

child.stderr.on('data', (chunk) => {
  stderr += chunk.toString('utf8');
});

function send(method, params = {}, timeoutMs = 20_000) {
  const id = nextId++;
  const body = JSON.stringify({ jsonrpc: '2.0', id, method, params });

  child.stdin.write(`${body}\n`);

  return new Promise((resolve, reject) => {
    const timer = setTimeout(() => reject(new Error(`Timeout waiting for ${method}; stderr=${stderr}`)), timeoutMs);
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
  child.stdin.write(`${JSON.stringify({ jsonrpc: '2.0', method, params })}\n`);
}

function parseMessages() {
  while (true) {
    const newline = stdout.indexOf('\n');
    if (newline === -1) return;

    const line = stdout.slice(0, newline).toString('utf8').trim();
    stdout = stdout.slice(newline + 1);
    if (!line) continue;

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
  return parseToolResult(await send('tools/call', { name, arguments: args }));
}

async function main() {
  const init = await send('initialize', {
    protocolVersion: '2024-11-05',
    capabilities: {},
    clientInfo: { name: 'eventi-tech-mailpit-smoke', version: '1.0.0' },
  });

  notify('notifications/initialized');

  const tools = await send('tools/list');
  const toolNames = tools.tools.map((tool) => tool.name);
  const info = await callTool('mailpit_info');
  const list = await callTool('mailpit_list', { limit: 5 });

  let latest = null;
  let latestFor = null;
  let links = null;
  if (list.messages.length > 0) {
    latest = await callTool('mailpit_identify', { id: list.messages[0].id });
    if (list.messages[0].subject === latest.subject && list.messages[0].kind !== latest.kind) {
      throw new Error(`List kind ${list.messages[0].kind} differs from identify kind ${latest.kind}`);
    }
    const recipient = list.messages[0].to[0]?.email;

    if (recipient) {
      latestFor = await callTool('mailpit_latest_for', { email: recipient, limit: 20 });
    }

    links = await callTool('mailpit_extract_links', { id: list.messages[0].id });
  }

  const infoAfterReads = await callTool('mailpit_info');
  const unreadUnchanged = info.Unread === infoAfterReads.Unread;

  if (!unreadUnchanged) {
    throw new Error(`Mailpit unread count changed from ${info.Unread} to ${infoAfterReads.Unread}`);
  }

  console.log(JSON.stringify({
    server: init.serverInfo,
    tools: toolNames,
    info,
    listed: list.messages.length,
    latest,
    latestFor,
    links,
    unreadUnchanged,
  }, null, 2));
}

main()
  .finally(() => {
    child.kill('SIGTERM');
  })
  .catch((error) => {
    console.error(error.stack ?? error.message);
    process.exit(1);
  });
