# Installation

## System requirements

- PHP 8.1 or newer
- Composer 2
- PHP extensions: `json`, `curl`
- TeamSpeak 3.13.8 or TeamSpeak 6.0.0-beta12.1 with SSH and/or WebQuery enabled

## Composer

From a PHP project:

```bash
composer require ts3serverbot/query
```

From this package directory during development:

```bash
composer install
```

## Configuration

Create a `ClientConfig` in code. Copy `.env.example` to `.env` in this directory, or set the same variables in the environment. Do not commit `.env`.

```php
use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Config\ServerFamily;
use TS3ServerBot\Config\Transport;

$web = new ClientConfig(
    host: getenv('TS_HOST') ?: '127.0.0.1',
    transport: Transport::WebQuery,
    apiKey: getenv('TS_API_KEY') ?: '',
    virtualServerId: 1,
);

$ssh = new ClientConfig(
    host: getenv('TS_HOST') ?: '127.0.0.1',
    transport: Transport::Ssh,
    username: getenv('TS_QUERY_USER') ?: 'serveradmin',
    password: getenv('TS_QUERY_PASSWORD') ?: '',
    virtualServerId: 1,
);

$ts6 = new ClientConfig(
    host: getenv('TS_HOST') ?: '127.0.0.1',
    transport: Transport::WebQuery,
    apiKey: getenv('TS_API_KEY') ?: '',
    virtualServerId: 1,
    serverFamily: ServerFamily::TeamSpeak6,
);
```

There is no raw Query port setting.

## Local documentation site

Open `docs-site/index.html` in a browser. Optional:

```bash
php bin/docs-serve.php
```

Then http://localhost:8000
