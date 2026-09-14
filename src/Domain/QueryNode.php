<?php

declare(strict_types=1);

namespace TS3ServerBot\Domain;

/**
 * One decoded ServerQuery item (a key/value row from a list or info command).
 *
 * @package TS3ServerBot
 */
final class QueryNode
{
    /**
     * @param array<string, string> $properties
     */
    public function __construct(private readonly array $properties)
    {
    }

    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return $this->properties;
    }

    /**
     * Returns a decoded string property, or the default when the key is absent.
     */
    public function get(string $key, ?string $default = null): ?string
    {
        return $this->properties[$key] ?? $default;
    }

    /**
     * Returns a property cast to int, or the default when the key is empty.
     */
    public function getInt(string $key, ?int $default = null): ?int
    {
        if (!isset($this->properties[$key]) || $this->properties[$key] === '') {
            return $default;
        }

        return (int) $this->properties[$key];
    }

    /**
     * Whether the item contains the given property key.
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->properties);
    }
}
