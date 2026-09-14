<?php

declare(strict_types=1);

namespace TS3ServerBot\Tests\Client;

use PHPUnit\Framework\TestCase;
use TS3ServerBot\Client\TeamSpeakClient;
use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Config\Transport;
use TS3ServerBot\Connection\ConnectionState;
use TS3ServerBot\Transport\SshSessionInterface;

final class TeamSpeakClientSshTest extends TestCase
{
    public function testConnectIsAuthenticatedWithoutQueryLogin(): void
    {
        $session = new RecordingSshSession([
            'TS3',
            'Welcome to the TeamSpeak 3 ServerQuery interface, type "help" for a list of commands.',
            'error id=0 msg=ok',
            'client_nickname=HardTestBot client_id=1',
            'error id=0 msg=ok',
        ]);

        $client = TeamSpeakClient::ssh(new ClientConfig(
            host: '127.0.0.1',
            transport: Transport::Ssh,
            username: 'serveradmin',
            password: 'secret',
            nickname: 'HardTestBot',
            virtualServerId: 1,
        ), $session);

        $client->connect();

        self::assertSame(ConnectionState::Authenticated, $client->getState());
        self::assertTrue($session->authenticated);
        self::assertSame('HardTestBot', $client->whoami()->get('client_nickname'));
        self::assertSame('use sid=1 client_nickname=HardTestBot' . "\n", $session->writes[0]);
        self::assertSame('whoami' . "\n", $session->writes[1]);
    }

    public function testConnectWithoutVirtualServerDoesNotSendUse(): void
    {
        $session = new RecordingSshSession([
            'TS3',
            'Welcome to the TeamSpeak 3 ServerQuery interface, type "help" for a list of commands.',
            'version=3.13.8',
            'error id=0 msg=ok',
        ]);

        $client = TeamSpeakClient::ssh(new ClientConfig(
            host: '127.0.0.1',
            transport: Transport::Ssh,
            username: 'serveradmin',
            password: 'secret',
        ), $session);

        $client->connect();
        $client->version();

        self::assertSame(['version' . "\n"], $session->writes);
    }
}

final class RecordingSshSession implements SshSessionInterface
{
    public bool $authenticated = false;

    /** @var list<string> */
    public array $writes = [];

    /**
     * @param list<string> $lines
     */
    public function __construct(private array $lines)
    {
    }

    public function authenticate(string $host, int $port, string $username, string $password, float $timeout, ?string $hostFingerprint): void
    {
        $this->authenticated = true;
    }

    public function write(string $data): void
    {
        $this->writes[] = $data;
    }

    public function readLine(float $timeout): ?string
    {
        if ($this->lines === []) {
            return null;
        }

        return array_shift($this->lines);
    }

    public function disconnect(): void
    {
    }
}
