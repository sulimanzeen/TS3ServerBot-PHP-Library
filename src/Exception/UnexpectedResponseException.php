<?php

declare(strict_types=1);

namespace TS3ServerBot\Exception;

/**
 * Thrown when the server returns a payload that does not match the documented response shape.
 *
 * @package TS3ServerBot
 */
final class UnexpectedResponseException extends ProtocolException
{
}
