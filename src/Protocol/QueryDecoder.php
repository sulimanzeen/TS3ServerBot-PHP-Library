<?php

declare(strict_types=1);

namespace TS3ServerBot\Protocol;

use TS3ServerBot\Domain\QueryNode;
use TS3ServerBot\Exception\ProtocolException;

/**
 * Parses ServerQuery text responses documented for TeamSpeak 3.13.8.
 *
 * @package TS3ServerBot
 */
final class QueryDecoder
{
    /**
     * Parses a ServerQuery payload into items and an error line.
     */
    public function decode(string $payload): QueryResponse
    {
        $payload = str_replace(["\r\n", "\r"], "\n", $payload);
        $lines = array_values(array_filter(explode("\n", $payload), static fn (string $line): bool => $line !== ''));

        if ($lines === []) {
            throw new ProtocolException('Empty ServerQuery response.');
        }

        $errorLine = null;
        $bodyLines = [];
        $notifications = [];

        foreach ($lines as $line) {
            $line = self::stripPromptPrefix($line);
            if ($line === '') {
                continue;
            }

            if (str_starts_with($line, 'error ')) {
                $errorLine = $line;
                continue;
            }

            if (str_starts_with($line, 'notify')) {
                $notifications[] = $line;
                continue;
            }

            $bodyLines[] = $line;
        }

        if ($errorLine === null) {
            throw new ProtocolException('ServerQuery response did not end with an error line.');
        }

        $error = $this->parsePairs(substr($errorLine, strlen('error ')));
        $errorId = isset($error['id']) ? (int) $error['id'] : -1;
        $errorMessage = $error['msg'] ?? '';
        unset($error['id'], $error['msg']);

        $items = [];
        foreach ($bodyLines as $bodyLine) {
            foreach ($this->splitUnescaped($bodyLine, '|') as $item) {
                if ($item === '') {
                    continue;
                }
                $items[] = new QueryNode($this->parsePairs($item));
            }
        }

        return new QueryResponse($errorId, $errorMessage, $items, $error);
    }

    /**
     * Parses `key=value` tokens from one Query line.
     *
     * @return array<string, string>
     */
    public function parsePairs(string $line): array
    {
        $pairs = [];
        foreach ($this->splitUnescaped(trim($line), ' ') as $token) {
            if ($token === '') {
                continue;
            }

            $equals = strpos($token, '=');
            if ($equals === false) {
                $pairs[QueryEscaper::decode($token)] = '';
                continue;
            }

            $key = QueryEscaper::decode(substr($token, 0, $equals));
            $value = QueryEscaper::decode(substr($token, $equals + 1));
            $pairs[$key] = $value;
        }

        return $pairs;
    }

    /**
     * Splits a line on an unescaped delimiter (`|` or space).
     *
     * @return list<string>
     */
    public function splitUnescaped(string $input, string $delimiter): array
    {
        $parts = [];
        $current = '';
        $length = strlen($input);

        for ($i = 0; $i < $length; $i++) {
            if ($input[$i] === '\\' && $i + 1 < $length) {
                $current .= $input[$i] . $input[$i + 1];
                $i++;
                continue;
            }

            if ($input[$i] === $delimiter) {
                $parts[] = $current;
                $current = '';
                continue;
            }

            $current .= $input[$i];
        }

        $parts[] = $current;

        return $parts;
    }

    /**
     * Removes a ServerQuery prompt (`name@port(sid):status>`) that may prefix a payload line.
     */
    public static function stripPromptPrefix(string $line): string
    {
        $line = preg_replace('/\x1b\[[0-9;?]*[ -\/]*[@-~]/', '', $line) ?? $line;
        $line = str_replace(["\x07", "\r"], '', $line);
        $stripped = preg_replace(
            '/^[^\s]+@\d+\(\d+\):(online|offline|virtual)>/i',
            '',
            $line
        );

        return is_string($stripped) ? trim($stripped) : trim($line);
    }
}
