<?php

declare(strict_types=1);

namespace TS3ServerBot\Tests\Protocol;

use PHPUnit\Framework\TestCase;
use TS3ServerBot\Config\ServerFamily;
use TS3ServerBot\Exception\UnsupportedOperationException;
use TS3ServerBot\Protocol\CommandCatalog;

final class CommandCatalogTest extends TestCase
{
    public function testLoadsDocumentedCommands(): void
    {
        $catalog = new CommandCatalog();

        self::assertTrue($catalog->has('clientlist'));
        self::assertTrue($catalog->get('clientlist')->webQuery);
        self::assertFalse($catalog->get('login')->webQuery);
        self::assertTrue($catalog->get('login')->sshOnly);
        self::assertFalse($catalog->get('servernotifyregister')->webQuery);
        self::assertTrue($catalog->get('ftinitupload')->sshOnly);
        self::assertSame('privilegekeyadd', $catalog->get('tokenadd')->aliasOf);
        self::assertGreaterThanOrEqual(139, count($catalog->all()));
    }

    public function testRejectsUnknownCommands(): void
    {
        $this->expectException(UnsupportedOperationException::class);
        (new CommandCatalog())->get('not_a_real_command');
    }

    public function testFindReturnsNullForUnknownCommands(): void
    {
        self::assertNull((new CommandCatalog())->find('not_a_real_command'));
        self::assertSame('clientlist', (new CommandCatalog())->find('clientlist')?->name);
    }

    public function testTeamSpeak6CatalogContainsOnlyCommands(): void
    {
        $catalog = CommandCatalog::forFamily(ServerFamily::TeamSpeak6);

        self::assertSame(ServerFamily::TeamSpeak6, $catalog->family());
        self::assertTrue($catalog->has('banfind'));
        self::assertTrue($catalog->has('authenticationtoken'));
        self::assertTrue($catalog->has('homebaselist'));
        self::assertTrue($catalog->get('ftgetchannelfilehttptoken')->sshOnly);
        self::assertFalse($catalog->get('ftgetchannelfilehttptoken')->webQuery);
        self::assertTrue($catalog->get('banfind')->webQuery);
        self::assertContains('homebaseonly', $catalog->get('clientdblist')->options);
        self::assertGreaterThanOrEqual(149, count($catalog->all()));
        self::assertFalse($catalog->has('not_a_real_command'));
    }
}
