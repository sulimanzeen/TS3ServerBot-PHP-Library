<?php

declare(strict_types=1);

namespace TS3ServerBot\Exception;

/**
 * Thrown when the active transport fails independently of TeamSpeak command errors.
 *
 * @package TS3ServerBot
 */
final class TransportException extends TeamSpeakException
{
}
