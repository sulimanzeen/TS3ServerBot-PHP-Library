# Home

The **TS3ServerBot Query** library is a Composer package for TeamSpeak Query over SSH and WebQuery.

It is developed under TS3ServerBot.com so PHP bots, automation, and management tools can share one client for TeamSpeak 3.13.8 and TeamSpeak 6.0.0-beta12.1.

## Supported servers

- TeamSpeak 3.13.8
- TeamSpeak 6.0.0-beta12.1 — see [Compatibility](guides/compatibility.md) and [TS6-only commands](reference/teamspeak6-commands.md)

## Supported transports

- SSH ServerQuery on port 10022
- WebQuery HTTP/JSON on port 10080

Raw/telnet Query on port 10011 is **not supported**. See [Not supported](guides/not-supported.md).

## Important limitations

- File-transfer **bytes** after `ftkey` are not documented; only FT control commands can be sent over SSH.
- `servernotifyregister` notify field lists are not in the 3.13.8 docs.
- Native TeamSpeak HTTPS Query on 10443 is not claimed; use a reverse proxy if you need TLS for WebQuery.
- This library follows the Query command help for TeamSpeak 3.13.8 and 6.0.0-beta12.1, not stale HTML ServerQuery manuals.

## Next

1. [Installation](guides/installation.md)
2. [First connection](guides/first-connection.md)
3. [SSH transport](guides/ssh.md)
4. [WebQuery transport](guides/webquery.md)
5. [API reference](api.md)
6. [Command matrix](reference/commands.md)
