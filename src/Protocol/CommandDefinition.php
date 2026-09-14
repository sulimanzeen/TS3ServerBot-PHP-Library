<?php

declare(strict_types=1);

namespace TS3ServerBot\Protocol;

/**
 * Metadata for one documented ServerQuery command.
 *
 * @package TS3ServerBot
 */
final class CommandDefinition
{
    /**
     * @param list<string> $permissions
     * @param list<string> $parameters
     * @param list<string> $options
     */
    public function __construct(
        public readonly string $name,
        public readonly string $usage,
        public readonly array $permissions,
        public readonly array $parameters,
        public readonly array $options,
        public readonly bool $webQuery,
        public readonly bool $sshOnly,
        public readonly string $category,
        public readonly ?string $aliasOf,
        public readonly string $source,
    ) {
    }
}
