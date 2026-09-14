<?php

declare(strict_types=1);

namespace TS3ServerBot\Protocol;

/**
 * Encodes and decodes ServerQuery escape sequences documented for TeamSpeak 3.13.8.
 *
 * @package TS3ServerBot
 */
final class QueryEscaper
{
    /**
     * @var array<string, string>
     */
    private const ENCODE = [
        '\\' => '\\\\',
        '/' => '\\/',
        ' ' => '\\s',
        '|' => '\\p',
        "\x07" => '\\a',
        "\x08" => '\\b',
        "\x0c" => '\\f',
        "\n" => '\\n',
        "\r" => '\\r',
        "\t" => '\\t',
        "\v" => '\\v',
    ];

    /**
     * @var array<string, string>
     */
    private const DECODE = [
        '\\\\' => '\\',
        '\\/' => '/',
        '\\s' => ' ',
        '\\p' => '|',
        '\\a' => "\x07",
        '\\b' => "\x08",
        '\\f' => "\x0c",
        '\\n' => "\n",
        '\\r' => "\r",
        '\\t' => "\t",
        '\\v' => "\v",
    ];

    /**
     * Encodes a PHP string using the documented ServerQuery escape table.
     */
    public static function encode(string $value): string
    {
        return strtr($value, self::ENCODE);
    }

    /**
     * Decodes ServerQuery escape sequences in a value.
     */
    public static function decode(string $value): string
    {
        $result = '';
        $length = strlen($value);

        for ($i = 0; $i < $length; $i++) {
            if ($value[$i] !== '\\' || $i + 1 >= $length) {
                $result .= $value[$i];
                continue;
            }

            $pair = $value[$i] . $value[$i + 1];
            if (isset(self::DECODE[$pair])) {
                $result .= self::DECODE[$pair];
                $i++;
                continue;
            }

            $result .= $value[$i];
        }

        return $result;
    }
}
