<?php

declare(strict_types=1);

namespace TS3ServerBot\Transport;

use TS3ServerBot\Config\ClientConfig;
use TS3ServerBot\Config\Transport;
use TS3ServerBot\Exception\ConnectionException;
use TS3ServerBot\Exception\InvalidStateException;
use TS3ServerBot\Exception\ProtocolException;
use TS3ServerBot\Exception\TimeoutException;
use TS3ServerBot\Protocol\QueryCommand;
use TS3ServerBot\Protocol\QueryDecoder;
use TS3ServerBot\Protocol\QueryEncoder;
use TS3ServerBot\Protocol\QueryResponse;

/**
 * Encrypted ServerQuery session over SSH (documented default port 10022). Does not use raw/telnet Query.
 *
 * @package TS3ServerBot
 */
final class SshQueryTransport implements TransportInterface
{
    /**
     * @var list<string>
     */
    private array $pendingNotifications = [];

    private bool $connected = false;

    public function __construct(
        private readonly ClientConfig $config,
        private readonly SshSessionInterface $session,
        private readonly QueryEncoder $encoder = new QueryEncoder(),
        private readonly QueryDecoder $decoder = new QueryDecoder(),
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function name(): Transport
    {
        return Transport::Ssh;
    }

    /**
     * {@inheritDoc}
     */
    public function connect(): void
    {
        if ($this->connected) {
            return;
        }

        $this->session->authenticate(
            $this->config->host,
            $this->config->sshPort,
            (string) $this->config->username,
            (string) $this->config->password,
            $this->config->timeoutSeconds,
            $this->config->sshHostFingerprint,
        );

        $this->connected = true;
        $this->readBanner();
    }

    /**
     * {@inheritDoc}
     */
    public function disconnect(): void
    {
        if ($this->connected) {
            try {
                $this->session->write("quit\n");
            } catch (\Throwable) {
                // Best-effort close.
            }
        }

        $this->session->disconnect();
        $this->connected = false;
        $this->pendingNotifications = [];
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
        $this->assertConnected();

        $line = $this->encoder->encode($command);
        $this->session->write($line . "\n");

        return $this->decoder->decode($this->readUntilError($line));
    }

    /**
     * Unsolicited notify lines collected during command reads.
     *
     * The 3.13.8 docs do not define notify field lists. These strings are returned unverified.
     *
     * @return list<string>
     */
    public function drainNotifications(): array
    {
        $pending = $this->pendingNotifications;
        $this->pendingNotifications = [];

        return $pending;
    }

    private function assertConnected(): void
    {
        if (!$this->connected) {
            throw new InvalidStateException('SSH Query transport is not connected.');
        }
    }

    private function readBanner(): void
    {
        $deadline = microtime(true) + $this->config->timeoutSeconds;
        $seen = '';

        while (microtime(true) < $deadline) {
            $remaining = $deadline - microtime(true);
            try {
                $line = $this->session->readLine(max(0.1, $remaining));
            } catch (TimeoutException) {
                break;
            }

            if ($line === null) {
                break;
            }

            $seen .= $line . "\n";
            if (str_contains($seen, 'ServerQuery')) {
                return;
            }
        }

        if (!str_contains($seen, 'TS3') && !str_contains($seen, 'ServerQuery')) {
            throw new ConnectionException('SSH connected but the TeamSpeak ServerQuery banner was not received.');
        }
    }

    private function readUntilError(string $sentCommand): string
    {
        $buffer = '';
        $deadline = microtime(true) + $this->config->timeoutSeconds;

        while (microtime(true) < $deadline) {
            $remaining = $deadline - microtime(true);
            $line = $this->session->readLine(max(0.1, $remaining));
            if ($line === null) {
                break;
            }

            $line = QueryDecoder::stripPromptPrefix($line);
            if ($this->isIgnorableResponseLine($line, $sentCommand)) {
                continue;
            }

            if (str_starts_with($line, 'notify')) {
                $this->pendingNotifications[] = $line;
                continue;
            }

            $buffer .= $line . "\n";
            if (str_starts_with($line, 'error ')) {
                return $buffer;
            }
        }

        if ($buffer === '') {
            throw new TimeoutException('Timed out waiting for a ServerQuery response over SSH.');
        }

        throw new ProtocolException('SSH Query response ended without an error line.');
    }

    private function isIgnorableResponseLine(string $line, string $sentCommand): bool
    {
        if ($line === '') {
            return true;
        }

        if (str_starts_with($line, 'error ') || str_starts_with($line, 'notify')) {
            return false;
        }

        if ($line === $sentCommand) {
            return true;
        }

        $name = explode(' ', $sentCommand, 2)[0];
        if ($line === $name || $line === $name . $name || str_starts_with($line, $name . $name)) {
            return true;
        }

        return str_starts_with($line, $name . ' ');
    }
}
