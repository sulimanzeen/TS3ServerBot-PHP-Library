<?php

declare(strict_types=1);

use TS3ServerBot\Client\TeamSpeakClient;
use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Config\ServerFamily;
use TS3ServerBot\Config\Transport;

require dirname(__DIR__) . '/vendor/autoload.php';

$config = new ClientConfig(
    host: '127.0.0.1',
    transport: Transport::WebQuery,
    apiKey: 'your-api-key',
    virtualServerId: 1,
    serverFamily: ServerFamily::TeamSpeak6,
);

$client = TeamSpeakClient::webQuery($config);
$client->connect();

$server = $client->server(1);
echo 'Clients: ' . count($server->getClients()) . PHP_EOL;
echo 'Channels: ' . count($server->getChannels()) . PHP_EOL;

$client->disconnect();
