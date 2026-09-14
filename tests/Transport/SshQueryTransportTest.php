<?php

declare(strict_types=1);

namespace TS3ServerBot\Tests\Transport;

use PHPUnit\Framework\TestCase;
use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Config\Transport;
use TS3ServerBot\Protocol\QueryCommand;
use TS3ServerBot\Transport\SshQueryTransport;
use TS3ServerBot\Transport\SshSessionInterface;

final class SshQueryTransportTest extends TestCase
{
    public function testExecutesEncodedCommandWithoutPty(): void
    {
        $session = new class implements SshSessionInterface {
            public array $writes = [];
            /** @var list<string> */
            public array $lines = [
                'TS3',
                'Welcome to the TeamSpeak 3 ServerQuery interface, type "help" for a list of commands.',
                'clid=5 client_nickname=ScP',
                'error id=0 msg=ok',
            ];

            public function authenticate(string $host, int $port, string $username, string $password, float $timeout, ?string $hostFingerprint): void
            {
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
        };

        $config = new ClientConfig(
            host: '127.0.0.1',
            transport: Transport::Ssh,
            username: 'serveradmin',
            password: 'secret',
        );
        $transport = new SshQueryTransport($config, $session);
        $transport->connect();
        $response = $transport->execute(new QueryCommand('clientlist'));

        self::assertTrue($response->isOk());
        self::assertSame('clientlist' . "\n", $session->writes[0]);
        self::assertSame('ScP', $response->first()?->get('client_nickname'));
    }

    public function testIgnoresPromptAndCommandEcho(): void
    {
        $session = new class implements SshSessionInterface {
            public array $writes = [];
            /** @var list<string> */
            public array $lines = [
                'TS3',
                'Welcome to the TeamSpeak 3 ServerQuery interface, type "help" for a list of commands.',
                'serveradmin@9987(1):online>',
                'clientlist',
                'clid=5 client_nickname=ScP',
                'error id=0 msg=ok',
            ];

            public function authenticate(string $host, int $port, string $username, string $password, float $timeout, ?string $hostFingerprint): void
            {
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
        };

        $config = new ClientConfig(
            host: '127.0.0.1',
            transport: Transport::Ssh,
            username: 'serveradmin',
            password: 'secret',
        );
        $transport = new SshQueryTransport($config, $session);
        $transport->connect();
        $response = $transport->execute(new QueryCommand('clientlist'));

        self::assertTrue($response->isOk());
        self::assertCount(1, $response->items);
        self::assertSame('ScP', $response->first()?->get('client_nickname'));
    }

    public function testIgnoresAnsiRedrawnCommandEcho(): void
    {
        $session = new class implements SshSessionInterface {
            public array $writes = [];
            /** @var list<string> */
            public array $lines = [
                'TS3',
                'Welcome to the TeamSpeak 3 ServerQuery interface, type "help" for a list of commands.',
                "whoami\x1b[29G\x1b[Jwhoami\x1b[35G",
                'client_nickname=HardTestBot client_id=1',
                'error id=0 msg=ok',
            ];

            public function authenticate(string $host, int $port, string $username, string $password, float $timeout, ?string $hostFingerprint): void
            {
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
        };

        $config = new ClientConfig(
            host: '127.0.0.1',
            transport: Transport::Ssh,
            username: 'serveradmin',
            password: 'secret',
        );
        $transport = new SshQueryTransport($config, $session);
        $transport->connect();
        $response = $transport->execute(new QueryCommand('whoami'));

        self::assertTrue($response->isOk());
        self::assertCount(1, $response->items);
        self::assertSame('HardTestBot', $response->first()?->get('client_nickname'));
    }
}
