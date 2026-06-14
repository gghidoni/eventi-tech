#!/usr/bin/env node
const serverName = 'eventi-tech-mailpit';
const serverVersion = '1.0.0';
const mailpitBaseUrl = (process.env.MAILPIT_BASE_URL || 'http://127.0.0.1:8025').replace(/\/+$/, '');
const maxLimit = 100;
const defaultLimit = 20;
const maxPreviewChars = 800;

const instructions = [
  'Local read-only Mailpit MCP for Eventi Tech.',
  'Use it to check whether an email arrived, identify what email it is, inspect compact details, and extract links.',
  'Do not use destructive Mailpit APIs from this server; only read local Mailpit on 127.0.0.1:8025 unless MAILPIT_BASE_URL is explicitly set.',
].join(' ');

let inputBuffer = Buffer.alloc(0);

process.stdin.on('data', (chunk) => {
  inputBuffer = Buffer.concat([inputBuffer, chunk]);
  readMessages();
});

process.stdin.on('end', () => process.exit(0));

function readMessages() {
  while (true) {
    const newline = inputBuffer.indexOf('\n');
    if (newline === -1) return;

    const line = inputBuffer.slice(0, newline).toString('utf8').trim();
    inputBuffer = inputBuffer.slice(newline + 1);

    if (line !== '') {
      void handleMessage(JSON.parse(line));
    }
  }
}

