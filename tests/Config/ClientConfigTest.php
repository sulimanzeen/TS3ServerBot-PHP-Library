<?php

declare(strict_types=1);

namespace TS3ServerBot\Tests\Config;

use PHPUnit\Framework\TestCase;
use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Config\ServerFamily;
use TS3ServerBot\Config\Transport;
use TS3ServerBot\Exception\InvalidParameterException;

final class ClientConfigTest extends TestCase
{
    public function testWebQueryRequiresApiKey(): void
    {
        $this->expectException(InvalidParameterException::class);
        new ClientConfig(host: '127.0.0.1', transport: Transport::WebQuery);
    }

    public function testSshRequiresCredentials(): void
    {
        $this->expectException(InvalidParameterException::class);
        new ClientConfig(host: '127.0.0.1', transport: Transport::Ssh);
    }

    public function testDefaultsDoNotUseRawQueryPort(): void
    {
        $config = new ClientConfig(
            host: '127.0.0.1',
            transport: Transport::WebQuery,
            apiKey: 'placeholder',
        );

        self::assertSame(10022, $config->sshPort);
        self::assertSame(10080, $config->webQueryPort);
        self::assertSame(ServerFamily::TeamSpeak3, $config->serverFamily);
        self::assertNotSame(10011, $config->sshPort);
        self::assertNotSame(10011, $config->webQueryPort);
    }

    public function testDebugInfoRedactsSecrets(): void
    {
        $config = new ClientConfig(
            host: '127.0.0.1',
            transport: Transport::Ssh,
            username: 'serveradmin',
            password: 'secret-password',
        );

        $debug = $config->__debugInfo();
        self::assertSame('***', $debug['password']);
        self::assertSame('serveradmin', $debug['username']);
        self::assertSame('teamspeak3', $debug['serverFamily']);
    }
}
