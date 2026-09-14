<?php

declare(strict_types=1);

namespace TS3ServerBot\Config;

/**
 * Supported Query transports. Raw/telnet Query is intentionally absent.
 *
 * Used for TeamSpeak 3.13.8 and TeamSpeak 6 Query.
 *
 * @package TS3ServerBot
 */
enum Transport: string
{
    case Ssh = 'ssh';
    case WebQuery = 'webquery';
}
