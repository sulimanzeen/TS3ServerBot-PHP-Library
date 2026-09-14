# Architecture

```text
Developer app
    -> TS3ServerBot\Client\TeamSpeakClient
        -> Domain (Instance, VirtualServer, QueryNode)
        -> Protocol (catalog, encoder, decoder, WebQuery mapper)
        -> Transport (SshQueryTransport | WebQueryTransport)
            -> TeamSpeak 3.13.8 or TeamSpeak 6.0.0-beta12.1 Query
```

## Layers

- **Client:** connect, login, useServer, execute, disconnect
- **Domain:** documented entities and operations
- **Protocol:** escape/encode/parse; never a place for business rules
- **Transport:** SSH or WebQuery only
- **Config:** host, ports 10022/10080, credentials, timeouts, `ServerFamily` (TeamSpeak 3 or TeamSpeak 6)
- **Exception:** typed failures

There is no `RawQueryTransport`.

## Catalog

`src/Protocol/command-catalog.json` is the TeamSpeak **3.13.8** Query command list. `src/Protocol/command-catalog-ts6.json` is the TeamSpeak **6.0.0-beta12.1** list. On TeamSpeak 3, unknown command names are rejected and WebQuery-incompatible names are rejected on the WebQuery transport. On TeamSpeak 6, the 6.0.0-beta12.1 catalog is used the same way; names missing from that catalog are still sent.

## Versioning

Semantic versioning. 0.x may change the public API. 1.0.0 will start the compatibility promise. Query support covers TeamSpeak 3.13.8 and TeamSpeak 6.0.0-beta12.1.
