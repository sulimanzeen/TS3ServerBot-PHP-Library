<?php

declare(strict_types=1);

namespace TS3ServerBot\Protocol;

use TS3ServerBot\Domain\QueryNode;
use TS3ServerBot\Exception\ProtocolException;

/**
 * Maps structured Query commands to WebQuery HTTP JSON as documented in doc/webquery.md.
 *
 * @package TS3ServerBot
 */
final class WebQueryMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toBody(QueryCommand $command): array
    {
        $body = [];

        foreach ($command->parameters as $key => $value) {
            $body[$key] = $this->normalizeValue($value);
        }

        foreach ($command->options as $option) {
            $body['-' . ltrim($option, '-')] = '';
        }

        return $body;
    }

    /**
     * Builds the WebQuery URL path, including `/{id}/` or `/byport/{port}/` when a server is selected.
     */
    public function path(QueryCommand $command): string
    {
        if ($command->virtualServerPort !== null) {
            return '/byport/' . $command->virtualServerPort . '/' . $command->name;
        }

        if ($command->virtualServerId !== null) {
            return '/' . $command->virtualServerId . '/' . $command->name;
        }

        return '/' . $command->name;
    }

    /**
     * @param array<string, mixed> $json
     */
    public function fromJson(array $json): QueryResponse
    {
        if (!isset($json['status']) || !is_array($json['status'])) {
            throw new ProtocolException('WebQuery response is missing a status object.');
        }

        $status = $json['status'];
        $errorId = isset($status['code']) ? (int) $status['code'] : -1;
        $errorMessage = isset($status['message']) ? (string) $status['message'] : '';

        $items = [];
        $body = $json['body'] ?? [];
        if (is_array($body)) {
            $isList = $body === [] || array_is_list($body);
            $rows = $isList ? $body : [$body];
            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $properties = [];
                foreach ($row as $key => $value) {
                    $properties[(string) $key] = is_scalar($value) || $value === null ? (string) $value : json_encode($value, JSON_THROW_ON_ERROR);
                }
                $items[] = new QueryNode($properties);
            }
        }

        return new QueryResponse($errorId, $errorMessage, $items);
    }

    private function normalizeValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $item) {
                $normalized[] = $this->scalar($item);
            }

            return $normalized;
        }

        return $this->scalar($value);
    }

    private function scalar(mixed $value): string|int|float
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_int($value) || is_float($value) || is_string($value)) {
            return $value;
        }

        return (string) $value;
    }
}
