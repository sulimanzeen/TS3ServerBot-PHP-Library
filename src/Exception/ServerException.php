<?php

declare(strict_types=1);

namespace TS3ServerBot\Exception;

/**
 * Thrown when the TeamSpeak server returns a non-success error id / WebQuery status code.
 *
 * @package TS3ServerBot
 */
class ServerException extends TeamSpeakException
{
    /**
     * @param array<string, string> $extra Extra error fields from the Query error line (never includes credentials).
     */
    public function __construct(
        string $message,
        private readonly int $serverErrorId,
        private readonly array $extra = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * TeamSpeak error id from `error id=` or WebQuery `status.code`.
     */
    public function getServerErrorId(): int
    {
        return $this->serverErrorId;
    }

    /**
     * @return array<string, string>
     */
    public function getExtra(): array
    {
        return $this->extra;
    }
}
