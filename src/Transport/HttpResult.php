<?php

declare(strict_types=1);

namespace TS3ServerBot\Transport;

/**
 * Result of a WebQuery HTTP call.
 *
 * @package TS3ServerBot
 */
final class HttpResult
{
    public function __construct(
        public readonly int $statusCode,
        public readonly string $body,
    ) {
    }
}
