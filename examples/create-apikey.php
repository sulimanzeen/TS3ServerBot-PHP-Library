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
);

$client = TS3ServerBot\Client\TeamSpeakClient::ssh($config);
$client->connect();

$key = $client->createApiKey('manage', 0);
echo 'Created API key id=' . ($key->get('id') ?? '') . PHP_EOL;
echo 'Store the apikey value securely. It is not printed by this example.' . PHP_EOL;

$client->disconnect();
