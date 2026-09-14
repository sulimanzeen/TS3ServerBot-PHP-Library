# Configuration reference

| Option | Type | Default | Notes |
|---|---|---|---|
| host | string | required | Hostname or IP |
| transport | `Transport::Ssh` or `Transport::WebQuery` | required | No raw option |
| sshPort | int | 10022 | SSH Query |
| webQueryPort | int | 10080 | WebQuery HTTP |
| webQueryTls | bool | false | Use `https://` (reverse proxy) |
| timeoutSeconds | float | 10 | Must be > 0 |
| username | string or null | null | Required for SSH |
| password | string or null | null | Required for SSH; redacted |
| apiKey | string or null | null | Required for WebQuery; redacted |
| nickname | string or null | null | `use` client_nickname |
| virtualServerId | int or null | null | WebQuery path and SSH `use` |
| virtualServerPort | int or null | null | `/byport/` or `use port=` |
| verifyTls | bool | true | HTTPS certificate check |
| sshHostFingerprint | string or null | null | SHA256 of SSH host key |
| serverFamily | `ServerFamily::TeamSpeak3` or `ServerFamily::TeamSpeak6` | TeamSpeak3 | TS3 uses the 3.13.8 catalog; TS6 uses 6.0.0-beta12.1. Unknown TS6 names are still sent |

There is **no** `queryPort` / raw Query setting.
