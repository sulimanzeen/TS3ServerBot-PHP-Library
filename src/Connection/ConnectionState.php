<?php

declare(strict_types=1);

namespace TS3ServerBot\Connection;

/**
 * Connection lifecycle for the public client.
 *
 * @package TS3ServerBot
 */
enum ConnectionState: string
{
    case Disconnected = 'disconnected';
    case Connected = 'connected';
    case Authenticated = 'authenticated';
}
