<?php

declare(strict_types=1);

namespace TS3ServerBot\Config;

use TS3ServerBot\Exception\InvalidParameterException;
use TS3ServerBot\Support\SecretRedactor;

/**
 * Immutable connection settings for TS3ServerBot Query.
 *
 * There is no raw/telnet Query port option. SSH defaults to 10022 and WebQuery to 10080
 * as documented for TeamSpeak 3.13.8 Query. TeamSpeak 6 uses the same SSH and WebQuery
 * transports; set {@see ServerFamily::TeamSpeak6} when connecting to a TS6 server.
 *
 * @package TS3ServerBot
 */
final class ClientConfig
{
    public const DEFAULT_SSH_PORT = 10022;

    public const DEFAULT_WEBQUERY_PORT = 10080;

    /**
     * @param string $host Hostname or IP of the TeamSpeak server.
     * @param Transport $transport SSH or WebQuery only.
     * @param int $sshPort SSH ServerQuery port. Documented default is 10022.
     * @param int $webQueryPort WebQuery HTTP port. Documented default is 10080.
     * @param bool $webQueryTls When true, WebQuery uses https:// (typically a reverse proxy). Native TeamSpeak HTTPS on 10443 is not claimed.
     * @param float $timeoutSeconds Socket and HTTP timeout.
     * @param string|null $username ServerQuery username. For SSH this is the SSH login; the handshake is Query authentication. Never logged.
     * @param string|null $password ServerQuery password. For SSH this is the SSH password. Never logged.
     * @param string|null $apiKey WebQuery API key. Never logged. Sent as header x-api-key.
     * @param string|null $nickname Optional Query nickname used with the documented `use` client_nickname parameter.
     * @param int|null $virtualServerId Default virtual server id for WebQuery paths and SSH `use`.
     * @param int|null $virtualServerPort Default virtual server voice port for `/byport/` or `use port=`.
     * @param bool $verifyTls Verify TLS certificates for WebQuery HTTPS.
     * @param string|null $sshHostFingerprint Optional SHA256 fingerprint of the SSH host key. Connection fails on mismatch when set.
     * @param ServerFamily $serverFamily TeamSpeak 3 uses the 3.13.8 catalog. TeamSpeak 6 uses the 6.0.0-beta12.1 catalog; unknown names are still sent.
     */
    public function __construct(
        public readonly string $host,
        public readonly Transport $transport,
        public readonly int $sshPort = self::DEFAULT_SSH_PORT,
        public readonly int $webQueryPort = self::DEFAULT_WEBQUERY_PORT,
        public readonly bool $webQueryTls = false,
        public readonly float $timeoutSeconds = 10.0,
        public readonly ?string $username = null,
        public readonly ?string $password = null,
        public readonly ?string $apiKey = null,
        public readonly ?string $nickname = null,
        public readonly ?int $virtualServerId = null,
        public readonly ?int $virtualServerPort = null,
        public readonly bool $verifyTls = true,
        public readonly ?string $sshHostFingerprint = null,
        public readonly ServerFamily $serverFamily = ServerFamily::TeamSpeak3,
    ) {
        if ($this->host === '') {
            throw new InvalidParameterException('Host must not be empty.');
        }

        if ($this->sshPort < 1 || $this->sshPort > 65535) {
            throw new InvalidParameterException('SSH port must be between 1 and 65535.');
        }

        if ($this->webQueryPort < 1 || $this->webQueryPort > 65535) {
            throw new InvalidParameterException('WebQuery port must be between 1 and 65535.');
        }

        if ($this->timeoutSeconds <= 0) {
            throw new InvalidParameterException('Timeout must be greater than zero.');
        }

        if ($this->transport === Transport::WebQuery && ($this->apiKey === null || $this->apiKey === '')) {
            throw new InvalidParameterException('WebQuery requires an API key.');
        }

        if ($this->transport === Transport::Ssh && ($this->username === null || $this->username === '' || $this->password === null || $this->password === '')) {
            throw new InvalidParameterException('SSH Query requires a username and password to establish the encrypted session.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return SecretRedactor::redactArray([
            'host' => $this->host,
            'transport' => $this->transport->value,
            'sshPort' => $this->sshPort,
            'webQueryPort' => $this->webQueryPort,
            'webQueryTls' => $this->webQueryTls,
            'timeoutSeconds' => $this->timeoutSeconds,
            'username' => $this->username,
            'password' => $this->password,
            'apiKey' => $this->apiKey,
            'nickname' => $this->nickname,
            'virtualServerId' => $this->virtualServerId,
            'virtualServerPort' => $this->virtualServerPort,
            'verifyTls' => $this->verifyTls,
            'sshHostFingerprint' => $this->sshHostFingerprint,
            'serverFamily' => $this->serverFamily->value,
        ]);
    }
}
