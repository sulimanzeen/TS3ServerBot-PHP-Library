# Troubleshooting

## Authentication failed (WebQuery)

- Confirm the API key is current (`apikeyadd` over SSH if needed).
- Confirm WebQuery is enabled (`query_protocols` includes `http`).
- Confirm the header is `x-api-key` (the library sets this).

## Authentication failed (SSH)

- Confirm `query_protocols` includes `ssh` and port 10022 is open.
- Confirm username/password. 3.13.8 shows the serveradmin password only once at first start.
- Optional fingerprint mismatch means the host key changed or the configured hash is wrong.

## UnsupportedOperationException for login / use / notify / ft

You are on WebQuery. Those commands are documented as unsupported there. Use SSH.

## Flood / disconnects

The server defaults to 10 commands per 3 seconds and a 300-second idle timeout, and 5 Query connections per IP. Allowlist the bot IP.

## Empty client list

`clientlist` only returns clients in channels the Query client can subscribe to.

## Raw port 10011 connection refused by this library

Intentional. Enable SSH/HTTP on the server. See [Not supported](not-supported.md).

## PHP version

This library requires PHP 8.1+. PHP 7.4 cannot load it.