async function handleMessage(message) {
  if (!Object.prototype.hasOwnProperty.call(message, 'id')) return;

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
      return {
        protocolVersion: params.protocolVersion ?? '2024-11-05',
        capabilities: { tools: {}, resources: {} },
        serverInfo: { name: serverName, version: serverVersion },
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
  let value;

  switch (name) {
    case 'mailpit_info':
      value = await mailpitInfo();
      break;

    case 'mailpit_list':
      value = await mailpitList(normalizeLimit(args.limit, defaultLimit), normalizeStart(args.start));
      break;

    case 'mailpit_search':
      value = await mailpitSearch(requireString(args.query, 'query'), normalizeLimit(args.limit, defaultLimit), normalizeStart(args.start));
      break;

    case 'mailpit_latest_for':
      value = await mailpitLatestFor(requireString(args.email, 'email'), {
        subjectContains: optionalString(args.subject_contains),
        limit: normalizeLimit(args.limit, 50),
      });
      break;

    case 'mailpit_get':
      value = await mailpitGet(requireString(args.id, 'id'), { includeBody: Boolean(args.include_body) });
      break;

    case 'mailpit_identify':
      value = await mailpitIdentify(args.id ? requireString(args.id, 'id') : 'latest');
      break;

    case 'mailpit_extract_links':
      value = await mailpitExtractLinks(args.id ? requireString(args.id, 'id') : 'latest');
      break;

    default:
      throw new Error(`Unknown tool: ${name}`);
  }

  return jsonContent(withToolMeta(value, name, startedAt));
}

function toolDefinitions() {
  return [
    {
      name: 'mailpit_info',
      description: 'Return local Mailpit runtime information and message totals.',
      inputSchema: { type: 'object', additionalProperties: false, properties: {} },
    },
    {
      name: 'mailpit_list',
      description: 'List recent Mailpit messages with compact metadata: id, subject, from, to, date, size, read status, and inferred kind.',
      inputSchema: {
        type: 'object',
        additionalProperties: false,
        properties: {
          limit: { type: 'integer', minimum: 1, maximum: maxLimit, default: defaultLimit },
          start: { type: 'integer', minimum: 0, default: 0 },
        },
      },
    },
    {
      name: 'mailpit_search',
      description: 'Search Mailpit messages using Mailpit search syntax, returning compact metadata and inferred kind.',
      inputSchema: {
        type: 'object',
        additionalProperties: false,
        required: ['query'],
        properties: {
          query: { type: 'string' },
          limit: { type: 'integer', minimum: 1, maximum: maxLimit, default: defaultLimit },
          start: { type: 'integer', minimum: 0, default: 0 },
        },
      },
    },
    {
      name: 'mailpit_latest_for',
      description: 'Find the latest recent message for one recipient email, optionally filtering by subject substring. Use this to answer whether a user received an email.',
      inputSchema: {
        type: 'object',
        additionalProperties: false,
        required: ['email'],
        properties: {
          email: { type: 'string' },
          subject_contains: { type: 'string' },
          limit: { type: 'integer', minimum: 1, maximum: maxLimit, default: 50 },
        },
      },
    },
    {
      name: 'mailpit_get',
      description: 'Get compact details for one message id or latest. Body is omitted by default and returned as truncated previews only when include_body is true.',
      inputSchema: {
        type: 'object',
        additionalProperties: false,
        required: ['id'],
        properties: {
          id: { type: 'string', description: 'Message id or latest.' },
          include_body: { type: 'boolean', default: false },
        },
      },
    },
    {
      name: 'mailpit_identify',
      description: 'Identify what a Mailpit message is: auth verification, password reset, CFP notification, event/community notification, or generic.',
      inputSchema: {
        type: 'object',
        additionalProperties: false,
        properties: {
          id: { type: 'string', description: 'Message id or latest. Defaults to latest.' },
        },
      },
    },
    {
      name: 'mailpit_extract_links',
      description: 'Extract links from one Mailpit message id or latest, useful for verification and password reset flows.',
      inputSchema: {
        type: 'object',
        additionalProperties: false,
        properties: {
          id: { type: 'string', description: 'Message id or latest. Defaults to latest.' },
        },
      },
    },
  ];
}

async function mailpitInfo() {
  return fetchJson('/api/v1/info');
}

async function mailpitList(limit, start) {
  const data = await fetchJson(`/api/v1/messages?start=${start}&limit=${limit}`);
  const messages = extractMessages(data).map(compactMessage);

  return {
    total: data.total ?? data.Total ?? messages.length,
    unread: data.unread ?? data.Unread,
    messages,
  };
}

async function mailpitSearch(query, limit, start) {
  const data = await fetchJson(`/api/v1/search?query=${encodeURIComponent(query)}&start=${start}&limit=${limit}`);
  const messages = extractMessages(data).map(compactMessage);

  return {
    query,
    total: data.total ?? data.Total ?? messages.length,
    messages,
  };
}

async function mailpitLatestFor(email, options) {
  const normalizedEmail = email.toLowerCase();
  const listed = await mailpitList(options.limit, 0);
  let match = listed.messages.find((message) => {
    const recipients = [...message.to, ...message.cc, ...message.bcc].map((recipient) => recipient.email.toLowerCase());
    const recipientMatch = recipients.includes(normalizedEmail);
    const subjectMatch = !options.subjectContains || message.subject.toLowerCase().includes(options.subjectContains.toLowerCase());

    return recipientMatch && subjectMatch;
  });

  if (!match) {
    const searched = await mailpitSearch(email, options.limit, 0);
    match = searched.messages.find((message) => {
      const recipients = [...message.to, ...message.cc, ...message.bcc].map((recipient) => recipient.email.toLowerCase());
      const recipientMatch = recipients.includes(normalizedEmail);
      const subjectMatch = !options.subjectContains || message.subject.toLowerCase().includes(options.subjectContains.toLowerCase());

      return recipientMatch && subjectMatch;
    });
  }

  if (!match) {
    return {
      arrived: false,
      email,
      subject_contains: options.subjectContains ?? null,
      searched_recent: options.limit,
      message: null,
    };
  }

  const details = await mailpitGet(match.id, { includeBody: false });

  return {
    arrived: true,
    email,
    subject_contains: options.subjectContains ?? null,
    message: details,
  };
}

async function mailpitGet(id, options) {
  const message = await fetchMessageWithoutMarkingRead(id);
  const compact = compactMessage(message.summary);
  const body = collectBody(message);
  const links = extractLinks(body.text, body.html);

  return {
    ...compact,
    kind: inferKind(message),
    attachments: compactAttachments(message),
    links: links.slice(0, 20),
    body: options.includeBody ? {
      text_preview: truncate(body.text),
      html_preview: truncate(stripHtml(body.html)),
    } : undefined,
  };
}

async function mailpitIdentify(id) {
  const message = await fetchMessageWithoutMarkingRead(id);
  const body = collectBody(message);
  const compact = compactMessage(message.summary);
  const kind = inferKind(message);

  return {
    id: compact.id,
    subject: compact.subject,
    from: compact.from,
    to: compact.to,
    date: compact.date,
    kind,
    confidence: kind === 'generic' ? 'low' : 'medium',
    evidence: evidenceForKind(kind, message, body),
  };
}

async function mailpitExtractLinks(id) {
  const message = await fetchMessageWithoutMarkingRead(id);
  const body = collectBody(message);

  return {
    id: message.summary.ID ?? message.summary.Id ?? message.summary.id ?? id,
    subject: message.summary.Subject ?? message.summary.subject ?? '',
    links: extractLinks(body.text, body.html).slice(0, 50),
  };
}

async function fetchMessageWithoutMarkingRead(id) {
  const summary = await findSummary(id);
  const raw = await fetchText(`/api/v1/message/${encodeURIComponent(id)}/raw`);
  const parsed = parseRawEmail(raw);

  return {
    summary: summary ?? summaryFromRaw(id, parsed),
    raw,
    parsed,
  };
}

async function findSummary(id) {
  const list = await mailpitList(maxLimit, 0);

  if (id === 'latest') {
    return list.messages[0] ? summaryFromCompact(list.messages[0]) : null;
  }

  const compact = list.messages.find((message) => message.id === id);

  return compact ? summaryFromCompact(compact) : null;
}

async function fetchJson(path) {
  const response = await fetch(`${mailpitBaseUrl}${path}`, {
    method: 'GET',
    headers: { accept: 'application/json' },
  });

  if (!response.ok) {
    const text = await response.text().catch(() => '');
    throw new Error(`Mailpit API ${response.status} ${response.statusText}: ${truncate(text, 300)}`);
  }

  return response.json();
}

async function fetchText(path) {
  const response = await fetch(`${mailpitBaseUrl}${path}`, {
    method: 'GET',
    headers: { accept: 'text/plain' },
  });

  if (!response.ok) {
    const text = await response.text().catch(() => '');
    throw new Error(`Mailpit API ${response.status} ${response.statusText}: ${truncate(text, 300)}`);
  }

  return response.text();
}

function extractMessages(data) {
  if (Array.isArray(data)) return data;
  if (Array.isArray(data.messages)) return data.messages;
  if (Array.isArray(data.Messages)) return data.Messages;
  if (Array.isArray(data.items)) return data.items;

  return [];
}

function compactMessage(message) {
  return {
    id: message.ID ?? message.Id ?? message.id ?? '',
    subject: message.Subject ?? message.subject ?? '',
    from: firstAddress(message.From ?? message.from),
    to: addressList(message.To ?? message.to),
    cc: addressList(message.Cc ?? message.CC ?? message.cc),
    bcc: addressList(message.Bcc ?? message.BCC ?? message.bcc),
    date: message.Date ?? message.Created ?? message.created ?? message.date ?? '',
    size: message.Size ?? message.size,
    read: message.Read ?? message.read,
    kind: inferKind(message),
  };
}

function firstAddress(value) {
  const list = addressList(value);

  return list[0] ?? { name: '', email: String(value ?? '') };
}

function addressList(value) {
  if (!value) return [];
  if (Array.isArray(value)) return value.flatMap(addressList);
  if (typeof value === 'string') return [{ name: '', email: value }];

  const email = value.Email ?? value.Address ?? value.Mailbox ?? value.email ?? value.address ?? '';
  const name = value.Name ?? value.name ?? '';

  if (email) return [{ name, email }];

  return [];
}

function collectBody(message) {
  if (message.parsed) {
    return {
      text: message.parsed.bodyText,
      html: message.parsed.bodyHtml,
    };
  }

  const text = [
    message.Text,
    message.text,
    message.TextBody,
    message.textBody,
    message.Body,
    message.body,
  ].filter(Boolean).join('\n');

  const html = [
    message.HTML,
    message.Html,
    message.html,
    message.HTMLBody,
    message.htmlBody,
  ].filter(Boolean).join('\n');

  return { text, html };
}

function inferKind(message) {
  const body = collectBody(message);
  const summary = message.summary ?? message;
  const haystack = [
    summary.Subject ?? summary.subject ?? '',
    body.text,
    stripHtml(body.html),
  ].join(' ').toLowerCase();

  if (containsAny(haystack, ['verifica email', 'verifica il tuo indirizzo', 'verify email', 'email verification'])) {
    return 'auth_email_verification';
  }
  if (containsAny(haystack, ['reset password', 'reimpost', 'password reset', 'recupera password'])) {
    return 'auth_password_reset';
  }
  if (containsAny(haystack, ['candidatura', 'cfp', 'proposta in revisione', 'submission'])) {
    if (containsAny(haystack, ['stato', 'status', 'aggiornat', 'aggiornamento candidatura'])) return 'cfp_submission_status';
    if (containsAny(haystack, ['ricevuta', 'received', 'organizzatore'])) return 'cfp_submission_received';
    return 'cfp_submission';
  }
  if (containsAny(haystack, ['evento approvato', 'event approved', 'community favorite'])) {
    return 'event_approved_notification';
  }
  if (containsAny(haystack, ['nuovo evento', 'new event'])) {
    return 'admin_new_event';
  }
  if (containsAny(haystack, ['nuova community', 'new community'])) {
    return 'admin_new_community';
  }

  return 'generic';
}

function evidenceForKind(kind, message, body) {
  const summary = message.summary ?? message;
  const subject = summary.Subject ?? summary.subject ?? '';
  const text = `${subject}\n${stripHtml(body.html)}\n${body.text}`;

  return {
    subject,
    preview: truncate(text.replace(/\s+/g, ' ').trim(), 300),
    links_found: extractLinks(body.text, body.html).length,
    inferred_from: kind === 'generic' ? [] : ['subject_or_body_keywords'],
  };
}

function parseRawEmail(raw) {
  const normalized = String(raw ?? '').replace(/\r\n/g, '\n');
  const splitAt = normalized.search(/\n\n/);
  const headerText = splitAt === -1 ? normalized : normalized.slice(0, splitAt);
  const bodyText = splitAt === -1 ? '' : normalized.slice(splitAt + 2);
  const headers = {};
  let current = null;

  for (const line of headerText.split('\n')) {
    if (/^\s/.test(line) && current) {
      headers[current] = `${headers[current]} ${line.trim()}`;
      continue;
    }

    const colon = line.indexOf(':');
    if (colon === -1) continue;

    current = line.slice(0, colon).toLowerCase();
    headers[current] = line.slice(colon + 1).trim();
  }

  return {
    headers,
    bodyText: decodeQuotedPrintable(bodyText),
    bodyHtml: decodeQuotedPrintable(bodyText),
  };
}

function decodeQuotedPrintable(value) {
  return String(value ?? '')
    .replace(/=\r?\n/g, '')
    .replace(/=([A-Fa-f0-9]{2})/g, (_, hex) => String.fromCharCode(Number.parseInt(hex, 16)));
}

function summaryFromRaw(id, parsed) {
  return {
    ID: id,
    Subject: parsed.headers.subject ?? '',
    From: parsed.headers.from ?? '',
    To: parsed.headers.to ?? '',
    Cc: parsed.headers.cc ?? '',
    Bcc: parsed.headers.bcc ?? '',
    Date: parsed.headers.date ?? '',
  };
}

function summaryFromCompact(message) {
  return {
    ID: message.id,
    Subject: message.subject,
    From: message.from,
    To: message.to,
    Cc: message.cc,
    Bcc: message.bcc,
    Date: message.date,
    Size: message.size,
    Read: message.read,
  };
}

function compactAttachments(message) {
  const attachments = message.Attachments ?? message.attachments ?? [];
  if (!Array.isArray(attachments)) return [];

  return attachments.map((attachment) => ({
    filename: attachment.FileName ?? attachment.Filename ?? attachment.filename ?? '',
    content_type: attachment.ContentType ?? attachment.contentType ?? '',
    size: attachment.Size ?? attachment.size,
    part_id: attachment.PartID ?? attachment.PartId ?? attachment.partID ?? attachment.partId,
  }));
}

function extractLinks(text = '', html = '') {
  const links = new Set();
  const combined = `${text}\n${html}`;
  const hrefPattern = /href\s*=\s*["']([^"']+)["']/gi;
  const urlPattern = /https?:\/\/[^\s"'<>]+/gi;

  for (const match of combined.matchAll(hrefPattern)) {
    links.add(cleanUrl(match[1]));
  }
  for (const match of combined.matchAll(urlPattern)) {
    links.add(cleanUrl(match[0]));
  }

  return [...links].filter(Boolean);
}

function cleanUrl(url) {
  return String(url).replace(/&amp;/g, '&').replace(/[).,;]+$/g, '');
}

function stripHtml(html = '') {
  return String(html)
    .replace(/<style[\s\S]*?<\/style>/gi, ' ')
    .replace(/<script[\s\S]*?<\/script>/gi, ' ')
    .replace(/<[^>]+>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>');
}

function containsAny(value, needles) {
  return needles.some((needle) => value.includes(needle));
}

function normalizeLimit(value, fallback) {
  const parsed = Number.parseInt(value ?? fallback, 10);
  if (!Number.isInteger(parsed) || parsed < 1) {
    throw new Error('limit must be a positive integer.');
  }

  return Math.min(parsed, maxLimit);
}

function normalizeStart(value) {
  const parsed = Number.parseInt(value ?? 0, 10);
  if (!Number.isInteger(parsed) || parsed < 0) {
    throw new Error('start must be zero or a positive integer.');
  }

  return parsed;
}

function requireString(value, name) {
  if (typeof value !== 'string' || value.trim() === '') {
    throw new Error(`${name} is required.`);
  }

  return value.trim();
}

function optionalString(value) {
  return typeof value === 'string' && value.trim() !== '' ? value.trim() : null;
}

function truncate(value, max = maxPreviewChars) {
  const text = String(value ?? '');
  if (text.length <= max) return text;

  return `${text.slice(0, max)}... [truncated ${text.length - max} chars]`;
}

function jsonContent(value) {
  return {
    content: [{ type: 'text', text: JSON.stringify(value) }],
  };
}

function withToolMeta(value, toolName, startedAt) {
  return {
    ...value,
    _mcp: {
      tool: toolName,
      mailpitBaseUrl,
      serverElapsedMs: Number((performance.now() - startedAt).toFixed(1)),
    },
  };
}

function send(message) {
  process.stdout.write(`${JSON.stringify(message)}\n`);
}
