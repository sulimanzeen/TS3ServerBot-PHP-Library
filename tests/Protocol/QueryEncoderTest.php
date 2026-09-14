<?php

declare(strict_types=1);

namespace TS3ServerBot\Tests\Protocol;

use PHPUnit\Framework\TestCase;
use TS3ServerBot\Protocol\QueryCommand;
use TS3ServerBot\Protocol\QueryEncoder;

final class QueryEncoderTest extends TestCase
{
    public function testEncodesClientlistWithOptions(): void
    {
        $encoder = new QueryEncoder();
        $line = $encoder->encode(new QueryCommand('clientlist', [], ['uid', 'away']));

        self::assertSame('clientlist -uid -away', $line);
    }

    public function testEncodesKickWithPipeSeparatedIds(): void
    {
        $encoder = new QueryEncoder();
        $line = $encoder->encode(new QueryCommand('clientkick', [
            'reasonid' => 4,
            'reasonmsg' => 'Go away!',
            'clid' => [5, 6],
        ]));

        self::assertSame('clientkick reasonid=4 reasonmsg=Go\\saway! clid=5|clid=6', $line);
    }

    public function testRejectsNonIdentifierCommandNames(): void
    {
        $this->expectException(\TS3ServerBot\Exception\InvalidParameterException::class);
        new QueryCommand('../login');
    }
}
