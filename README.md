# TS3ServerBot Query

[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/php-%5E8.1-777BB4.svg)](composer.json)

PHP Query client for **TeamSpeak 3** and **TeamSpeak 6** over SSH and WebQuery.

Composer: **`ts3serverbot/query`**. Built under [TS3ServerBot.com](https://ts3serverbot.com) and released as MIT so other PHP projects can share one client.

Supported servers: **TeamSpeak 3.13.8** and **TeamSpeak 6.0.0-beta12.1**. This package does not include TeamSpeak server binaries.

## Features

- High-level PHP API — you do not write ServerQuery command strings
- **SSH ServerQuery** (port `10022`)
- **WebQuery HTTP/JSON** (port `10080`) with `x-api-key`
- Catalogs for TeamSpeak 3.13.8 and TeamSpeak 6.0.0-beta12.1
- Offline developer docs (`docs-site/index.html`)

**Raw/telnet Query (port 10011) is not supported** and will not be added. Enable `query_protocols=ssh,http` on the TeamSpeak server.

## Requirements

- PHP 8.1 or newer
- Composer 2
- `ext-json`, `ext-curl`, and `ext-openssl`
- A TeamSpeak 3.13.8 or TeamSpeak 6.0.0-beta12.1 server with SSH and/or WebQuery enabled

## Install

```bash
composer require ts3serverbot/query
```



Prefer keeping secrets in `.env` in this directory. Copy [`.env.example`](.env.example) to `.env`, then pass values into `ClientConfig` (for example `getenv('TS_API_KEY')`). Never commit `.env`.

## Quick start

```php
use TS3ServerBot\Client\TeamSpeakClient;
use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Config\ServerFamily;
use TS3ServerBot\Config\Transport;

$config = new ClientConfig(
    host: getenv('TS_HOST') ?: '127.0.0.1',
    transport: Transport::WebQuery,
    apiKey: getenv('TS_API_KEY') ?: '',
    virtualServerId: 1,
);

$client = TeamSpeakClient::webQuery($config);
$client->connect();

$server = $client->server(1);
$clients = $server->getClients();
$channels = $server->getChannels();

$client->disconnect();
```

For TeamSpeak 6.0.0-beta12.1, add `serverFamily: ServerFamily::TeamSpeak6`. High-level methods stay the same.

SSH uses the Query username and password for the handshake. That is login; do not call `$client->login()` unless you switch identity.

Never commit real API keys or Query passwords.

## Documentation

- [Compatibility](docs/guides/compatibility.md)
- [Installation](docs/guides/installation.md)
- [First connection](docs/guides/first-connection.md)
- [Contributing](CONTRIBUTING.md)
- [Security](SECURITY.md)
- [Changelog](CHANGELOG.md)

Open [`docs-site/index.html`](docs-site/index.html) in a browser (no PHP server required). After changing guides or PHPDoc:

```bash
php bin/docs-build.php
```

Optional live server:

```bash
php bin/docs-serve.php
```

Then open `http://localhost:8000`.

## Contributors

You do not need to work at TS3ServerBot to help. This client exists so more PHP TeamSpeak 3 and TeamSpeak 6 projects can start from the same Query layer.

Docs, tests, bug reports, examples, and pull requests are all useful. Read [CONTRIBUTING.md](CONTRIBUTING.md) for the full rules, then:

1. Open an issue or start a branch for one focused change.
2. Keep credentials out of git, issues, logs, and examples.
3. Run the checks below before you open a pull request.

```bash
composer install
php bin/docs-build.php
php bin/validate-docs.php
vendor/bin/phpunit
```

Include the TeamSpeak server version (3.13.8 or 6.0.0-beta12.1), PHP version, transport (SSH or WebQuery), and a redacted error.

## Security

Do not file public issues for vulnerabilities that include credentials or exploit details. See [SECURITY.md](SECURITY.md).

## License

This project is open source under the [MIT License](LICENSE).

Copyright (c) 2026 TS3ServerBot.com
