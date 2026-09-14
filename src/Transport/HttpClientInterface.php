<?php

declare(strict_types=1);

namespace TS3ServerBot\Transport;

/**
 * HTTP POST used by WebQueryTransport.
 *
 * @package TS3ServerBot
 */
interface HttpClientInterface
{
    /**
     * @param array<string, string> $headers
     */
    public function post(string $url, array $headers, string $body, float $timeout, bool $verifyTls): HttpResult;
}
