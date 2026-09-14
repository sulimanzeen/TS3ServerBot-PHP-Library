# Contributing

Thank you for contributing to TS3ServerBot Query (TS3ServerBot.com).

## Who can contribute

Anyone in the PHP or TeamSpeak community. You do not need to be part of TS3ServerBot. Useful work includes:

- Bug reports and reproductions (redact secrets)
- Documentation and example fixes
- Tests
- TeamSpeak 6 catalog / API updates that match server Query help
- Pull requests that keep the public API consistent

## How to proceed

1. Open an issue to discuss larger changes, or go straight to a small focused pull request.
2. Fork or clone the repository. Create a branch named after the change.
3. Put local credentials in `.env` in this directory (copy `.env.example`). Never commit `.env`.
4. Make your change.
5. Run the checks below.
6. Open a pull request: what changed, why, TeamSpeak version, PHP version, and transport.

## Rules

1. Support Query over SSH and WebQuery for TeamSpeak 3.13.8 and TeamSpeak 6.0.0-beta12.1.
2. Treat the JSON catalogs in `src/Protocol/` as the verified Query command lists for TeamSpeak **3.13.8** and **6.0.0-beta12.1**. Do not invent commands, ports, or HTTP paths. Do not ship TeamSpeak server binaries with this package.
3. Do not invent TeamSpeak commands, parameters, or HTTP endpoints.
4. Do not add raw/telnet Query (port 10011).
5. Do not document unimplemented features as available.
6. Keep credentials out of git, logs, examples, and exception messages.

## Setup

```bash
composer install
php bin/docs-build.php
php bin/validate-docs.php
vendor/bin/phpunit
```

PHP 8.1+ is required.

## Pull requests

- Keep the public API consistent.
- Add tests for protocol and behavior changes.
- Update `docs/` and PHPDoc when you change public methods, then run `php bin/docs-build.php`.
- Run the documentation validator.
- One focused change per pull request.

## Issues

Include the TeamSpeak server version (3.13.8 or 6.0.0-beta12.1), PHP version, transport (SSH or WebQuery), `ServerFamily`, and a redacted error. Never paste API keys or Query passwords.
