<?php

declare(strict_types=1);

namespace TS3ServerBot\Tests\Protocol;

use PHPUnit\Framework\TestCase;
use TS3ServerBot\Protocol\QueryDecoder;

final class QueryDecoderTest extends TestCase
{
    public function testParsesClientlistExample(): void
    {
        $payload = "clid=5 cid=7 client_database_id=40 client_nickname=ScP client_type=0 client_away=1 client_away_message=not\\shere|clid=6 cid=7 client_nickname=Other\nerror id=0 msg=ok\n";
        $response = (new QueryDecoder())->decode($payload);

        self::assertTrue($response->isOk());
        self::assertCount(2, $response->items);
        self::assertSame('ScP', $response->items[0]->get('client_nickname'));
        self::assertSame('not here', $response->items[0]->get('client_away_message'));
        self::assertSame('6', $response->items[1]->get('clid'));
    }

    public function testTreatsEmptyDatabaseResultAsOk(): void
    {
        $response = (new QueryDecoder())->decode("error id=1281 msg=database\\sempty\\sresult\\sset\n");

        self::assertTrue($response->isOk());
        self::assertSame([], $response->items);
    }

    public function testParsesErrorExtraFields(): void
    {
        $response = (new QueryDecoder())->decode("error id=2568 msg=insufficient\\sclient\\spermissions failed_permid=12\n");

        self::assertFalse($response->isOk());
        self::assertSame(2568, $response->errorId);
        self::assertSame('insufficient client permissions', $response->errorMessage);
        self::assertSame('12', $response->errorExtra['failed_permid']);
    }

    public function testIgnoresQueryPromptLines(): void
    {
        $payload = "serveradmin@9987(1):online>\nclid=5 client_nickname=ScP\nerror id=0 msg=ok\n";
        $response = (new QueryDecoder())->decode($payload);

        self::assertTrue($response->isOk());
        self::assertCount(1, $response->items);
        self::assertSame('ScP', $response->items[0]->get('client_nickname'));
    }

    public function testStripsPromptGluedToPayload(): void
    {
        $payload = "serveradmin@9987(1):online>clid=5 client_nickname=ScP\nerror id=0 msg=ok\n";
        $response = (new QueryDecoder())->decode($payload);

        self::assertCount(1, $response->items);
        self::assertSame('5', $response->items[0]->get('clid'));
        self::assertFalse($response->items[0]->has('serveradmin@9987(1):online>'));
    }

    public function testStripsAnsiCursorCodesFromPayload(): void
    {
        $payload = "serveradmin@9987(1):online>\x1b[29G\x1b[Jclid=5 client_nickname=ScP\nerror id=0 msg=ok\n";
        $response = (new QueryDecoder())->decode($payload);

        self::assertCount(1, $response->items);
        self::assertSame('ScP', $response->items[0]->get('client_nickname'));
    }
}
