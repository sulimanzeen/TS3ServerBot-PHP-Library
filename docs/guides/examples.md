# Examples

Runnable PHP files live in `/examples`. They use placeholder credentials only.

- `examples/webquery-list-clients.php` — connect with WebQuery, list clients and channels
- `examples/ssh-connect.php` — SSH connect (handshake is login), whoami, version
- `examples/error-handling.php` — typed exceptions
- `examples/create-apikey.php` — `apikeyadd` over SSH (prints id only)
- `examples/teamspeak6-webquery.php` — WebQuery against TeamSpeak 6.0.0-beta12.1
- `examples/teamspeak6-ssh.php` — SSH Query against TeamSpeak 6.0.0-beta12.1

Replace placeholders, or copy `.env.example` to `.env` in this package and use `getenv()`. Never commit real secrets.
