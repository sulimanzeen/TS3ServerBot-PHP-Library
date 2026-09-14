<?php

declare(strict_types=1);

use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Config\Transport;

require dirname(__DIR__) . '/vendor/autoload.php';

$config = new ClientConfig(
    host: '127.0.0.1',
    transport: Transport::WebQuery,
    apiKey: 'your-api-key',
    virtualServerId: 1,
);

$client = TS3ServerBot\Client\TeamSpeakClient::webQuery($config);
$client->connect();

$server = $client->server(1);
$info = $server->getInfo();
$clients = $server->getClients(['uid']);
$channels = $server->getChannels(['topic']);

echo 'Server name: ' . ($info->get('virtualserver_name') ?? '') . PHP_EOL;
echo 'Online clients: ' . count($clients) . PHP_EOL;
echo 'Channels: ' . count($channels) . PHP_EOL;

$client->disconnect();
