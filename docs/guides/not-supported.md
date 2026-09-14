# Not supported

## Raw / telnet ServerQuery

TeamSpeak 3.13.8 can still listen on port **10011** (`query_port`) for unencrypted Query. This framework **will not connect there**.

Reasons:

- Unencrypted
- The 3.13.8 quickstart already recommends more secure protocols
- Project policy: raw mode will not be implemented, including as a fallback or test transport

If the server only has `query_protocols=raw`, this library cannot talk to it. Enable SSH and/or HTTP:

```text
query_protocols=ssh,http
```

## Other unsupported items

- TeamSpeak versions other than 3.13.8 and TeamSpeak 6.0.0-beta12.1 Query
- Voice / UDP client protocol
- TSDNS as a management API
- OS-level SSH shells
- Invented HTTP endpoints
- File-transfer binary protocol after `ftkey`
- Documented-as-available notify field schemas (they are not in the 3.13.8 or 6.0.0-beta12.1 Query help)
- SSH public-key login (not documented for Query)
