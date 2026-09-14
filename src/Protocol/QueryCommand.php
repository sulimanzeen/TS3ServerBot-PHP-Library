<?php

declare(strict_types=1);

namespace TS3ServerBot\Protocol;

use TS3ServerBot\Exception\InvalidParameterException;

/**
 * Structured ServerQuery command. Callers pass a Query command name and PHP values, never a raw Query string.
 *
 * @package TS3ServerBot
 */
final class QueryCommand
{
    /**
     * @param array<string, scalar|list<scalar>> $parameters
     * @param list<string> $options Option names without the leading minus, e.g. "uid".
     *
     * @throws InvalidParameterException
     */
    public function __construct(
        public readonly string $name,
        public readonly array $parameters = [],
        public readonly array $options = [],
        public readonly ?int $virtualServerId = null,
        public readonly ?int $virtualServerPort = null,
    ) {
        if ($this->name === '' || !preg_match('/^[a-z][a-z0-9_]*$/', $this->name)) {
            throw new InvalidParameterException('Command name is not a ServerQuery identifier.');
        }
    }
}
