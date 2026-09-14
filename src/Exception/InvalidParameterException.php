<?php

declare(strict_types=1);

namespace TS3ServerBot\Exception;

/**
 * Thrown when a caller supplies a parameter that the command catalog or protocol cannot accept.
 *
 * @package TS3ServerBot
 */
final class InvalidParameterException extends TeamSpeakException
{
}
