<?php

declare(strict_types=1);

namespace TS3ServerBot\Tests\Client;

use PHPUnit\Framework\TestCase;
use TS3ServerBot\Client\TeamSpeakClient;
use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Config\ServerFamily;
use TS3ServerBot\Config\Transport;
use TS3ServerBot\Exception\InvalidParameterException;
use TS3ServerBot\Exception\UnsupportedOperationException;
use TS3ServerBot\Transport\HttpClientInterface;
use TS3ServerBot\Transport\HttpResult;

final class TeamSpeak6QueryTest extends TestCase
{
    public function testUnknownCommandIsRejectedOnTeamSpeak3(): void
    {
        $client = $this->webQueryClient(ServerFamily::TeamSpeak3);
        $client->connect();

        $this->expectException(UnsupportedOperationException::class);
        $client->execute('ts6onlycommand');
    }

    public function testUnknownCommandIsSentOnTeamSpeak6(): void
    {
        $http = $this->httpSpy();
        $client = $this->webQueryClient(ServerFamily::TeamSpeak6, $http);
        $client->connect();

        $response = $client->execute('ts6onlycommand', ['clid' => 5]);

        self::assertSame('http://127.0.0.1:10080/1/ts6onlycommand', $http->lastUrl);
        self::assertSame(0, $response->errorId);
        self::assertFalse(str_contains($http->lastUrl, 'api-key='));
    }

    public function testWebQuerySessionCommandIsRejectedOnTeamSpeak6Catalog(): void
    {
        $client = $this->webQueryClient(ServerFamily::TeamSpeak6);
        $client->connect();

        $this->expectException(UnsupportedOperationException::class);
        $client->execute('login', [
            'client_login_name' => 'serveradmin',
            'client_login_password' => 'secret',
        ]);
    }

    public function testBanfindIsSentOnTeamSpeak6WebQuery(): void
    {
        $http = $this->httpSpy();
        $client = $this->webQueryClient(ServerFamily::TeamSpeak6, $http);
        $client->connect();

        $client->server(1)->findBans('1.2.3.4');

        self::assertSame('http://127.0.0.1:10080/1/banfind', $http->lastUrl);
    }

    public function testHomebaselistIsSentOnTeamSpeak6WebQuery(): void
    {
        $http = $this->httpSpy();
        $client = $this->webQueryClient(ServerFamily::TeamSpeak6, $http);
        $client->connect();

        $client->server(1)->listHomebases('abc');

        self::assertSame('http://127.0.0.1:10080/1/homebaselist', $http->lastUrl);
    }

    public function testFindBansRequiresAFilter(): void
    {
        $client = $this->webQueryClient(ServerFamily::TeamSpeak6);
        $client->connect();

        $this->expectException(InvalidParameterException::class);
        $client->server(1)->findBans();
    }

    public function testBanfindIsRejectedOnTeamSpeak3(): void
    {
        $client = $this->webQueryClient(ServerFamily::TeamSpeak3);
        $client->connect();

        $this->expectException(UnsupportedOperationException::class);
        $client->server(1)->findBans('1.2.3.4');
    }

    public function testFileHttpTokenIsRejectedOnTeamSpeak6WebQuery(): void
    {
        $client = $this->webQueryClient(ServerFamily::TeamSpeak6);
        $client->connect();

        $this->expectException(UnsupportedOperationException::class);
        $client->server(1)->getChannelFileHttpToken(1);
    }

    public function testInvalidQueryIdentifierIsRejectedOnTeamSpeak6(): void
    {
        $client = $this->webQueryClient(ServerFamily::TeamSpeak6);
        $client->connect();

        $this->expectException(InvalidParameterException::class);
        $client->execute('../login');
    }

    public function testWebQuerySessionCommandIsRejectedOnTeamSpeak3(): void
    {
        $client = $this->webQueryClient(ServerFamily::TeamSpeak3);
        $client->connect();

        $this->expectException(UnsupportedOperationException::class);
        $client->execute('login', [
            'client_login_name' => 'serveradmin',
            'client_login_password' => 'secret',
        ]);
    }

    private function webQueryClient(ServerFamily $family, ?HttpSpy $http = null): TeamSpeakClient
    {
        $config = new ClientConfig(
            host: '127.0.0.1',
            transport: Transport::WebQuery,
            apiKey: 'placeholder-key',
            virtualServerId: 1,
            serverFamily: $family,
        );

        return TeamSpeakClient::webQuery($config, $http ?? $this->httpSpy());
    }

    private function httpSpy(): HttpSpy
    {
        return new HttpSpy();
    }
}

final class HttpSpy implements HttpClientInterface
{
    public string $lastUrl = '';

    public function post(string $url, array $headers, string $body, float $timeout, bool $verifyTls): HttpResult
    {
        $this->lastUrl = $url;

        return new HttpResult(200, '{"body":[],"status":{"code":0,"message":"ok"}}');
    }
}
