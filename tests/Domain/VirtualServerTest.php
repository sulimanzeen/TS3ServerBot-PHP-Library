<?php

declare(strict_types=1);

namespace TS3ServerBot\Tests\Domain;

use PHPUnit\Framework\TestCase;
use TS3ServerBot\Client\TeamSpeakClient;
use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Config\Transport;
use TS3ServerBot\Exception\UnsupportedOperationException;
use TS3ServerBot\Transport\HttpClientInterface;
use TS3ServerBot\Transport\HttpResult;

final class VirtualServerTest extends TestCase
{
    public function testGetClientsUsesWebQueryJson(): void
    {
        $http = new class implements HttpClientInterface {
            public string $lastUrl = '';
            public array $lastHeaders = [];

            public function post(string $url, array $headers, string $body, float $timeout, bool $verifyTls): HttpResult
            {
                $this->lastUrl = $url;
                $this->lastHeaders = $headers;

                return new HttpResult(200, json_encode([
                    'body' => [
                        ['clid' => '5', 'client_nickname' => 'ScP'],
                    ],
                    'status' => ['code' => 0, 'message' => 'ok'],
                ], JSON_THROW_ON_ERROR));
            }
        };

        $config = new ClientConfig(
            host: '127.0.0.1',
            transport: Transport::WebQuery,
            apiKey: 'placeholder-key',
            virtualServerId: 1,
        );
        $client = TeamSpeakClient::webQuery($config, $http);
        $client->connect();
        $clients = $client->server(1)->getClients(['uid']);

        self::assertSame('http://127.0.0.1:10080/1/clientlist', $http->lastUrl);
        self::assertSame('placeholder-key', $http->lastHeaders['x-api-key']);
        self::assertFalse(str_contains($http->lastUrl, 'api-key='));
        self::assertSame('ScP', $clients[0]->get('client_nickname'));
    }

    public function testLoginIsRejectedOnWebQuery(): void
    {
        $http = new class implements HttpClientInterface {
            public function post(string $url, array $headers, string $body, float $timeout, bool $verifyTls): HttpResult
            {
                return new HttpResult(200, '{"body":[],"status":{"code":0,"message":"ok"}}');
            }
        };

        $config = new ClientConfig(
            host: '127.0.0.1',
            transport: Transport::WebQuery,
            apiKey: 'placeholder-key',
        );
        $client = TeamSpeakClient::webQuery($config, $http);
        $client->connect();

        $this->expectException(UnsupportedOperationException::class);
        $client->login('serveradmin', 'secret');
    }

    public function testExecuteRejectsUndocumentedOptions(): void
    {
        $http = new class implements HttpClientInterface {
            public function post(string $url, array $headers, string $body, float $timeout, bool $verifyTls): HttpResult
            {
                return new HttpResult(200, '{"body":[],"status":{"code":0,"message":"ok"}}');
            }
        };

        $config = new ClientConfig(
            host: '127.0.0.1',
            transport: Transport::WebQuery,
            apiKey: 'placeholder-key',
            virtualServerId: 1,
        );
        $client = TeamSpeakClient::webQuery($config, $http);
        $client->connect();

        $this->expectException(\TS3ServerBot\Exception\InvalidParameterException::class);
        $client->execute('clientlist', [], ['notarealoption']);
    }
}
