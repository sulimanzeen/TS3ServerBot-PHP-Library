# WebQuery transport

## What it is

HTTP JSON interface documented for TeamSpeak 3.13.8 (same contract on 6.0.0-beta12.1). Default port **10080**.

## When to use it

Stateless request/response management: list clients, list channels, server info, kick, edit, and other commands that WebQuery supports.

## Configuration

- Host
- `webQueryPort` (default 10080)
- `apiKey` (required)
- `virtualServerId` or `virtualServerPort`
- Optional `webQueryTls` for `https://` when you terminate TLS on a reverse proxy

Native TeamSpeak `https` on 10443 is **not claimed**; the 3.13.8 quickstart tells operators to use a reverse proxy.

## Authentication

Every call requires an API key as HTTP header `x-api-key`. The library does not put the key in the URL.

Keys are created at first server start or with SSH command `apikeyadd` (scopes `manage`, `write`, `read`).

## Lifecycle

WebQuery has no Query session. `connect()` marks the client ready. Each method is one HTTP POST.

## Operations

Most catalog commands work. These do **not** (from `webquery.md`):

- `ft*`
- `help`
- `login`, `logout`, `quit`
- `servernotifyregister`, `servernotifyunregister`
- `use`

The framework throws `UnsupportedOperationException` if you call them on WebQuery.

Virtual server selection is the URL path `/{serverId}/{command}` or `/byport/{port}/{command}`.

## Errors

JSON `status.code` is 0 on success. `1281` (empty database result) is treated as success with no items. HTTP 401/403 becomes `AuthenticationException`.

## Security

Port 10080 is cleartext HTTP in the 3.13.8 docs. Prefer an HTTPS reverse proxy. Keep API keys secret.

## Limitations

No event stream. No file-transfer commands. No `use`/`login` session.
