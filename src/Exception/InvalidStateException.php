<?php

declare(strict_types=1);

namespace TS3ServerBot\Exception;

/**
 * Thrown when an operation is requested while the client is disconnected or otherwise unusable.
 *
 * @package TS3ServerBot
 */
final class InvalidStateException extends TeamSpeakException
{
}
