<?php

declare(strict_types=1);

namespace TS3ServerBot\Tests\Protocol;

use PHPUnit\Framework\TestCase;
use TS3ServerBot\Protocol\QueryCommand;
use TS3ServerBot\Protocol\WebQueryMapper;

final class WebQueryMapperTest extends TestCase
{
    public function testBuildsServerPathAndFlagBody(): void
    {
        $mapper = new WebQueryMapper();
        $command = new QueryCommand('channellist', [], ['topic', 'icon'], 1);

        self::assertSame('/1/channellist', $mapper->path($command));
        self::assertSame([
            '-topic' => '',
            '-icon' => '',
        ], $mapper->toBody($command));
    }

    public function testBuildsByPortPath(): void
    {
        $mapper = new WebQueryMapper();
        $command = new QueryCommand('clientlist', [], [], null, 9987);

        self::assertSame('/byport/9987/clientlist', $mapper->path($command));
    }

    public function testParsesDocumentedJsonExample(): void
    {
        $json = [
            'body' => [
                [
                    'client_nickname' => 'serveradmin',
                    'virtualserver_id' => '1',
                ],
            ],
            'status' => [
                'code' => 0,
                'message' => 'ok',
            ],
        ];

        $response = (new WebQueryMapper())->fromJson($json);

        self::assertTrue($response->isOk());
        self::assertSame('serveradmin', $response->first()?->get('client_nickname'));
    }
}
