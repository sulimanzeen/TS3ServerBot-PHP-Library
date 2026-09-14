<?php

declare(strict_types=1);

use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Config\Transport;
use TS3ServerBot\Exception\AuthenticationException;
use TS3ServerBot\Exception\PermissionException;
use TS3ServerBot\Exception\ServerException;
use TS3ServerBot\Exception\TeamSpeakException;

require dirname(__DIR__) . '/vendor/autoload.php';

$config = new ClientConfig(
    host: '127.0.0.1',
    transport: Transport::WebQuery,
    apiKey: 'your-api-key',
    virtualServerId: 1,
);

$client = TS3ServerBot\Client\TeamSpeakClient::webQuery($config);

try {
    $client->connect();
    $client->server(1)->kick([1], 5, 'example');
} catch (AuthenticationException $exception) {
    fwrite(STDERR, "Authentication failed.\n");
} catch (PermissionException $exception) {
    fwrite(STDERR, "Missing permission. Server error id: {$exception->getServerErrorId()}\n");
} catch (ServerException $exception) {
    fwrite(STDERR, "Server error {$exception->getServerErrorId()}: {$exception->getMessage()}\n");
} catch (TeamSpeakException $exception) {
    fwrite(STDERR, "Framework error.\n");
} finally {
    $client->disconnect();
}
