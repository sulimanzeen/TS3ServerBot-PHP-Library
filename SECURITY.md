# Security

## Reporting

Email security issues privately to the maintainers of TS3ServerBot.com. Do not open a public issue with credentials or exploit details.

## Framework safeguards

- Raw/telnet Query is not implemented.
- WebQuery API keys are sent in the `x-api-key` header, not `?api-key=`.
- `ClientConfig::__debugInfo()` redacts passwords and API keys.
- Query values are escaped before they are written on SSH.
- Command names must exist in the active catalog (`ServerFamily::TeamSpeak3` → 3.13.8, `TeamSpeak6` → 6.0.0-beta12.1). On TeamSpeak 6, unknown Query identifiers are still forwarded to the server.
- Timeouts are required and default to 10 seconds.

## Operator responsibilities

- Enable SSH and/or WebQuery on TeamSpeak 3.13.8 or 6.0.0-beta12.1. Do not rely on raw Query.
- Put WebQuery behind HTTPS (a reverse proxy). Cleartext HTTP is what 3.13.8 documents on port 10080.
- Store API keys and Query passwords outside the repository.
- Add automation hosts to `query_ip_allowlist.txt` so flood protection does not block bots.
- Use the least privilege Query account that can perform the required commands.

## Known server-side limits (3.13.8)

- Default Query inactivity timeout is 300 seconds.
- Default flood limit is 10 commands / 3 seconds unless allowlisted.
- Default maximum is 5 Query connections per IP unless allowlisted.
- Snapshot deploy does not re-check permissions and can be abused; treat snapshot data as sensitive.
