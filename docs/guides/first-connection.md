# First connection

## WebQuery

```php
use TS3ServerBot\Client\TeamSpeakClient;
use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Config\Transport;

$client = TeamSpeakClient::webQuery(new ClientConfig(
    host: '127.0.0.1',
    transport: Transport::WebQuery,
    apiKey: 'your-api-key',
    virtualServerId: 1,
));

$client->connect();
$version = $client->version();
$clients = $client->server(1)->getClients();
$client->disconnect();
```

`version()` maps to the documented `version` command. `getClients()` maps to `clientlist`.

If the connection is down, `InvalidStateException` is thrown. If the API key is rejected, `AuthenticationException` is thrown.

## SSH

```php
$client = TeamSpeakClient::ssh(new ClientConfig(
    host: '127.0.0.1',
    transport: Transport::Ssh,
    username: 'serveradmin',
    password: 'your-query-password',
    virtualServerId: 1,
));

$client->connect();
$server = $client->server(1);
$info = $server->getInfo();
$client->disconnect();
```

`connect()` opens SSH without a PTY, authenticates with the Query username and password, waits for the ServerQuery banner, and selects the configured virtual server. That SSH handshake **is** Query login. Do not call `$client->login()` unless you need to switch Query identity (TeamSpeak then deselects the virtual server, so call `useServer()` again).

`$client->login()` remains available for that identity switch. WebQuery never uses it; the API key is the credential.

## TeamSpeak 6

Use the same WebQuery or SSH client. Set `serverFamily: ServerFamily::TeamSpeak6` to use the TeamSpeak 6.0.0-beta12.1 catalog.

```php
use TS3ServerBot\Client\TeamSpeakClient;
use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Config\ServerFamily;
use TS3ServerBot\Config\Transport;

$client = TeamSpeakClient::webQuery(new ClientConfig(
    host: '127.0.0.1',
    transport: Transport::WebQuery,
    apiKey: 'your-api-key',
    virtualServerId: 1,
    serverFamily: ServerFamily::TeamSpeak6,
));
```

High-level methods such as `getClients()` and `getChannels()` stay the same. Commands that exist only on TeamSpeak 6.0.0-beta12.1 are in that version’s catalog; see [TeamSpeak 6-only commands](../reference/teamspeak6-commands.md).
