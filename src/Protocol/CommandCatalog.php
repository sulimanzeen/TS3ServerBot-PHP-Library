<?php

declare(strict_types=1);

namespace TS3ServerBot\Protocol;

use TS3ServerBot\Config\ServerFamily;
use TS3ServerBot\Exception\ProtocolException;
use TS3ServerBot\Exception\UnsupportedOperationException;

/**
 * Catalog of ServerQuery commands for TeamSpeak 3.13.8 and 6.0.0-beta12.1.
 *
 * TeamSpeak 3 uses command-catalog.json (3.13.8). TeamSpeak 6 uses command-catalog-ts6.json
 * (6.0.0-beta12.1). Names missing from the active catalog are not invented locally.
 *
 * @package TS3ServerBot
 */
final class CommandCatalog
{
    /**
     * @var array<string, CommandDefinition>
     */
    private array $commands;

    /**
     * Loads the catalog that matches a TeamSpeak server family.
     */
    public static function forFamily(ServerFamily $family): self
    {
        $path = $family === ServerFamily::TeamSpeak6
            ? __DIR__ . '/command-catalog-ts6.json'
            : __DIR__ . '/command-catalog.json';

        return new self($path, $family);
    }

    /**
     * @param string|null $catalogPath Path to a catalog JSON file. Defaults to the TeamSpeak 3.13.8 catalog.
     * @param ServerFamily $family Used in error messages and {@see family()}.
     */
    public function __construct(
        ?string $catalogPath = null,
        private readonly ServerFamily $family = ServerFamily::TeamSpeak3,
    ) {
        $path = $catalogPath ?? __DIR__ . '/command-catalog.json';
        if (!is_readable($path)) {
            throw new ProtocolException('Command catalog is missing.');
        }

        $json = (string) file_get_contents($path);
        $json = preg_replace('/^\xEF\xBB\xBF/', '', $json) ?? $json;
        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new ProtocolException('Command catalog JSON is invalid.');
        }

        $this->commands = [];
        foreach ($decoded as $row) {
            if (!is_array($row) || !isset($row['name'])) {
                continue;
            }

            $name = (string) $row['name'];
            $this->commands[$name] = new CommandDefinition(
                $name,
                (string) ($row['usage'] ?? ''),
                array_values(array_map('strval', $row['permissions'] ?? [])),
                array_values(array_map('strval', $row['parameters'] ?? [])),
                array_values(array_map('strval', $row['options'] ?? [])),
                (bool) ($row['webQuery'] ?? false),
                (bool) ($row['sshOnly'] ?? false),
                (string) ($row['category'] ?? 'query'),
                isset($row['aliasOf']) && $row['aliasOf'] !== null ? (string) $row['aliasOf'] : null,
                (string) ($row['source'] ?? ''),
            );
        }

        if (!isset($this->commands['help'])) {
            $this->commands['help'] = new CommandDefinition(
                'help',
                'help [command]',
                [],
                [],
                [],
                false,
                true,
                'session',
                null,
                'serverquerydocs/help.txt'
            );
        }
    }

    /**
     * Server family this catalog was built for.
     */
    public function family(): ServerFamily
    {
        return $this->family;
    }

    /**
     * Looks up a documented command by name, or null when it is not in this catalog.
     */
    public function find(string $name): ?CommandDefinition
    {
        return $this->commands[$name] ?? null;
    }

    /**
     * Looks up a documented command by name.
     *
     * @throws UnsupportedOperationException
     */
    public function get(string $name): CommandDefinition
    {
        $definition = $this->find($name);
        if ($definition === null) {
            throw new UnsupportedOperationException(
                'Command "' . $name . '" is not in the ' . $this->catalogLabel() . '.'
            );
        }

        return $definition;
    }

    /**
     * Whether the name exists in this catalog.
     */
    public function has(string $name): bool
    {
        return isset($this->commands[$name]);
    }

    /**
     * @return list<CommandDefinition>
     */
    public function all(): array
    {
        return array_values($this->commands);
    }

    private function catalogLabel(): string
    {
        return $this->family === ServerFamily::TeamSpeak6
            ? 'TeamSpeak 6.0.0-beta12.1 catalog'
            : 'TeamSpeak 3.13.8 catalog';
    }
}
