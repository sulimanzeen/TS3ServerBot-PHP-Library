<?php

declare(strict_types=1);

namespace TS3ServerBot\Transport;

use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Config\Transport;
use TS3ServerBot\Exception\AuthenticationException;
use TS3ServerBot\Exception\InvalidStateException;
use TS3ServerBot\Exception\ProtocolException;
use TS3ServerBot\Exception\TransportException;
use TS3ServerBot\Protocol\QueryCommand;
use TS3ServerBot\Protocol\QueryResponse;
use TS3ServerBot\Protocol\WebQueryMapper;

/**
 * WebQuery HTTP/JSON transport (documented default port 10080 for TeamSpeak 3.13.8 Query).
 *
 * TeamSpeak 6 uses the same HTTP JSON client. Paths follow the documented `/{sid}/{command}` pattern.
 *
 * @package TS3ServerBot
 */
final class WebQueryTransport implements TransportInterface
{
    private bool $connected = false;

    public function __construct(
        private readonly ClientConfig $config,
        private readonly HttpClientInterface $http,
        private readonly WebQueryMapper $mapper = new WebQueryMapper(),
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function name(): Transport
    {
        return Transport::WebQuery;
    }

    /**
     * {@inheritDoc}
     */
    public function connect(): void
    {
        $this->connected = true;
    }

    /**
     * {@inheritDoc}
     */
    public function disconnect(): void
    {
        $this->connected = false;
    }

    /**
     * {@inheritDoc}
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(QueryCommand $command): QueryResponse
    {
        if (!$this->connected) {
            throw new InvalidStateException('WebQuery transport is not connected.');
        }

        $scheme = $this->config->webQueryTls ? 'https' : 'http';
        $url = $scheme . '://' . $this->config->host . ':' . $this->config->webQueryPort . $this->mapper->path($command);
        $body = json_encode($this->mapper->toBody($command), JSON_THROW_ON_ERROR);

        $result = $this->http->post(
            $url,
            [
                'Content-Type' => 'application/json',
                'x-api-key' => (string) $this->config->apiKey,
            ],
            $body,
            $this->config->timeoutSeconds,
            $this->config->verifyTls,
        );

        if ($result->statusCode === 401 || $result->statusCode === 403) {
            throw new AuthenticationException('WebQuery rejected the API key.');
        }

        if ($result->statusCode >= 400 && $result->body === '') {
            throw new TransportException('WebQuery HTTP request was rejected.');
        }

        try {
            $json = json_decode($result->body, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new ProtocolException('WebQuery returned non-JSON output.', 0, $exception);
        }

        if (!is_array($json)) {
            throw new ProtocolException('WebQuery JSON root must be an object.');
        }

        return $this->mapper->fromJson($json);
    }
}
