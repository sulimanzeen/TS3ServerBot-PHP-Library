<?php

declare(strict_types=1);

namespace TS3ServerBot\Protocol;

use TS3ServerBot\Domain\QueryNode;
use TS3ServerBot\Exception\AuthenticationException;
use TS3ServerBot\Exception\PermissionException;
use TS3ServerBot\Exception\ServerException;
use TS3ServerBot\Exception\UnexpectedResponseException;

/**
 * Parsed ServerQuery or WebQuery response.
 *
 * Error id 1281 (empty database result) is treated as success with no items, as documented for WebQuery clients.
 *
 * @package TS3ServerBot
 */
final class QueryResponse
{
    public const ERROR_OK = 0;

    public const ERROR_DATABASE_EMPTY_RESULT = 1281;

    /**
     * @param list<QueryNode> $items
     * @param array<string, string> $errorExtra
     */
    public function __construct(
        public readonly int $errorId,
        public readonly string $errorMessage,
        public readonly array $items = [],
        public readonly array $errorExtra = [],
    ) {
    }

    /**
     * True when the TeamSpeak error id is 0 or the documented empty-result code 1281.
     */
    public function isOk(): bool
    {
        return $this->errorId === self::ERROR_OK || $this->errorId === self::ERROR_DATABASE_EMPTY_RESULT;
    }

    /**
     * @throws AuthenticationException
     * @throws PermissionException
     * @throws ServerException
     */
    public function throwIfError(): void
    {
        if ($this->isOk()) {
            return;
        }

        $message = $this->errorMessage !== '' ? $this->errorMessage : 'TeamSpeak server returned an error.';

        if ($this->looksLikePermissionError()) {
            throw new PermissionException($message, $this->errorId, $this->errorExtra, $this->errorId);
        }

        if ($this->looksLikeAuthenticationError()) {
            throw new AuthenticationException($message, $this->errorId);
        }

        throw new ServerException($message, $this->errorId, $this->errorExtra, $this->errorId);
    }

    /**
     * First result row, or null when the body is empty.
     */
    public function first(): ?QueryNode
    {
        return $this->items[0] ?? null;
    }

    /**
     * First result row, or UnexpectedResponseException when the body is empty.
     */
    public function requireFirst(): QueryNode
    {
        $first = $this->first();
        if ($first === null) {
            throw new UnexpectedResponseException('The server returned no items.');
        }

        return $first;
    }

    /**
     * First result row that has a non-empty value for the given key.
     */
    public function firstHaving(string $key): ?QueryNode
    {
        foreach ($this->items as $item) {
            $value = $item->get($key);
            if ($value !== null && $value !== '') {
                return $item;
            }
        }

        return null;
    }

    private function looksLikePermissionError(): bool
    {
        return isset($this->errorExtra['failed_permid'])
            || str_contains(strtolower($this->errorMessage), 'permission');
    }

    private function looksLikeAuthenticationError(): bool
    {
        $message = strtolower($this->errorMessage);

        return str_contains($message, 'invalid login')
            || str_contains($message, 'invalid password')
            || str_contains($message, 'not logged in');
    }
}
