<?php

/**
 * Example local config. Copy to client.php (gitignored) and fill in values.
 * Never commit real credentials.
 */

use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Config\Transport;

return new ClientConfig(
    host: '127.0.0.1',
    transport: Transport::WebQuery,
    apiKey: 'your-api-key',
    virtualServerId: 1,
);
