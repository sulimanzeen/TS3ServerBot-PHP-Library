<?php

declare(strict_types=1);

namespace TS3ServerBot\Transport;

use TS3ServerBot\Exception\AuthenticationException;
use TS3ServerBot\Exception\ConnectionException;
use TS3ServerBot\Exception\TimeoutException;
use TS3ServerBot\Exception\TransportException;
use phpseclib3\Net\SSH2;

/**
 * phpseclib SSH2 session that never requests a PTY, matching the 3.13.8 changelog guidance for bots.
 *
 * TeamSpeak Query over SSH uses the Query username and password for the SSH handshake. That
 * handshake is Query authentication. A second ServerQuery `login` command is not required.
 *
 * @package TS3ServerBot
 */
final class PhpSecLibSshSession implements SshSessionInterface
{
    private ?SSH2 $ssh = null;

    private string $buffer = '';

    /**
     * {@inheritDoc}
     */
    public function authenticate(
        string $host,
        int $port,
        string $username,
        string $password,
        float $timeout,
        ?string $hostFingerprint
    ): void {
        try {
            $ssh = new SSH2($host, $port, (int) ceil($timeout));
            $ssh->setTimeout($timeout);
            $ssh->setPreferredAlgorithms([
                'hostkey' => $this->preferredHostKeyAlgorithms(),
            ]);
            $ssh->disablePTY();

            if (!$ssh->login($username, $password)) {
                throw new AuthenticationException('SSH authentication to the TeamSpeak Query interface failed.');
            }

            if ($hostFingerprint !== null && $hostFingerprint !== '') {
                $actual = $this->fingerprint($ssh);
                if (!hash_equals(strtolower($hostFingerprint), strtolower($actual))) {
                    $ssh->disconnect();
                    throw new AuthenticationException('SSH host key fingerprint did not match the configured value.');
                }
            }
        } catch (AuthenticationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            if (isset($ssh) && $ssh instanceof SSH2) {
                try {
                    $ssh->disconnect();
                } catch (\Throwable) {
                }
            }

            throw new ConnectionException(
                'SSH connection to the TeamSpeak Query interface failed: ' . $exception->getMessage(),
                0,
                $exception
            );
        }

        $this->ssh = $ssh;
        $this->buffer = '';
    }

    /**
     * {@inheritDoc}
     */
    public function write(string $data): void
    {
        $this->ssh()->write($data);
    }

    /**
     * {@inheritDoc}
     */
    public function readLine(float $timeout): ?string
    {
        $ssh = $this->ssh();
        $ssh->setTimeout($timeout);
        $deadline = microtime(true) + $timeout;

        while (!str_contains($this->buffer, "\n")) {
            if (microtime(true) >= $deadline) {
                throw new TimeoutException('Timed out waiting for a ServerQuery line over SSH.');
            }

            $chunk = $ssh->read('', SSH2::READ_NEXT);
            if ($chunk === false || $chunk === '') {
                if ($this->buffer === '') {
                    return null;
                }
                break;
            }

            $this->buffer .= str_replace("\r\n", "\n", (string) $chunk);
            $this->sanitizeIncoming();
        }

        $this->sanitizeIncoming();
        $offset = strpos($this->buffer, "\n");
        if ($offset === false) {
            $line = $this->buffer;
            $this->buffer = '';

            return $line === '' ? null : $line;
        }

        $line = substr($this->buffer, 0, $offset);
        $this->buffer = substr($this->buffer, $offset + 1);

        return $line;
    }

    /**
     * {@inheritDoc}
     */
    public function disconnect(): void
    {
        if ($this->ssh !== null) {
            $this->ssh->disconnect();
            $this->ssh = null;
        }
        $this->buffer = '';
    }

    private function ssh(): SSH2
    {
        if ($this->ssh === null) {
            throw new ConnectionException('SSH session is not connected.');
        }

        return $this->ssh;
    }

    /**
     * Prefer rsa-sha2-512 first. TeamSpeak 3.13.8 signs RSA host keys with that algorithm; phpseclib
     * otherwise negotiates rsa-sha2-256 and throws a host-key mismatch.
     *
     * @return list<string>
     */
    private function preferredHostKeyAlgorithms(): array
    {
        return array_values(array_unique(array_merge(
            ['rsa-sha2-512', 'rsa-sha2-256'],
            SSH2::getSupportedHostKeyAlgorithms(),
        )));
    }

    /**
     * ServerQuery over SSH still emits ANSI cursor codes and a prompt without a newline.
     * Strip both so they cannot glue onto the next payload line.
     */
    private function sanitizeIncoming(): void
    {
        $this->buffer = preg_replace('/\x1b\[[0-9;?]*[ -\/]*[@-~]/', '', $this->buffer) ?? $this->buffer;
        $this->buffer = preg_replace('/\x1b[@-Z\\\\-_]/', '', $this->buffer) ?? $this->buffer;
        $this->buffer = str_replace(["\x07", "\r"], '', $this->buffer);
        $this->buffer = preg_replace(
            '/[^\n]*@[0-9]+\([0-9]+\):(online|offline|virtual)>/i',
            '',
            $this->buffer
        ) ?? $this->buffer;
    }

    private function fingerprint(SSH2 $ssh): string
    {
        $key = $ssh->getServerPublicHostKey();
        if (!is_string($key) || $key === '') {
            throw new TransportException('Unable to read the SSH host public key.');
        }

        return hash('sha256', $key);
    }
}
