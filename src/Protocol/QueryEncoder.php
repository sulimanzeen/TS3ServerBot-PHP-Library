<?php

declare(strict_types=1);

namespace TS3ServerBot\Protocol;

use TS3ServerBot\Exception\InvalidParameterException;

/**
 * Builds a ServerQuery command line from structured PHP values.
 *
 * @package TS3ServerBot
 */
final class QueryEncoder
{
    /**
     * Returns the ServerQuery command line, including escaped parameters and dash options.
     */
    public function encode(QueryCommand $command): string
    {
        if ($command->name === '' || !preg_match('/^[a-z][a-z0-9_]*$/', $command->name)) {
            throw new InvalidParameterException('Command name is not a documented ServerQuery identifier.');
        }

        $parts = [$command->name];

        foreach ($command->parameters as $key => $value) {
            if (!is_string($key) || $key === '' || !preg_match('/^[a-z][a-z0-9_]*$/', $key)) {
                throw new InvalidParameterException('Parameter name is invalid.');
            }

            if (is_array($value)) {
                $encodedValues = [];
                foreach ($value as $item) {
                    $encodedValues[] = $key . '=' . QueryEscaper::encode($this->stringify($item));
                }
                if ($encodedValues !== []) {
                    $parts[] = implode('|', $encodedValues);
                }
                continue;
            }

            $parts[] = $key . '=' . QueryEscaper::encode($this->stringify($value));
        }

        foreach ($command->options as $option) {
            $option = ltrim($option, '-');
            if ($option === '' || !preg_match('/^[a-z][a-z0-9_]*$/', $option)) {
                throw new InvalidParameterException('Option name is invalid.');
            }
            $parts[] = '-' . $option;
        }

        return implode(' ', $parts);
    }

    private function stringify(mixed $value): string
    {
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_int($value) || is_float($value) || is_string($value)) {
            return (string) $value;
        }

        throw new InvalidParameterException('Parameter values must be scalar.');
    }
}
