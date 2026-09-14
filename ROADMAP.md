# Roadmap

Milestones for the TS3 PHP Framework. Completion criteria are listed for each item.

## M0 — Command inventory

**Objective:** Record every TeamSpeak 3.13.8 ServerQuery command from that version’s Query help.

**Tasks:** Parse usage, permissions, parameters, options, aliases, and WebQuery vs SSH support.

**Deliverables:** `docs/reference/commands.md`, `docs/reference/commands.json`, `src/Protocol/command-catalog.json`.

**Tests:** Catalog unit tests reject unknown commands and mark SSH-only commands.

**Docs:** Command matrix published on the local docs site.

**Risks:** Stale HTML manuals; catalogs must follow Query help for 3.13.8 and 6.0.0-beta12.1, not HTML ServerQuery manuals.

**Completion:** 139 documented commands plus `help` are listed. Raw mode is not a catalog transport.

**Status:** Complete.

## M1 — Skeleton

**Objective:** Installable Composer library with namespaces, exceptions, config, and docs home page.

**Deliverables:** `composer.json` (`ts3serverbot/query`), `src/`, `LICENSE`, `README.md`.

**Completion:** `composer dump-autoload` works on PHP 8.1+. Config has no raw Query port.

**Status:** Complete.

## M2 — Protocol core

**Objective:** Encode/decode ServerQuery text and WebQuery JSON from documented examples.

**Tests:** Escaper, encoder, decoder, WebQuery mapper.

**Completion:** Documented `clientlist` / `clientkick` / WebQuery JSON examples parse.

**Status:** Complete.

## M3 — MVP API

**Objective:** Connect, authenticate, select a server, read version/whoami/serverinfo/clients/channels.

**Examples:** `examples/webquery-list-clients.php`, `examples/ssh-connect.php`.

**Completion:** High-level methods exist and are covered by mocked tests.

**Status:** Complete.

## M4 — Management slice

**Objective:** Kick, move, poke, text message, server edit, groups, bans — all mapped to catalog commands.

**Completion:** `VirtualServer` methods call only documented commands.

**Status:** Complete.

## M5 — SSH session features

**Objective:** SSH lifecycle, `login`/`use`/`logout`/`quit`, API key creation, notification register (payloads unlabeled as undocumented).

**Completion:** WebQuery rejects session-only commands with `UnsupportedOperationException`.

**Status:** Complete.

## M6 — Documentation website

**Objective:** Local site at `http://localhost:8000` with guides, generated API reference, command matrix, troubleshooting.

**Validation:** `php bin/validate-docs.php`.

**Completion:** Site starts, public methods have PHPDoc, raw mode is documented as unsupported only.

**Status:** Complete.

## M7 — Release checklist

**Objective:** Tests, security notes, 3.13.8 compatibility statement, confirmation that raw is absent.

**Completion:** README, CHANGELOG, SECURITY, and tests/Transport/NoRawTransportTest.php.

**Status:** Complete.

## Later (not in 0.1.0)

- File-transfer **data plane** (binary protocol after `ftkey` is not documented)
- Typed notify events (field lists are not in 3.13.8 / 6.0.0-beta12.1 docs)
- Native TeamSpeak HTTPS Query on 10443 (not in 3.13.8 or 6.0.0-beta12.1 quickstart)
- Packagist publication under `ts3serverbot/query`
