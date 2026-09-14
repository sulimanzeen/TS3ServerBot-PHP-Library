<?php

declare(strict_types=1);

namespace TS3ServerBot\Exception;

/**
 * Thrown when a transport cannot establish or keep a connection to the TeamSpeak 3 server.
 *
 * @package TS3ServerBot
 */
class ConnectionException extends TeamSpeakException
{
}
