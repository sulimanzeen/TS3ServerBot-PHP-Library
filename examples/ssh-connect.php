<?php

declare(strict_types=1);

use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Config\Transport;

require dirname(__DIR__) . '/vendor/autoload.php';

$config = new ClientConfig(
    host: '127.0.0.1',
    transport: Transport::Ssh,
    username: 'serveradmin',
    password: 'your-query-password',
    nickname: 'TS3ServerBot',
    virtualServerId: 1,
);

$client = TS3ServerBot\Client\TeamSpeakClient::ssh($config);
$client->connect();

echo 'Who am I: ' . ($client->whoami()->get('client_nickname') ?? '') . PHP_EOL;
echo 'Version: ' . ($client->version()->get('version') ?? '') . PHP_EOL;
echo 'Clients: ' . count($client->server(1)->getClients()) . PHP_EOL;

$client->disconnect();
