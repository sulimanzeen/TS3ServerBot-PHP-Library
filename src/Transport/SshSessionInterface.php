<?php

declare(strict_types=1);

namespace TS3ServerBot\Transport;

/**
 * Minimal SSH session used by SshQueryTransport. The default implementation uses phpseclib and does not allocate a PTY.
 *
 * @package TS3ServerBot
 */
interface SshSessionInterface
{
    /**
     * Authenticates to the TeamSpeak SSH Query listener without allocating a PTY.
     */
    public function authenticate(
        string $host,
        int $port,
        string $username,
        string $password,
        float $timeout,
        ?string $hostFingerprint
    ): void;

    /**
     * Writes bytes to the SSH channel.
     */
    public function write(string $data): void;

    /**
     * Reads one line from the SSH channel or null at EOF.
     */
    public function readLine(float $timeout): ?string;

    /**
     * Closes the SSH channel.
     */
    public function disconnect(): void;
}
