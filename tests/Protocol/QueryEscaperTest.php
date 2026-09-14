<?php

declare(strict_types=1);

namespace TS3ServerBot\Tests\Protocol;

use PHPUnit\Framework\TestCase;
use TS3ServerBot\Protocol\QueryEscaper;

final class QueryEscaperTest extends TestCase
{
    public function testEncodesDocumentedSpecialCharacters(): void
    {
        $input = "TeamSpeak ]|[ Server";
        $encoded = QueryEscaper::encode($input);

        self::assertSame('TeamSpeak\\s]\\p[\\sServer', $encoded);
        self::assertSame($input, QueryEscaper::decode($encoded));
    }

    public function testRoundTripsSlashAndBackslash(): void
    {
        $input = 'P5H2hrN6+gpQI4n/dXp3p17vtY0=';
        $encoded = QueryEscaper::encode($input);

        self::assertSame('P5H2hrN6+gpQI4n\\/dXp3p17vtY0=', $encoded);
        self::assertSame($input, QueryEscaper::decode($encoded));
    }
}
