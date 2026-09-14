<?php

declare(strict_types=1);

namespace TS3ServerBot\Exception;

/**
 * Thrown when an operation is documented for one transport but not the active transport,
 * or when the framework intentionally refuses a feature (raw Query, undocumented payloads).
 *
 * @package TS3ServerBot
 */
final class UnsupportedOperationException extends TeamSpeakException
{
}
