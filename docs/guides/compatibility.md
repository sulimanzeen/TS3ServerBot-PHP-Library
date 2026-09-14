# Compatibility

The library talks to TeamSpeak through **Query**, not the voice client protocol.

| Component | Supported |
|---|---|
| TeamSpeak 3 Server | 3.13.8 |
| TeamSpeak 6 Server | 6.0.0-beta12.1 |
| PHP | 8.1+ |
| Transports | SSH (10022), WebQuery HTTP (10080) |
| Raw Query 10011 | Not supported |

Command lists ship in this package as JSON catalogs for those two server versions. The published Composer package does not include TeamSpeak server binaries.

## How TeamSpeak 6 is supported

Set the server family when you connect:

```php
use TS3ServerBot\Config\ServerFamily;

$config = new ClientConfig(
    host: '127.0.0.1',
    transport: Transport::WebQuery,
    apiKey: 'your-api-key',
    virtualServerId: 1,
    serverFamily: ServerFamily::TeamSpeak6,
);
```

On `ServerFamily::TeamSpeak3` (the default), unknown command names are rejected locally. On `ServerFamily::TeamSpeak6`, names in the 6.0.0-beta12.1 catalog get WebQuery/option checks from that version’s Query help. Names **not** in that catalog are still sent (a newer beta may add commands before the catalog is regenerated).

Nine commands exist only on 6.0.0-beta12.1. See [TeamSpeak 6-only commands](../reference/teamspeak6-commands.md).

WebQuery still does not support `ft*`, `help`, `login`, `logout`, `quit`, `servernotifyregister`, `servernotifyunregister`, or `use` (same list in TeamSpeak 3.13.8 and 6.0.0-beta12.1 WebQuery docs).
