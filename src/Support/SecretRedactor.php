<?php

declare(strict_types=1);

namespace TS3ServerBot\Support;

/**
 * Removes secrets from arrays and strings before logging or debug output.
 *
 * @package TS3ServerBot
 */
final class SecretRedactor
{
    private const SECRET_KEYS = [
        'password',
        'client_login_password',
        'apikey',
        'api-key',
        'apiKey',
        'x-api-key',
        'token',
        'pw',
        'cpw',
        'tcpw',
        'ftkey',
        'salt',
    ];

    /**
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    public static function redactArray(array $values): array
    {
        $redacted = [];
        foreach ($values as $key => $value) {
            if (self::isSecretKey((string) $key)) {
                $redacted[$key] = $value === null || $value === '' ? $value : '***';
                continue;
            }

            if (is_array($value)) {
                /** @var array<string, mixed> $value */
                $redacted[$key] = self::redactArray($value);
                continue;
            }

            $redacted[$key] = $value;
        }

        return $redacted;
    }

    /**
     * Redacts known secret patterns from a log or debug string.
     */
    public static function redactText(string $text): string
    {
        $patterns = [
            '/(api-key=)([^&\s]+)/i',
            '/(x-api-key:\s*)(\S+)/i',
            '/(client_login_password=)(\S+)/i',
            '/(password=)(\S+)/i',
            '/(apikey=)(\S+)/i',
            '/(ftkey=)(\S+)/i',
            '/(token=)(\S+)/i',
        ];

        foreach ($patterns as $pattern) {
            $text = preg_replace($pattern, '$1***', $text) ?? $text;
        }

        return $text;
    }

    private static function isSecretKey(string $key): bool
    {
        $normalized = strtolower($key);

        foreach (self::SECRET_KEYS as $secret) {
            if ($normalized === strtolower($secret)) {
                return true;
            }
        }

        return false;
    }
}
