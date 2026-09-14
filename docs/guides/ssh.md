# SSH transport

## What it is

Encrypted ServerQuery on TCP port **10022** (`query_ssh_port`). This is not an OS shell. After SSH is up, the session is the documented ServerQuery text protocol. TeamSpeak 6 uses the same SSH Query client.

## When to use it

- Persistent bots
- Commands WebQuery cannot run (`login`, `use`, `quit`, `servernotifyregister`, `ft*`, `help`)
- Creating API keys with `apikeyadd` when you do not already have one

## Configuration

- Host
- `sshPort` (default 10022)
- Username and password (required)
- Optional `sshHostFingerprint` (SHA256 of the host key; mismatch fails the connection)
- Timeout

## Authentication

1. SSH password authentication with the configured Query username and password (phpseclib, no PTY). That handshake **is** Query authentication.
2. Optional Query `login` only to switch identity. After that command, TeamSpeak deselects the virtual server; call `useServer()` again.

The 3.13.8 docs do not specify SSH public-key login. This library does not implement SSH keys.

## Lifecycle

`connect()` → banner → already logged in → automatic `use` when `virtualServerId` or `virtualServerPort` is set → commands → `disconnect()` (`quit`).

Optional: `login()` to switch identity, then `useServer()` again.

Inactivity: the server default `query_timeout` is 300 seconds. No keep-alive command is documented.

## Operations

All catalog commands are allowed on SSH, including session-only commands.

## Errors

Query `error id=` lines become `ServerException`, `PermissionException`, or `AuthenticationException`. Timeouts become `TimeoutException`.

## Security

SSH is encrypted. Still restrict Query accounts, allowlist automation IPs, and never log passwords.

## Limitations

- Interactive SSH has a 4096-character line limit; this client does not allocate a PTY, matching the 3.13.8 changelog for bots.
- Notify payloads are not documented; `drainNotifications()` returns unverified lines.
