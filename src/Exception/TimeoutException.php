<?php

declare(strict_types=1);

namespace TS3ServerBot\Exception;

/**
 * Thrown when a connection or command exceeds the configured timeout.
 *
 * @package TS3ServerBot
 */
final class TimeoutException extends ConnectionException
{
}
